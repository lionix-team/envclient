<?php

namespace Lionix\EnvClient\Services;

use Dotenv\Dotenv;
use Lionix\EnvClient\Services\EnvSetter;
use Lionix\EnvClient\Interfaces\EnvGetterInterface;

class EnvGetter implements EnvGetterInterface
{
    public function get(string $key)
    {
        return env($key);
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