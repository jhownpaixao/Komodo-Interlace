<?php

/*******************************************************************************************
Komodo Lib - Logger
____________________________________________________________________________________________
 *
 * Desenvolvido por: Jhonnata Paixão (Líder de Projeto)
 * Iniciado em: 15/10/2022
 * Arquivo: HTMLTransport.php
 * Data da Criação Fri Jul 14 2023
 * Copyright (c) 2023
 *
 *********************************************************************************************/

namespace Komodo\Logger\Transports;

use Komodo\Logger\LogData;

/**
 * [Description FileTransport]
 */
class FileTransport implements Transport
{
    /**
     * asasdasd
     *
     * @var string
     */
    private $file;

    /**
     * @var string
     */
    private $path;

    public function __construct($path = './logs')
    {
        $this->path = $path;
    }

    /**
     * @param LogData $data
     *
     * @return string
     */
    public function log($data)
    {
        $name = $data->name;
        $msg = $data->msg;
        $oringi = $data->oringin;
        $content = '| ' . json_encode($data->content);
        $level = $data->level;
        $timestamp = $data->timestamp;

        $log = "[$level][$name][$oringi][$timestamp]:: $msg $content" . PHP_EOL;
        $file = $this->prepare($level);
        file_put_contents($file, $log, FILE_APPEND);

        /* process ALL*/
        $file = $this->prepare('ALL');
        file_put_contents($file, $log, FILE_APPEND);
        return $log;
    }

    /**
     * @param mixed $level
     *
     * @return [type]
     */
    private function prepare($level)
    {
        $level = strtolower($level);
        $absolute = $this->path . '/' . $level . '.log';
        if (!is_dir($this->path)) {
            mkdir($this->path, 0777, true);
        }

        if (!file_exists($absolute)) {
            fopen($absolute, 'w');
            /*  return !!file_put_contents($absolute, ''); */
        }

        return $absolute;
    }
}
