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
     * @param null $dataKey
     */
    public function __construct($id, $dataKey = null)
    {
        $this->id = $id;
        $this->dataKey = $dataKey;
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
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

}