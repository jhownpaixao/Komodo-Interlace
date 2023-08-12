<?php

namespace Komodo\Logger;

/*
|-----------------------------------------------------------------------------
| Komodo Logger
|-----------------------------------------------------------------------------
|
| Desenvolvido por: Jhonnata Paixão (Líder de Projeto)
| Iniciado em: 15/10/2022
| Arquivo: LogObject.php
| Data da Criação Tue Aug 01 2023
| Copyright (c) 2023
|
|-----------------------------------------------------------------------------
|*/

class LogData
{
    public $name;
    public $msg;
    public $oringin;
    
    /**
     * $content
     *
     * @var mixed
     */
    public $content;
    public $level;
    public $timestamp;

    public function __construct($name, $msg, $oringin, $content, $level, $timestamp)
    {
        $this->name = $name;
        $this->msg = $msg;
        $this->oringin = $oringin;
        $this->content = $content;
        $this->level = $level;
        $this->timestamp = $timestamp;
    }
}
