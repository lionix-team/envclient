<?php

declare(strict_types=1);

namespace Lionix\EnvClient\Interfaces;

interface EnvForgetterInterface extends EnvSetterInterface
{
    /**
     * Queue variables to be removed from the environment file on save.
     *
     * @param  list<string>  $keys
     */
    public function forget(array $keys): void;
}
