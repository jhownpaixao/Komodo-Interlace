<?php

namespace Komodo\Interlace\Enums;

/*
|-----------------------------------------------------------------------------
| Komodo Interlace
|-----------------------------------------------------------------------------
|
| Desenvolvido por: Jhonnata Paixão (Líder de Projeto)
| Iniciado em: 08/2023
| Arquivo: AssociationType.php
| Data da Criação Fri Aug 11 2023
| Copyright (c) 2023
|
|-----------------------------------------------------------------------------
|*/


enum AssociationType: string
{
    case hasOne = "has_one";
    case hasMany = "has_many";
    case belongsTo = "blg_one";
    case belongsToMany = "blg_many";
}
