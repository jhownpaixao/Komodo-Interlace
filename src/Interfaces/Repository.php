<?php

namespace Komodo\Interlace\Interfaces;

/*
|-----------------------------------------------------------------------------
| Komodo Interlace
|-----------------------------------------------------------------------------
|
| Desenvolvido por: Jhonnata Paixão (Líder de Projeto)
| Iniciado em: 08/2023
| Arquivo: Repository.php
| Data da Criação Wed Aug 16 2023
| Copyright (c) 2023
|
|-----------------------------------------------------------------------------
|*/
use Komodo\Interlace\Entity;

/**
 * @template TEntity of Entity
 */
interface Repository
{
    /**
     * Procura pela chave primária
     *
     * @param string|int $pk
     *
     * @return \Komodo\Interlace\Entity|TEntity
     */
    public function findByPk($pk);

    /**
     * Retorna todos os registros sem excessão
     *
     * @return \Komodo\Interlace\Entity[]|TEntity[]
     */
    public function getAll();

    /**
     * Procura por um único registro que atenda
     * à condição específica
     *
     * @param OperatorParams $params
     *
     * @return \Komodo\Interlace\Entity|TEntity|null
     */
    public function findOne($params);

    /**
     * Procura por todos os registros que atendem
     * às condições fornecidas
     *
     * @param OperatorParams $params
     *
     * @return \Komodo\Interlace\Entity[]|TEntity[]
     */
    public function findAll($params);

    /**
     * Retorna o total de registros encontrados
     *
     * @param OperatorParams $params
     *
     * @return int
     */
    public function count($params);

    /**
     * Excluí todos os registros que atendam
     * à condição fornecida
     *
     * @param OperatorParams $params
     *
     * @return bool
     */
    public function deleteAll($params);

    /**
     * Salva o registro na db
     *
     * @param TEntity $entity
     *
     * @return int|false
     */
    public function persist(&$entity);

    /**
     * Atualiza o registro
     *
     * @param TEntity $entity
     *
     * @return bool
     */
    public function update(&$entity);

    /**
     * Excluí o registro
     *
     * @param TEntity $entity
     *
     * @return bool
     */
    public function delete(&$entity);
}
