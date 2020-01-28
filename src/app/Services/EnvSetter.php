<?php

namespace Lionix\EnvClient\Services;

use Lionix\EnvClient\Interfaces\EnvSetterInterface;

class EnvSetter implements EnvSetterInterface
{
    protected $variablesToSet = [];

    public function set(array $toSet) : void
    {
        $this->variablesToSet = array_merge(
            $this->variablesToSet,
            array_map([$this, "sanitize"], $toSet)
        );
    }

    public function save() : void
    {
        $filepath = app()->environmentFilePath();
        $contents = file_get_contents($filepath);
        foreach ($this->variablesToSet as $key => $value) {
            if (!preg_match("/\b".preg_quote($key)."\b=/", $contents)) {
                $contents .= PHP_EOL . "$key=$value";
            } else {
                $contents = preg_replace(
                    "/^".preg_quote($key)."=[^\r\n]*/m",
                    $key."=".$value, 
                    $contents
                );
            }
        }
        file_put_contents($filepath, $contents);
        $_ENV = array_merge($_ENV, $this->variablesToSet);
        $this->variablesToSet = [];
    }

    protected function sanitize(string $value) : string
    {
        $toReturn = $value;
        if (is_string($value)) {
            $toReturn = trim($toReturn);
            if (preg_match("/\s/", $toReturn)) {
                $toReturn = "\"$toReturn\"";
            }
        }
        return $toReturn;
    }
}