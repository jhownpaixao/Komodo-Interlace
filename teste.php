<?php

include_once __DIR__ . '/vendor/autoload.php';

use Komodo\Interlace\Adapter\MySQLConnection;
use Komodo\Interlace\Enums\Op;
use Tests\DDD\App\Providers\ConnectionProvider;
use Tests\MVC\Models\Cliente;

$crmConnection = MySQLConnection::create('localhost', 'root', '', 'crm');

ConnectionProvider::setConnections([
    'crm' => $crmConnection,
]);

$clientes = Cliente::findAll([
    'where' => [
        Op::OR => [
            ["id" => 1],
            ['id' => [Op::BETWEEN => [9941, 9945]]],
        ],
        'updated_at' => Op::NOT_NULL
    ],
]);

foreach ($clientes as $cliente) {
    print_r($cliente->nome . PHP_EOL);
}
