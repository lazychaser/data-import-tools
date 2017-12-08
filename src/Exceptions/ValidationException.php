<?php

namespace Lazychaser\DataImportTools\Exceptions;

use Illuminate\Validation\ValidationException as BaseValidationException;
use Illuminate\Validation\Validator;

class ValidationException extends BaseValidationException
{
    /**
     * @var string
     */
    protected $id;

    /**
     * @param string $id
     * @param Validator $validator
     */
    public function __construct($id, Validator $validator)
    {
        parent::__construct($validator);

        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getModelId()
    {
        return $this->id;
    }

}