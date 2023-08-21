<?php

namespace Komodo\Interlace\Adapter;

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

use Komodo\Interlace\Association;
use Komodo\Interlace\Entity;
use Komodo\Interlace\Enums\Op;
use Komodo\Interlace\Model;
use Komodo\Interlace\QueryBuilder\QueryBuilder;

class Operator
{
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
    private $associations = [  ];

    private function __construct(string $class)
    {
        $this->model = new $class;
        $this->builder = new QueryBuilder($this->model->getTablename());
    }

    /**
     * @param string $class
     *
     * @return Operator
     */
    public static function get($class)
    {
        return new self($class);
    }
    /**
     * @param array<\BackedEnum,string|string> $conditions
     * @param string $owner
     *
     * @return string[]
     */
    public function createCondition($conditions, $owner)
    {
        $query = [  ];

        foreach ($conditions as $op => $values) {
            if (is_array($values)) {
                if ((array_keys($values)[ 0 ]) instanceof Op) {
                    $values = self::createCondition($values, $owner);
                }
                switch ($op) {
                    case Op::or:
                        $v = sprintf("({$op->value})", ...$values);
                        break;

                    case Op::in:
                        $v = sprintf($op->value, $owner, implode(',', $values));
                        break;

                    default:
                        $v = sprintf($op->value, $owner, ...$values);
                        break;
                }
            } else {
                $v = sprintf("$op", $owner, $values);
            }
            $query[  ] = $v;
        }
        return $query;
    }

    /**
     *
     * @param string $owner
     * @param array $conditions
     *
     * @return void
     */
    public function mountConditions($owner, $conditions)
    {
        $query = [  ];
        if (!$conditions) {
            return;
        }

        foreach ($conditions as $collum => $condition) {
            $collum = self::fullCollumName($collum, $owner);

            if (is_array($condition)) {
                $condition = self::createCondition($condition, $collum);
                $condition = implode(" AND ", $condition);
            } else {
                $condition = "$collum = '$condition'";
            }
            $query[  ] = $condition;
        }
        $this->builder
            ->addConditionQuery(implode(" AND ", $query));
    }

    public function createInclude($include)
    {

        $associations = $this->model->getAssociations();
        if (!array_key_exists($include, $associations)) {
            return '';
        }

        $association = (object) $associations[ $include ];
        $model = new $association->entity;
        $table = $model->getTablename();

        $this->builder
            ->select((array) $model->getProps(), $table)
            ->from()
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

        $i = [  ];
        if (is_array($includes)) {
            foreach ($includes as $key => $include) {
                $i[  ] = self::createInclude($include);
            }
        } else {
            $i[  ] = self::createInclude($includes);
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

    public function mountSelect($selects)
    {
        if (!$selects) {
            return $this->builder->select((array) $this->model->getProps())->from();
        }
        foreach ($selects as $collunm => $op) {
            switch ($op) {
                case Op::count:
                    $this->builder->select([  ])->count($collunm)->from();
                    break;
                case Op::distinct:
                    $this->builder->select([  ])->distinct($collunm)->from();
                    break;
                case Op::countDistinct:
                    $this->builder->select([  ])->countDistinc($collunm)->from();
                    break;
            }
        }
    }
    /**
     * @param array $associations
     *
     * @return void
     */
    public function mountAssociations($associations)
    {
        $a = [  ];
        if (!$associations) {
            return;
        }

        /**
         * @var  Association[]
         */
        $associates = $this->model->getAssociations();

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

            if (isset($data[ 'require' ]) && $data[ 'require' ]) {
                $joinType = 'inner';
            }

            $tb1 = '';
            $k1 = '';
            $tb2 = '';
            $k2 = '';

            switch ($joinType) {
                case 'inner':
                    $this->builder->innerJoin($model->getTablename());
                    $k1 = $associate->getOringinKey();
                    $tb2 = $model->getTablename();
                    $k2 = $associate->getForeingkey();
                    break;
                case 'left':
                    $this->builder->leftJoin($model->getTablename());
                    $k1 = $associate->getOringinKey();
                    $tb2 = $model->getTablename();
                    $k2 = $associate->getForeingkey();
                    break;
                case 'right':
                    $this->builder->rightJoin($model->getTablename());
                    $k1 = $associate->getForeingkey();
                    $tb2 = $model->getTablename();
                    $k2 = $associate->getOringinKey();
                    break;
            }
            $this->builder->on($k1)->equalColumm($k2, $tb2);

            $attributes = array_key_exists('attributes', $data) ? $data[ 'attributes' ] : [  ];
            $conditions = array_key_exists('where', $data) ? $data[ 'where' ] : [  ];
            $group = array_key_exists('group', $data) ? $data[ 'group' ] : '';
            $order = array_key_exists('order', $data) ? $data[ 'order' ] : [  ];
            // $count = isset($data[ 'count' ]) ? $data[ 'count' ] : false;
            // $includes = array_key_exists('include', $data) ? $data[ 'include' ] : [  ];
            // $association = array_key_exists('association', $data) ? $data[ 'association' ] : [  ];
            // ?Conditions
            if ($conditions) {
                $this->builder->and();
                self::mountConditions($model->getTablename(), $conditions);
            }

            // ?Attributes
            // self::createQueryColumms($attributes, $model->getTablename());

            // ?Includes
            // self::mountIncludes($includes);

            // ?Associations
            // $this->associations = is_array($association) ? $association : [ $association ];
            // self::mountAssociations(is_array($association) ? $association : [ $association ]);

            // ?Order
            self::mountOrder($order, $model->getTablename());

            // ?Group
            self::mountGroup($group, $model->getTablename());

            // ?Limit
            if (isset($data[ 'limit' ])) {
                self::mountLimit($data[ 'limit' ]);
            }

            // ?offset
            if (isset($data[ 'offset' ])) {
                self::mountOffset($data[ 'offset' ]);
            }
        }
    }

    public function mountQuery($owner, $params)
    {

        $select = isset($params[ 'select' ]) ? $params[ 'select' ] : [  ];
        $attributes = array_key_exists('attributes', $params) ? $params[ 'attributes' ] : [  ];
        $conditions = array_key_exists('where', $params) ? $params[ 'where' ] : [  ];
        $includes = array_key_exists('include', $params) ? $params[ 'include' ] : [  ];
        $association = array_key_exists('association', $params) ? $params[ 'association' ] : [  ];
        $group = array_key_exists('group', $params) ? $params[ 'group' ] : '';
        $order = array_key_exists('order', $params) ? $params[ 'order' ] : [  ];

        // ?Attributes
        self::createQueryColumms($attributes, $owner);

        // ?Includes
        self::mountIncludes($includes);

        // ?Associations
        $this->associations = is_array($association) ? $association : [ $association ];
        self::mountAssociations(is_array($association) ? $association : [ $association ]);

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
        if (isset($params[ 'limit' ])) {
            self::mountLimit($params[ 'limit' ]);
        }

        // ?offset
        if (isset($params[ 'offset' ])) {
            self::mountOffset($params[ 'offset' ]);
        }

        // ?Select structure
        self::mountSelect($select);

        return $this->builder->mount();
    }

    /**
     * Criar a uma string contendo as colunas com seus respectivos tablename
     *
     * @return string
     */
    public function createQueryColumms($columms = [  ], $table = '')
    {
        $cols = [  ];
        foreach ($columms as $columm) {
            $cols[  ] = self::fullCollumName($columm, $table);
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
