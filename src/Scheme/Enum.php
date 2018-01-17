<?php

namespace Lazychaser\DataImportTools\Scheme;

class Enum extends BasicAttribute
{
    /**
     * @var array
     */
    private $options;

    /**
     * Enum constructor.
     *
     * @param $id
     * @param array $options
     * @param null $dataKey
     */
    public function __construct($id, array $options, $dataKey = null)
    {
        parent::__construct($id);

        $this->options = $options;
    }

    /**
     * @param mixed $value
     *
     * @return mixed|null
     */
    public function normalize($value)
    {
        $value = parent::normalize($value);

        return isset($this->options[$value]) ? $this->options[$value] : $value;
    }

    /**
     * @param $id
     * @param array $options
     * @param null $dataKey
     *
     * @return Enum
     */
    public static function make($id, array $options, $dataKey = null)
    {
        return new static($id, $options, $dataKey);
    }
}