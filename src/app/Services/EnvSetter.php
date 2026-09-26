<?php

declare(strict_types=1);

namespace Lionix\EnvClient\Services;

use InvalidArgumentException;
use Lionix\EnvClient\Interfaces\EnvSetterInterface;
use Stringable;

class EnvSetter implements EnvSetterInterface
{
    /**
     * Sanitized variables waiting to be written to the environment file.
     *
     * @var array<string, string>
     */
    protected array $variablesToSet = [];

    public function set(array $values): void
    {
        foreach ($values as $key => $value) {
            $this->variablesToSet[$this->validateKey((string) $key)] = $this->sanitize($value);
        }
    }

    public function save(): void
    {
        if ($this->variablesToSet === []) {
            return;
        }

        $path = app()->environmentFilePath();

        $contents = is_file($path) ? (string) file_get_contents($path) : '';

        foreach ($this->variablesToSet as $key => $value) {
            $pattern = '/^([ \t]*(?:export[ \t]+)?)'.preg_quote($key, '/').'[ \t]*=[^\r\n]*/m';

            if (preg_match($pattern, $contents) === 1) {
                $contents = (string) preg_replace_callback(
                    $pattern,
                    static fn (array $matches): string => $matches[1].$key.'='.$value,
                    $contents,
                );

                continue;
            }

            if ($contents !== '' && ! str_ends_with($contents, "\n")) {
                $contents .= PHP_EOL;
            }

            $contents .= $key.'='.$value.PHP_EOL;
        }

        file_put_contents($path, $contents, LOCK_EX);

        $this->variablesToSet = [];
    }

    /**
     * Ensure the key is a valid environment variable name.
     *
     * @throws InvalidArgumentException
     */
    protected function validateKey(string $key): string
    {
        if (preg_match('/^[A-Za-z_][A-Za-z0-9_.]*$/', $key) !== 1) {
            throw new InvalidArgumentException("Invalid environment variable name [{$key}].");
        }

        return $key;
    }

    /**
     * Convert the given value into a string safe to be written to the environment file.
     */
    protected function sanitize(mixed $value): string
    {
        $value = match (true) {
            $value === null => 'null',
            is_bool($value) => $value ? 'true' : 'false',
            is_scalar($value), $value instanceof Stringable => trim((string) $value),
            default => throw new InvalidArgumentException(
                'Environment values must be scalar, null or Stringable, ['.get_debug_type($value).'] given.'
            ),
        };

        if (preg_match('/[\r\n]/', $value) === 1) {
            throw new InvalidArgumentException('Environment values must not contain line breaks.');
        }

        if ($this->isQuoted($value) || preg_match('/[\s#"\'\\\\]/', $value) !== 1) {
            return $value;
        }

        return '"'.addcslashes($value, '"\\').'"';
    }

    /**
     * Determine if the value is already wrapped in matching quotes.
     */
    protected function isQuoted(string $value): bool
    {
        return strlen($value) > 1
            && in_array($value[0], ['"', "'"], true)
            && str_ends_with($value, $value[0]);
    }
}
