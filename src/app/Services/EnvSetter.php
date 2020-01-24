<?php

namespace Lionix\EnvClient\Services;

use Lionix\EnvClient\EnvGetter;
use Lionix\EnvClient\Interfaces\EnvSetterInterface;

class EnvSetter implements EnvSetterInterface
{
    protected $variables = [];

    public function set(array $toSet) : void
    {
        $sanitized = array_map([ $this, 'sanitize' ], $toSet);
        $this->variables = array_merge($this->variables, $sanitized);
    }

    public function save() : bool
    {
        $envFile = app()->environmentFilePath();
        $envContents = file_get_contents($envFile);
        foreach($this->variables as $key => $value) {
            if(!$this->processEnvFileContents(strtoupper($key), $value, $envContents)){
                return false;
            };
        }
        $fp = fopen($envFile, 'w');
        fwrite($fp, $envContents);
        fclose($fp);
        return true;
    }

    protected function processEnvFileContents(string $key, string $value, string &$envContents)
    {
        $valueExisted = !is_null(env($key));
        $sanitizedOldValue = $this->envExport($key);
        if(!$valueExisted){
            $envContents .= PHP_EOL . "$key=$value";
        } elseif(strlen($value)){
            $envContents = preg_replace("/\b$key\b\=$sanitizedOldValue/i", "$key=$value", $envContents);
        } else {
            return false;
        }
        return true;
    }

    protected function envExport(string $key) : string
    {
        $env = env($key);
        if(!is_string($env)){
            ob_start();
            var_export($env);
            $env = ob_get_contents();
            ob_get_clean();
        }
        return $this->sanitize($env);
    }

    protected function sanitize(string $value) : string
    {
        $toReturn = strval($value);
        $toReturn = trim($value);
        $toReturn = stripslashes($toReturn);
        if(preg_match('/\s/', $toReturn)){
            $toReturn = "\"$toReturn\"";
        }
        return $toReturn;
    }
}