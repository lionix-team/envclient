<?php

declare(strict_types=1);

namespace Lionix\EnvClient\Concerns;

use Illuminate\Support\Str;

trait MasksSecrets
{
    /**
     * Variable name patterns considered secret when `env.hidden` is not configured.
     *
     * @var list<string>
     */
    protected static array $defaultHiddenPatterns = [
        '*PASSWORD*',
        '*SECRET*',
        '*TOKEN*',
        '*PRIVATE*',
        '*_KEY',
        '*_KEY_ID',
    ];

    /**
     * Determine if the variable holds a secret that should be masked in output.
     */
    protected function isSecret(string $key): bool
    {
        $patterns = (array) config('env.hidden', static::$defaultHiddenPatterns);

        return Str::is(array_map('strtoupper', $patterns), strtoupper($key));
    }

    /**
     * Format a value for console output, masking it when it is a secret.
     */
    protected function displayValue(string $key, mixed $value, bool $reveal = false): string
    {
        if (! $reveal && $this->isSecret($key) && ! in_array($value, [null, ''], true)) {
            return '********';
        }

        return match (true) {
            $value === true => 'true',
            $value === false => 'false',
            $value === null => 'null',
            is_scalar($value), $value instanceof \Stringable => (string) $value,
            default => get_debug_type($value),
        };
    }
}
