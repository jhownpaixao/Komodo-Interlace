<?php
namespace Komodo\Interlace\QueryBuilder;

/*
|-----------------------------------------------------------------------------
| Komodo Interlace
|-----------------------------------------------------------------------------
|
| Desenvolvido por: Jhonnata Paixão (Líder de Projeto)
| Iniciado em: 08/2023
| Arquivo: CRUDQuery.php
| Data da Criação Wed Aug 30 2023
| Copyright (c) 2023
|
|-----------------------------------------------------------------------------
|*/

trait CRUDQuery
{
    /**
     * @param string|array $collumms
     *
     * @return $this
     */
    public function select($collumms, $table = '', $prefix = '')
    {
        $table = $table ?: $this->tablename;
        $this->query->crud->operation = "SELECT %s FROM `{$this->tablename}`";

        if (is_array($collumms)) {
            $collumms = array_map(function ($var) use ($table, $prefix) {
                $c = $this->normalizeCollum($var, $table);
                return $prefix ? "$c AS '$prefix$var'" : $c;
            }, $collumms);
            $collumms = array_values($collumms);

            $this->query->crud->params = array_merge($this->query->crud->params, $collumms);
        } else {
            
            $collumms = $this->normalizeCollum($collumms, $table);
            $this->query->crud->params[  ] = $prefix ? "$collumms AS '$prefix$collumms'" : $collumms;
        }
        return $this;
    }

    public function insert($collumms, $table = '')
    {
        $table = $table ?: $this->tablename;
        if (!is_array($collumms)) {
            $collumms = [ $collumms ];
        }
        $collumms = implode("`,`", $collumms);
        $this->query->crud->operation = "INSERT INTO`{$table}`(`$collumms`) VALUES (%s)";
        return $this;
    }

    public function delete($table = '')
    {
        $table = $table ?: $this->tablename;
        $this->query->crud->operation = "DELETE FROM `{$table}`";
        return $this;
    }

    /**
     * @return $this
     */
    public function update($table = '')
    {
        $table = $table ?: $this->tablename;
        $this->query->crud->operation = "UPDATE `{$table}`";
        return $this;
    }

    /**
     * @param array $data
     *
     * @return $this
     */
    public function set($data)
    {
        $d = [  ];
        foreach ($data as $collum => $value) {
            $d[  ] = "`{$this->tablename}`.`{$collum}`=$value";
        }
        $d = implode(',', $d);
        $this->query->crud->operation .= " SET $d";
        return $this;
    }

    public function from($table = '')
    {
        $table = $table ?: $this->tablename;
        $this->query->crud->operation = $this->query->crud->operation . " FROM `{$table}`";
        return $this;
    }

    /**
     * @param array|string $values
     *
     * @return mixed
     */
    public function values($values)
    {
        if (is_array($values)) {
            $this->query->crud->params = array_merge($this->query->crud->params, $values);
        } else {
            $this->query->crud->params[  ] = $values;
        }
        return $this;
    }
}
