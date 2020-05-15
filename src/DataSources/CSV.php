<?php

namespace Lazychaser\DataImportTools\DataSources;

use Illuminate\Support\Arr;
use Lazychaser\DataImportTools\Contracts\DataSource;
use Lazychaser\DataImportTools\Exceptions\DataSourceException;
use Illuminate\Support\Collection;

class CSV implements DataSource
{
    /**
     * @var string
     */
    protected $sourceEncoding;

    /**
     * @var string
     */
    protected $targetEncoding = 'UTF-8';

    /**
     * @var string
     */
    protected $filename;

    /**
     * @var \Illuminate\Support\Collection
     */
    protected $header;

    /**
     * @var CSV delimiter
     */
    protected $delimiter = ',';

    /**
     * @var resource
     */
    protected $f;

    /**
     * @var array
     */
    protected $attributes;

    /**
     * @param string $filename
     * @param string $sourceEncoding
     */
    public function __construct($filename, $sourceEncoding = 'Windows-1251')
    {
        $this->sourceEncoding = $sourceEncoding;
        $this->filename = $filename;
    }

    /**
     * Start the reading.
     *
     * @throws DataSourceException
     */
    public function start()
    {
        if (!$this->f = fopen($this->filename, 'r')) {
            throw new DataSourceException('Could not read the file.');
        }

        $this->header = $this->readHeader();
    }

    /**
     * @param int $limit
     *
     * @return \Illuminate\Support\Collection
     *
     * @throws DataSourceException
     */
    public function read($limit = 100)
    {
        if (!$this->f) {
            throw new DataSourceException('Please start data source reading first.');
        }

        $data = new Collection;

        while ($limit-- > 0 && $row = $this->nextRow()) {
            $data->push($this->convertRow($row));
        }

        return $data;
    }

    /**
     * Finalize the data source.
     */
    public function stop()
    {
        if ($this->f) {
            fclose($this->f);

            $this->f = null;
        }
    }

    /**
     * Read header and return available fields.
     *
     * @return \Illuminate\Support\Collection
     *
     * @throws DataSourceException
     */
    protected function readHeader()
    {
        if (false === $row = $this->nextRow()) {
            throw new DataSourceException('Could not read CSV header.');
        }

        $header = [];

        foreach ($row as $index => $value) {
            if ($col = $this->parseHeaderCell($value, $index)) {
                $header[] = $col;
            }
        }

        if (empty($header)) {
            throw new DataSourceException('CSV header is empty.');
        }

        return Collection::make($header);
    }

    /**
     * Extract value.
     *
     * @param string $value
     *
     * @return string|null
     */
    protected function value($value)
    {
        $value = trim($value);

        if ($value === '' || $value === '-') {
            return null;
        }

        if ($this->sourceEncoding == $this->targetEncoding) {
            return $value;
        }

        return iconv($this->sourceEncoding, $this->targetEncoding, $value);
    }

    /**
     * Convert single row.
     *
     * @param array $data
     *
     * @return array
     */
    protected function convertRow(array $data)
    {
        $attributes = $this->getAvailableAttributes();

        $row = array_combine($attributes, array_pad([], count($attributes), null));

        foreach ($this->header as $col) {
            if (null !== $value = $this->value($data[$col->index])) {
                Arr::set($row, $col->path, $value);
            }
        }

        return $row;
    }

    /**
     * Read CSV line.
     *
     * @return array
     */
    protected function nextRow()
    {
        return fgetcsv($this->f, 0, $this->delimiter);
    }

    /**
     * @param string $value
     * @param int $index
     *
     * @return object|false
     */
    protected function parseHeaderCell($value, $index)
    {
        if (!$value = $this->value($value)) {
            return false;
        }

        if (preg_match('/(.*)\(([^)]+)\)\s*$/i', $value, $matches)) {
            return (object)[
                'title' => trim($matches[1]),
                'path' => trim($matches[2]),
                'index' => $index,
            ];
        }

        return (object)[ 'path' => $value, 'index' => $index ];
    }

    /**
     * @param string $value
     */
    public function setDelimiter($value)
    {
        $this->delimiter = $value;
    }

    /**
     * @param string $value
     */
    public function setTargetEncoding($value)
    {
        $this->targetEncoding = $value;
    }

    /**
     * Available after `convert` is executed.
     *
     * @return \Illuminate\Support\Collection|null
     */
    public function getHeader()
    {
        return $this->header;
    }

    /**
     * @return array
     */
    public function getAvailableAttributes()
    {
        if ($this->attributes !== null) {
            return $this->attributes;
        }

        return $this->attributes = $this->header
            ->pluck('path')
            ->map(function ($path) {
                $pos = strpos($path, '.');

                return $pos !== false ? substr($path, 0, $pos) : $path;
            })
            ->unique()
            ->all();
    }

}