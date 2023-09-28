<?php

namespace Komodo\Interlace\Adapter;

/*
|-----------------------------------------------------------------------------
| Komodo Interlace
|-----------------------------------------------------------------------------
|
| Desenvolvido por: Jhonnata Paixão (Líder de Projeto)
| Iniciado em: 08/2023
| Arquivo: MysqlDatabase.php
| Data da Criação Fri Aug 11 2023
| Copyright (c) 2023
|
|-----------------------------------------------------------------------------
|*/

use Komodo\Interlace\Interfaces\RemoteConnection;
use Komodo\Logger\Logger;
use PDO;

class MySQLConnection implements RemoteConnection
{
    const TYPE_TINY = "TINY";
    const TYPE_SHORT = "SHORT";
    const TYPE_LONG = "LONG";
    const TYPE_FLOAT = "FLOAT";
    const TYPE_DOUBLE = "DOUBLE";
    const TYPE_TIMESTAMP = "TIMESTAMP";
    const TYPE_LONGLONG = "LONGLONG";
    const TYPE_INT24 = "INT24";
    const TYPE_DATE = "DATE";
    const TYPE_TIME = "TIME";
    const TYPE_DATETIME = "DATETIME";
    const TYPE_YEAR = "YEAR";
    const TYPE_ENUM = "ENUM";
    const TYPE_SET = "SET";
    const TYPE_TINY_BLOB = "TINYBLOB";
    const TYPE_MEDIUM_BLOB = "MEDIUMBLOB";
    const TYPE_LONG_BLOB = "LONGBLOB";
    const TYPE_BLOB = "BLOB";
    const TYPE_VAR_STRING = "VAR_STRING";
    const TYPE_STRING = "STRING";
    const TYPE_NULL = "NULL";
    const TYPE_NEWDATE = "NEWDATE";
    const TYPE_INTERVAL = "INTERVAL";
    const TYPE_GEOMETRY = "GEOMETRY";

    /** @var PDO */
    protected $connection;
    /** @var string */
    protected $host;
    /** @var string */
    protected $user;
    /** @var string */
    protected $pass;
    /** @var string */
    protected $dbname;
    /** @var string */
    protected $tablename;
    /** @var string */
    protected $charset;

    protected function __construct(string $host, string $user, string $pass, string $dbname, string $charset)
    {
        $this->host = $host;
        $this->user = $user;
        $this->pass = $pass;
        $this->dbname = $dbname;
        $this->charset = $charset;

        $this->connect();
    }

    /**
     * @param string $host
     * @param string $user
     * @param string $pass
     * @param string $dbname
     * @param string $charset
     * @param Logger $logger
     *
     * @return MySQLConnection
     */
    public static function create($host, $user, $pass, $dbname, $charset = 'utf8')
    {
        return new self($host, $user, $pass, $dbname, $charset);
    }

    /**
     * fetch
     *
     * @param  mixed $query
     * @param  array<string,string> $params
     * @return mixed
     */
    public function fetch($query, $params = [])
    {
        return $this->query($query, $params, 'fetch', PDO::FETCH_ASSOC);
    }

    /**
     * fetch
     *
     * @param  mixed $query
     * @param  array<string,string> $params
     * @return mixed
     */
    public function fetchAll($query, $params = [])
    {
        return $this->query($query, $params, 'fetchAll', PDO::FETCH_ASSOC);
    }

    /**
     * fetch
     *
     * @param  mixed $query
     * @param  array<string,string> $params
     * @return mixed
     */
    public function fetchColumm($query, $params = [])
    {
        return $this->query($query, $params, 'fetchColumn', PDO::FETCH_ASSOC);
    }

    // #Privaate Methods
    /**
     * query
     *
     * @param  mixed $query
     * @param  array<string,string> $params
     * @param  string $fetchMethod
     * @param  int $fetchMode
     * @return mixed
     */
    protected function query($query, $params, $fetchMethod, $fetchMode)
    {

        try {
            $statment = $this->connection->prepare($query);
            $statment->setFetchMode($fetchMode);
            if (!$statment->execute($params)) {
                return null;
            }
            if ($result = call_user_func_array([$statment, $fetchMethod], [])) {
                if (is_array($result)) {
                    $result = $this->convertTypes($statment, $result);
                }
            };

            return $result;
        } catch (\Throwable $th) {
            throw $th;
        }
    }
    public function execute($query, $params = [])
    {
        try {
            $statment = $this->connection->prepare($query);
            if ($statment->execute($params) && $statment->rowCount() > 0) {
                return true;
            } elseif ($statment->rowCount() < 1) {
                throw new \Exception('Nenhum dado foi modificado');
            }
            return false;
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function lastInsertId()
    {
        return $this->connection->lastInsertId();
    }

    protected function connect(): void
    {

        try {
            $pdo = new PDO(
                "mysql:host={$this->host};dbname={$this->dbname};charset={$this->charset}",
                $this->user,
                $this->pass
            );
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $this->connection = $pdo;
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    /**
     * convertTypes
     *
     * @param  \PDOStatement $statment
     * @param  array<string,string|array> $data
     * @return array<string,mixed>
     */
    protected function convertTypes($statment, $data)
    {
        foreach ($data as $column => $value) {
            $i = array_search($column, array_keys($data));
            if (false === $i || !$meta = $statment->getColumnMeta($i)) {
                continue;
            }
            $type = $meta['native_type'];

            if (is_array($value)) {
                $data[$column] = $this->convertTypes($statment, $value);
            } else {
                $data[$column] = $this->valueTypeResolver($type, $value);
            }
        }
        return $data;
    }

    protected function valueTypeResolver(string $type, $var)
    {
        if (is_null($var)) {
            return null;
        }
        switch ($type) {
            case static::TYPE_TINY:
                return (bool) $var;
            case static::TYPE_SHORT:
                return $var;
            case static::TYPE_LONG:
                return (int) $var;
            case static::TYPE_FLOAT:
                return (float) $var;
            case static::TYPE_DOUBLE:
                return (float) $var;
            case static::TYPE_TIMESTAMP:
                return date('d/m/Y H:i:s', strtotime($var));
            case static::TYPE_LONGLONG:
                return $var;
            case static::TYPE_INT24:
                return $var;
            case static::TYPE_DATE:
                return date('d/m/Y', strtotime($var));
            case static::TYPE_TIME:
                return date('H:i:s', strtotime($var));
            case static::TYPE_DATETIME:
                return date('d/m/Y H:i:s', strtotime($var));
            case static::TYPE_YEAR:
                return (int) $var;
            case static::TYPE_ENUM:
                return $var;
            case static::TYPE_SET:
                return $var;
            case static::TYPE_TINY_BLOB:
                return $var;
            case static::TYPE_MEDIUM_BLOB:
                return $var;
            case static::TYPE_LONG_BLOB:
                return $var;
            case static::TYPE_BLOB:
                return $var;
            case static::TYPE_VAR_STRING:
                return (string) $var;
            case static::TYPE_STRING:
                return (string) $var;
            case static::TYPE_NULL:
                return null;
            case static::TYPE_NEWDATE:
                return $var;
            case static::TYPE_INTERVAL:
                return $var;
            case static::TYPE_GEOMETRY:
                return $var;
            default:
                return $var;
        }
    }

    public function setEntity($entity)
    {
        $this->tablename = $entity;
    }
}
