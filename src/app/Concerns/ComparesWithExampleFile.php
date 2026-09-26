<?php

declare(strict_types=1);

namespace Lionix\EnvClient\Concerns;

use Illuminate\Support\Str;
use Lionix\EnvClient\Support\EnvironmentFile;

trait ComparesWithExampleFile
{
    /**
     * Get the path of the example file, relative to the targeted environment file.
     */
    protected function examplePath(): string
    {
        $example = $this->stringOption('example') ?: '.env.example';

        if (Str::startsWith($example, ['/', '\\']) || preg_match('/^[A-Za-z]:[\\\\\/]/', $example) === 1) {
            return $example;
        }

        return $this->laravel->environmentPath().DIRECTORY_SEPARATOR.$example;
    }

    /**
     * Get the keys declared in the example file but not in the environment file, and vice versa.
     *
     * @return array{0: list<string>, 1: list<string>}
     */
    protected function compareWithExample(string $examplePath): array
    {
        $envKeys = EnvironmentFile::keys(EnvironmentFile::read());
        $exampleKeys = EnvironmentFile::keys(EnvironmentFile::read($examplePath));

        return [
            array_values(array_diff($exampleKeys, $envKeys)),
            array_values(array_diff($envKeys, $exampleKeys)),
        ];
    }
}
