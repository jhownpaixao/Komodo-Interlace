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
| Arquivo: HTMLTransport.php
| Data da Criação Tue Aug 01 2023
| Copyright (c) 2023
|
|-----------------------------------------------------------------------------
|*/

class HTMLTransport implements Transport
{
    /**
     * @var string;
     */
    private $tag;

    public function __construct($tag = null)
    {
        $this->tag = $tag ?: 'pre';
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
        $content = '| ' . json_encode($data->content, true);
        $level = $data->level;
        $timestamp = $data->timestamp;

        $log = "[$level][$oringi][$name][$timestamp]:: $msg $content";
        $msg = "<{$this->tag}>$log</{$this->tag}>";
        echo $msg . PHP_EOL;
        return $msg;
    }
}
