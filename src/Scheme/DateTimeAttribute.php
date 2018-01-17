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
     * @param string $dataKey
     */
    public function __construct($id, $format, $dataKey = null)
    {
        parent::__construct($id, $dataKey);

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

}