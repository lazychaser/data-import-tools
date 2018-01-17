<?php

namespace Lazychaser\DataImportTools\Scheme\Helpers;

use Lazychaser\DataImportTools\Scheme\BelongsTo;
use Lazychaser\DataImportTools\Scheme\BelongsToMany;
use Lazychaser\DataImportTools\Scheme\HasMany;

/**
 * Class Relation
 *
 * @method static BelongsTo belongsTo($id, $repository, $attribute = null)
 * @method static BelongsToMany belongsToMany($id, $repository, $attribute = null)
 *
 * @package Lazychaser\DataImportTools\Scheme\Helpers
 */
class Relation
{
    /**
     * @var array
     */
    public static $types = [
        'belongsTo' => BelongsTo::class,
        'belongsToMany' => BelongsToMany::class,
    ];

    /**
     * @param $id
     * @param $dataMapper
     * @param $primaryKey
     * @param null $dataKey
     *
     * @return HasMany
     */
    public static function hasMany($id, $dataMapper, $primaryKey, $dataKey = null)
    {
        return new HasMany($id, $dataMapper, $primaryKey, $dataKey);
    }

    /**
     * @inheritDoc
     */
    public static function __callStatic($name, $arguments)
    {
        $dataKey = count($arguments) > 2 ? $arguments[2] : null;

        return new self::$types[$name]($arguments[0], $arguments[1], $dataKey);
    }

}