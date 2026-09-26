<?php

declare(strict_types=1);

namespace Lionix\EnvClient\Tests\Commands;

use Dotenv\Dotenv;
use Illuminate\Support\Facades\File;
use Lionix\EnvClient\Tests\Fixtures\DeploymentGenerator;
use Lionix\EnvClient\Tests\Fixtures\NotAValidator;
use Lionix\EnvClient\Tests\Fixtures\ValidatorWithRules;
use Lionix\EnvClient\Tests\TestCase;
use UnexpectedValueException;

class EnvGenerateCommandTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        DeploymentGenerator::$calls = 0;
    }

    public function test_fills_only_missing_and_empty_variables(): void
    {
        config()->set('env.generators', [DeploymentGenerator::class]);

        $this->artisan('env:generate')
            ->expectsOutputToContain('2 environment variable(s) generated')
            ->assertSuccessful();

        $env = Dotenv::parse($this->envContents());

        $this->assertSame('lionix/envclient', $env['APP_NAME']);
        $this->assertSame('220', $env['NUMERIC_VALUE']);
        $this->assertSame('filled', $env['EMPTY_VALUE']);
        $this->assertSame('secret value', $env['GENERATED_SECRET']);
    }

    public function test_force_overwrites_existing_values(): void
    {
        config()->set('env.generators', [DeploymentGenerator::class]);

        $this->artisan('env:generate', ['--force' => true])->assertSuccessful();

        $env = Dotenv::parse($this->envContents());

        $this->assertSame('Generated Name', $env['APP_NAME']);
        $this->assertSame('99', $env['NUMERIC_VALUE']);
    }

    public function test_closures_are_not_called_for_skipped_variables(): void
    {
        config()->set('env.generators', [DeploymentGenerator::class]);

        $this->writeEnv("GENERATED_SECRET=keep\n");
        $this->artisan('env:generate')->assertSuccessful();

        $this->assertSame(0, DeploymentGenerator::$calls);
        $this->assertStringContainsString("GENERATED_SECRET=keep\n", $this->envContents());
    }

    public function test_dry_run_does_not_write(): void
    {
        config()->set('env.generators', [DeploymentGenerator::class]);

        $original = $this->envContents();

        $this->artisan('env:generate', ['--dry-run' => true])
            ->expectsOutputToContain('GENERATED_SECRET')
            ->expectsOutputToContain('Dry run')
            ->assertSuccessful();

        $this->assertSame($original, $this->envContents());
    }

    public function test_nothing_is_written_when_a_generated_value_is_invalid(): void
    {
        config()->set('env.generators', [DeploymentGenerator::class]);
        config()->set('env.rules', [ValidatorWithRules::class]);

        $generator = new class extends DeploymentGenerator {
            public function values(): array
            {
                return ['NUMERIC_VALUE' => 'NaN', 'OTHER' => 'ok'];
            }
        };

        $this->app->instance(DeploymentGenerator::class, $generator);

        $original = $this->envContents();

        $this->artisan('env:generate', ['--force' => true])
            ->expectsOutputToContain('NUMERIC_VALUE')
            ->expectsOutputToContain('Nothing was written')
            ->assertFailed();

        $this->assertSame($original, $this->envContents());
    }

    public function test_all_set(): void
    {
        config()->set('env.generators', [DeploymentGenerator::class]);

        $this->writeEnv("EMPTY_VALUE=x\nGENERATED_SECRET=y\n");

        $this->artisan('env:generate')
            ->expectsOutputToContain('already set')
            ->assertSuccessful();
    }

    public function test_without_generators(): void
    {
        $this->artisan('env:generate')
            ->expectsOutputToContain('No environment generators configured')
            ->assertSuccessful();
    }

    public function test_rejects_classes_that_are_not_generators(): void
    {
        config()->set('env.generators', [NotAValidator::class]);

        $this->expectException(UnexpectedValueException::class);

        $this->artisan('env:generate')->run();
    }

    public function test_make_envgenerator_generates_class(): void
    {
        $path = $this->app->path('Env/DeploymentEnv.php');

        $this->artisan('make:envgenerator', ['name' => 'DeploymentEnv'])->assertSuccessful();

        $contents = File::get($path);

        $this->assertStringContainsString('namespace App\Env;', $contents);
        $this->assertStringContainsString('class DeploymentEnv extends EnvGenerator', $contents);

        File::deleteDirectory($this->app->path('Env'));
    }
}
