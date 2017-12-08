<?php

namespace Lazychaser\DataImportTools\Events;

use Lazychaser\DataImportTools\ImportService;

class ImportWillBeStarted
{
    /**
     * @var ImportService
     */
    public $service;

    /**
     * @param ImportService $service
     */
    public function __construct(ImportService $service)
    {
        $this->service = $service;
    }

}