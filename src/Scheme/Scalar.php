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
     * @param null $dataKey
     */
    public function __construct($id, $type = 'string', $dataKey = null)
    {
        parent::__construct($id, $dataKey);

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

        switch ($this->type) {
            case 'bool':
                return (bool)$value;

            case 'int':
                return (int)Helpers::parseInt($value);

            case 'float':
                return (float)Helpers::parseFloat($value);

            case 'number':
                return Helpers::parseFloat($value);

            default:
                return $value;
        }
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

        $dataKey = isset($arguments[1]) ? $arguments[1] : null;

        return new self($arguments[0], $type, $dataKey);
    }
}