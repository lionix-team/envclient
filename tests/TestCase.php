<?php

declare(strict_types=1);

namespace Lionix\EnvClient\Tests;

use Illuminate\Foundation\Bootstrap\LoadEnvironmentVariables;
use Lionix\EnvClient\Providers\EnvClientServiceProvider;
use Lionix\EnvClient\Support\EnvironmentFile;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected string $envDirectory;

    /**
     * @var array{0: array<string, mixed>, 1: array<string, mixed>, 2: array<string, string>}
     */
    protected array $processEnvironment;

    protected function setUp(): void
    {
        $this->processEnvironment = [$_ENV, $_SERVER, getenv()];

        parent::setUp();
    }

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

        // Testbench registers package providers before this runs.
        $app->instance(EnvironmentFile::BOOTED_PATH, $app->environmentFilePath());

        $app['config']->set('env.rules', []);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        foreach (glob($this->envDirectory.'/{,.}[!.,!..]*', GLOB_BRACE) ?: [] as $file) {
            @unlink($file);
        }

        @rmdir($this->envDirectory);

        [$_ENV, $_SERVER, $variables] = $this->processEnvironment;

        foreach (array_diff_key(getenv(), $variables) as $key => $value) {
            putenv((string) $key);
        }

        foreach ($variables as $key => $value) {
            putenv($key.'='.$value);
        }
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
