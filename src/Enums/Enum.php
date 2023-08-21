<?php

namespace Komodo\Interlace\Enums;

/*
|-----------------------------------------------------------------------------
| Komodo Interlace
|-----------------------------------------------------------------------------
|
| Desenvolvido por: Jhonnata Paixão (Líder de Projeto)
| Iniciado em: 08/2023
| Arquivo: Enum.php
| Data da Criação Mon Aug 21 2023
| Copyright (c) 2023
|
|-----------------------------------------------------------------------------
|*/

abstract class Enum
{

    final public static function isValid($value)
    {
        $reflection = new \ReflectionClass(static::class);
        $consts = $reflection->getConstants();
        return in_array($value, $consts);
    }
}
