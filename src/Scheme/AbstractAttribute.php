<?php

namespace Lazychaser\DataImportTools\Scheme;

use Lazychaser\DataImportTools\Contracts\Attribute;

abstract class AbstractAttribute implements Attribute
{
    /**
     * @var string
     */
    protected $id;

    /**
     * Attribute on model.
     *
     * @var string
     */
    protected $attribute;

    /**
     * @param $id
     * @param null $attribute
     */
    public function __construct($id, $attribute = null)
    {
        $this->id = $id;
        $this->attribute = $attribute ?: $id;
    }

    /**
     * @param mixed $value
     *
     * @return mixed
     */
    public function normalize($value)
    {
        return $value;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

}