<?php

declare(strict_types=1);

namespace Lionix\EnvClient\Services;

use Illuminate\Support\Env;
use Lionix\EnvClient\Interfaces\EnvGetterInterface;
use Lionix\EnvClient\Support\EnvironmentFile;

class EnvGetter implements EnvGetterInterface
{
    /**
     * @deprecated Use EnvironmentFile::LINE_PATTERN instead.
     */
    public const LINE_PATTERN = EnvironmentFile::LINE_PATTERN;

    /**
     * Get the value of the variable.
     *
     * Values come from the runtime environment when the targeted file is the one
     * loaded at boot, otherwise (another file, or cached config) from the file itself.
     */
    public function get(string $key): mixed
    {
        if (EnvironmentFile::isLoaded()) {
            return Env::get($key);
        }

        return EnvironmentFile::cast(EnvironmentFile::parse(EnvironmentFile::read())[$key] ?? null);
    }

    public function all(): array
    {
        $contents = EnvironmentFile::read();
        $keys = EnvironmentFile::keys($contents);

        if (EnvironmentFile::isLoaded()) {
            return array_combine($keys, array_map(Env::get(...), $keys));
        }

        $values = EnvironmentFile::parse($contents);

        return array_combine($keys, array_map(
            static fn (string $key): mixed => EnvironmentFile::cast($values[$key] ?? null),
            $keys,
        ));
    }

    public function has(string $key): bool
    {
        return in_array($key, EnvironmentFile::keys(EnvironmentFile::read()), true);
    }
}
