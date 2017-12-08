<?php

namespace Lazychaser\DataImportTools\Events;

use Lazychaser\DataImportTools\ImportService;
use Lazychaser\DataImportTools\ImportStats;

class ImportHasEnded
{
    /**
     * @var ImportService
     */
    public $service;

    /**
     * @var ImportStats
     */
    public $stats;

    /**
     * @param ImportService $service
     * @param ImportStats $stats
     */
    public function __construct(ImportService $service, ImportStats $stats)
    {
        $this->service = $service;
        $this->stats = $stats;
    }
}