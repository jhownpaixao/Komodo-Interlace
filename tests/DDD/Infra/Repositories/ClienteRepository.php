<?php
namespace Tests\DDD\Infra\Repositories;

use Komodo\Interlace\Repositories\RemoteRepository;
use Tests\DDD\App\Providers\ConnectionProvider;
use Tests\DDD\Domain\Cliente\Cliente;

/**
 * @extends RemoteRepository<Cliente>
 */
class ClienteRepository extends RemoteRepository
{
    protected function setup()
    {

        return [
            'entity' => 'clientes', #Tablename/Entity in remote database
            'entityClass' => Cliente::class, #Representative class of the entity
            'connection' => ConnectionProvider::getConnection('crm'), #Connection
         ];
    }
}
