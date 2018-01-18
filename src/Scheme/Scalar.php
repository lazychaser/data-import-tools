<?php

namespace Lazychaser\DataImportTools\Scheme;

use Lazychaser\DataImportTools\Helpers;

/**
 * Class Scalar
 *
 * @method static \Lazychaser\DataImportTools\Scheme\Scalar string($id, $attribute = null)
 * @method static \Lazychaser\DataImportTools\Scheme\Scalar bool($id, $attribute = null)
 * @method static \Lazychaser\DataImportTools\Scheme\Scalar int($id, $attribute = null)
 * @method static \Lazychaser\DataImportTools\Scheme\Scalar float($id, $attribute = null)
 * @method static \Lazychaser\DataImportTools\Scheme\Scalar number($id, $attribute = null)
 *
 * @package \Lazychaser\DataImportTools\Scheme
 */
class Scalar extends BasicAttribute
{
    /**
     * List of supported types.
     */
    const TYPES = [ 'int', 'bool', 'float', 'string', 'number' ];

    /**
     * @var string
     */
    protected $type;

    /**
     * Scalar constructor.
     *
     * @param $id
     * @param string $type
     */
    public function __construct($id, $type = 'string')
    {
        parent::__construct($id);

        $this->type = $type;
    }

    /**
     * @param mixed $value
     *
     * @return mixed
     */
    public function normalize($value)
    {
        if (null === $value = parent::normalize($value)) {
            return $value;
        }

        return $this->cast($value);
    }

    /**
     * @return \Lazychaser\DataImportTools\Scheme\Scalar
     */
    public static function __callStatic($type, $arguments)
    {
        if ( ! in_array($type, self::TYPES)) {
            throw new \InvalidArgumentException("Unknown scalar attribute type [$type].");
        }

        if ( ! isset($arguments[0])) {
            throw new \InvalidArgumentException('The id of the attribute is not specified.');
        }

        return new self($arguments[0], $type);
    }

    /**
     * @param $value
     *
     * @return bool|float|int
     */
    public function cast($value)
    {
        switch ($this->type) {
            case 'bool':
                return (bool)$value;

            case 'int':
                return (int)$value;

            case 'float':
                return (float)$value;

            case 'number':
                return (float)$value;

            default:
                return $value;
        }
    }
}