<?php

namespace Lionix\EnvClient\Services;

use Lionix\EnvClient\Interfaces\EnvGetterInterface;

class EnvGetter implements EnvGetterInterface
{
    protected $errors = [];

    public function get(string $key)
    {
        return env($key, null);
    }

    public function all() : array
    {
        return $_ENV;
    }

    public function has(string $key) : bool
    {
        return array_key_exists($key, $this->all());
    }
}