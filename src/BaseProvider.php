<?php

namespace Lazychaser\DataImportTools;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

abstract class BaseProvider
{
    /**
     * Only read existing items.
     */
    const READ = 1;

    /**
     * Only create new items.
     */
    const CREATE = 2;

    /**
     * Allow to update and create items.
     */
    const READ_CREATE = self::READ|self::CREATE;

    /**
     * @var array
     */
    protected $columns;

    /**
     * @var bool
     */
    protected $slugKey;

    /**
     * @var string
     */
    protected $originalKeyAttr;

    /**
     * @var array
     */
    public $defaultAttributes;

    /**
     * BaseProvider constructor.
     *
     * @param array $columns
     */
    public function __construct(array $columns = [ '*' ])
    {
        $this->columns = $columns;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Model
     */
    abstract public function newEmptyModel();

    /**
     * Preload models from database by keys. This is needed when importing
     * a bunch of models.
     *
     * @param mixed $keys
     *
     * @return $this
     */
    abstract public function preload($keys);

    /**
     * @return string
     */
    abstract public function primaryKey();

    /**
     * Fetch a model by a key.
     *
     * If allowed by mode, the model will be created if it is not exists.
     *
     * @param mixed $key
     * @param int $mode
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    abstract public function fetch($key, $mode = self::READ_CREATE);

    /**
     * @param array $keys
     * @param int $mode
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function fetchMany(array $keys, $mode = self::READ_CREATE)
    {
        $items = array_map(function ($key) use ($mode) {
            return $this->fetch($key, $mode);
        }, $keys);

        return new EloquentCollection(array_filter(array_combine($keys, $items)));
    }

    /**
     * @param $key
     *
     * @return Model
     */
    protected function create($key)
    {
        $model = $this->newModel($key);

        $model->save();

        return $model;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function newQuery()
    {
        return $this->newEmptyModel()->newQuery();
    }

    /**
     * @param mixed $key
     *
     * @return mixed
     */
    public function normalizeKey($key)
    {
        return is_string($key) ? trim($key) : $key;
    }

    /**
     * @param $mode
     *
     * @return bool
     */
    protected function createAllowed($mode)
    {
        return ($mode & self::CREATE) > 0;
    }

    /**
     * @param $mode
     *
     * @return bool
     */
    protected function updateAllowed($mode)
    {
        return ($mode & self::READ) > 0;
    }

    /**
     * @param string $attribute
     *
     * @return $this
     */
    public function saveOriginalKeyTo($attribute)
    {
        $this->originalKeyAttr = $attribute;

        return $this;
    }

    /**
     * @param bool $value
     *
     * @return $this
     */
    public function slugKey($value = true)
    {
        $this->slugKey = $value;

        return $this;
    }

    public function setDefaultAttributes(array $value)
    {
        $this->defaultAttributes = $value;

        return $this;
    }

    /**
     * @param $key
     *
     * @return Model
     */
    protected function newModel($key)
    {
        $model = $this->newEmptyModel();

        if ($this->defaultAttributes) {
            $model->setRawAttributes($this->defaultAttributes);
        }

        if ($this->originalKeyAttr) {
            $model->setAttribute($this->originalKeyAttr, $key);
        }

        if ($this->slugKey) {
            $key = str_slug($key);
        }

        $model->setAttribute($this->primaryKey(), $key);

        return $model;
    }

}