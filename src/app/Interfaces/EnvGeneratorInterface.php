<?php

declare(strict_types=1);

namespace Lionix\EnvClient\Interfaces;

interface EnvGeneratorInterface
{
    /**
     * Get the variables this generator provides.
     *
     * Values may be closures; they are resolved through the container
     * only when the variable is actually written.
     *
     * @return array<string, scalar|\Stringable|\Closure|null>
     */
    public function values(): array;
}
