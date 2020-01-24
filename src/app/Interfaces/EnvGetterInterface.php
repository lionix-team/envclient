<?php

namespace Lionix\EnvClient\Interfaces;

interface EnvGetterInterface 
{
    /**
     * Check if value exists
     *
     * @param string $key
     * 
     * @return bool
     */
    public function has(string $key) : bool;

    /**
     * Get single env variable value
     *
     * @param string $key
     * 
     * @return mixed
     */
    public function get(string $key);

    /**
     * Get all env variables
     *
     * @return array
     */
    public function all() : array;
}