<?php
namespace Tests\DDD\App\Providers;

/*
|-----------------------------------------------------------------------------
| Komodo Interlace
|-----------------------------------------------------------------------------
|
| Desenvolvido por: Jhonnata Paixão (Líder de Projeto)
| Iniciado em: 08/2023
| Arquivo: RemoteConnectionProvider.php
| Data da Criação Sat Aug 19 2023
| Copyright (c) 2023
|
|-----------------------------------------------------------------------------
|*/
use Komodo\Interlace\Interfaces\LocalConnection;
use Komodo\Interlace\Interfaces\RemoteConnection;

final class ConnectionProvider
{

    /**
     * List of adapterss
     *
     * @var array<LocalConnection|RemoteConnection>
     */
    protected static $adapters = [  ];

    /**
     * Method getConnection
     *
     * @param $name $name [explicite description]
     *
     * @return LocalConnection|RemoteConnection
     */
    public static function getConnection($name)
    {
        if (self::$adapters[ $name ]) {
            return self::$adapters[ $name ];
        }
        throw new \Exception('Connection adapter not found: $name');
    }

    /**
     * Method setConnection
     *
     * @param string $name Name of connection
     * @param LocalConnection|RemoteConnection $adapter Connection adapter
     *
     * @return void
     */
    public static function setConnection($name, $adapter)
    {
        self::$adapters[ $name ] = $adapter;
    }

    /**
     * Method setConnections
     *
     * @param array<string,LocalConnection|RemoteConnection>$connections $connections [explicite description]
     *
     * @return void
     */
    public static function setConnections($connections)
    {
        foreach ($connections as $name => $adapter) {
            if (!$adapter instanceof LocalConnection && !$adapter instanceof RemoteConnection) {
                throw new \Exception('The object entered is not a valid connection adapter');
            }
            self::$adapters[ $name ] = $adapter;
        }
    }
}
