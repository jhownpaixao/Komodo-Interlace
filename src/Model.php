<?php

namespace Komodo\Interlace;

/*
|-----------------------------------------------------------------------------
| Komodo Interlace
|-----------------------------------------------------------------------------
|
| Desenvolvido por: Jhonnata Paixão (Líder de Projeto)
| Iniciado em: 08/2023
| Arquivo: Model.php
| Data da Criação Fri Aug 11 2023
| Copyright (c) 2023
|
|-----------------------------------------------------------------------------
|*/

use Error;
use Exception;
use Komodo\Interlace\Bases\ModelStaticFunctions;
use Komodo\Interlace\Interfaces\Connection;
use Komodo\Interlace\QueryBuilder\QueryBuilder;
use Komodo\Logger\Logger;
use ReflectionClass;
use ReflectionProperty;
use Throwable;

/**
 * @property string $created_at
 * @property string $updated_at
 */
#[\AllowDynamicProperties ]
class Model
{
    use ModelStaticFunctions;
    /** @var string|int*/
    public $id;
    /** @var \stdClass */
    protected $props;
    /** @var string */
    protected $tablename;
    /** @var bool */
    protected $timestamp;
    /** @var Association[] */
    protected $associations;
    /** @var Connection */
    protected $repository;
    /** @var Logger */
    protected $logger;

    public function __construct($data = [  ], $associations = [  ])
    {
        $this->init($data, $associations);
    }

    // #Protected Methods
    /**
     * Sincroniza a entidade filha com os dados informados na construção
     *
     * @param array $data
     *
     * @return void
     */
    protected function syncData($data, $associations)
    {
        foreach ($data as $prop => $value) {
            if (!in_array($prop, (array) $this->props)) {
                continue;
            }

            $this->{$prop} = $value;
        }

        foreach ($associations as $association => $list) {
            if (!array_key_exists($association, $this->associations)) {
                continue;
            }
            $associate = $this->associations[ $association ];
            $this->{$association} = [  ];

            foreach ($list as $data) {
                $class = $associate->getModelClassname();
                $model = new $class($data);
                switch ($associate->getType()) {
                    case 'has_one':
                        $this->{$association} = $model;
                        break;
                    case 'has_many':
                        $this->{$association}[  ] = $model;
                        break;

                    case 'blg_one':
                        $this->{$association} = $model;
                        break;

                    case 'blg_many':
                        $this->{$association}[  ] = $model;
                        break;
                }
            }
        }
    }

    /**
     * Define todas as proprieades que serão trabalhadas,
     * informadas pela entidade filha
     *
     * @return void
     */
    protected function resolverProperties()
    {

        if ($this->timestamp) {
            $this->{"created_at"} = "created_at";
            $this->{"updated_at"} = "updated_at";
        }

        $props = (array) $this;
        $this->props = new \stdClass;
        foreach ($props as $prop => $value) {
            if (str_contains($prop, '*') || str_contains($prop, '\\')) {
                continue;
            }
            $this->props->{$prop} = $prop;
        }
    }

    protected function init(array $data, array $associations)
    {
        $setup = $this->setup();

        $thisClassName = (new ReflectionClass($this))->getShortName();

        $this->logger = isset($setup[ 'logger' ]) && $setup[ 'logger' ] instanceof Logger ? clone $setup[ 'logger' ] : new Logger;
        $this->tablename = isset($setup[ 'tablename' ]) ? $setup[ 'tablename' ] : strtolower($thisClassName) . 's';
        $this->timestamp = isset($setup[ 'timestamp' ]) ? $setup[ 'timestamp' ] : true;
        $this->logger->register(static::class);

        // !Required
        if (!isset($setup[ 'connection' ])) {
            throw new Exception("No connection reported for this entity: $thisClassName");
        } elseif (!$setup[ 'connection' ] instanceof Connection) {
            throw new Exception("The specified connection object is not compatible with this model. Expected: " . Connection::class);
        }
        $this->repository = $setup[ 'connection' ];

        $this->associations = $this->associate();
        $this->resolverProperties();
        $this->syncData($data, $associations);
    }

    /**
     * Inicializa as associações definidas pela entidade
     *
     * @return Association[]
     */
    protected function associate()
    {
        return [  ];
    }

    /**
     * Defini os parametros de operação fornecidos pela entidade
     *
     * @return array
     */
    protected function setup()
    {
        return [  ];
    }

    // #Public Methods

    /**
     * Excluí esta entidade
     *
     * @return bool
     */
    public function delete()
    {
        try {
            $builder = new QueryBuilder($this->tablename);
            $builder->delete()->where('id')->equal($this->id);
            $r = $this->repository->execute($builder->mount());

            $refl = new ReflectionClass($this);

            foreach ($this->props as $prop) {
                $this->{$prop} = null;
            }

            return $r;
        } catch (Throwable $th) {
            $this->logger->error($th->getMessage());
            throw $th;
        }
    }

    /**
     * Atualiza a database desta entidade com base nos valores atuais,
     * e/ou fornecidos no parametro $data
     *
     * @param array<string,string|int> $data Dados opcionais para substituir nesta atualização
     *
     * @return bool
     */
    public function update($data = [  ])
    {

        if ($data) {
            foreach ($data as $prop => $value) {
                if (!in_array($prop, (array) $this->props)) {
                    continue;
                }
                $this->{$prop} = $value;
            }
        }

        $refl = new ReflectionClass($this);
        $bindValues = [  ];
        $sets = [  ];
        foreach ($this->props as $prop) {
            if (in_array($prop, [ 'updated_at', 'created_at', 'id' ])) {
                continue;
            }

            $reflProp = $refl->getProperty($prop);

            if ($reflProp instanceof ReflectionProperty) {
                #Definir o valor à ser salvo
                $v = $reflProp->getValue($this);

                #Keys para vincular
                $bindValues[ ":$prop" ] = $v;
                $sets[ $prop ] = ":$prop";
            }
        }

        try {
            $builder = new QueryBuilder($this->tablename);
            $builder->update()->set($sets)->where('id')->equal($this->id);
            $r = $this->repository->execute($builder->mount(), $bindValues);

            return $r;
        } catch (Throwable $th) {
            $this->logger->error($th->getMessage());
            throw $th;
        }
    }

    /**
     * Salva esta entidade na database e defini seu novo ID
     * caso esta entidade ja tenha um ID, a operação será ignorada
     *
     * @return bool
     */
    public function persist()
    {
        if ($this->id) {
            return false;
        }
        $refl = new ReflectionClass($this);
        $bindValues = [  ];
        $sets = [  ];

        foreach ($this->props as $prop) {
            if (in_array($prop, [ 'updated_at', 'created_at', 'id' ])) {
                continue;
            }
            $reflProp = $refl->getProperty($prop);

            if ($reflProp instanceof ReflectionProperty) {
                #Definir o valor à ser salvo
                $v = $reflProp->getValue($this);

                #Keys para vincular
                $bindValues[ ":$prop" ] = $v;
                $sets[ $prop ] = ":$prop";
            }
        }

        try {
            $builder = new QueryBuilder($this->tablename);
            $builder->insert(array_keys($sets))->values($sets);
            $r = $this->repository->execute($builder->mount(), $bindValues);

            if ($r) {
                $this->id = $this->repository->lastInsertId();
            }

            return $r;
        } catch (Throwable $th) {
            $this->logger->error($th->getMessage());
            return false;
            // throw new ResponseError($th->getMessage(), HTTPResponseCode::ITERNALERRO);
        }
    }

    /**
     * Retorna as propriedades desta entidade
     *
     * @return \stdClass
     */
    public function getProps()
    {
        return $this->props;
    }

    public function getCollumns()
    {
        return (array) $this->props;
    }

    /**
     * Retorna o nome da tabela/entidade que está sendo usado
     * por este modelo
     *
     * @return string
     */
    public function getTablename()
    {
        return $this->tablename;
    }

    /**
     * Retorna todas os parametros de associação
     * desta entidade
     *
     * @return array
     */
    public function getAssociations()
    {
        return $this->associations;
    }

    /**
     * Retorna o repositório desta entidade
     *
     * @return void
     */
    public function getConnection()
    {
        return $this->repository;
    }

    /**
     * Retorna o objeto de Logger em uso
     *
     * @return Logger
     */
    public function getLogger()
    {
        return $this->logger;
    }
}
