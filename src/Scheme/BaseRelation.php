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
        $relation = $model->{$this->id}();

        if ( ! is_a($relation, $expectedClass)) {
            throw new \RuntimeException("The relation [{$this->id}] is not an instance of [{$expectedClass}].");
        }

        return $relation;
    }

}