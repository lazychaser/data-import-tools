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
     */
    public function __construct($id, $provider)
    {
        parent::__construct($id);

        $this->provider = $provider;
    }

    /**
     * @param $id
     * @param $provider
     *
     * @return static
     */
    public static function make($id, $provider)
    {
        return new static($id, $provider);
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
}