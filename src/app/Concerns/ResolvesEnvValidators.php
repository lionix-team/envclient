<?php

declare(strict_types=1);

namespace Lionix\EnvClient\Concerns;

use Illuminate\Contracts\Container\Container;
use Lionix\EnvClient\Interfaces\EnvValidatorInterface;
use UnexpectedValueException;

trait ResolvesEnvValidators
{
    /**
     * Resolve the validators listed in the `env.rules` configuration.
     *
     * @return list<EnvValidatorInterface>
     *
     * @throws UnexpectedValueException
     */
    protected function envValidators(Container $container): array
    {
        $validators = [];

        foreach ((array) config('env.rules', []) as $class) {
            $validator = $container->make($class);

            if (! $validator instanceof EnvValidatorInterface) {
                throw new UnexpectedValueException(sprintf(
                    'Environment rule [%s] must implement [%s].', $class, EnvValidatorInterface::class
                ));
            }

            $validators[] = $validator;
        }

        return $validators;
    }
}
