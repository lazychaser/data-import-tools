<?php

namespace Lazychaser\DataImportTools\Scheme;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Lazychaser\DataImportTools\DataMapper;
use Lazychaser\DataImportTools\Helpers;

class HasMany extends AbstractAttribute
{
    /**
     * @var DataMapper
     */
    protected $dataMapper;

    /**
     * @var string
     */
    protected $primaryKey;

    /**
     * HasMany constructor.
     *
     * @param $id
     * @param string|DataMapper $dataMapper
     * @param null $dataKey
     */
    public function __construct($id, $dataMapper, $primaryKey, $dataKey = null)
    {
        parent::__construct($id, $dataKey);

        $this->dataMapper = $dataMapper;
        $this->primaryKey = $primaryKey;
    }

    /**
     * @param $id
     * @param $dataMapper
     * @param $primaryKey
     * @param null $dataKey
     *
     * @return HasMany
     */
    public static function make($id, $dataMapper, $primaryKey, $dataKey = null)
    {
        return new self($id, $dataMapper, $primaryKey, $dataKey);
    }

    /**
     * Do some stuff before items are imported.
     *
     * @param \Illuminate\Support\Collection $items
     *
     * @return $this
     */
    public function preload(Collection $items)
    {
        $items = $items->pluck($this->id)->flatten(1);

        $this->getDataMapper()->preloadAttributes($items);

        return $this;
    }

    /**
     * @param Collection $value
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     */
    public function setValueOnModel($value, Model $model)
    {
        /**
         * @var \Illuminate\Database\Eloquent\Relations\HasMany $relation
         */
        $relation = Helpers::relation($model, $this->id, HasMany::class);

        $currentItems = $model->wasRecentlyCreated
            ? collect()
            : $model->getRelationValue($this->id)->keyBy($this->primaryKey);

        foreach ($value as $data) {
            if ( ! $instance = $currentItems->pull($data[$this->primaryKey])) {
                $instance = $relation->getModel()->newInstance();
            }

            $this->getDataMapper()->fill($instance, $data);

            $relation->save($instance);
        }

        foreach ($currentItems as $item) {
            $item->delete();
        }
    }

    /**
     * @param mixed $value
     *
     * @return Collection|mixed
     */
    public function normalize($value)
    {
        return $this->getDataMapper()->normalizeMany(collect($value));
    }

    /**
     * @return DataMapper
     */
    public function getDataMapper()
    {
        if (is_string($this->dataMapper)) {
            return $this->dataMapper = app($this->dataMapper);
        }

        return $this->dataMapper;
    }

    public static function __callStatic($name, $arguments)
    {
        if (count($arguments) < 2) {
            throw new \Exception("Not enough arguments.");
        }

        $dataKey = count($arguments) > 2 ? $arguments[2] : null;

        return new self($name, $arguments[0], $arguments[1], $dataKey);
    }

}