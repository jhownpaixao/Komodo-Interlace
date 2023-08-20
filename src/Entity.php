<?php

namespace Komodo\Interlace;

/*
|-----------------------------------------------------------------------------
| Komodo Interlace
|-----------------------------------------------------------------------------
|
| Desenvolvido por: Jhonnata Paixão (Líder de Projeto)
| Iniciado em: 08/2023
| Arquivo: Model.php
| Data da Criação Fri Aug 11 2023
| Copyright (c) 2023
|
|-----------------------------------------------------------------------------
|*/

use ReflectionClass;

/**
 * @property string $created_at
 * @property string $updated_at
 */
#[\AllowDynamicProperties ]
abstract class Entity
{
    /**
     * @var string|int
     */
    public $id;

    /**
     * @var \stdClass Propriedades da entidade
     */
    protected $props;

    /**
     * @var Association[]
     */
    protected $associations;

    /**
     * @param array $data
     *
     * @return $this
     */
    public function __construct($data = [  ], $associations = [  ])
    {
        $this->associations = $this->associate();
        $this->setProps();
        $this->sync($data, $associations);
    }

    // #Protected Methods
    /**
     * Sincroniza a entidade atual com os dados informados
     *
     * @param array $data
     *
     * @return void
     */
    protected function sync($data, $associations)
    {
        foreach ($data as $prop => $value) {
            if (!in_array($prop, (array) $this->props)) {
                continue;
            }

            $this->{$prop} = $value;
        }

        foreach ($associations as $association => $list) {
            if (!array_key_exists($association, $this->associations)) {
                continue;
            }
            $associate = $this->associations[ $association ];
            $this->{$association} = [  ];

            foreach ($list as $data) {
                $class = $associate->getModelClassname();
                $model = new $class($data);
                switch ($associate->getType()) {
                    case 'has_one':
                        $this->{$association} = $model;
                        break;
                    case 'has_many':
                        $this->{$association}[  ] = $model;
                        break;

                    case 'blg_one':
                        $this->{$association} = $model;
                        break;

                    case 'blg_many':
                        $this->{$association}[  ] = $model;
                        break;
                }
            }
        }
    }

    /**
     * Recupera todas as propriedades definidas pela classe filha
     *
     * @return void
     */
    protected function setProps()
    {

        $props = (array) $this;
        $this->props = new \stdClass();
        foreach ($props as $prop => $value) {
            if (str_contains($prop, '*') || str_contains($prop, '\\')) {
                continue;
            }
            $this->props->{$prop} = $prop;
        }
    }

    /**
     * @return Association[]
     */
    protected function associate()
    {
        return [  ];
    }

    // #Public Methods
    /**
     * @return \stdClass
     */
    public function getProps()
    {
        return $this->props;
    }

    /**
     * @return array
     */
    public function getAssociations()
    {
        return $this->associations;
    }
}
