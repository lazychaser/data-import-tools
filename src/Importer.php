<?php

namespace Lazychaser\DataImportTools;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Lazychaser\DataImportTools\Contracts\Importer as ImporterContact;

abstract class Importer extends DataMapper implements ImporterContact
{
    /**
     * @var \Lazychaser\DataImportTools\BaseProvider
     */
    protected $provider;

    /**
     * @var int
     */
    protected $mode;

    /**
     * @var string
     */
    protected $primaryKey;

    /**
     * @param \Lazychaser\DataImportTools\BaseProvider $provider
     * @param null $attributes
     * @param int $mode
     */
    public function __construct(BaseProvider $provider,
                                $attributes = null,
                                $mode = BaseProvider::READ_CREATE
    ) {
        $this->provider = $provider;
        $this->attributes = $attributes;
        $this->mode = $mode;
    }

    /**
     * Primary key name on external data.
     *
     * By default, model primary key from repository is used.
     *
     * @return string
     */
    protected function primaryKey($data)
    {
        $pk = $this->primaryKey ?: $this->provider->primaryKey();

        return $this->provider->normalizeKey(Arr::get($data, $pk));
    }

    /**
     * @inheritdoc
     */
    public function import(Collection $data)
    {
        if ( ! $key = $this->itemKey($data)) {
            return false;
        }

        if (method_exists($this, 'validate')) {
            $this->validate($key, $data);
        }

        if ( ! $model = $this->provider->fetch($key, $this->mode)) {
            return false;
        }

        if ( ! $this->save($model, $data)) {
            return false;
        }

        return $model;
    }

    /**
     * @param Model $model
     * @param Collection $data
     *
     * @return bool
     */
    protected function save($model, Collection $data)
    {
        $this->fill($model, $data);

        if ($model->exists && ! $model->isDirty()) {
            return false;
        }

        return $model->save();
    }

    /**
     * @param Collection $items
     *
     * @return $this
     */
    protected function preloadModels(Collection $items)
    {
        $this->provider->preload($this->itemsKeys($items));

        return $this;
    }

    public function normalize(array $data)
    {
        $result = parent::normalize($data);

        $result[$this->provider->primaryKey()] = $this->primaryKey($data);

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function startBatch(Collection $items)
    {
        $items = $this->normalizeMany($items);

        $this->preloadModels($items)->preloadAttributes($items);

        return $items;
    }

    /**
     * Indicate that batch import has ended.
     *
     * @internal param Collection $items
     */
    public function endBatch()
    {
        //
    }

    /**
     * @return BaseProvider
     */
    public function getProvider()
    {
        return $this->provider;
    }

    /**
     * @param Collection $data
     *
     * @return mixed
     */
    protected function itemKey(Collection $data)
    {
        return $data->get($this->provider->primaryKey());
    }

    /**
     * @param Collection $items
     *
     * @return array
     */
    protected function itemsKeys(Collection $items)
    {
        return $items
            ->map(function ($data) { return $this->itemKey($data); })
            ->all();
    }
}