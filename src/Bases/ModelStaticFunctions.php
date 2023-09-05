<?php
namespace Komodo\Interlace\Bases;

/*
|-----------------------------------------------------------------------------
| Komodo Interlace
|-----------------------------------------------------------------------------
|
| Desenvolvido por: Jhonnata Paixão (Líder de Projeto)
| Iniciado em: 08/2023
| Arquivo: ModelStaticFunctions.php
| Data da Criação Sun Aug 20 2023
| Copyright (c) 2023
|
|-----------------------------------------------------------------------------
|*/

use Error;
use Komodo\Interlace\Adapter\Operator\OperatorResolver;
use Komodo\Interlace\Association;
use Komodo\Interlace\Enums\Op;
use Komodo\Interlace\Interfaces\Connection;
use Komodo\Interlace\Model;
use Komodo\Interlace\QueryBuilder\QueryBuilder;
use Komodo\Logger\Logger;
use Throwable;

trait ModelStaticFunctions
{
    /** @var Logger */
    private static $staticLogger;

    public static function setLogger(Logger $logger)
    {
        self::$staticLogger = $logger;
    }
    /**
     * @param string|int $pk
     *
     * @return $this|null
     */
    public static function findByPk($pk)
    {
        if (!$pk) {
            return null;
        }

        try {
            $m = get_called_class();
            $m = new $m;

            $builder = new QueryBuilder($m->getTablename());
            $builder->select((array) $m->getProps())->where('id')->equal($pk)->limit(1);

            /**
             * @var Connection
             */
            $repository = $m->getConnection();
            $r = $repository->fetch($builder->mount());

            if (!$r) {
                return null;
                // throw new ResponseError('Registro não encontrado', 404);
            }
            ;
            $model = new $m($r);

            return $model;
        } catch (Throwable $th) {
            if (self::$staticLogger) {
                self::$staticLogger->error($th->getMessage());
            }
            ;
            return null;
        }
    }

    /**
     *
     * @return $this[]|
     */
    public static function getAll()
    {

        try {
            $m = get_called_class();
            $m = new $m;

            $builder = new QueryBuilder($m->getTablename());
            $builder->select((array) $m->getProps());

            /**
             * @var Connection
             */
            $repository = $m->getConnection();
            $r = $repository->fetchAll($builder->mount());

            if (!$r) {
                return $r;
                // throw new ResponseError('Nenhum registro encontrado', 404);
            }
            ;

            return self::sqlMapResult($r);
        } catch (Throwable $th) {
            if (self::$staticLogger) {
                self::$staticLogger->error($th->getMessage());
            }
            ;
            return [  ];
        }
    }

    /**
     * @param array{where?: Where, select?: Select, associations?: Assoc } $params
     *
     * @return $this|null
     */
    public static function findOne($params)
    {
        try {
            $m = get_called_class();
            $m = new $m;

            $operator = OperatorResolver::get(static::class);
            // $params[ 'limit' ] = 1;
            $query = $operator->mountQuery($m->getTablename(), $params);
            /**
             * @var Connection
             */
            $repository = $m->getConnection();
            $r = $repository->fetchAll($query);

            if (!$r) {
                return null;
            }
            ;
            $data = self::filterSQLData($m, $r, $operator->getAssociations());
            $model = new $m($data[ 0 ], $data[ 1 ]);

            return $model;
        } catch (Throwable $th) {
            if (self::$staticLogger) {
                self::$staticLogger->error($th->getMessage());
            }
            ;
            return null;
        }
    }

    /**
     * @param array{where?: Where, select?: Select, associations?: Assoc } $params
     *
     * @return array| $this[]
     */
    public static function findAll($params)
    {

        try {
            $m = get_called_class();
            $m = new $m;

            $operator = OperatorResolver::get(static::class);
            $query = $operator->mountQuery($m->getTablename(), $params);
            /**
             * @var Connection
             */
            $repository = $m->getConnection();
            $r = $repository->fetchAll($query);

            if (!$r) {
                return $r;
            }
            ;

            return self::filterMultiSQLData($m, $r, $operator->getAssociations());
        } catch (Throwable $th) {
            if (self::$staticLogger) {
                self::$staticLogger->error($th->getMessage());
            }
            ;
            return [  ];
        }
    }

    /**
     * @param array{where?: Where, select?: Select, associations?: Assoc } $params
     *
     * @return array| $this[]
     */
    public static function findData($params)
    {

        try {
            $m = get_called_class();
            $m = new $m;

            $operator = OperatorResolver::get(static::class);
            $query = $operator->mountQuery($m->getTablename(), $params);
            /**
             * @var Connection
             */
            $repository = $m->getConnection();
            $r = $repository->fetchAll($query);

            return $r;
        } catch (Throwable $th) {
            if (self::$staticLogger) {
                self::$staticLogger->error($th->getMessage());
            }
            ;
            return [  ];
        }
    }

    /**
     * @param array{where?: Where, select?: Select, associations?: Assoc } $params
     *
     * @return int
     */
    public static function count($params)
    {
        try {
            $m = get_called_class();
            $m = new $m;

            $operator = OperatorResolver::get(static::class);
            $params[ 'attributes' ] = [
                'id' => Op::COUNT_DISTINCT,
             ];
            $query = $operator->mountQuery($m->getTablename(), $params);

            /**
             * @var Connection
             */
            $repository = $m->getConnection();
            $r = $repository->fetchColumm($query);

            return intval($r);
        } catch (Throwable $th) {
            if (self::$staticLogger) {
                self::$staticLogger->error($th->getMessage());
            }
            ;
            throw $th;
        }
    }

    /**
     * @param array{where?: Where, select?: Select, associations?: Assoc } $params
     *
     * @return bool
     */
    public static function deleteAll($params)
    {
        try {
            if (!isset($params[ 'where' ])) {
                throw new Error('Para excluir varios registros, é necessário informar condições');
            }
            $m = get_called_class();
            $m = new $m;
            $tablename = $m->getTablename();
            $operator = OperatorResolver::get(static::class);

            $params[ 'attributes' ] = 'delete';
            $query = $operator->mountQuery($tablename, $params);
           
            /**
             * @var Connection
             */
            $repository = $m->getConnection();
            $r = $repository->execute($query);

            if (!$r) {
                throw new Error('Nenhum registro encontrado');
            };

            return true;
        } catch (Throwable $th) {
            if (self::$staticLogger) {
                self::$staticLogger->error($th->getMessage());
            };
            return false;
        }
    }

    /**
     * @param array<string,int|string> $data
     * @param array<string,array|string> $associations
     *
     * @return $this|array|false
     */
    public static function create($data, $associations = [  ])
    {
        if (!$data) {
            return false;
        }
        ;
        try {
            $c = get_called_class();
            $m = new $c($data);
            $p = $m->persist();
            if (!$p) {
                return $p;
            }
            ;

            if ($associations) {
                $assoc = self::createAssociation($m, $associations);
                return [
                    'model' => $m,
                    'associations' => $assoc,
                 ];
            }
            ;
            return $m;
        } catch (Throwable $th) {
            if (self::$staticLogger) {
                self::$staticLogger->error($th->getMessage());
            }
            ;
            return false;
        }
    }

    public static function getProperties()
    {
        $m = new static();
        return $m->getCollumns();
    }

    // #Private Methods
    /**
     * @param array $data
     *
     * @return Model[] | $this[]
     */
    private static function sqlMapResult($data, $associations = [  ])
    {

        return array_map(function ($var) use ($associations) {
            $child = get_called_class();
            $f = self::filterSQLData(new $child, [ $var ], $associations);
            $model = new $child($f[ 0 ], $f[ 1 ]);
            $associations = self::findAssociationData(new $child, $var, $associations);
            return new $child($var, $associations);
        }, $data);
    }

    /**
     * @param Model $model
     * @param array $sqldata
     * @param array $associations
     *
     * @return Model[]
     */
    private static function findAssociationData($model, $sqldata, $associations)
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
     * Cria uma lista com os dados de associações encontradas
     *
     * @param array $associations
     *
     * @return object[]
     */
    private static function createAssociation($oringinModel, $associations)
    {
        $a = [  ];
        if (!$associations) {
            return $a;
        }

        /**
         * @var  Association[]
         */
        $associates = $oringinModel->getAssociations();

        foreach ($associations as $name => $data) {
            if (!$data || !array_key_exists($name, $associates)) {
                continue;
            }

            $association = $associates[ $name ];
            $model = $association->getModel();

            // #This condition guarantees the authenticity of the association
            $data[ $association->getForeingkey() ] = $oringinModel->{$association->getOringinKey()};
            $a[ $name ] = $model->create($data);
        }

        return $a;
    }

    /**
     * Separa dados do modelo principal e das associações em um resultado
     * vindo de uma mesma query (JOIN)
     *
     * @param Model $model
     * @param array $sqldata
     * @param array $associations
     *
     * @return Model[]
     */
    private static function filterSQLData($model, $sqldata, $associations = [  ])
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

                if (array_filter($d)) {
                    $associationGroup[ $d[ 'id' ] ] = $d;
                }
            }
        }
        $a = array_filter($sqldata[ 0 ], function ($v, $k) use ($association) {
            return !strpos($k, ":") !== false;
        }, ARRAY_FILTER_USE_BOTH);

        return [ $a, $assoc ];
    }

    /**
     * Separa dados do modelo principal e das associações em um resultado
     * vindo de uma mesma query (JOIN)
     *
     * @param Model $model
     * @param array $sqldata
     * @param array $associations
     *
     * @return Model[]
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
                if (array_filter($d)) {
                    $associationGroup[ $d[ 'id' ] ] = $d;
                }
            }
        }
        $r = array_map(function ($var) use ($assoc, $child) {
            return new $child($var, $assoc[ $var[ 'id' ] ]);
        }, $r);
        return array_values($r);
    }

    public static function getLogger()
    {
        return self::$staticLogger;
    }
}
