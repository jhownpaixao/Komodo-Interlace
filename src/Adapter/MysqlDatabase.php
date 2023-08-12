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


use Komodo\Interlace\Interfaces\DatabaseAdapter;
use Komodo\Interlace\Model;
use PDO;

class MysqlDatabase implements DatabaseAdapter
{
    /**
     * @var PDO
     */
    private $connection;

    /**
     * @var string
     */
    private $host;

    /**
     * @var string
     */
    private $user;

    /**
     * @var string
     */
    private $pass;

    /**
     * @var string
     */
    private $dbname;

    /**
     * @var string
     */
    private $charset;

    /**
     * @param string $host
     * @param string $user
     * @param string $pass
     * @param string $dbname
     * @param string $charset
     *
     * @return void
     */
    public function __construct($host, $user, $pass, $dbname, $charset = 'utf8')
    {
        $this->host = $host;
        $this->user = $user;
        $this->pass = $pass;
        $this->dbname = $dbname;
        $this->charset = $charset;

        $this->connect();
    }

    public function fetch($query, $params = [])
    {
        return $this->query($query, $params, 'fetch', PDO::FETCH_ASSOC);
    }

    public function fetchAll($query, $params = [])
    {
        return $this->query($query, $params, 'fetchAll', PDO::FETCH_ASSOC);
    }

    public function fetchColumm($query, $params = [])
    {
        return $this->query($query, $params, 'fetchColumn', PDO::FETCH_ASSOC);
    }

    // #Privaate Methods
    private function query($query, $params, $fetchMethod, $fetchMode)
    {

        try {
            $statment = $this->connection->prepare($query);
            $statment->setFetchMode($fetchMode);
            if (!$statment->execute($params)) {
                return null;
            }
            return call_user_func_array([$statment, $fetchMethod], []);
        } catch (\Throwable $th) {
            Model::$logger->error($th->getMessage());
            throw $th;
        }
    }

    public function execute($query, $params = [])
    {
        try {
            $statment = $this->connection->prepare($query);
            return $statment->execute($params);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function lastInsertId()
    {
        return $this->connection->lastInsertId();
    }
    private function connect()
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
            Model::$logger->error($th->getMessage());
            throw $th;
        }
    }
}
