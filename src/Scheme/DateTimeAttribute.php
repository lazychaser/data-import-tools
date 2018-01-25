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
     * @var string
     */
    protected $timezone;

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
        if (empty($value)) {
            return null;
        }

        $value = Carbon::createFromFormat($this->format, $value, $this->timezone);

        if ($this->timezone &&
            $this->timezone !== ($currentTimezone = config('app.timezone'))
        ) {
            $value->setTimezone($currentTimezone);
        }

        return $value;
    }

    /**
     * @param $value
     *
     * @return $this
     */
    public function timezone($value)
    {
        $this->timezone = $value;

        return $this;
    }

    /**
     * @param $id
     * @param $format
     *
     * @return static
     */
    public static function make($id, $format)
    {
        return new static($id, $format);
    }
}