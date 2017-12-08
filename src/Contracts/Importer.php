<?php

namespace Lazychaser\DataImportTools\Contracts;

use Illuminate\Support\Collection;

interface Importer
{
    /**
     * Import single data item.
     *
     * @param array $data
     *
     * @return \Illuminate\Database\Eloquent\Model|false
     */
    public function import(array $data);

    /**
     * Indicate that batch import has started.
     *
     * @param \Illuminate\Support\Collection $items
     */
    public function startBatch(Collection $items);

    /**
     * Indicate that batch import has ended.
     *
     * @return void
     */
    public function endBatch();

}