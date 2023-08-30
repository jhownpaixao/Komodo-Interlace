<?php

namespace Komodo\Interlace\QueryBuilder;

/*
|-----------------------------------------------------------------------------
| Komodo Interlace
|-----------------------------------------------------------------------------
|
| Desenvolvido por: Jhonnata Paixão (Líder de Projeto)
| Iniciado em: 08/2023
| Arquivo: QueryBuilder.php
| Data da Criação Fri Aug 11 2023
| Copyright (c) 2023
|
|-----------------------------------------------------------------------------
|*/

use stdClass;

class QueryBuilder
{
    use JoinQuery;
    use WhereQuery;
    use OrderQuery;
    use GroupQuery;
    use PaginationQuery;
    use CRUDQuery;
    private $tablename;

    /**
     * @var string
     */
    private $lastCondition;

    private $query;

    public function __construct(string $tablename)
    {
        $this->tablename = $tablename;
        $this->query = new stdClass;

        // ?CRUD
        $this->query->crud = (object) [  ];
        $this->query->crud->operation = '';
        $this->query->crud->params = [  ];

        // ?Composition
        $this->query->join = [  ];
        $this->query->condition = [  ];
        $this->query->group = [  ];
        $this->query->order = [  ];
    }

    public function count($collumms = '*', $table = '')
    {

        if ('*' == $collumms) {
            $this->query->crud->params = [ "COUNT({$collumms})" ];
        } elseif (is_array($collumms)) {
            $collumms = array_map(function ($var) use ($table) {
                $col = $this->normalizeCollum($var, $table);
                return "COUNT({$col})";
            }, $collumms);

            $this->query->crud->params = $collumms;
        } else {
            $collumms = $this->normalizeCollum($collumms, $table);
            $this->query->crud->params = [ "COUNT({$collumms})" ];
        }
        return $this;
    }

    public function countDistinc($collumms = '*', $table = '')
    {
        $table = $table ?: $this->tablename;

        if ('*' == $collumms) {
            $this->query->crud->params = [ "COUNT( DISTINCT {$collumms} )" ];
        } elseif (is_array($collumms)) {
            $collumms = array_map(function ($var) use ($table) {
                $col = $this->normalizeCollum($var, $table);
                return "COUNT( DISTINCT {$col} )";
            }, $collumms);

            $this->query->crud->params = $collumms;
        } else {
            $collumms = $this->normalizeCollum($collumms, $table);
            $this->query->crud->params = [ "COUNT( DISTINCT {$collumms} )" ];
        }
        return $this;
    }

    public function distinct($collumms = '*', $table = '')
    {

        if ('*' == $collumms) {
            $this->query->crud->params = [ "DISTINCT ({$collumms})" ];
        } elseif (is_array($collumms)) {
            $collumms = array_map(function ($var) use ($table) {
                $col = $this->normalizeCollum($var, $table);
                return "DISTINCT ({$col})";
            }, $collumms);

            $this->query->crud->params = $collumms;
        } else {
            $collumms = $this->normalizeCollum($collumms, $table);
            $this->query->crud->params = [ "DISTINCT ({$collumms})" ];
        }
        return $this;
    }

    public function mount()
    {
        // ?CRUD
        $crudOperation = $this->query->crud->operation;
        $crudParams = implode(',', $this->query->crud->params);
        $crud = sprintf($crudOperation, $crudParams);

        // ?Data
        $join = implode(' ', $this->query->join);
        $condition = implode(' ', $this->query->condition);

        // ?Organization
        $group = $this->query->group ? "GROUP BY " . implode(',', $this->query->group) : '';
        $order = $this->query->order ? "ORDER BY " . implode(',', $this->query->order) : '';
        $limit = isset($this->query->limit) ? "LIMIT {$this->query->limit}" : "";
        $offset = isset($this->query->offset) ? "OFFSET {$this->query->offset}" : "";
        $organization = sprintf("%s %s %s %s", $group, $order, $limit, $offset);

        return sprintf("%s %s %s %s", $crud, $join, $condition, $organization);
    }
    
    public function normalizeCollum($collum, $table = '')
    {
        $normalized = '';
        switch ($table) {
            case '':
                $normalized = "`{$this->tablename}`.`{$collum}`";
                break;

            case 'null':
                $normalized = $collum;
                break;

            default:
                $normalized = "`{$table}`.`{$collum}`";
                break;
        }
        return $normalized;
    }
    public function addConditionQuery($query)
    {
        $this->query->condition[  ] = $query;
    }
}
