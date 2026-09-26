<?php

declare(strict_types=1);

namespace Lionix\EnvClient\Events;

class EnvironmentVariablesGenerated
{
    /**
     * @param  string  $path  The environment file that was written.
     * @param  list<string>  $keys  Variables written by the generators.
     */
    public function __construct(
        public readonly string $path,
        public readonly array $keys,
    ) {}
}
