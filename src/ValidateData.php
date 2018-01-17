<?php

namespace Lazychaser\DataImportTools;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator as ValidatorFacade;
use Lazychaser\DataImportTools\Exceptions\ValidationException;

trait ValidateData
{
    /**
     * @param $key
     * @param \Illuminate\Support\Collection $data
     *
     * @throws ValidationException
     */
    protected function validate($key, Collection $data)
    {
        $validator = ValidatorFacade::make($data->all(), $this->rules($key));

        if ($validator->fails()) {
            throw new ValidationException($key, $validator);
        }
    }

    /**
     * @param string $key
     *
     * @return array
     */
    protected function rules($key)
    {
        return [ ];
    }
}