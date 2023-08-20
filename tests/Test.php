<?php

namespace Tests;

use Komodo\Configurator\ConfigurationProvider;
use Komodo\Interlace\Adapter\MySQLConnection;
use Komodo\Interlace\Interfaces\Repository;
use Komodo\Interlace\Model;
use PHPUnit\Framework\TestCase;
use Tests\DDD\App\Providers\ConnectionProvider;
use Tests\DDD\Domain\Cliente\Cliente;
use Tests\DDD\Infra\Repositories\ClienteRepository;
use Tests\MVC\Models\Cliente as ModelsCliente;

$id = null;
$cpf = null;
class Test extends TestCase
{
    /* DDD */
    public function testCreate()
    {
        global $id, $cpf;
        $idd =  &$id;
        $cpff =  &$cpf;

        $cpff = date('His');
        $this->init();

        $repository = ClienteRepository::load();
        $this->assertInstanceOf(Repository::class, $repository);

        $cliente = new Cliente();
        $this->assertInstanceOf(Cliente::class, $cliente);

        $cliente->nome = 'Teste';
        $cliente->cpf = $cpff;

        $this->assertNotFalse($repository->persist($cliente));
        $this->assertNotNull($cliente->id);
        $idd = $cliente->id;
    }
    public function testEntity()
    {
        global $id, $cpf;

        $this->init();
        $repository = ClienteRepository::load();
        $this->assertInstanceOf(Repository::class, $repository);

        $cliente = $repository->findByPk($id);
        $this->assertInstanceOf(Cliente::class, $cliente);
        $this->assertIsString($cliente->nome);
        $this->assertEquals($cliente->cpf, $cpf);
    }
    public function testUpdate()
    {
        global $id;

        $this->init();

        $repository = ClienteRepository::load();
        $this->assertInstanceOf(Repository::class, $repository);

        $cliente = $repository->findByPk($id);
        $this->assertInstanceOf(Cliente::class, $cliente);

        $newCpf = date('His');
        $cliente->cpf = $newCpf;
        $this->assertTrue($repository->update($cliente));

        $cliente = $repository->findByPk($id);
        $this->assertInstanceOf(Cliente::class, $cliente);
        $this->assertEquals($cliente->cpf, $newCpf);
    }
    public function testDelete()
    {
        global $id;

        $this->init();

        $repository = ClienteRepository::load();
        $this->assertInstanceOf(Repository::class, $repository);

        $cliente = $repository->findByPk($id);
        $this->assertInstanceOf(Cliente::class, $cliente);
        $this->assertTrue($repository->delete($cliente));
        $this->assertNull($cliente->nome);
        $this->assertNull($cliente->cpf);

        $cliente = $repository->findByPk($id);
        $this->assertNull($cliente);
    }
    private function init()
    {
        $crmConnection = MySQLConnection::create('localhost', 'root', '', 'crm');
        $this->assertInstanceOf(MySQLConnection::class, $crmConnection);

        $azcallConnection = MySQLConnection::create('localhost', 'root', '', 'azcall');
        $this->assertInstanceOf(MySQLConnection::class, $azcallConnection);

        ConnectionProvider::setConnections([
            'crm' => $crmConnection,
            'azcall' => $azcallConnection,
         ]);
    }

    /* MVC */
    private function initMVC()
    {
        ConfigurationProvider::init(__DIR__ . '/MVC/Config');
        Model::init([  ]);
    }
    public function testMVCCreate()
    {
        $this->initMVC();
        global $id, $cpf;
        $idd =  &$id;
        $cpff =  &$cpf;

        $cpff = date('His');

        $cliente = ModelsCliente::create([
            "nome" => 'Teste',
            "cpf" => $cpf,
         ]);
        $this->assertInstanceOf(ModelsCliente::class, $cliente);
        $this->assertEquals($cpf, $cliente->cpf);
        $idd = $cliente->id;
    }
    public function testMVCGet()
    {
        global $id;

        $cliente = ModelsCliente::findByPk($id);
        $this->assertInstanceOf(ModelsCliente::class, $cliente);
    }
    public function testMVCUpdate()
    {
        global $id;

        $cliente = ModelsCliente::findByPk($id);
        $this->assertInstanceOf(ModelsCliente::class, $cliente);
        $newCpf = date('His');

        $cliente->cpf = $newCpf;

        $this->assertTrue($cliente->update());

        $cliente = ModelsCliente::findByPk($id);
        $this->assertInstanceOf(ModelsCliente::class, $cliente);
        $this->assertEquals($cliente->cpf, $newCpf);
    }
    public function testMVCDelete()
    {
        global $id;

        $cliente = ModelsCliente::findByPk($id);
        $this->assertInstanceOf(ModelsCliente::class, $cliente);
        var_dump($cliente);
        $this->assertTrue($cliente->delete());
    }
}
