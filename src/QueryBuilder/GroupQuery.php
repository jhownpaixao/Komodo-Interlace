<?php
namespace Komodo\Interlace\QueryBuilder;

/*
|-----------------------------------------------------------------------------
| Komodo Interlace
|-----------------------------------------------------------------------------
|
| Desenvolvido por: Jhonnata Paixão (Líder de Projeto)
| Iniciado em: 08/2023
| Arquivo: GroupQuery.php
| Data da Criação Fri Aug 11 2023
| Copyright (c) 2023
|
|-----------------------------------------------------------------------------
|*/


trait GroupQuery
{

    /**
     * @param string $table
     *
     * @return $this
     */
    public function groupBy($columm, $table = '')
    {
        $table = $table ?: $this->tablename;
        $columm = $this->normalizeCollum($columm, $table);
        $this->query->group[  ] = $columm;
        return $this;
    }
}
