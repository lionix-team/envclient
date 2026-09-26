<?php

declare(strict_types=1);

namespace Lionix\EnvClient\Exceptions;

use Illuminate\Support\MessageBag;
use RuntimeException;

class InvalidEnvironmentException extends RuntimeException
{
    public function __construct(public readonly MessageBag $errors)
    {
        parent::__construct('Invalid environment configuration: '.implode(' ', $errors->all()));
    }
}
