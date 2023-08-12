<?php

namespace Komodo\Logger;

/*******************************************************************************************
Komodo Lib - Logger
____________________________________________________________________________________________
 *
 * Desenvolvido por: Jhonnata Paixão (Líder de Projeto)
 * Iniciado em: 15/10/2022
 * Arquivo: Logger.php
 * Data da Criação Sun May 14 2023
 * Copyright (c) 2023
 *
 *********************************************************************************************/
use Komodo\Logger\LogData;
use Komodo\Logger\Transports\HTMLTransport;
use Komodo\Logger\Transports\Transport;

class Logger
{
    private $name;
    /**
     * @var Transport
     */
    private $transport;

    /**
     * @var string
     */
    private $lastLogMsg;

    /**
     * @var LogData
     */
    private $lastLogFull;

    /**
     * @param Transport|null $transport
     */
    public function __construct($transport = null)
    {
        $this->transport = $transport ?: new HTMLTransport('pre');
    }

    public function register($name)
    {
        $this->name = $name;
    }

    private function log($data, $msg, $level)
    {
        $bt = debug_backtrace();
        $logData = new LogData(
            $this->name ?: 'ANONYMOUS',
            $msg,
            basename($bt[ 1 ][ 'file' ]),
            $data,
            $level,
            date('H:i:s')
        );
        $msg = $this->transport->log($logData);
        $this->lastLogFull = $logData;
        $this->lastLogMsg = $msg;
    }

    public function logAndBreack($data, $msg = null)
    {
        $this->log($data, $msg, "FORCED");
        die();
    }

    public function error($data, $msg = null)
    {
        $this->log($data, $msg, "ERROR");
    }

    public function info($data, $msg = null)
    {
        $this->log($data, $msg, "INFO");
    }

    public function warn($data, $msg = null)
    {
        $this->log($data, $msg, "WARN");
    }

    public function trace($data, $msg = null)
    {
        $this->log($data, $msg, "TRACE");
    }

    public function all($data, $msg = null)
    {
        $this->log($data, $msg, "ERROR");
    }

    public function debug($data, $msg = null)
    {
        $this->log($data, $msg, "DEBUG");
    }

    /**
     * @return string
     */
    public function getLastMessage()
    {
        return $this->lastLogMsg;
    }

    /**
     * @return LogData
     */
    public function getLastLog()
    {
        return $this->lastLogFull;
    }
}
