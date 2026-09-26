<?php

declare(strict_types=1);

namespace Lionix\EnvClient\Support;

use Illuminate\Contracts\Container\Container;
use Illuminate\Support\MessageBag;
use Lionix\EnvClient\Exceptions\InvalidEnvironmentException;
use Lionix\EnvClient\Interfaces\EnvClientInterface;
use Lionix\EnvClient\Interfaces\EnvValidatorInterface;
use Psr\Log\LoggerInterface;
use UnexpectedValueException;

class EnvironmentChecker
{
    public function __construct(protected Container $container) {}

    /**
     * Validate the environment against the configured rules.
     */
    public function errors(): MessageBag
    {
        $client = $this->container->make(EnvClientInterface::class);
        $values = $client->all();

        foreach ((array) config('env.rules', []) as $class) {
            $validator = $this->container->make($class);

            if (! $validator instanceof EnvValidatorInterface) {
                throw new UnexpectedValueException(sprintf(
                    'Class [%s] listed in [env.rules] must implement [%s].', $class, EnvValidatorInterface::class
                ));
            }

            $client->useValidator($validator)->validate($values);
        }

        return $client->errors();
    }

    /**
     * Validate the environment and react according to the given mode.
     *
     * @param  'log'|'exception'  $mode
     *
     * @throws InvalidEnvironmentException
     */
    public function check(string $mode): void
    {
        $errors = $this->errors();

        if ($errors->isEmpty()) {
            return;
        }

        if ($mode === 'exception') {
            throw new InvalidEnvironmentException($errors);
        }

        $this->container->make(LoggerInterface::class)->warning(
            'Invalid environment configuration.',
            ['errors' => $errors->toArray()],
        );
    }
}
