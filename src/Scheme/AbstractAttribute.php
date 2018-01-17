<?php

namespace Lazychaser\DataImportTools\Scheme;

use Illuminate\Support\Arr;
use Lazychaser\DataImportTools\Contracts\Attribute;

abstract class AbstractAttribute implements Attribute
{
    /**
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    protected $dataKey;

    /**
     * @param $id
     */
    public function __construct($id)
    {
        $this->id = $id;
    }

    /**
     * @param array $data
     *
     * @return mixed
     */
    public function value(array $data)
    {
        $key = $this->dataKey ?: $this->id;

        return $this->normalize(Arr::get($data, $key));
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
     * @param $value
     *
     * @return $this
     */
    public function dataKey($value)
    {
        $this->dataKey = $value;

        return $this;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

}