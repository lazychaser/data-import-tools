<?php

namespace Lazychaser\DataImportTools;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator as ValidatorFacade;
use Lazychaser\DataImportTools\Contracts\Importer as ImporterContact;
use Lazychaser\DataImportTools\Exceptions\ValidationException;

class Importer implements ImporterContact
{
    /**
     * @var Collection|\Lazychaser\DataImportTools\Contracts\Attribute[]
     */
    protected $scheme;

    /**
     * @var \Lazychaser\DataImportTools\BaseProvider
     */
    protected $provider;

    /**
     * @var array|null
     */
    protected $attributes;

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
     * Defines a list of attributes.
     *
     * @return \Lazychaser\DataImportTools\Contracts\Attribute[]
     */
    protected function attributes()
    {
        return [ ];
    }

    /**
     * @inheritdoc
     */
    public function import(array $data)
    {
        if ( ! $key = $this->itemKey($data)) {
            return false;
        }

        $this->validate($key, $data);

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
     * @param array $data
     *
     * @return Model
     */
    protected function fill(Model $model, array $data)
    {
        $scheme = $this->getScheme();

        if (is_null($this->attributes)) {
            foreach ($scheme as $id => $attr) {
                if (array_key_exists($id, $data)) {
                    $attr->setValueOnModel($data[$id], $model);
                }
            }

            return $model;
        }

        foreach ($this->attributes as $id) {
            if ($scheme->has($id) && array_key_exists($id, $data)) {
                $scheme->get($id)->setValueOnModel($data[$id], $model);
            }
        }

        return $model;
    }

    /**
     * @param Model $model
     * @param array $data
     *
     * @return bool
     */
    protected function save($model, array $data)
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

    /**
     * @param Collection $items
     *
     * @return $this
     */
    protected function preloadAttributes(Collection $items)
    {
        foreach ($this->getScheme() as $attribute) {
            $attribute->preload($items);
        }

        return $this;
    }

    /**
     * @param $key
     * @param array $data
     *
     * @throws ValidationException
     */
    protected function validate($key, array $data)
    {
        $validator = ValidatorFacade::make($data, $this->rules($key));

        if ($validator->fails()) {
            throw new ValidationException($key, $validator);
        }
    }

    /**
     * @param string $key
     *
     * @return array
     */
    protected function rules($key)
    {
        return [ ];
    }

    /**
     * @param array $data
     *
     * @return array
     */
    public function normalize(array $data)
    {
        $result = [];

        $scheme = $this->getScheme();

        foreach ($scheme as $id => $attribute) {
            $result[$id] = $attribute->value($data);
        }

        $result[$this->provider->primaryKey()] = $this->primaryKey($data);

        return $result;
    }

    /**
     * @param \Illuminate\Support\Collection $items
     *
     * @return \Illuminate\Support\Collection
     */
    protected function normalizeMany(Collection $items)
    {
        return $items->map(function ($item) {
            return $this->normalize($item);
        });
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
     * @return \Lazychaser\DataImportTools\Contracts\Attribute|\Illuminate\Support\Collection
     */
    public function getScheme()
    {
        if ( ! is_null($this->scheme)) {
            return $this->scheme;
        }

        $this->scheme = new Collection;

        foreach ($this->attributes() as $attr) {
            $this->scheme->put($attr->getId(), $attr);
        }

        return $this->scheme;
    }

    /**
     * @return BaseProvider
     */
    public function getProvider()
    {
        return $this->provider;
    }

    /**
     * @param array $data
     *
     * @return mixed
     */
    protected function itemKey(array $data)
    {
        return Arr::get($data, $this->provider->primaryKey());
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