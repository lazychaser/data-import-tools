<?php

namespace Lazychaser\DataImportTools\Scheme;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Lazychaser\DataImportTools\BaseProvider;
use Lazychaser\DataImportTools\Helpers;

abstract class BaseRelation extends AbstractAttribute
{
    /**
     * @var string|\Lazychaser\DataImportTools\BaseProvider
     */
    protected $provider;

    /**
     * @var int
     */
    protected $mode = BaseProvider::READ_CREATE;

    /**
     * @param string $id
     * @param string|\Lazychaser\DataImportTools\BaseProvider $provider
     * @param null|string $dataKey
     */
    public function __construct($id, $provider, $dataKey = null)
    {
        parent::__construct($id, $dataKey);

        $this->provider = $provider;
    }

    /**
     * @param $id
     * @param $provider
     * @param null $dataKey
     *
     * @return static
     */
    public static function make($id, $provider, $dataKey = null)
    {
        return new static($id, $provider, $dataKey);
    }

    /**
     * @return \Lazychaser\DataImportTools\BaseProvider
     */
    public function getProvider()
    {
        if (is_string($this->provider)) {
            return $this->provider = app($this->provider);
        }

        return $this->provider;
    }

    /**
     * @inheritDoc
     */
    public function normalize($value)
    {
        return $this->getProvider()->normalizeKey($value);
    }

    /**
     * @return $this
     */
    public function dontCreateModels()
    {
        $this->mode = BaseProvider::READ;

        return $this;
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param string $expectedClass
     *
     * @return Relation
     */
    protected function relation(Model $model, $expectedClass)
    {
        return Helpers::relation($model, $this->id, $expectedClass);
    }

    public static function __callStatic($name, $arguments)
    {
        if (count($arguments) < 1) {
            throw new \Exception("Not enough arguments.");
        }

        $dataKey = count($arguments) > 1 ? $arguments[1] : null;

        return new static($name, $arguments[0], $dataKey);
    }

}