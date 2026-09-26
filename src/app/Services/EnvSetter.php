<?php

declare(strict_types=1);

namespace Lionix\EnvClient\Services;

use InvalidArgumentException;
use Lionix\EnvClient\Events\EnvironmentFileUpdated;
use Lionix\EnvClient\Interfaces\EnvForgetterInterface;
use Lionix\EnvClient\Support\EnvironmentFile;
use Stringable;

class EnvSetter implements EnvForgetterInterface
{
    /**
     * Sanitized variables waiting to be written to the environment file.
     *
     * @var array<string, string>
     */
    protected array $variablesToSet = [];

    /**
     * Variables waiting to be removed from the environment file.
     *
     * @var array<string, true>
     */
    protected array $variablesToForget = [];

    public function set(array $values): void
    {
        foreach ($values as $key => $value) {
            $key = $this->validateKey((string) $key);

            $this->variablesToSet[$key] = $this->sanitize($value);

            unset($this->variablesToForget[$key]);
        }
    }

    public function forget(array $keys): void
    {
        foreach ($keys as $key) {
            $key = $this->validateKey((string) $key);

            $this->variablesToForget[$key] = true;

            unset($this->variablesToSet[$key]);
        }
    }

    public function save(): void
    {
        if ($this->variablesToSet === [] && $this->variablesToForget === []) {
            return;
        }

        $path = EnvironmentFile::path();
        $original = EnvironmentFile::read($path);
        $contents = $original;

        foreach ($this->variablesToSet as $key => $value) {
            $pattern = EnvironmentFile::keyPattern($key);

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

        foreach (array_keys($this->variablesToForget) as $key) {
            $contents = (string) preg_replace(
                '/^[ \t]*(?:export[ \t]+)?'.preg_quote($key, '/').'[ \t]*=[^\r\n]*(?:\r\n|\r|\n|$)/m',
                '',
                $contents,
            );
        }

        $set = $this->variablesToSet;
        $forgotten = array_keys($this->variablesToForget);

        $this->variablesToSet = [];
        $this->variablesToForget = [];

        if ($contents === $original && is_file($path)) {
            return;
        }

        $this->backup($path);

        file_put_contents($path, $contents, LOCK_EX);

        if (EnvironmentFile::isLoaded($path)) {
            $this->refreshRuntime($set, $forgotten);
        }

        if (app()->bound('events')) {
            app('events')->dispatch(new EnvironmentFileUpdated($path, $set, $forgotten));
        }
    }

    /**
     * Copy the current file to `<file>.backup` when backups are enabled.
     */
    protected function backup(string $path): void
    {
        if (config('env.backup', false) && is_file($path)) {
            copy($path, $path.'.backup');
        }
    }

    /**
     * Update the runtime environment so `env()` reflects the saved values.
     *
     * Configuration values that were already resolved from the environment are not affected.
     *
     * @param  array<string, string>  $set
     * @param  list<string>  $forgotten
     */
    protected function refreshRuntime(array $set, array $forgotten): void
    {
        foreach ($set as $key => $value) {
            $value = EnvironmentFile::parse($key.'='.$value)[$key] ?? '';

            $_ENV[$key] = $_SERVER[$key] = $value;

            putenv($key.'='.$value);
        }

        foreach ($forgotten as $key) {
            unset($_ENV[$key], $_SERVER[$key]);

            putenv($key);
        }
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
