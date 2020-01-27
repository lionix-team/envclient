<?php

namespace Lionix\EnvClient\Interfaces;

use Illuminate\Support\MessageBag;

interface EnvValidatorInterface
{
    /**
     * Merge current errors with given ones
     *
     * @param Illuminate\Support\MessageBag $errors
     * 
     * @return void
     */
    public function mergeErrors(MessageBag $errors) : void;

    /**
     * Get validation rules
     *
     * @return array
     */
    public function rules() : array;

    /**
     * Check if variables are valid
     *
     * @param array $values
     * 
     * @return boolean
     */
    public function validate(array $values) : bool;

    /**
     * Get validation errors MessageBag
     * 
     * @return Illuminate\Support\MessageBag
     */
    public function errors() : MessageBag;
}