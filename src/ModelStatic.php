<?php

namespace Komodo\Interlace;

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


use Komodo\Logger\Logger;
use Komodo\Interlace\Adapter\MysqlDatabase;
use Komodo\Interlace\Bases\ModelStaticBase;
use Komodo\Interlace\Interfaces\DatabaseAdapter;
use ReflectionClass;
use Throwable;

abstract class ModelStatic
{
    use ModelStaticBase;

    /**
     *
     * @var DatabaseAdapter[] Objeto de conexão da db
     */
    protected static $repositories;

    /**
     * @var object
     */
    private static $config;

    /**
     * @var Logger
     */
    public static $logger;

    // #Private Methods
    /**
     * Inicializa o banco de dados e carrega a entidade atual
     *
     * @return void
     */
    public static function init($logger = null)
    {
        self::$logger = $logger ? clone $logger : new Logger;
        self::$logger->register('PHP-Crm\\Model');
        if (self::$repositories) {
            return;
        }

        try {
            $config = require_once __DIR__ . '/../../Config/Database.php';
            if (is_string(array_key_first($config))) {
                foreach ($config as $name => $params) {
                    self::$repositories[$name] = new MysqlDatabase(
                        $params['host'],
                        $params['user'],
                        $params['pass'],
                        $params['database']
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
     * @return DatabaseAdapter
     */
    public static function getRepository($name = '')
    {
        return $name ? self::$repositories[$name] : array_values(self::$repositories)[0];
    }

    /**
     * @param string $name Name of repository in config
     *
     * @return DatabaseAdapter[]
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
