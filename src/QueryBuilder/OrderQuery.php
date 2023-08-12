<?php
namespace Komodo\Interlace\QueryBuilder;

/*
|-----------------------------------------------------------------------------
| Komodo Interlace
|-----------------------------------------------------------------------------
|
| Desenvolvido por: Jhonnata Paixão (Líder de Projeto)
| Iniciado em: 08/2023
| Arquivo: OrderQuery.php
| Data da Criação Fri Aug 11 2023
| Copyright (c) 2023
|
|-----------------------------------------------------------------------------
|*/


trait OrderQuery
{

    /**
     * @param string $table
     *
     * @return $this
     */
    public function orderBy($orders, $table = '')
    {
        $table = $table ?: $this->tablename;
        foreach ($orders as $columm => $order) {
            $columm = $this->normalizeCollum($columm, $table);
            $this->query->order[  ] = "$columm $order";
        }
        return $this;
    }
}
