<?php

namespace Komodo\Interlace\Static;

/*
|-----------------------------------------------------------------------------
| Komodo Interlace
|-----------------------------------------------------------------------------
|
| Desenvolvido por: Jhonnata Paixão (Líder de Projeto)
| Iniciado em: 08/2023
| Arquivo: ModelStatic.php
| Data da Criação Fri Aug 11 2023
| Copyright (c) 2023
|
|-----------------------------------------------------------------------------
|*/

use Komodo\Configurator\ConfigurationProvider;
use Komodo\Interlace\Adapter\MySQLConnection;
use Komodo\Interlace\Interfaces\Connection;
use Komodo\Logger\Logger;
use ReflectionClass;
use Throwable;

abstract class ModelRepository
{
    /**
     *
     * @var Connection[] Conexões
     */
    protected static $repositories;

    /**
     * @var Logger
     */
    public static $logger;

    // #Private Methods

    /**
     * Method init
     *
     * @param array $params $params Parametros de conexões
     * @param Logger $logger $logger [explicite description]
     *
     * @return void
     */
    public static function init($params, $logger = null)
    {
        if (self::$repositories) {
            return;
        }

        self::$logger = $logger ? clone $logger : new Logger;
        self::$logger->register(static::class);

        try {
            if (class_exists(ConfigurationProvider::class)) {
                $configs = [ 'Interlace', 'interlace', 'Database', 'database', 'Model', 'model', 'ORM', 'orm', 'Connection', 'connection' ];
                foreach ($configs as $config) {
                    $config = ConfigurationProvider::get($config);
                  
                    if ($config) {
                        $config = (array) $config;
                       
                        break;
                    }
                }
            } elseif (isset($params[ 'databases' ])) {
                $config = $params[ 'databases' ];
            } else {
                throw new \Exception('No connection settings reported');
            }

            if (!$config) {
                throw new \Exception('Unable to resolve connection settings automatically');
            }

            if (is_string(array_key_first($config))) {
                foreach ($config as $name => $params) {
                    $params = (array) $params;
                    self::$repositories[ $name ] = MySQLConnection::create(
                        $params[ 'host' ],
                        $params[ 'user' ],
                        $params[ 'password' ],
                        $params[ 'database' ]
                    );
                }
            }
        } catch (Throwable $th) {
            self::$logger->error($th->getMessage());
            throw $th;
        }
    }

    //#Public Methods
    /**
     * @param string $name Name of repository in config
     *
     * @return Connection
     */
    public static function getRepository($name = '')
    {
        return $name?self::$repositories[ $name ]:array_values(self::$repositories)[ 0 ];
    }

    /**
     * @param string $name Name of repository in config
     *
     * @return Connection[]
     */
    public static function getRepositories($name = '')
    {
        return self::$repositories;
    }

    /**
     * @return array<string>
     */
    public static function getProperties()
    {
        $class = get_called_class();
        $client = new ReflectionClass($class);

        $properties = array_map(function ($var) {
            return $var->name;
        }, $client->getProperties(\ReflectionProperty::IS_PUBLIC));
        return $properties;
    }
}
