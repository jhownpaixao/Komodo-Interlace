<?php

namespace Komodo\Interlace\Enums;

/*
|-----------------------------------------------------------------------------
| Komodo Interlace
|-----------------------------------------------------------------------------
|
| Desenvolvido por: Jhonnata Paixão (Líder de Projeto)
| Iniciado em: 08/2023
| Arquivo: Op.php
| Data da Criação Fri Aug 11 2023
| Copyright (c) 2023
|
|-----------------------------------------------------------------------------
|*/

abstract class Op extends Enum
{
// #Number comparation
    const EQ = "%s = %s";
    const MAJOR = "%s > %s";
    const MINOR = "%s < %s";
    const MIN_EQ = "%s <= %s";
    const MAJ_EQ = "%s >= %s";
    const NOT_EQ = "%s != %s";
    const BETWEEN = "%s BETWEEN %s AND %s";
    const NOT_BETWEEN = "%s NOT BETWEEN '%s' AND '%s'";

    // #Comparation
    const NULL = "%s IS NULL";
    const NOT_NULL = "%s IS NOT NULL";
    const IS = "%s IS %s";
    const LIKE = "%s LIKE '%%%s%%'";
    const NOT_LIKE = "%s NOT LIKE '%%%s%%'";

    const START = "%s LIKE '%s%%'";
    const END = "%s LIKE '%%%s'";

    const IN = "%s IN (%s)";
    const NOT_IN = "%s NOT IN (%s)";

    const  OR  = "%s OR %s";
    const  AND  = "%s AND %s";

    // #Functions
    const DATE = "DATE(%s)";
    const CUR_DATE = "curdate()";
    const DISTINCT = "DISTINCT(%s)";
    const COUNT = "COUNT(%s)";
    const COUNT_DISTINCT = "COUNT(DISTINCT(%s))";
}
