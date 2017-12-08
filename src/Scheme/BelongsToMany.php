<?php

namespace Lazychaser\DataImportTools\Scheme;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany as BelongsToManyRelation;
use Illuminate\Support\Collection;

/**
 * Class BelongsToMany
 *
 * @package Lazychaser\DataImportTools\Scheme
 */
class BelongsToMany extends BaseRelation
{
    /**
     * @var string
     */
    public $indexAttribute;

    /**
     * Specify the name of the attribute that will hold order value.
     *
     * @param string $attribute
     *
     * @return $this
     */
    public function indexBy($attribute)
    {
        $this->indexAttribute = $attribute;

        return $this;
    }

    /**
     * @param array|string $value
     *
     * @return array
     */
    public function normalize($value)
    {
        if (empty($value)) {
            return [ ];
        }

        if (is_string($value)) {
            $value = explode(',', $value);
        }

        return array_map(function ($item) {
            return $this->getProvider()->normalizeKey($item);
        }, $value);
    }

    /**
     * Do some stuff before items are imported.
     *
     * @param Collection $items
     *
     * @return $this
     */
    public function preload(Collection $items)
    {
        $keys = [ ];

        foreach ($items->pluck($this->id) as $data) {
            if (is_array($data)) {
                $keys = array_merge($keys, $data);
            }
        }

        $this->getProvider()->preload(array_unique($keys));

        return $this;
    }

    /**
     * @param array $value
     *
     * @param Model $model
     *
     * @return $this
     */
    public function setValueOnModel($value, Model $model)
    {
        /** @var BelongsToManyRelation $relation */
        $relation = $this->relation($model, BelongsToManyRelation::class);

        $value = $this->modelKeys($value);

        $relation->sync($this->maybeIndexed($value));

        return $this;
    }

    /**
     * @param array $keys
     *
     * @return array
     */
    protected function modelKeys($keys)
    {
        if (empty($keys)) {
            return [];
        }

        return $this->getProvider()
                    ->fetchMany($keys, $this->mode)
                    ->modelKeys();
    }

    /**
     * @param $ids
     *
     * @return array
     */
    protected function maybeIndexed($ids)
    {
        if (empty($ids) || ! $this->indexAttribute) {
            return $ids;
        }

        return array_combine($ids, $this->generateIndexes(count($ids)));
    }

    /**
     * @param $amount
     *
     * @return array
     */
    protected function generateIndexes($amount)
    {
        return array_map(function ($index) {

            return [ $this->indexAttribute => $index ];

        }, range(1, $amount));
    }
}