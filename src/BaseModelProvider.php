<?php

namespace Lazychaser\DataImportTools;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Lazychaser\DataImportTools\BaseProvider;

abstract class BaseModelProvider extends BaseProvider
{
    /**
     * @var array
     */
    protected $loaded = [];

    /**
     * @var array
     */
    public $eager = [];

    /**
     * Load a model from database by primary key if it is not already loaded.
     *
     * @return \Illuminate\Database\Eloquent\Model|false
     */
    protected function load($key)
    {
        if ( ! $hash = $this->hashKey($key)) {
            return null;
        }

        if ( ! array_key_exists($hash, $this->loaded)) {
            $this->preload($key);
        }

        return $this->loaded[$hash];
    }

    /**
     * @inheritDoc
     */
    public function fetch($key, $mode = self::READ_CREATE)
    {
        if (empty($key)) {
            throw new \RuntimeException;
        }

        if ($model = $this->load($key)) {
            return $this->updateAllowed($mode) ? $model : null;
        }

        if ( ! $this->createAllowed($mode)) {
            return null;
        }

        $this->remember($model = $this->create($key));

        return $model;
    }

    /**
     * @inheritdoc
     */
    public function preload($keys)
    {
        return Collection::make($keys)
            ->map([ $this, 'hashKey' ])
            ->filter()
            ->diff(array_keys($this->loaded))
            ->pipe(function (Collection $keys) {
                if ($keys->isEmpty()) {
                    return $this;
                }

                // Add to the loaded array false values so that models won't be
                // considered missing if they aren't present in database
                $this->loaded += $keys->combine(array_pad([], $keys->count(), false))->all();

                $this->rememberMany($this->fetchModels($keys));

                return $this;
            });
    }

    /**
     * @param array|\Illuminate\Contracts\Support\Arrayable $keys
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function fetchModels($keys)
    {
        return $this->newQuery()
                    ->whereIn($this->primaryKey(), $keys)
                    ->with($this->eager)
                    ->get($this->processColumns($this->columns));
    }

    /**
     * @param array $columns
     *
     * @return array
     */
    protected function processColumns(array $columns)
    {
        if ($columns == [ '*' ]) {
            return $columns;
        }

        return array_unique(array_merge($columns, [
            $this->primaryKey(), $this->newEmptyModel()->getKeyName()
        ]));
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $model
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function remember($model)
    {
        $this->loaded[$this->hashModel($model)] = $model;

        return $model;
    }

    /**
     * @param \Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model[] $models
     *
     * @return $this
     */
    public function rememberMany($models)
    {
        foreach ($models as $item) {
            $this->remember($item);
        }

        return $this;
    }

    /**
     * @param array $relations
     *
     * @return $this
     */
    public function eager(array $relations)
    {
        $this->eager = $relations;

        return $this;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getLoaded()
    {
        return (new EloquentCollection($this->loaded))->filter();
    }

    /**
     * @param $key
     *
     * @return string
     */
    public function hashKey($key)
    {
        return $this->slugKey ? str_slug($key) : $key;
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $model
     *
     * @return string
     */
    public function hashModel($model)
    {
        return $model->getAttributeValue($this->primaryKey());
    }

}