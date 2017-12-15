<?php

namespace Lazychaser\DataImportTools;

class BasicProvider extends BaseModelProvider
{
    /**
     * @var string
     */
    protected $className;

    /**
     * @var string
     */
    protected $primaryKey;

    /**
     * @param string $className
     * @param string $primaryKey
     * @param array $columns
     */
    public function __construct(
        $className, $primaryKey = 'id', array $columns = [ '*' ])
    {
        parent::__construct($columns);

        $this->className = $className;
        $this->primaryKey = $primaryKey;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function newEmptyModel()
    {
        return new $this->className;
    }

    /**
     * @return string
     */
    public function primaryKey()
    {
        return $this->primaryKey;
    }
}