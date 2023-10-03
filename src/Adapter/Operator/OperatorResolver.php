<?php

namespace Komodo\Interlace\Adapter\Operator;

/*
|-----------------------------------------------------------------------------
| Komodo Interlace
|-----------------------------------------------------------------------------
|
| Desenvolvido por: Jhonnata Paixão (Líder de Projeto)
| Iniciado em: 08/2023
| Arquivo: Operator.php
| Data da Criação Fri Aug 11 2023
| Copyright (c) 2023
|
|-----------------------------------------------------------------------------
|*/

use Komodo\Interlace\Adapter\Operator\Condition;
use Komodo\Interlace\Association;
use Komodo\Interlace\Entity;
use Komodo\Interlace\Enums\Op;
use Komodo\Interlace\Model;
use Komodo\Interlace\QueryBuilder\QueryBuilder;

class OperatorResolver
{
    use Condition;
    use Attributes;
    /**
     * model
     *
     * @var Model|Entity
     */
    private $model;

    /**
     * @var QueryBuilder
     */
    private $builder;

    /**
     * @var AssociationParams
     */
    private $associations = [];

    private function __construct(string $class)
    {
        $this->model = new $class;
        $this->builder = new QueryBuilder($this->model->getTablename());
    }

    /**
     * @param string $class
     *
     * @return OperatorResolver
     */
    public static function get($class)
    {
        return new self($class);
    }

    public function createInclude($include)
    {

        $associations = $this->model->getAssociations();
        if (!array_key_exists($include, $associations)) {
            return '';
        }

        $association = (object) $associations[$include];
        $model = new $association->entity;
        $table = $model->getTablename();

        $this->builder
            ->select((array) $model->getProps(), $table)
            ->leftJoin($table)
            ->on($association->originKey)
            ->equal("`{$table}`.`{$association->foreingkey}`");
    }

    /**
     * @param array $includes
     *
     * @return array
     */
    public function mountIncludes($includes)
    {

        $i = [];
        if (is_array($includes)) {
            foreach ($includes as $key => $include) {
                $i[] = self::createInclude($include);
            }
        } else {
            $i[] = self::createInclude($includes);
        }

        return $i;
    }

    public function mountOrder($orders, $owner)
    {
        if (!$orders) {
            return;
        }
        $this->builder->orderBy($orders, $owner);
    }

    public function mountGroup($group, $owner)
    {
        if (!$group) {
            return;
        }
        $this->builder->groupBy($group, $owner);
    }

    public function mountLimit($limit)
    {
        if (!$limit) {
            return;
        }
        $this->builder->limit($limit);
    }

    public function mountOffset($offset)
    {
        if (!$offset) {
            return;
        }
        $this->builder->offset($offset);
    }

    /**
     * @param array $associations
     *
     * @return void
     */
    public function mountAssociations($associations, $ownerModel = null)
    {
        $ownerModel = $ownerModel ?: $this->model;
        $a = [];
        if (!$associations) {
            return;
        }

        /**
         * @var  Association[]
         */
        $associates = $ownerModel->getAssociations();

        foreach ($associations as $name => $selected) {
            if (is_string($name)) {
                $key = $name;
                $data = $selected;
            } else {
                $key = $selected;
                $data = [];
            }

            if (!array_key_exists($key, $associates)) {
                continue;
            }

            $associate = $associates[$key];
            $model = $associate->getModel();

            $this->builder->select((array) $model->getProps(), $model->getTablename(), $key . ":");

            $joinType = 'left';
            switch ($associate->getType()) {
                case 'has_one':
                    $joinType = 'left';
                    break;
                case 'has_many':
                    $joinType = 'left';
                    break;
                case 'blg_one':
                    $joinType = 'right';
                    break;
                case 'blg_many':
                    $joinType = 'right';
                    break;
            }

            if (isset($data['require']) && $data['require']) {
                $joinType = 'inner';
            }

            $k1 = '';
            $tb1 = '';
            $tb2 = '';
            $k2 = '';

            switch ($joinType) {
                case 'inner':
                    $this->builder->innerJoin($model->getTablename());
                    $k1 = $associate->getOringinKey();
                    $k2 = $associate->getForeingkey();
                    $tb1 = $ownerModel->getTablename();
                    $tb2 = $model->getTablename();
                    break;
                case 'left':
                    $this->builder->leftJoin($model->getTablename());
                    $k1 = $associate->getOringinKey();
                    $k2 = $associate->getForeingkey();
                    $tb1 = $ownerModel->getTablename();
                    $tb2 = $model->getTablename();
                    break;
                case 'right':
                    $this->builder->rightJoin($model->getTablename());
                    $k1 = $associate->getForeingkey();
                    $k2 = $associate->getOringinKey();
                    $tb1 = $ownerModel->getTablename();
                    $tb2 = $model->getTablename();
                    break;
            }

            $this->builder->on($k1, $tb1)->equalColumm($k2, $tb2);

            $attributes = array_key_exists('attributes', $data) ? $data['attributes'] : [];
            $conditions = array_key_exists('where', $data) ? $data['where'] : [];
            $group = array_key_exists('group', $data) ? $data['group'] : '';
            $order = array_key_exists('order', $data) ? $data['order'] : [];
            // $count = isset($data[ 'count' ]) ? $data[ 'count' ] : false;
            // $includes = array_key_exists('include', $data) ? $data[ 'include' ] : [  ];
            $association = array_key_exists('association', $data) ? $data['association'] : [];

            // ?Conditions
            if ($conditions) {
                $this->builder->and();
                self::mountOnConditions($model, $conditions);
            }

            // ?Attributes
            // self::createQueryColumms($attributes, $model->getTablename());

            // ?Includes
            // self::mountIncludes($includes);

            // ?Associations
            $this->associations = array_merge($this->associations, is_array($association) ? $association : [$association]);
            self::mountAssociations(is_array($association) ? $association : [$association], $model);

            // ?Order
            self::mountOrder($order, $model->getTablename());

            // ?Group
            self::mountGroup($group, $model->getTablename());

            // ?Limit
            if (isset($data['limit'])) {
                self::mountLimit($data['limit']);
            }

            // ?offset
            if (isset($data['offset'])) {
                self::mountOffset($data['offset']);
            }
        }
    }

    public function mountQuery($owner, $params)
    {
        $attributes = array_key_exists('attributes', $params) ? $params['attributes'] : [];
        $conditions = array_key_exists('where', $params) ? $params['where'] : [];
        $includes = array_key_exists('include', $params) ? $params['include'] : [];
        $association = array_key_exists('association', $params) ? $params['association'] : [];
        $group = array_key_exists('group', $params) ? $params['group'] : '';
        $order = array_key_exists('order', $params) ? $params['order'] : [];

        // ?Includes
        self::mountIncludes($includes);

        // ?Associations
        $this->associations = array_merge($this->associations, is_array($association) ? $association : [$association]);
        self::mountAssociations(is_array($association) ? $association : [$association]);

        // ?Conditions
        if ($conditions) {
            $this->builder->where();
        }
        self::mountConditions($owner, $conditions);

        // ?Order
        self::mountOrder($order, $owner);

        // ?Group
        self::mountGroup($group, $owner);

        // ?Limit
        if (isset($params['limit'])) {
            self::mountLimit($params['limit']);
        }

        // ?offset
        if (isset($params['offset'])) {
            self::mountOffset($params['offset']);
        }

        // ?Attributes
        if ($attributes) {
            self::mountAttributes($attributes);
        } else {
            $this->builder->select($this->model->getCollumns());
        }

        return $this->builder->mount();
    }

    /**
     * Criar a uma string contendo as colunas com seus respectivos tablename
     *
     * @return string
     */
    public function createQueryColumms($columms = [], $table = '')
    {
        $cols = [];
        foreach ($columms as $columm) {
            $cols[] = self::fullCollumName($columm, $table);
        }
        return implode(',', $cols);
    }

    /**
     * Retorna o nome da coluna completo com tablename
     *
     * @param string $col
     *
     * @return string
     */
    public function fullCollumName($col, $table)
    {
        return "`{$table}`.`{$col}`";
    }

    public function getAssociations()
    {
        return $this->associations;
    }

    /**
     * @return QueryBuilder
     */
    public function getBuilder()
    {
        return $this->builder;
    }
}
