<?php

declare(strict_types=1);

namespace Lionix\EnvClient\Facades;

use Illuminate\Support\Facades\Facade;
use Lionix\EnvClient\Interfaces\EnvClientInterface;

/**
 * Each static call resolves a fresh client, so chain the calls
 * that should share validators and errors.
 *
 * @method static \Lionix\EnvClient\Interfaces\EnvClientInterface useGetter(\Lionix\EnvClient\Interfaces\EnvGetterInterface $getter)
 * @method static \Lionix\EnvClient\Interfaces\EnvClientInterface useSetter(\Lionix\EnvClient\Interfaces\EnvSetterInterface $setter)
 * @method static \Lionix\EnvClient\Interfaces\EnvClientInterface useValidator(\Lionix\EnvClient\Interfaces\EnvValidatorInterface $validator)
 * @method static array<string, mixed> all()
 * @method static bool has(string $key)
 * @method static mixed get(string $key)
 * @method static \Lionix\EnvClient\Interfaces\EnvClientInterface set(array<string, scalar|\Stringable|null> $values)
 * @method static \Lionix\EnvClient\Interfaces\EnvClientInterface update(array<string, scalar|\Stringable|null> $values)
 * @method static bool validate(array<string, mixed> $values)
 *
 * @see \Lionix\EnvClient\Services\EnvClient
 */
class EnvClient extends Facade
{
    protected static $cached = false;

    protected static function getFacadeAccessor(): string
    {
        return EnvClientInterface::class;
    }
}
