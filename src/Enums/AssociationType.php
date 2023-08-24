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


abstract class AssociationType extends Enum
{
    const HAS_ONE = "has_one";
    const HAS_MANY = "has_many";
    const BELONGS_TO = "blg_one";
    const BELONGS_TO_MANY = "blg_many";
}
