<?php
namespace Tests\MVC\Models;

class Cliente extends \Komodo\Interlace\Model
{

    public $nome;
    public $cpf;

    protected function setup()
    {

        return [
            'connection' => \Komodo\Interlace\Providers\ConnectionProvider::getConnection('crm'), #Connection
         ];
    }
}
