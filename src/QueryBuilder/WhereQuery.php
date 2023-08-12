<?php

namespace Komodo\Interlace\QueryBuilder;

/*
|-----------------------------------------------------------------------------
| Komodo Interlace
|-----------------------------------------------------------------------------
|
| Desenvolvido por: Jhonnata Paixão (Líder de Projeto)
| Iniciado em: 08/2023
| Arquivo: WhereQuery.php
| Data da Criação Fri Aug 11 2023
| Copyright (c) 2023
|
|-----------------------------------------------------------------------------
|*/


trait WhereQuery
{

    private function setCondition($condition)
    {
        if (strpos($this->lastCondition, "ON") !== false) {
            $this->query->join[  ] = $condition;
        } else {
            $this->query->condition[  ] = $condition;
        }
    }
    /**
     * @param string $columm
     *
     * @return $this
     */
    public function where($columm = '')
    {
        $columm = $columm ? $this->normalizeCollum($columm) : '';
        $c = array_filter($this->query->condition, function ($var) {
            return strpos($var, "WHERE") !== false;
        });
        if ($c) {
            $condition = " AND ";
        } else {
            $condition = "WHERE $columm";
        }
        $this->query->condition[  ] = $condition;
        $this->lastCondition = $condition;
        return $this;
    }

    public function and($columm = '')
    {
        if ($columm) {
            $columm = $this->normalizeCollum($columm);
            $this->setCondition("AND $columm");
        } else {
            $this->setCondition(" AND ");
        }

        return $this;
    }

    public function or($columm)
    {
        $columm = $this->normalizeCollum($columm);
        $this->setCondition("OR $columm");
        return $this;
    }

    public function between($var1, $var2)
    {
        $this->setCondition("BETWEEN $var1 AND $var2");
        return $this;
    }

    public function notBetween($var1, $var2)
    {
        $this->setCondition("NOT BETWEEN $var1 AND $var2");
        return $this;
    }

    public function equal($var)
    {
        $this->setCondition("= $var");
        return $this;
    }

    public function equalColumm($columm, $table = '')
    {
        $table = $table ?: $this->tablename;
        $columm = $this->normalizeCollum($columm, $table);
        $this->setCondition("= $columm");
        return $this;
    }

    public function in($var)
    {
        $this->setCondition("IN ($var)");
        return $this;
    }

    public function notIn($var)
    {
        $this->setCondition(" NOT IN ($var)");
        return $this;
    }

    public function not($var)
    {
        $this->setCondition("NOT $var");
        return $this;
    }

    public function major($var)
    {
        $this->setCondition("> $var");
        return $this;
    }

    public function minor($var)
    {
        $this->setCondition("< $var");
        return $this;
    }

    public function majorEq($var)
    {
        $this->setCondition(">= $var");
        return $this;
    }

    public function minorEq($var)
    {
        $this->setCondition("<= $var");
        return $this;
    }

    public function notEq($var)
    {
        $this->setCondition("!= $var");
        return $this;
    }
    public function is($var)
    {
        $this->setCondition("IS $var");
        return $this;
    }
    public function like($var)
    {
        $this->setCondition("LIKE '%$var%'");
        return $this;
    }
    public function notLike($var)
    {
        $this->setCondition("NOT LIKE '%$var%'");
        return $this;
    }
    public function start($var)
    {
        $this->setCondition("LIKE '$var%'");
        return $this;
    }
    public function end($var)
    {
        $this->setCondition("LIKE '%$var'");
        return $this;
    }
}
