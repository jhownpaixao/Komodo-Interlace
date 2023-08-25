<?php

namespace Komodo\Interlace\Interfaces;

/*
|-----------------------------------------------------------------------------
| Komodo Interlace
|-----------------------------------------------------------------------------
|
| Desenvolvido por: Jhonnata Paixão (Líder de Projeto)
| Iniciado em: 08/2023
| Arquivo: Connection.php
| Data da Criação Fri Aug 11 2023
| Copyright (c) 2023
|
|-----------------------------------------------------------------------------
|*/

use Komodo\Logger\Logger;

interface Connection
{
    /**
     * @param mixed $query
     * @param array<string,string|int|bool> $params
     *
     * @return mixed
     */
    public function fetch($query, $params = [  ]);

    /**
     * @param mixed $query
     * @param array<string,string|int|bool> $params
     *
     * @return mixed
     */
    public function fetchAll($query, $params = [  ]);

    /**
     * @param mixed $query
     * @param array<string,string|int|bool> $params
     *
     * @return mixed
     */
    public function fetchColumm($query, $params = [  ]);

    /**
     * @param mixed $query
     * @param array<string,string|int|bool> $params
     *
     * @return bool
     */
    public function execute($query, $params = [  ]);

    /**
     * @return string|int
     */
    public function lastInsertId();

    /**
     * setLogger
     *
     * @param  Logger $logger
     * @return void
     */
    public function setLogger($logger);
}
