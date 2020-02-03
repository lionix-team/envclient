<?php

namespace Lionix\EnvClient\Services;

use Illuminate\Support\Env;
use Lionix\EnvClient\Interfaces\EnvGetterInterface;

class EnvGetter implements EnvGetterInterface
{
    /**
     * Wrap Illuminate\Support\Env get method
     * or env function value
     *
     * @param string $key
     * 
     * @return mixed
     */
    public function get(string $key)
    {
        return class_exists(Env::class) 
            ? Env::get($key) : env($key);
    }

    /**
     * Get all variable keys from the file
     * and map them with get method
     *
     * @return array
     */
    public function all() : array
    {
        $toReturn = [];
        $fp = fopen(app()->environmentFilePath(), "r");
        if ($fp) {
            while (($line = fgets($fp)) !== false) {
                if (preg_match("/^(\w*)"."=[^\r\n]*/m", $line)) {
                    $key = strtok($line, "=");
                    $toReturn[$key] = $this->get($key);
                }
            }
            fclose($fp);
        }
        return $toReturn;
    }

    /**
     * Check if env file has the given key
     *
     * @param string $key
     * 
     * @return boolean
     */
    public function has(string $key) : bool
    {
        return preg_match(
            "/^".preg_quote($key)."=[^\r\n]*/m",
            file_get_contents(app()->environmentFilePath())
        );
    }
}