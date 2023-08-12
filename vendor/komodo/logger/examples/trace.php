<?php

use Komodo\Logger\Transports\FileTransport;

require_once('../vendor/autoload.php');

/*******************************************************************************************
 Komodo Lib - Logger EXAMPLE
 ____________________________________________________________________________________________
 *
 * Desenvolvido por: Jhonnata Paixão (Líder de Projeto)
 * Iniciado em: 15/10/2022
 * Arquivo: trace.php
 * Data da Criação Fri Jul 14 2023
 * Copyright (c) 2023
 *
 *********************************************************************************************/


$logger = new Komodo\Logger\Logger(new FileTransport);
$logger->register('traceTest');

$logger->trace(['teste'], 'mensagem de teste');
$logger->info(['info'], 'mensagem de teste');

/* $logger->trace(['trusted' => true], 'mensagem de teste');
$logger->trace('testeeee');
$logger->trace('testeeeessssssssssss'); */
