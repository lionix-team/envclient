<?php

namespace Lionix\EnvClient\Tests;

use Illuminate\Foundation\Bootstrap\LoadEnvironmentVariables;
use Lionix\EnvClient\Providers\EnvClientServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app)
    {
        return [
            EnvClientServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        $root = dirname(__DIR__, 1);

        if (!file_exists($root . '/.env') && file_exists($root . '/.env.testing')) {
            copy($root . '/.env.testing', $root . '/.env');
        }

        $app->useEnvironmentPath($root);

        $app->bootstrapWith([LoadEnvironmentVariables::class]);

        parent::getEnvironmentSetUp($app);
    }

    public function __destruct()
    {
        $root = dirname(__DIR__, 1);
        !file_exists($root . '/.env') || unlink($root . '/.env');
    }
}
