<?php

namespace Komodo\Interlace;

/*
|-----------------------------------------------------------------------------
| Komodo Interlace
|-----------------------------------------------------------------------------
|
| Desenvolvido por: Jhonnata Paixão (Líder de Projeto)
| Iniciado em: 08/2023
| Arquivo: Association.php
| Data da Criação Fri Aug 11 2023
| Copyright (c) 2023
|
|-----------------------------------------------------------------------------
|*/


class Association
{
    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $model;

    /**
     * @var string
     */
    protected $foreingkey;

    /**
     * @var string
     */
    protected $oringinKey;

    /**
     * @param string $type Classname of model associate
     * @param string $model Classname of model associate
     * @param string $foreingkey Foreingkey of associate
     * @param string $oringinKey Key in associator
     * @param string $name Name of association
     *
     * @return $this
     */
    public function __construct($type, $model, $foreingkey, $oringinKey = 'id')
    {
        $this->type = $type;
        $this->model = $model;
        $this->foreingkey = $foreingkey;
        $this->oringinKey = $oringinKey;
    }

    /**
     * Get the value of model
     *
     * @return  Model
     */
    public function getModel()
    {
        return new $this->model;
    }

    /**
     * Get the value of model
     *
     * @return  string
     */
    public function getModelClassname()
    {
        return $this->model;
    }

    /**
     * Get the value of key from associate
     *
     * @return  string
     */
    public function getForeingkey()
    {
        return $this->foreingkey;
    }

    /**
     * Get the value of key from associator
     *
     * @return  string
     */
    public function getOringinKey()
    {
        return $this->oringinKey;
    }

    /**
     * Get the value of association type
     *
     * @return  string
     */
    public function getType()
    {
        return $this->type;
    }
}
