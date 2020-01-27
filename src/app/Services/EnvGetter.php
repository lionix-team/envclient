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
        $toReturn = [];
        foreach ($_ENV as $key => $value) {
            $toReturn[$key] = env($key);
        }
        return $toReturn;
    }

    public function has(string $key) : bool
    {
        return array_key_exists($key, $_ENV);
    }
}