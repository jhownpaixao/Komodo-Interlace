<?php
namespace Tests\MVC\Models;

use Cliente;
use Komodo\Interlace\Association;
use Komodo\Interlace\Enums\AssociationType;
use Komodo\Interlace\Model;

/*
|-----------------------------------------------------------------------------
| Linxsys - PHP CRM
|-----------------------------------------------------------------------------
|
| Desenvolvido por: Jhonnata Paixão (Líder de Projeto)
| Iniciado em: 15/10/2022
| Arquivo: Acionamento.php
| Data da Criação Wed Aug 23 2023
| Copyright (c) 2023
|
|-----------------------------------------------------------------------------
|*/

/**
 * @property \Tests\DDD\Domain\Cliente\Cliente $cliente
 */
class Acionamento extends Model
{
    public $clienteId;
    public $ownerId;
    public $triggered;
    public $filter;
    public $created_by;

    protected function associate()
    {
        return [
            'cliente' => new Association(AssociationType::BELONGS_TO, Cliente::class, "clienteId", "id"),
         ];
    }
}
