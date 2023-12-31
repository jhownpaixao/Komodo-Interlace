<?php

namespace Komodo\Interlace\Adapter\Operator;

use Komodo\Interlace\Entity;
use Komodo\Interlace\Enums\Op;
use Komodo\Interlace\Model;
use Komodo\Interlace\QueryBuilder\QueryBuilder;

/*
|-----------------------------------------------------------------------------
| Komodo Interlace
|-----------------------------------------------------------------------------
|
| Desenvolvido por: Jhonnata Paixão (Líder de Projeto)
| Iniciado em: 08/2023
| Arquivo: Condition.php
| Data da Criação Sun Aug 20 2023
| Copyright (c) 2023
|
|-----------------------------------------------------------------------------
|*/

trait Condition
{
    /**
     * @var QueryBuilder
     */
    private $builder;

    /**
     * model
     *
     * @var \Komodo\Interlace\Model|\Komodo\Interlace\Entity
     */
    private $model;

    public function parseConditions($conditions)
    {
        if (!$conditions) {
            return;
        }

        foreach ($conditions as $type => $condition) {
        }
    }

    /**
     *
     * @param Model|Entity|string $owner
     * @param array<string,string|int|bool|array<string,string|int|bool>> $conditions
     *
     * @return array|null
     */
    public function processAllConditions($owner, $conditions)
    {
        if (!is_string($owner)) {
            $model = $owner;
            $owner = $model->getTablename();
        } else {
            $model = null;
        }

        if (!$conditions) {
            return;
        }
        $query = [];
        foreach ($conditions as $type => $condition) {
            switch (true) {
                case $this->isProperty($type, $model):
                    $query = array_merge($this->parsePropertyCondition($owner, $type, $condition), $query);
                    break;

                case $this->isOperator($type):
                    $query = array_merge($this->parseOperatorCondition($owner, $type, $condition), $query);
                    break;

                case is_int($type) && is_array($condition): #Is array of conditions in 'OR' and 'AND' clauses
                    $query = array_merge($this->parseArrayOfCondition($owner, $type, $condition), $query);
                    break;

                case is_string($type) && is_array($condition): #? similar to 'isProperty', but it is used for associations
                    $query = array_merge($this->parsePropertyCondition($owner, $type, $condition), $query);
                    break;

                default:
                    throw new \InvalidArgumentException('Some data informed in the "where" parameter is invalid: ' . $type);
            }
        }
        return $query;
    }

    /**
     * parseDefaultCondition
     *
     * @param  array<string,string|int|bool> $queryConditions
     * @param  string $column
     * @param  string|array $condition
     * @return array
     */
    private function parseArrayOfCondition($owner, $property, $condition)
    {
        return $this->processAllConditions($owner, $condition);
    }

    private function parsePropertyCondition($owner, $property, $condition)
    {

        $column = $this->builder->normalizeCollum($property, $owner);
        $query = [];
        if (is_array($condition)) {
            foreach ($condition as $op => $value) {
                if (!$this->isOperator($op)) {
                    throw new \InvalidArgumentException("The value entered for column '$column' is not a valid operator");
                }

                $query[] = $this->resolverCondition($op, $value, $column);
            }
        } else {
            if ($this->isOperator($condition)) {
                $query[] = $this->resolverCondition($condition, '', $column); #Valid only for the cause: Collumn=> Op
            } else {
                $query[] = "$column = " . $this->convertValueToQuery($condition);
            }
        }
        return $query;
    }

    private function parseOperatorCondition($owner, $property, $condition)
    {
        $column = $this->builder->normalizeCollum($property, $owner);
        $query = [];

        switch ($property) {
            case Op::AND:
                $r = $this->processAllConditions($owner, $condition);
                $query[] = "(" . implode(' AND ', $r) . ")";
                break;

            case Op::OR:
                $r = $this->processAllConditions($owner, $condition);
                $query[] = "(" . implode(' OR ', $r) . ")";
                break;

            case Op::DATE:
                $r = $this->processAllConditions($owner, $condition);
                $query[] = preg_replace('/`(.*?\.`.*?)`/', 'DATE(${0})', implode(' AND ', $r));
                break;

            case Op::TIMESTAMP:
                $r = $this->processAllConditions($owner, $condition);
                $query[] = preg_replace('/`(.*?\.`.*?)`/', 'TIMESTAMP(${0})', implode(' AND ', $r));
                break;

            default:
                $query[] = $this->resolverCondition($property, $condition, $column);
                break;
        }
        return $query;
    }

    /**
     * @param Model|Entity|string $owner
     * @param array $condition
     * @return void
     */
    public function mountConditions($owner, $condition)
    {
        if (!$condition) {
            return;
        }

        $this->builder->addConditionQuery(implode(" AND ", $this->processAllConditions($owner, $condition)));
    }

    /**
     * @param Model|Entity|string $owner
     * @param array $condition
     * @return void
     */
    public function mountOnConditions($owner, $condition)
    {
        if (!$condition) {
            return;
        }

        $this->builder->addOnCondition(implode(" AND ", $this->processAllConditions($owner, $condition)));
    }

    /**
     * @param string $name
     * @param Model|Entity $owner
     * @return boolean
     */
    private function isProperty($name, $owner)
    {

        $model = is_string($owner) || is_null($owner) ? $this->model : $owner;
        $props = $model->getCollumns();

        foreach ($props as $key => $value) {
            if ($key === $name) {
                return true;
            }
        }

        return false;
    }

    private function isOperator($name)
    {
        return Op::isValid($name);
    }

    /**
     * resolverCondition
     *
     * @param  string $op
     * @param  mixed $value
     * @param  string $collunm
     * @return string
     */
    private function resolverCondition($op, $value, $collunm = '')
    {
        if (!$this->isOperator($op)) {
            throw new \InvalidArgumentException("This parameter is incompatible. Expected: " . Op::class);
        }

        $requireTraitValues = function (string $type, int $quant = 0) use ($value) {
            if (gettype($value) !== $type) {
                throw new \Exception("The entered value is not compatible with this type. Expected: $type");
            }
            if ($quant > 0 && count($value) < $quant) {
                throw new \Exception("The number of informed values does not correspond to the type of operator. Expected quantity: $quant");
            }
        };

        switch ($op) {
            case Op::BETWEEN:
                $requireTraitValues('array', 2);
                $v = sprintf($op, $collunm, ...$value);
                break;

            case Op::NOT_BETWEEN:
                $requireTraitValues('array', 2);
                $v = sprintf($op, $collunm, ...$value);
                break;

            case Op::IN:
                $requireTraitValues('array');
                $v = sprintf($op, $collunm, implode(',', $value));
                break;

            case Op::NOT_IN:
                $requireTraitValues('array');
                $v = sprintf($op, $collunm, implode(',', $value));
                break;

            case Op::LIKE:
                $requireTraitValues('string');
                $v = sprintf($op, $collunm, $value);
                break;

            case Op::NOT_LIKE:
                $requireTraitValues('string');
                $v = sprintf($op, $collunm, $value);
                break;

            case Op::START:
                $requireTraitValues('string');
                $v = sprintf($op, $collunm, $value);
                break;

            case Op::END:
                $requireTraitValues('string');
                $v = sprintf($op, $collunm, $value);
                break;

            case Op::CUR_DATE:
                $v = Op::CUR_DATE;
                break;

            case Op::CURRENT_TIMESTAMP:
                $var = Op::CURRENT_TIMESTAMP;
                break;

            default:
                $value = $this->convertValueToQuery($value);
                $v = sprintf($op, $collunm, $value);
                break;
        }

        return $v;
    }

    private function convertValueToQuery($value)
    {
        if ($this->isOperator($value)) {
            switch ($value) {
                case Op::CUR_DATE:
                    $var = Op::CUR_DATE;
                    break;

                case Op::CURRENT_TIMESTAMP:
                    $var = Op::CURRENT_TIMESTAMP;
                    break;

                default:
                    $var = $value;
                    break;
            }
        } else {
            $type = gettype($value);
            $var = '';
            switch ($type) {
                case 'array':
                    $var = implode(',', array_values($value));
                    break;
                case 'object':
                    $var = implode(',', array_values((array) $value));
                    break;
                case 'string':
                    $var = "'$value'";
                    break;
                default:
                    $var = $value;
                    break;
            }
        }
        return $var;
    }
}
