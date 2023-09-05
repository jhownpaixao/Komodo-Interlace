<?php

include_once __DIR__ . '/vendor/autoload.php';

/* use Komodo\Interlace\Adapter\MySQLConnection;
use Komodo\Interlace\Enums\Op;
use \Komodo\Interlace\Providers\ConnectionProvider;
use Tests\MVC\Models\Cliente;

$crmConnection = MySQLConnection::create('localhost', 'root', '', 'crm');

ConnectionProvider::setConnections([
    'crm' => $crmConnection,
]);

$clientes = Cliente::findAll([
    'where' => [
        'created_at' => [
            Op::DATE => ''
        ]
    ]
]);
var_dump($clientes);

foreach ($clientes as $cliente) {
    print_r($cliente->nome . PHP_EOL);
} */

$TESTE = "`date`.`ee`='sdsdsd' AND `sdsds`.`sdsd`='sdsds'";




var_dump(preg_replace("/`(.*?\.`.*?)`/", 'DATE(${0})', $TESTE));
