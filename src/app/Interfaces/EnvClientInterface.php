<?php

namespace Lionix\EnvClient\Interfaces;

use Lionix\EnvClient\EnvGetter;
use Lionix\EnvClient\EnvSetter;
use Illuminate\Support\MessageBag;

interface EnvClientInterface 
{
    /**
     * Change client getter provider
     *
     * @param EnvGetterInterface $getter
     * 
     * @return EnvClientInterface
     */
    public function useGetter(EnvGetterInterface $getter) : EnvClientInterface;

    /**
     * Change client setter provider
     *
     * @param EnvSetterInterface $setter
     * 
     * @return EnvClientInterface
     */
    public function useSetter(EnvSetterInterface $setter) : EnvClientInterface;

    /**
     * Change client validator provider
     *
     * @param EnvValidatorInterface $validator
     * 
     * @return EnvClientInterface
     */
    public function useValidator(EnvValidatorInterface $validator) : EnvClientInterface;

    /**
     * Get all .env variables
     *
     * @return array
     */
    public function all() : array;

    /**
     * Check if .env variable key exists
     *
     * @param string $key
     * 
     * @return boolean
     */
    public function has(string $key) : bool;

    /**
     * Get .env variable by its key
     *
     * @param string $key
     * 
     * @return void
     */
    public function get(string $key);

    /**
     * Set .env variables using associative array
     *
     * @param array $values
     * 
     * @return EnvClientInterface
     */
    public function set(array $values) : EnvClientInterface;

    /**
     * Validate associative array values
     *
     * @param array $values
     * 
     * @return boolean
     */
    public function validate(array $values) : bool;

    /**
     * Get all client errors during class lifetime
     *
     * @return MessageBag
     */
    public function errors() : MessageBag;

    /**
     * Save changes to .env file
     *
     * @return EnvClientInterface
     */
    public function save() : EnvClientInterface;

    /**
     * Set and update associative array values
     *
     * @param array $values
     * 
     * @return EnvClientInterface
     */
    public function update(array $values) : EnvClientInterface;

    /**
     * Get validator ruleset
     *
     * @return array
     */
    public function rules() : array;
}