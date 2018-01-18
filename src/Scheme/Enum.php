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
     */
    public function __construct($id, array $options)
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
     *
     * @return Enum
     */
    public static function make($id, array $options)
    {
        return new static($id, $options);
    }
}