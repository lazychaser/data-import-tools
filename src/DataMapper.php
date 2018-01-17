<?php

namespace Lazychaser\DataImportTools;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Lazychaser\DataImportTools\Contracts\Attribute;

abstract class DataMapper
{
    /**
     * @var Collection|\Lazychaser\DataImportTools\Contracts\Attribute[]
     */
    protected $scheme;

    /**
     * @var array|null
     */
    protected $attributes;

    /**
     * @return \Lazychaser\DataImportTools\Contracts\Attribute[]|\Illuminate\Support\Collection
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
     * @param array $data
     *
     * @return Collection
     */
    public function normalize(array $data)
    {
        return $this->getScheme()->map(function (Attribute $attribute) use ($data) {
            return $attribute->value($data);
        });
    }

    /**
     * Defines a list of attributes.
     *
     * @return \Lazychaser\DataImportTools\Contracts\Attribute[]
     */
    abstract protected function attributes();

    /**
     * @param Model $model
     * @param Collection $data
     *
     * @return Model
     */
    public function fill(Model $model, Collection $data)
    {
        $scheme = $this->getScheme();

        if (is_null($this->attributes)) {
            foreach ($scheme as $id => $attr) {
                if ($data->has($id)) {
                    $attr->setValueOnModel($data[$id], $model);
                }
            }

            return $model;
        }

        foreach ($this->attributes as $id) {
            if ($scheme->has($id) && $data->has($id)) {
                $scheme->get($id)->setValueOnModel($data[$id], $model);
            }
        }

        return $model;
    }

    /**
     * @param \Illuminate\Support\Collection $items
     *
     * @return \Illuminate\Support\Collection
     */
    public function normalizeMany(Collection $items)
    {
        return $items->map(function ($item) {
            return $this->normalize($item);
        });
    }

    /**
     * @param \Illuminate\Support\Collection $items
     *
     * @return $this
     */
    public function preloadAttributes(Collection $items)
    {
        foreach ($this->getScheme() as $attribute) {
            $attribute->preload($items);
        }

        return $this;
    }
}