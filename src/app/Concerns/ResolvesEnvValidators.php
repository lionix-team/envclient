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
        return $this->resolveConfiguredClasses($container, 'env.rules', EnvValidatorInterface::class);
    }

    /**
     * Resolve the classes listed under the given configuration key.
     *
     * @template T of object
     *
     * @param  class-string<T>  $interface
     * @return list<T>
     *
     * @throws UnexpectedValueException
     */
    protected function resolveConfiguredClasses(Container $container, string $key, string $interface): array
    {
        $instances = [];

        foreach ((array) config($key, []) as $class) {
            $instance = $container->make($class);

            if (! $instance instanceof $interface) {
                throw new UnexpectedValueException(sprintf(
                    'Class [%s] listed in [%s] must implement [%s].', $class, $key, $interface
                ));
            }

            $instances[] = $instance;
        }

        return $instances;
    }
}
