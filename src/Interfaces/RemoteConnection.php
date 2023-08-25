<?php

namespace Komodo\Interlace\Interfaces;

use Komodo\Logger\Logger;

/*
|-----------------------------------------------------------------------------
| Komodo Interlace
|-----------------------------------------------------------------------------
|
| Desenvolvido por: Jhonnata Paixão (Líder de Projeto)
| Iniciado em: 08/2023
| Arquivo: Connection
.php
| Data da Criação Fri Aug 11 2023
| Copyright (c) 2023
|
|-----------------------------------------------------------------------------
|*/

interface RemoteConnection extends Connection
{
    /**
     * @param mixed $query
     * @param array<string,string> $params
     *
     * @return mixed
     */
    public function fetch($query, $params = [  ]);

    /**
     * @param mixed $query
     * @param array<string,string> $params
     *
     * @return mixed
     */
    public function fetchAll($query, $params = [  ]);

    /**
     * @param mixed $query
     * @param array<string,string> $params
     *
     * @return mixed
     */
    public function fetchColumm($query, $params = [  ]);

    /**
     * @param mixed $query
     * @param array<string,string> $params
     *
     * @return bool
     */
    public function execute($query, $params = [  ]);

    /**
     * @return string|int
     */
    public function lastInsertId();

    /**
     * Method create
     *
     * @param string $host [explicite description]
     * @param string $user [explicite description]
     * @param string $pass [explicite description]
     * @param string $dbname [explicite description]
     *
     * @return $this
     */
    public static function create($host, $user, $pass, $dbname);

    /**
     * Defini a entidade/tabela à ser usada pela conexão remota
     *
     * @param string $entity Nome da entidade na base de dados
     *
     * @return void
     */
    public function setEntity($entity);
}
