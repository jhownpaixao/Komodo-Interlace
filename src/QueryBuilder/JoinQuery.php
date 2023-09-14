<?php
namespace Komodo\Interlace\QueryBuilder;

/*
|-----------------------------------------------------------------------------
| Komodo Interlace
|-----------------------------------------------------------------------------
|
| Desenvolvido por: Jhonnata Paixão (Líder de Projeto)
| Iniciado em: 08/2023
| Arquivo: JoinQuery.php
| Data da Criação Fri Aug 11 2023
| Copyright (c) 2023
|
|-----------------------------------------------------------------------------
|*/

trait JoinQuery
{

    /**
     * @param string $table
     *
     * @return $this
     */
    public function innerJoin($table = '')
    {
        $table = $table ?: $this->tablename;
        $this->query->join[  ] = "INNER JOIN `$table`";
        return $this;
    }

    /**
     * @param string $table
     *
     * @return $this
     */
    public function leftJoin($table = '')
    {
        $table = $table ?: $this->tablename;
        $this->query->join[  ] = "LEFT JOIN `$table`";
        return $this;
    }

    /**
     * @param string $table
     *
     * @return $this
     */
    public function rightJoin($table = '')
    {
        $table = $table ?: $this->tablename;
        $this->query->join[  ] = "RIGHT JOIN `$table`";
        return $this;
    }

    /**
     * @param string $table
     *
     * @return $this
     */
    public function crossJoin($table = '')
    {
        $table = $table ?: $this->tablename;
        $this->query->join[  ] = "CROSS JOIN `$table`";
        return $this;
    }

    public function on($columm, $table = '')
    {
        $columm = $this->normalizeCollum($columm, $table);
        $condition = "ON $columm";
        $this->query->join[  ] = $condition;
        $this->lastCondition = $condition;
        return $this;
    }

    public function addOnCondition($query)
    {
        $this->query->join[  ] = $query;
        return $this;
    }
}
