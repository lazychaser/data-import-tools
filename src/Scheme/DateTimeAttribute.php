<?php

namespace Lazychaser\DataImportTools\Scheme;

use Carbon\Carbon;

class DateTimeAttribute extends BasicAttribute
{
    /**
     * @var string
     */
    protected $format;

    /**
     * DateTimeAttribute constructor.
     *
     * @param $id
     * @param string $format
     */
    public function __construct($id, $format)
    {
        parent::__construct($id);

        $this->format = $format;
    }

    /**
     * @param string $value
     *
     * @return Carbon|null
     */
    public function normalize($value)
    {
        return empty($value)
            ? null
            : Carbon::createFromFormat($this->format, $value);
    }

    /**
     * @param $name
     * @param $arguments
     *
     * @return static
     *
     * @throws \Exception
     */
    public static function __callStatic($name, $arguments)
    {
        if (count($arguments) < 1) {
            throw new \Exception("Not enough arguments.");
        }

        return new static($name, $arguments[0]);
    }
}