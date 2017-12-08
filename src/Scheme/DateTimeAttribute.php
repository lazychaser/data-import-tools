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
     * @param string $attribute
     */
    public function __construct($id, $format, $attribute = null)
    {
        parent::__construct($id, $attribute);

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