<?php

namespace Lazychaser\DataImportTools;

use Illuminate\Support\Collection;
use Lazychaser\DataImportTools\Contracts\DataSource as DataSourceContract;
use Lazychaser\DataImportTools\Contracts\Importer as ImporterContract;
use Lazychaser\DataImportTools\Contracts\ModelProvider;
use Lazychaser\DataImportTools\Events\ModelWasImported;
use Lazychaser\DataImportTools\Events\BatchWasImported;
use Lazychaser\DataImportTools\Events\BatchWillBeImported;
use Lazychaser\DataImportTools\Events\ImportHasEnded;
use Lazychaser\DataImportTools\Events\ImportWillBeStarted;
use Lazychaser\DataImportTools\Exceptions\ValidationException;
use Monolog\Handler\NullHandler;
use Monolog\Logger;

class ImportService
{
    /**
     * @var DataSourceContract
     */
    protected $dataSource;

    /**
     * @var ImporterContract
     */
    protected $importer;

    /**
     * @var int
     */
    protected $mode = BaseProvider::READ_CREATE;

    /**
     * @var int
     */
    protected $batchSize = 100;

    /**
     * @var Logger
     */
    protected $log;

    /**
     * @var null|array
     */
    protected $attributes;

    /**
     * @param DataSourceContract $dataSource
     * @param ImporterContract $importer
     */
    public function __construct(DataSourceContract $dataSource,
                                ImporterContract $importer
    ) {
        $this->dataSource = $dataSource;
        $this->importer = $importer;
    }

    /**
     * Do the import.
     *
     * @return ImportStats
     * @throws \Exception
     */
    public function import()
    {
        $this->ensureLogger();

        $stats = new ImportStats;

        $this->fire(new ImportWillBeStarted($this));

        $this->log->info('Import started.');

        try {
            $this->dataSource->start();

            while ($items = $this->dataSource->read($this->batchSize)) {
                $stats->merge($this->importItems($items));
            }
        }

        catch (\Exception $e) {
            $this->log->error($this->formatException($e));
        }

        finally {
            $this->dataSource->stop();
        }

        $this->fire(new ImportHasEnded($this, $stats));

        $this->log->info('Import done.', compact('stats'));

        return $stats;
    }

    /**
     * @param Collection $items
     *
     * @return ImportStats
     */
    protected function importItems(Collection $items)
    {
        $this->log->info('Importing a batch of '.$items->count().' item(s)...');

        $this->fire(new BatchWillBeImported($this, $items, $this->attributes));

        $items = $this->importer->startBatch($items);

        $result = new ImportStats;

        foreach ($items as $item) {
            try {
                $model = $this->importer->import($item);

                if ($model) {
                    $this->fire(new ModelWasImported($model));
                }

                $result->imported($model);
            }

            catch (\Exception $e) {
                $this->log->addError($this->formatException($e));

                $result->errored();
            }
        }

        $this->importer->endBatch();

        $this->fire(new BatchWasImported($this, $result));

        $this->log->info('Batch done.', compact('result'));

        return $result;
    }

    /**
     * @return void
     */
    protected function ensureLogger()
    {
        if ($this->log) {
            return;
        }

        $this->log = new Logger('importer', [ new NullHandler() ]);
    }

    /**
     * @param \Exception $e
     *
     * @return string
     */
    private function formatException($e)
    {
        if ($e instanceof ValidationException) {
            return $this->formatValidationException($e);
        }

        return $e;
    }

    /**
     * @param ValidationException $e
     *
     * @return string
     */
    protected function formatValidationException(ValidationException $e)
    {
        $text = 'Validation failed for ['.$e->getModelId().']:'.PHP_EOL;

        $text .= implode(PHP_EOL, $e->errors()->all());

        return $text;
    }

    /**
     * Fire an event.
     *
     * @param $event
     */
    protected function fire($event)
    {
        if (function_exists('event')) {
            event($event);
        }
    }

    /**
     * @return ImporterContract
     */
    public function getImporter()
    {
        return $this->importer;
    }

    /**
     * @return DataSourceContract
     */
    public function getDataSource()
    {
        return $this->dataSource;
    }

    /**
     * @param $value
     *
     * @return $this
     */
    public function setMode($value)
    {
        $this->mode = $value;

        return $this;
    }

    /**
     * @return int
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * @param $value
     *
     * @return $this
     */
    public function setBatchSize($value)
    {
        $this->batchSize = $value;

        return $this;
    }

    /**
     * @return int
     */
    public function getBatchSize()
    {
        return $this->batchSize;
    }

    /**
     * @param Logger $log
     *
     * @return $this
     */
    public function setLogger(Logger $log)
    {
        $this->log = $log;

        return $this;
    }

    /**
     * @return Logger
     */
    public function getLogger()
    {
        return $this->log;
    }

    /**
     * @param array|null $attributes
     *
     * @return $this
     */
    public function setAttributes($attributes)
    {
        $this->attributes = $attributes;

        return $this;
    }

    /**
     * @return array|null
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

}