<?php

namespace Lionix\EnvClient\Services;

use Lionix\EnvClient\Interfaces\EnvSetterInterface;

class EnvSetter implements EnvSetterInterface
{
    /**
     * Variables that are to be merged with current
     * env variables
     *
     * @var array
     */
    protected $variablesToSet = [];

    /**
     * Set sanitized env values and merge them with $_ENV global
     *
     * @param array $values
     *
     * @return void
     */
    public function set(array $values): void
    {
        $this->variablesToSet = array_merge(
            $this->variablesToSet,
            array_map([$this, 'sanitize'], $values)
        );
    }

    /**
     * Save env values to the file
     *
     * @return void
     */
    public function save(): void
    {
        $filepath = app()->environmentFilePath();

        $contents = file_get_contents($filepath);

        foreach ($this->variablesToSet as $key => $value) {
            if (!preg_match('/\b' . preg_quote($key) . '\b=/', $contents)) {
                $contents .= PHP_EOL . '$key=$value';
            } else {
                $contents = preg_replace(
                    '/^' . preg_quote($key) . '=[^\r\n]*/m',
                    $key . '=' . $value,
                    $contents
                );
            }
        }

        file_put_contents($filepath, $contents);

        $this->variablesToSet = [];
    }

    /**
     * Sanitize given value
     *
     * @param string $value
     *
     * @return string
     */
    protected function sanitize(string $value): string
    {
        $toReturn = $value;
        if (is_string($value)) {
            $toReturn = trim($toReturn);
            if (preg_match('/\s/', $toReturn)) {
                $toReturn = '"' . $toReturn . '"';
            }
        }
        return $toReturn;
    }
}
