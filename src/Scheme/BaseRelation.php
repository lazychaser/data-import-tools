<?php

namespace Lazychaser\DataImportTools\Scheme;

use Lazychaser\DataImportTools\BaseProvider;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;

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
     * @param null|string $attribute
     */
    public function __construct($id, $provider, $attribute = null)
    {
        parent::__construct($id, $attribute);

        $this->provider = $provider;
    }

    /**
     * @param $id
     * @param $provider
     * @param null $attribute
     *
     * @return static
     */
    public static function make($id, $provider, $attribute = null)
    {
        return new static($id, $provider, $attribute);
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
        $relation = $model->{$this->attribute}();

        if ( ! is_a($relation, $expectedClass)) {
            throw new \RuntimeException("The relation [{$this->attribute}] is not an instance of [{$expectedClass}].");
        }

        return $relation;
    }

}