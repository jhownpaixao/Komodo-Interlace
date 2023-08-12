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
use Komodo\Interlace\QueryBuilder\QueryBuilder;
use ReflectionClass;
use ReflectionProperty;
use Throwable;

/**
 * @property string $created_at
 * @property string $updated_at
 */
class Model extends ModelStatic
{
    /**
     * @var string|int
     */
    public $id;

    /**
     * @var \stdClass Propriedades da entidade
     */
    protected $props;

    /**
     * @var string
     */
    protected $tablename;

    /**
     * @var bool
     */
    protected $timestamp = true;

    /**
     * @var Association[]
     */
    protected $associations;

    /**
     * @var string
     */
    protected $repository;

    /**
     * @param array $data
     *
     * @return $this
     */
    public function __construct($data = [], $associations = [])
    {
        $this->associations = $this->associate();
        $this->setProps();
        $this->sync($data, $associations);
    }

    // #Protected Methods
    /**
     * Sincroniza a entidade atual com os dados informados
     *
     * @param array $data
     *
     * @return void
     */
    protected function sync($data, $associations)
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
            $associate = $this->associations[$association];
            $this->{$association} = [];

            foreach ($list as $data) {
                $class = $associate->getModelClassname();
                $model = new $class($data);
                switch ($associate->getType()) {
                    case 'has_one':
                        $this->{$association} = $model;
                        break;
                    case 'has_many':
                        $this->{$association}[] = $model;
                        break;

                    case 'blg_one':
                        $this->{$association} = $model;
                        break;

                    case 'blg_many':
                        $this->{$association}[] = $model;
                        break;
                }
            }
        }
    }

    /**
     * Recupera todas as propriedades definidas pela classe filha
     *
     * @return void
     */
    protected function setProps()
    {

        $classname = (new ReflectionClass($this))->getShortName();

        $this->tablename = $this->tablename ?: strtolower($classname) . 's';

        if (!$this->repository) {
            $this->repository = array_key_first(parent::getRepositories());
        }
        if (!isset(parent::getRepositories()[$this->repository])) {
            throw new Error('Modelos com base de dados não reconhecidas');
        }

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

    /**
     * @return Association[]
     */
    protected function associate()
    {
        return [];
    }

    // #Public Methods
    public function delete()
    {
        try {
            $builder = new QueryBuilder($this->tablename);
            $builder->delete()->where('id')->equal($this->id);
            $repository = parent::getRepository($this->repository);
            $r = $repository->execute($builder->mount());

            $refl = new ReflectionClass($this);

            foreach ($this->props as $prop) {
                $property = $refl->getProperty($prop);
                if ($property instanceof ReflectionProperty) {
                    $property->setValue($this, null);
                }
            }

            return $r;
        } catch (Throwable $th) {
            Model::$logger->error($th->getMessage());
            throw $th;
        }
    }

    /**
     * update
     *
     * @param array<string,string|int> $data
     *
     * @return mixed
     */
    public function update($data = [])
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
        $bindValues = [];
        $sets = [];
        foreach ($this->props as $prop) {
            if (in_array($prop, ['updated_at', 'created_at', 'id'])) {
                continue;
            }

            $reflProp = $refl->getProperty($prop);

            if ($reflProp instanceof ReflectionProperty) {
                #Definir o valor à ser salvo
                $v = $reflProp->getValue($this);

                #Keys para vincular
                $bindValues[":$prop"] = $v;
                $sets[$prop] = ":$prop";
            }
        }

        try {
            $builder = new QueryBuilder($this->tablename);
            $builder->update()->set($sets)->where('id')->equal($this->id);
            $repository = parent::getRepository($this->repository);
            $r = $repository->execute($builder->mount(), $bindValues);

            return $r;
        } catch (Throwable $th) {
            Model::$logger->error($th->getMessage());
            throw $th;
        }
    }

    public function persist()
    {
        if ($this->id) {
            return false;
        }
        $refl = new ReflectionClass($this);
        $bindValues = [];
        $sets = [];

        foreach ($this->props as $prop) {
            if (in_array($prop, ['updated_at', 'created_at', 'id'])) {
                continue;
            }
            $reflProp = $refl->getProperty($prop);

            if ($reflProp instanceof ReflectionProperty) {
                #Definir o valor à ser salvo
                $v = $reflProp->getValue($this);

                #Keys para vincular
                $bindValues[":$prop"] = $v;
                $sets[$prop] = ":$prop";
            }
        }

        try {
            $builder = new QueryBuilder($this->tablename);
            $builder->insert(array_keys($sets))->values($sets);

            $repository = parent::getRepository($this->repository);
            $r = $repository->execute($builder->mount(), $bindValues);

            if ($r) {
                $this->id = $repository->lastInsertId();
            }

            return $r;
        } catch (Throwable $th) {
            Model::$logger->error($th->getMessage());
            return false;
            // throw new ResponseError($th->getMessage(), HTTPResponseCode::ITERNALERRO);
        }
    }

    /**
     * @return \stdClass
     */
    public function getProps()
    {
        return $this->props;
    }

    /**
     * @return string
     */
    public function getTablename()
    {
        return $this->tablename;
    }

    /**
     * @return array
     */
    public function getAssociations()
    {
        return $this->associations;
    }

    public function getConnection()
    {
        return parent::getRepository($this->repository);
    }
}
