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