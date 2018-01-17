<?php

namespace Lazychaser\DataImportTools\Scheme;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class BasicAttribute extends AbstractAttribute
{
    /**
     * @var mixed
     */
    public $defaultValue;

    /**
     * Do some stuff before items are imported.
     *
     * @param \Illuminate\Support\Collection $items
     *
     * @return $this
     */
    public function preload(Collection $items)
    {
        return $this;
    }

    /**
     * @param mixed $value
     *
     * @return mixed
     */
    public function normalize($value)
    {
        if ( ! is_string($value)) {
            return $value;
        }

        return $value === '' ? null : trim($value);
    }

    /**
     * @param $value
     *
     * @return $this
     */
    public function defaultValue($value)
    {
        $this->defaultValue = $value;

        return $this;
    }

    /**
     * @param array $value
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     *
     * @return void
     */
    public function setValueOnModel($value, Model $model)
    {
        if ($value === '' || $value === null) {
            $value = $this->defaultValue;
        }

        $model->setAttribute($this->id, $value);
    }
}