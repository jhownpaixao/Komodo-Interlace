<?php

namespace Komodo\Logger\Transports;

use Komodo\Logger\LogData;

/*
|-----------------------------------------------------------------------------
| Komodo Logger
|-----------------------------------------------------------------------------
|
| Desenvolvido por: Jhonnata Paixão (Líder de Projeto)
| Iniciado em: 15/10/2022
| Arquivo: Transport.php
| Data da Criação Tue Aug 01 2023
| Copyright (c) 2023
|
|-----------------------------------------------------------------------------
|*/

interface Transport
{

    /**
     * @param LogData $data
     *
     * @return string
     */
    public function log($data);
}
