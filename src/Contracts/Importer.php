<?php

namespace Lazychaser\DataImportTools\Contracts;

use Illuminate\Support\Collection;

interface Importer
{
    /**
     * Import single data item.
     *
     * @param \Illuminate\Support\Collection $data
     *
     * @return \Illuminate\Database\Eloquent\Model|false
     */
    public function import(Collection $data);

    /**
     * Indicate that batch import has started.
     *
     * @param \Illuminate\Support\Collection $items
     *
     * @return \Illuminate\Support\Collection
     */
    public function startBatch(Collection $items);

    /**
     * Indicate that batch import has ended.
     *
     * @return void
     */
    public function endBatch();

}