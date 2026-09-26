<?php

declare(strict_types=1);

namespace Lionix\EnvClient\Support;

use Dotenv\Dotenv;
use Dotenv\Exception\ExceptionInterface as DotenvException;

class EnvironmentFile
{
    /**
     * Matches a `KEY=value` (optionally `export KEY=value`) declaration line.
     */
    public const LINE_PATTERN = '/^[ \t]*(?:export[ \t]+)?([A-Za-z_][A-Za-z0-9_.]*)[ \t]*=/m';

    /**
     * Container key holding the environment file loaded when the application booted.
     */
    public const BOOTED_PATH = 'envclient.booted_path';

    /**
     * Get the path of the environment file currently targeted by the application.
     */
    public static function path(): string
    {
        return app()->environmentFilePath();
    }

    /**
     * Determine if the targeted file is the one whose values were loaded into the
     * runtime environment (and config is not cached, which skips loading it).
     */
    public static function isLoaded(?string $path = null): bool
    {
        $app = app();

        if ($app->configurationIsCached()) {
            return false;
        }

        $booted = $app->bound(self::BOOTED_PATH) ? $app->make(self::BOOTED_PATH) : null;

        return $booted === null || $booted === ($path ?? static::path());
    }

    /**
     * Read the raw file contents, or an empty string when it does not exist.
     */
    public static function read(?string $path = null): string
    {
        $path ??= static::path();

        return is_file($path) ? (string) file_get_contents($path) : '';
    }

    /**
     * Get the variable names declared in the given contents, in order.
     *
     * @return list<string>
     */
    public static function keys(string $contents): array
    {
        preg_match_all(self::LINE_PATTERN, $contents, $matches);

        return array_values(array_unique($matches[1]));
    }

    /**
     * Get the declaration lines of the given contents keyed by variable name.
     *
     * @return array<string, string>
     */
    public static function lines(string $contents): array
    {
        $lines = [];

        foreach (preg_split('/\r\n|\r|\n/', $contents) ?: [] as $line) {
            if (preg_match(self::LINE_PATTERN, $line, $matches) === 1) {
                $lines[$matches[1]] ??= $line;
            }
        }

        return $lines;
    }

    /**
     * Parse the given contents into raw (uncast) values.
     *
     * @return array<string, string|null>
     */
    public static function parse(string $contents): array
    {
        try {
            return Dotenv::parse($contents);
        } catch (DotenvException) {
            return [];
        }
    }

    /**
     * Cast a raw value the same way Laravel's `env()` helper does.
     */
    public static function cast(?string $value): mixed
    {
        if ($value === null) {
            return null;
        }

        return match (strtolower($value)) {
            'true', '(true)' => true,
            'false', '(false)' => false,
            'empty', '(empty)' => '',
            'null', '(null)' => null,
            default => preg_match('/\A([\'"])(.*)\1\z/', $value, $matches) === 1 ? $matches[2] : $value,
        };
    }

    /**
     * Build the pattern matching the declaration line of the given key.
     */
    public static function keyPattern(string $key): string
    {
        return '/^([ \t]*(?:export[ \t]+)?)'.preg_quote($key, '/').'[ \t]*=[^\r\n]*/m';
    }
}
