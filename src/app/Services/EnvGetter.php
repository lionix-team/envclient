<?php

declare(strict_types=1);

namespace Lionix\EnvClient\Services;

use Illuminate\Support\Env;
use Lionix\EnvClient\Interfaces\EnvGetterInterface;

class EnvGetter implements EnvGetterInterface
{
    /**
     * Matches a `KEY=value` (optionally `export KEY=value`) declaration line.
     */
    public const LINE_PATTERN = '/^[ \t]*(?:export[ \t]+)?([A-Za-z_][A-Za-z0-9_.]*)[ \t]*=/m';

    public function get(string $key): mixed
    {
        return Env::get($key);
    }

    public function all(): array
    {
        preg_match_all(static::LINE_PATTERN, $this->contents(), $matches);

        $variables = [];

        foreach ($matches[1] as $key) {
            $variables[$key] = $this->get($key);
        }

        return $variables;
    }

    public function has(string $key): bool
    {
        $pattern = '/^[ \t]*(?:export[ \t]+)?'.preg_quote($key, '/').'[ \t]*=/m';

        return preg_match($pattern, $this->contents()) === 1;
    }

    /**
     * Read the environment file contents.
     */
    protected function contents(): string
    {
        $path = app()->environmentFilePath();

        return is_file($path) ? (string) file_get_contents($path) : '';
    }
}
