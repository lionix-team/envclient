<?php

declare(strict_types=1);

namespace Lionix\EnvClient\Events;

class EnvironmentFileUpdated
{
    /**
     * @param  string  $path  The environment file that was written.
     * @param  array<string, string>  $set  Variables written, with the value as stored in the file.
     * @param  list<string>  $forgotten  Variables removed from the file.
     */
    public function __construct(
        public readonly string $path,
        public readonly array $set,
        public readonly array $forgotten,
    ) {}

    /**
     * Get the names of all variables that were changed.
     *
     * @return list<string>
     */
    public function keys(): array
    {
        return array_values(array_unique([...array_keys($this->set), ...$this->forgotten]));
    }
}
