<?php
namespace Komodo\Interlace\Repositories;

/*
|-----------------------------------------------------------------------------
| Komodo Interlace
|-----------------------------------------------------------------------------
|
| Desenvolvido por: Jhonnata Paixão (Líder de Projeto)
| Iniciado em: 08/2023
| Arquivo: RemoteRepository.php
| Data da Criação Sat Aug 19 2023
| Copyright (c) 2023
|
|-----------------------------------------------------------------------------
|*/

use Exception;
use Komodo\Interlace\Adapter\Operator;
use Komodo\Interlace\Association;
use Komodo\Interlace\Entity;
use Komodo\Interlace\Enums\Op;
use Komodo\Interlace\Interfaces\Connection;
use Komodo\Interlace\Interfaces\RemoteConnection;
use Komodo\Interlace\Interfaces\Repository;
use Komodo\Interlace\QueryBuilder\QueryBuilder;
use Komodo\Logger\Logger;
use ReflectionClass;
use ReflectionProperty;

/**
 * @template TEntity of Entity
 * @implements Repository<TEntity>
 * @phpstan-type ConditionOperator array<Op,string|int>
 * @phpstan-type Where array<string,string|int|ConditionOperator>
 * @phpstan-type Select array<string,Op>
 * @phpstan-type Assoc array<string,RepositoryParams>
 *
 * @phpstan-type RepositoryParams array{where?: Where, select?: Select, associations?: Assoc }
 */
abstract class RemoteRepository implements Repository
{
    /**
     * $logger
     *
     * @var Logger
     */
    protected $logger;

    /**
     * $connection
     *
     * @var Connection
     */
    protected $connection;

    /**
     * $entity
     *
     * @var class-string<\Komodo\Interlace\Entity|TEntity>
     */
    protected $entity;

    /**
     * @var string
     */
    protected $tablename;

    /**
     * @var bool
     */
    protected $timestamp = true;

    private function __construct()
    {
        $this->init();
    }

    /**
     * Carregar repositório
     *
     * @param Connection $connectionAdapter
     * @param Logger|null $logger
     *
     * @return $this
     */
    public static function load()
    {
        return new static();
    }

    protected function setup()
    {
        return [  ];
    }

    protected function init()
    {
        $setup = $this->setup();

        $thisClassName = (new ReflectionClass($this))->getShortName();

        $this->logger = isset($setup[ 'logger' ]) && $setup[ 'logger' ] instanceof Logger ? clone $setup[ 'logger' ] : new Logger;
        $this->tablename = isset($setup[ 'entity' ]) ? $setup[ 'entity' ] : strtolower($thisClassName) . 's';
        $this->tablename = isset($setup[ 'entity' ]) ? $setup[ 'entity' ] : strtolower($thisClassName) . 's';
        $this->timestamp = isset($setup[ 'timestamp' ]) ? $setup[ 'timestamp' ] : true;
        $this->logger->register(static::class);
        // !Required
        if (!isset($setup[ 'connection' ])) {
            throw new Exception("No connection reported for this entity: $thisClassName");
        } elseif (!$setup[ 'connection' ] instanceof RemoteConnection) {
            throw new Exception("The specified connection object is not compatible with this repository: Komodo\Interlace\RepositoriesRemoteReporitory");
        }

        if (!isset($setup[ 'entityClass' ])) {
            throw new Exception("No entity class was specified for: $thisClassName");
        }

        if (!is_subclass_of($setup[ 'entityClass' ], Entity::class)) {
            throw new Exception("Specified entity class is not compatible with type: Komodo\Interlace\Entity");
        }

        $this->connection = $setup[ 'connection' ];
        $this->entity = $setup[ 'entityClass' ];
    }

    public function getAll()
    {
        try {
            /**
             * @var \Komodo\Interlace\Entity|TEntity
             */
            $m = new $this->entity;

            $builder = new QueryBuilder($this->tablename);
            $builder->select((array) $m->getProps())->from();

            if (!$r = $this->connection->fetchAll($builder->mount())) {
                return [  ];
            };

            return $this->sqlMapEntity($r);
        } catch (\Throwable $th) {
            $this->logger->error($th->getMessage());
            return [  ];
        }
    }

    /** @param RepositoryParams $params */
    public function findAll($params)
    {
        try {

            /**
             * @var \Komodo\Interlace\Entity|TEntity
             */
            $m = new $this->entity;
            [ $query, $operator ] = $this->getQueryOperator($params);

            $r = $this->connection->fetchAll($query);

            if (!$r) {
                return [  ];
            };

            return $this->filterMultiSQLData($m, $r, $operator->getAssociations());
        } catch (\Throwable $th) {
            $this->logger->error($th->getMessage());
            return [  ];
        }
    }
    public function findByPk($pk)
    {
        if (!$pk) {
            return null;
        }

        try {
            $m = new $this->entity;
            $builder = new QueryBuilder($this->tablename);
            $builder->select((array) $m->getProps())->from()->where('id')->equal($pk)->limit(1);

            $r = $this->connection->fetch($builder->mount());

            if (!$r) {
                return null;
            };

            return new $m($r);
        } catch (\Throwable $th) {
            $this->logger->error($th->getMessage());
            throw $th;
        }
    }
    public function findOne($params)
    {
        try {
            /**
             * @var \Komodo\Interlace\Entity|TEntity
             */
            $m = new $this->entity;

            [ $query, $operator ] = $this->getQueryOperator($params);

            $r = $this->connection->fetchAll($query);

            if (!$r) {
                return null;
            };
            $data = $this->filterSQLData($m, $r, $operator->getAssociations());
            return new $m($data[ 0 ], $data[ 1 ]);
        } catch (\Throwable $th) {
            $this->logger->error($th->getMessage());
            return null;
        }
    }

    private function getQueryOperator($params)
    {
        $operator = Operator::get($this->entity);
        $query = $operator->mountQuery($this->tablename, $params);
        return [ $query, $operator ];
    }

    public function persist(&$entity)
    {
        if ($entity->id) {
            return false;
        }
        $refl = new ReflectionClass($entity);
        $bindValues = [  ];
        $sets = [  ];

        foreach ($entity->getProps() as $prop) {
            if (in_array($prop, [ 'updated_at', 'created_at', 'id' ])) {
                continue;
            }
            $reflProp = $refl->getProperty($prop);

            if ($reflProp instanceof ReflectionProperty) {
                #Definir o valor à ser salvo
                $v = $reflProp->getValue($entity);

                #Keys para vincular
                $bindValues[ ":$prop" ] = $v;
                $sets[ $prop ] = ":$prop";
            }
        }

        try {
            $builder = new QueryBuilder($this->tablename);
            $builder->insert(array_keys($sets))->values($sets);

            $r = $this->connection->execute($builder->mount(), $bindValues);

            if ($r) {
                $entity->id = $this->connection->lastInsertId();
                return $entity->id;
            }

            return $r;
        } catch (\Throwable $th) {
            $this->logger->error($th->getMessage());
            return false;
            // throw new ResponseError($th->getMessage(), HTTPResponseCode::ITERNALERRO);
        }
    }
    public function update(&$entity)
    {
        $refl = new ReflectionClass($entity);
        $bindValues = [  ];
        $sets = [  ];
        foreach ($entity->getProps() as $prop) {
            if (in_array($prop, [ 'updated_at', 'created_at', 'id' ])) {
                continue;
            }

            $reflProp = $refl->getProperty($prop);

            if ($reflProp instanceof ReflectionProperty) {
                #Definir o valor à ser salvo
                $v = $reflProp->getValue($entity);

                #Keys para vincular
                $bindValues[ ":$prop" ] = $v;
                $sets[ $prop ] = ":$prop";
            }
        }
        try {
            $builder = new QueryBuilder($this->tablename);
            $builder->update()->set($sets)->where('id')->equal($entity->id);
            $r = $this->connection->execute($builder->mount(), $bindValues);

            return $r;
        } catch (\Throwable $th) {
            $this->logger->error($th->getMessage());
            throw $th;
        }
    }
    public function count($params)
    {
        try {
            /**
             * @var \Komodo\Interlace\Entity|TEntity
             */
            $m = new $this->entity;

            $operator = Operator::get($this->entity);
            $params[ 'select' ] = [
                'id' => Op::count,
             ];
            $query = $operator->mountQuery($this->tablename, $params);
            $r = $this->connection->fetchColumm($query);
            return intval($r);
        } catch (\Throwable $th) {
            $this->logger->error($th->getMessage());
            throw $th;
        }
    }
    public function delete(&$entity)
    {
        try {
            $builder = new QueryBuilder($this->tablename);
            $builder->delete()->where('id')->equal($entity->id);

            $r = $this->connection->execute($builder->mount());

            $refl = new ReflectionClass($entity);
            foreach ($entity->getProps() as $prop) {
                $property = $refl->getProperty($prop);
                if ($property instanceof ReflectionProperty) {
                    $property->setValue($entity, null);
                }
            }
            return $r;
        } catch (\Throwable $th) {
            $this->logger->error($th->getMessage());
            throw $th;
        }
    }
    public function deleteAll($params)
    {
        try {
            /**
             * @var \Komodo\Interlace\Entity|TEntity
             */
            $m = new $this->entity;

            [ $query, $operator ] = $this->getQueryOperator($params);
            $builder = new QueryBuilder($this->tablename);
            $builder->delete();

            $this->connection->fetchAll($builder->mount() . " " . $query);

            return true;
        } catch (\Throwable $th) {
            $this->logger->error($th->getMessage());
            throw new $th;
        }
    }

    // #Private Methods
    /**
     * @param array $data
     *
     * @return Entity[] | $this[]
     */
    private function sqlMapEntity($data, $associations = [  ])
    {

        return array_map(function ($var) use ($associations) {
            $child = $this->entity;
            $f = $this->filterSQLData(new $child, [ $var ], $associations);
            $model = new $child($f[ 0 ], $f[ 1 ]);
            $associations = $this->findAssociationData(new $child, $var, $associations);
            return new $child($var, $associations);
        }, $data);
    }

    /**
     * Separa dados do modelo principal e das associações em um resultado
     * vindo de uma mesma query (JOIN)
     *
     * @param Entity $model
     * @param array $sqldata
     * @param array $associations
     *
     * @return array<array<string,string>,Entity[]>
     */
    private function filterSQLData($model, $sqldata, $associations = [  ])
    {
        $a = [  ];
        $assoc = [  ];
        if (!$associations) {
            return [ $sqldata[ 0 ], [  ] ];
        }

        /**
         * @var  Association[]
         */
        $associates = $model->getAssociations();

        foreach ($associations as $association => $conditions) {
            if (!isset($associates[ $association ])) {
                continue;
            }

            // #se vazio, inicializa
            if (!isset($assoc[ $association ])) {
                $assoc[ $association ] = [  ];
            }
            $associationGroup =  &$assoc[ $association ];

            // #processa
            foreach ($sqldata as $row) {
                $d = array_filter($row, function ($v, $k) use ($association) {
                    return str_starts_with($k, $association);
                }, ARRAY_FILTER_USE_BOTH);

                $d = str_replace($association . ':', '', json_encode($d));
                $d = json_decode($d, true);

                // #ja está includído
                if (isset($associationGroup[ $d[ 'id' ] ])) {
                    continue;
                }

                $associate = $associates[ $association ];
                $class = $associate->getModelClassname();
                if (array_filter($d)) {
                    $associationGroup[ $d[ 'id' ] ] = new $class($d);
                }
            }
        }
        $a = array_filter($sqldata[ 0 ], function ($v, $k) use ($association) {
            return !strpos($k, ":") !== false;
        }, ARRAY_FILTER_USE_BOTH);

        return [ $a, $assoc ];
    }

    /**
     * @param Entity $model
     * @param array $sqldata
     * @param array $associations
     *
     * @return Entity[]
     */
    private function findAssociationData($model, $sqldata, $associations)
    {
        $a = [  ];
        if (!$associations) {
            return $a;
        }

        /**
         * @var  Association[]
         */
        $associates = $model->getAssociations();

        foreach ($associations as $name => $selected) {
            if (is_string($name)) {
                $key = $name;
                $data = $selected;
            } else {
                $key = $selected;
                $data = [  ];
            }

            if (!array_key_exists($key, $associates)) {
                continue;
            }

            $associate = $associates[ $key ];
            $model = $associate->getModel();

            if (array_key_exists("where", $data)) {
                $data[ 'where' ] = [  ];
            }

            // #This condition guarantees the authenticity of the association
            $data[ 'where' ][ $associate->getForeingkey() ] = $sqldata[ $associate->getOringinKey() ];

            $a[ $key ] = $model->findAll($data);
        }

        return $a;
    }

    /**
     * Separa dados do modelo principal e das associações em um resultado
     * vindo de uma mesma query (JOIN)
     *
     * @param Entity $model
     * @param array $sqldata
     * @param array $associations
     *
     * @return Entity[]
     */
    private static function filterMultiSQLData($model, $sqldata, $associations = [  ])
    {
        $r = [  ];
        $assoc = [  ];
        $child = get_called_class();
        /**
         * @var  Association[]
         */
        $associates = $model->getAssociations();

        foreach ($sqldata as $row) {
            $a = array_filter($row, function ($v, $k) {
                return !strpos($k, ":") !== false;
            }, ARRAY_FILTER_USE_BOTH);

            if (array_filter($a) || !isset($associationGroup[ $a[ 'id' ] ])) {
                $r[ $a[ 'id' ] ] = $a;
            }

            if (!isset($assoc[ $a[ 'id' ] ])) {
                $assoc[ $a[ 'id' ] ] = [  ];
            }
            foreach ($associations as $association => $conditions) {
                if (!isset($associates[ $association ])) {
                    continue;
                }

                $modelGroup =  &$assoc[ $a[ 'id' ] ];

                if (!isset($modelGroup[ $association ])) {
                    $modelGroup[ $association ] = [  ];
                }
                $associationGroup =  &$modelGroup[ $association ];

                $d = array_filter($row, function ($v, $k) use ($association) {
                    return str_starts_with($k, $association);
                }, ARRAY_FILTER_USE_BOTH);

                $d = str_replace($association . ':', '', json_encode($d));
                $d = json_decode($d, true);

                // #ja está includído
                if (isset($associationGroup[ $d[ 'id' ] ])) {
                    continue;
                }

                $associate = $associates[ $association ];
                $class = $associate->getModelClassname();
                if (array_filter($d)) {
                    $associationGroup[ $d[ 'id' ] ] = new $class($d);
                }
            }
        }
        $r = array_map(function ($var) use ($assoc, $child) {
            return new $child($var, $assoc[ $var[ 'id' ] ]);
        }, $r);
        return array_values($r);
    }
}
