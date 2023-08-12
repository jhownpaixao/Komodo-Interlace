<?php

namespace Komodo\Interlace\QueryBuilder;

/*
|-----------------------------------------------------------------------------
| Komodo Interlace
|-----------------------------------------------------------------------------
|
| Desenvolvido por: Jhonnata Paixão (Líder de Projeto)
| Iniciado em: 08/2023
| Arquivo: PaginationQuery.php
| Data da Criação Fri Aug 11 2023
| Copyright (c) 2023
|
|-----------------------------------------------------------------------------
|*/




trait PaginationQuery
{

    /**
     * @param int $limit
     *
     * @return void
     */
    public function limit($limit)
    {
        $this->query->limit = $limit;
        return $this;
    }

    /**
     * @param int $offset
     *
     * @return void
     */
    public function offset($offset)
    {
        $this->query->offset = $offset;
        return $this;
    }
}
