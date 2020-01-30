<?php

namespace Lionix\EnvClient\Interfaces;

use Illuminate\Support\MessageBag;

interface EnvClientInterface 
{
    /**
     * Setup default dependencies
     * 
     * @return void
     */
    public function __construct();

    /**
     * Change client getter dependency
     *
     * @param Lionix\EnvClient\Interfaces\EnvGetterInterface $getter
     * 
     * @return Lionix\EnvClient\Interfaces\EnvClientInterface
     */
    public function useGetter(EnvGetterInterface $getter) : EnvClientInterface;

    /**
     * Change client setter dependency
     *
     * @param Lionix\EnvClient\Interfaces\EnvGetterInterface $setter
     * 
     * @return Lionix\EnvClient\Interfaces\EnvClientInterface
     */
    public function useSetter(EnvSetterInterface $setter) : EnvClientInterface;

    /**
     * Change client validator dependency
     *
     * @param Lionix\EnvClient\Interfaces\EnvGetterInterface $validator
     * 
     * @return Lionix\EnvClient\Interfaces\EnvClientInterface
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
     * Get .env variable file value by its key
     *
     * @param string $key
     * 
     * @return mixed
     */
    public function get(string $key);

    /**
     * Set .env variables using associative array
     *
     * @param array $values
     * 
     * @return Lionix\EnvClient\Interfaces\EnvClientInterface
     */
    public function set(array $values) : EnvClientInterface;


    /**
     * Save changes to .env file
     *
     * @return Lionix\EnvClient\Interfaces\EnvClientInterface
     */
    public function save() : EnvClientInterface;

    /**
     * Set and update associative array values
     *
     * @param array $values
     * 
     * @return Lionix\EnvClient\Interfaces\EnvClientInterface
     */
    public function update(array $values) : EnvClientInterface;

    /**
     * Validate associative array values
     *
     * @param array $values
     * 
     * @return boolean
     */
    public function validate(array $values) : bool;

    /**
     * Get all client errors during client lifetime
     *
     * @return MessageBag
     */
    public function errors() : MessageBag;
}