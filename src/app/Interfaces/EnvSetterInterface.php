<?php

declare(strict_types=1);

namespace Lionix\EnvClient\Interfaces;

interface EnvSetterInterface
{
    /**
     * Queue variables to be written to the environment file.
     *
     * @param  array<string, scalar|\Stringable|null>  $values
     */
    public function set(array $values): void;

    /**
     * Write the queued variables to the environment file.
     */
    public function save(): void;
}
