<?php

namespace Lionix\EnvClient\Services;

use Illuminate\Support\MessageBag;
use Illuminate\Support\Facades\Validator;
use Lionix\EnvClient\Interfaces\EnvValidatorInterface;

class EnvValidator implements EnvValidatorInterface
{
    /**
     * Errors property
     *
     * @var Illuminate\Support\MessageBag
     */
    protected $errors;

    /**
     * Rules applied to the validation
     *
     * @return array
     */
    public function rules() : array
    {
        return [
            //
        ];
    }

    /**
     * Check if variables are valid
     *
     * @param array $values
     * 
     * @return boolean
     */
    public function validate(array $values) : bool
    {
        $rules = $this->rules();
        $validator = Validator::make($values, $rules);
        $names = [];
        foreach ($rules as $key => $value) {
            $names[$key] = $key;
        }
        $validator->setAttributeNames($names);
        $this->mergeErrors($validator->errors());
        return $validator->passes();
    }

    /**
     * Get validation errors
     * 
     * @return Illuminate\Support\MessageBag
     */
    public function errors() : MessageBag
    {
        return $this->errors ?? new MessageBag();
    }

    /**
     * Merge errors
     *
     * @param MessageBag $errors
     * 
     * @return void
     */
    public function mergeErrors(MessageBag $errors) : void
    {
        $this->errors = $this->errors()->merge($errors);
    }
}