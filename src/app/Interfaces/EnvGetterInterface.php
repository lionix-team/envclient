<?php

declare(strict_types=1);

namespace Lionix\EnvClient\Interfaces;

interface EnvGetterInterface
{
    /**
     * Determine if the environment file contains the given key.
     */
    public function has(string $key): bool;

    /**
     * Get the runtime value of an environment variable.
     */
    public function get(string $key): mixed;

    /**
     * Get all variables declared in the environment file.
     *
     * @return array<string, mixed>
     */
    public function all(): array;
}
