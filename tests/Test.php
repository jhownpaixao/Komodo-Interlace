<?php

namespace Tests;

use Komodo\Configurator\ConfigurationProvider;
use Komodo\Interlace\Adapter\MySQLConnection;
use Komodo\Interlace\Interfaces\Repository;
use Komodo\Interlace\Providers\ConnectionProvider;
use PHPUnit\Framework\TestCase;
use Tests\DDD\Domain\Cliente\Cliente;
use Tests\DDD\Infra\Repositories\ClienteRepository;
use Tests\MVC\Models\Cliente as ModelsCliente;

$id = null;
$cpf = null;
class Test extends TestCase
{
    private function loadConnections()
    {

        $connectionsConfig = ConfigurationProvider::get('Database');
        foreach ((array) $connectionsConfig as $name => $auth) {
            $connection = MySQLConnection::create($auth->host, $auth->user, $auth->password, $auth->database);
            ConnectionProvider::setConnection($name, $connection);
        }
    }
    private function init()
    {
        ConfigurationProvider::init(__DIR__ . '/Config');

        $this->loadConnections();
    }

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

    /* MVC */
    public function testMVCCreate()
    {
        $this->init();
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
        $this->assertTrue($cliente->delete());
    }

    /* MVC STATIC */
    public function testMVCStaticCreate()
    {
        $this->init();
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
    public function testMVCStaticDelete()
    {
        global $id;
        $delete = ModelsCliente::deleteAll([
            'where' => [
                "id" => $id,
             ],
         ]);
        $this->assertTrue($delete);
    }
}
