<?php

namespace Lazychaser\DataImportTools\Scheme;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo as BelongsToRelation;
use Illuminate\Support\Collection;

class BelongsTo extends BaseRelation
{
    /**
     * Do some stuff before items are imported.
     *
     * @param Collection $items
     *
     * @return $this
     */
    public function preload(Collection $items)
    {
        $this->getProvider()->preload($items->pluck($this->id)->all());

        return $this;
    }

    /**
     * @param mixed $value
     * @param Model $model
     *
     * @return $this
     */
    public function setValueOnModel($value, Model $model)
    {
        /** @var BelongsToRelation $relation */
        $relation = $this->relation($model, BelongsToRelation::class);

        $value = $value ? $this->getProvider()->fetch($value, $this->mode) : null;

        if ($value) {
            $relation->associate($value);
        } else {
            $relation->dissociate();
        }

        return $this;
    }

}