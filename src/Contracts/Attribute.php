<?php

namespace Lazychaser\DataImportTools\Contracts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

interface Attribute {

    /**
     * Do some stuff before items are imported.
     *
     * @param \Illuminate\Support\Collection $items
     *
     * @return $this
     */
    public function preload(Collection $items);

    /**
     * @param array $value
     *
     * @return mixed
     */
    public function value($data);

    /**
     * @param $value
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     */
    public function setValueOnModel($value, Model $model);

    /**
     * @return string
     */
    public function getId();

}