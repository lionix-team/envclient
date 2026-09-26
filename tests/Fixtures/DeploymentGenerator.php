<?php

declare(strict_types=1);

namespace Lionix\EnvClient\Tests\Fixtures;

use Lionix\EnvClient\Services\EnvGenerator;

class DeploymentGenerator extends EnvGenerator
{
    public static int $calls = 0;

    public function values(): array
    {
        return [
            'APP_NAME' => 'Generated Name',
            'EMPTY_VALUE' => 'filled',
            'NUMERIC_VALUE' => '99',
            'GENERATED_SECRET' => function (): string {
                static::$calls++;

                return 'secret value';
            },
        ];
    }
}
