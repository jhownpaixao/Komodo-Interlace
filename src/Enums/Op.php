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


use MyCLabs\Enum\Enum;

enum Op: string
{
    // #Number comparation
    case eq = "%s = '%s'";
    case major = "%s > '%s'";
    case minor = "%s < '%s'";
    case minEq = "%s <= '%s'";
    case majEq = "%s >= '%s'";
    case notEq = "%s != '%s'";
    case between = "%s BETWEEN %s AND %s";
    case notBetween = "%s NOT BETWEEN '%s' AND '%s'";

    // #Comparation
    case not = "%s NOT '%s'";
    case is = "%s IS '%s'";
    case like = "%s LIKE '%%%s%%'";
    case notLike = "%s NOT LIKE '%%%s%%'";

    case start = "%s LIKE '%s%%'";
    case end = "%s LIKE '%%%s'";

    case in = "%s IN (%s)";
    case notIn = "%s NOT IN (%s)";
    case or  = "%s OR %s";
}
