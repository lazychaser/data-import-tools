<?php

namespace Lazychaser\DataImportTools\Contracts;

use Illuminate\Support\Collection;

interface DataSource {

    /**
     * @return void
     */
    public function start();

    /**
     * @param int $limit
     *
     * @return Collection
     */
    public function read($limit = 100);

    /**
     * @return void
     */
    public function stop();

    /**
     * Get the list of attributes that this data source has.
     *
     * @return array
     */
    public function getAvailableAttributes();

}