<?php

declare(strict_types=1);

namespace Lionix\EnvClient\Tests;

use Illuminate\Foundation\Bootstrap\LoadEnvironmentVariables;
use Lionix\EnvClient\Providers\EnvClientServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected string $envDirectory;

    protected function getPackageProviders($app): array
    {
        return [
            EnvClientServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $this->envDirectory = sys_get_temp_dir().'/envclient-'.bin2hex(random_bytes(6));

        mkdir($this->envDirectory);

        copy(dirname(__DIR__).'/.env.testing', $this->envDirectory.'/.env');

        $app->useEnvironmentPath($this->envDirectory);

        $app->bootstrapWith([LoadEnvironmentVariables::class]);

        $app['config']->set('env.rules', []);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        @unlink($this->envDirectory.'/.env');
        @rmdir($this->envDirectory);
    }

    protected function envContents(): string
    {
        return (string) file_get_contents($this->envDirectory.'/.env');
    }

    protected function writeEnv(string $contents): void
    {
        file_put_contents($this->envDirectory.'/.env', $contents);
    }
}
