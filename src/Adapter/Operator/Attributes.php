<?php
namespace Komodo\Interlace\Adapter\Operator;

use Komodo\Interlace\Enums\Op;
use Komodo\Interlace\QueryBuilder\QueryBuilder;

/*
|-----------------------------------------------------------------------------
| Komodo Interlace
|-----------------------------------------------------------------------------
|
| Desenvolvido por: Jhonnata Paixão (Líder de Projeto)
| Iniciado em: 08/2023
| Arquivo: Attributes.php
| Data da Criação Wed Aug 30 2023
| Copyright (c) 2023
|
|-----------------------------------------------------------------------------
|*/

/**
 * @property QueryBuilder $builder
 */
trait Attributes
{
    /**
     * crudOperator
     *
     * @var "select"|"update"|"delete"
     */
    protected $crudOperator = 'select';

    protected $assigned = false;

    /**
     * mountAttributes
     *
     * @param  array|string $attributes
     * @return void
     */
    public function mountAttributes($attributes)
    {
        if (is_string($attributes)) {
            $this->crudOperator = $attributes;
        }
        switch ($this->crudOperator) {
            case 'select':
                $this->mountSelect($attributes);
                break;

            case 'update':
                $this->mountUpdate($attributes);
                break;

            case 'delete':
                $this->mountDelete($attributes);
                break;
        }
    }

    public function mountSelect($attributes)
    {
        foreach ($attributes as $collunm => $op) {
            switch ($op) {
                case Op::COUNT:
                    $this->builder->select([  ])->count($collunm);
                    break;
                case Op::DISTINCT:
                    $this->builder->select([  ])->distinct($collunm);
                    break;
                case Op::COUNT_DISTINCT:
                    $this->builder->select([  ])->countDistinc($collunm);
                    break;
            }
        }
    }

    public function mountUpdate($attributes)
    {
        return $this->builder->update();
    }

    public function mountDelete($attributes)
    {
        return $this->builder->delete();
    }
}
