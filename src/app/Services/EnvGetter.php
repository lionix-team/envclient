<?php

namespace Lionix\EnvClient\Services;

use Illuminate\Support\Env;
use Lionix\EnvClient\Interfaces\EnvGetterInterface;

class EnvGetter implements EnvGetterInterface
{
    public function get(string $key)
    {
        return Env::get($key);
    }

    public function all() : array
    {
        $toReturn = [];
        $fp = fopen(app()->environmentFilePath(), "r");
        if ($fp) {
            while (($line = fgets($fp)) !== false) {
                if (preg_match("/^(\w*)"."=[^\r\n]*/m", $line)) {
                    $key = strtok($line, "=");
                    $toReturn[$key] = env($key);
                }
            }
            fclose($fp);
        }
        return $toReturn;
    }

    public function has(string $key) : bool
    {
        return preg_match(
            "/^".preg_quote($key)."=[^\r\n]*/m", 
            file_get_contents(app()->environmentFilePath())
        );
    }
}