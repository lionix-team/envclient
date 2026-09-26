<?php

declare(strict_types=1);

namespace Lionix\EnvClient\Concerns;

use Closure;
use Illuminate\Encryption\Encrypter;
use Illuminate\Support\Env;
use Illuminate\Support\Str;
use Lionix\EnvClient\Support\EnvironmentFile;
use RuntimeException;
use Throwable;

trait InteractsWithEnvironmentFile
{
    /**
     * Options shared by the commands that read or write the environment file.
     */
    protected const FILE_OPTIONS = '
        {--file= : Use this environment file instead of .env (relative to the environment path)}
        {--encrypted : Work on the encrypted copy of the file (<file>.encrypted), as created by env:encrypt}
        {--key= : The encryption key for --encrypted (defaults to LARAVEL_ENV_ENCRYPTION_KEY)}
        {--cipher= : The encryption cipher for --encrypted (defaults to AES-256-CBC)}';

    /**
     * Run the callback against the environment file selected by the command options.
     *
     * @param  Closure(): int  $callback
     */
    protected function withEnvironmentFile(Closure $callback, bool $writes = false, bool $mustExist = true): int
    {
        $app = $this->laravel;
        $environmentPath = $app->environmentPath();
        $environmentFile = $app->environmentFile();

        try {
            if (($file = $this->stringOption('file')) !== '') {
                $this->targetEnvironmentFile($file);
            }

            if ($this->option('encrypted')) {
                return $this->withDecryptedEnvironmentFile($callback, $writes);
            }

            if ($mustExist && ! is_file(EnvironmentFile::path())) {
                $this->components->error('Environment file ['.EnvironmentFile::path().'] not found.');

                return self::FAILURE;
            }

            return $callback();
        } finally {
            $app->useEnvironmentPath($environmentPath);
            $app->loadEnvironmentFrom($environmentFile);
        }
    }

    /**
     * Point the application to the given environment file.
     */
    protected function targetEnvironmentFile(string $file): void
    {
        $path = Str::startsWith($file, ['/', '\\']) || preg_match('/^[A-Za-z]:[\\\\\/]/', $file) === 1
            ? $file
            : $this->laravel->environmentPath().DIRECTORY_SEPARATOR.$file;

        $this->laravel->useEnvironmentPath(dirname($path));
        $this->laravel->loadEnvironmentFrom(basename($path));
    }

    /**
     * Decrypt the encrypted environment file into a private temporary file, run the
     * callback against it and encrypt the changes back.
     *
     * @param  Closure(): int  $callback
     */
    protected function withDecryptedEnvironmentFile(Closure $callback, bool $writes): int
    {
        $encryptedPath = EnvironmentFile::path().'.encrypted';

        if (! is_file($encryptedPath)) {
            $this->components->error("Encrypted environment file [{$encryptedPath}] not found.");

            return self::FAILURE;
        }

        try {
            $encrypter = $this->environmentEncrypter();
            $contents = $encrypter->decrypt((string) file_get_contents($encryptedPath));
        } catch (Throwable $e) {
            $this->components->error('Unable to decrypt the environment file: '.$e->getMessage());

            return self::FAILURE;
        }

        $temporary = tempnam(sys_get_temp_dir(), 'envclient');

        if ($temporary === false) {
            throw new RuntimeException('Unable to create a temporary file.');
        }

        chmod($temporary, 0600);
        file_put_contents($temporary, $contents);

        $backup = config('env.backup', false);
        config(['env.backup' => false]);

        $this->laravel->useEnvironmentPath(dirname($temporary));
        $this->laravel->loadEnvironmentFrom(basename($temporary));

        try {
            $result = $callback();

            $updated = (string) file_get_contents($temporary);

            if ($writes && $updated !== $contents) {
                if ($backup) {
                    copy($encryptedPath, $encryptedPath.'.backup');
                }

                file_put_contents($encryptedPath, $encrypter->encrypt($updated), LOCK_EX);
            }

            return $result;
        } finally {
            config(['env.backup' => $backup]);

            @unlink($temporary);
        }
    }

    /**
     * Build the encrypter from the command options, the same way env:encrypt does.
     *
     * @throws RuntimeException
     */
    protected function environmentEncrypter(): Encrypter
    {
        $key = $this->stringOption('key') ?: Env::get('LARAVEL_ENV_ENCRYPTION_KEY');

        if (! is_string($key) || $key === '') {
            throw new RuntimeException('A key is required (use --key or LARAVEL_ENV_ENCRYPTION_KEY).');
        }

        if (Str::startsWith($key, 'base64:')) {
            $key = base64_decode(Str::after($key, 'base64:'));
        }

        return new Encrypter($key, $this->stringOption('cipher') ?: 'AES-256-CBC');
    }

    /**
     * Get a string option, or an empty string when it is not given.
     */
    protected function stringOption(string $name): string
    {
        $value = $this->option($name);

        return is_string($value) ? $value : '';
    }

    /**
     * Get a string argument, or an empty string when it is not given.
     */
    protected function stringArgument(string $name): string
    {
        $value = $this->argument($name);

        return is_string($value) ? $value : '';
    }
}
