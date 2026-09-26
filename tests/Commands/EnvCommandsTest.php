<?php

declare(strict_types=1);

namespace Lionix\EnvClient\Tests\Commands;

use Illuminate\Support\Facades\File;
use Lionix\EnvClient\Tests\Fixtures\FailingRules;
use Lionix\EnvClient\Tests\Fixtures\NotAValidator;
use Lionix\EnvClient\Tests\Fixtures\RequiresOtherVariable;
use Lionix\EnvClient\Tests\Fixtures\ValidatorWithRules;
use Lionix\EnvClient\Tests\TestCase;
use UnexpectedValueException;

class EnvCommandsTest extends TestCase
{
    public function test_env_get_prints_value(): void
    {
        $this->artisan('env:get', ['key' => 'APP_NAME'])
            ->expectsOutput('lionix/envclient')
            ->assertSuccessful();
    }

    public function test_env_get_prints_falsy_values(): void
    {
        $this->artisan('env:get', ['key' => 'BOOLEAN_VALUE'])
            ->expectsOutput('false')
            ->assertSuccessful();
    }

    public function test_env_get_fails_for_missing_variable(): void
    {
        $this->artisan('env:get', ['key' => 'MISSING_VALUE'])
            ->expectsOutputToContain('not found')
            ->assertFailed();
    }

    public function test_env_set_writes_value(): void
    {
        $this->artisan('env:set', ['key' => 'new_value', 'value' => 'hello world'])
            ->expectsOutputToContain('NEW_VALUE successfully set')
            ->assertSuccessful();

        $this->assertStringContainsString("NEW_VALUE=\"hello world\"\n", $this->envContents());
    }

    public function test_env_set_rejects_invalid_value(): void
    {
        config()->set('env.rules', [ValidatorWithRules::class]);

        $original = $this->envContents();

        $this->artisan('env:set', ['key' => 'NUMERIC_VALUE', 'value' => 'NaN'])
            ->expectsOutputToContain('NUMERIC_VALUE')
            ->assertFailed();

        $this->assertSame($original, $this->envContents());
    }

    public function test_env_set_is_not_blocked_by_rules_for_other_variables(): void
    {
        config()->set('env.rules', [RequiresOtherVariable::class]);

        $this->artisan('env:set', ['key' => 'NUMERIC_VALUE', 'value' => '42'])
            ->assertSuccessful();

        $this->assertStringContainsString("NUMERIC_VALUE=42\n", $this->envContents());
    }

    public function test_env_set_rejects_invalid_key(): void
    {
        $this->artisan('env:set', ['key' => 'INVALID KEY', 'value' => 'x'])
            ->expectsOutputToContain('Invalid environment variable name')
            ->assertFailed();
    }

    public function test_env_check_passes(): void
    {
        config()->set('env.rules', [ValidatorWithRules::class, RequiresOtherVariable::class]);

        $this->artisan('env:check')
            ->expectsOutputToContain('All environment variables are valid')
            ->assertSuccessful();
    }

    public function test_env_check_fails(): void
    {
        config()->set('env.rules', [ValidatorWithRules::class, FailingRules::class]);

        $this->artisan('env:check')
            ->expectsOutputToContain('MISSING_VALUE')
            ->expectsOutputToContain('URL_VALUE')
            ->assertFailed();
    }

    public function test_env_check_without_rules(): void
    {
        $this->artisan('env:check')
            ->expectsOutputToContain('No environment validation rules configured')
            ->assertSuccessful();
    }

    public function test_env_check_rejects_classes_that_are_not_validators(): void
    {
        config()->set('env.rules', [NotAValidator::class]);

        $this->expectException(UnexpectedValueException::class);

        $this->artisan('env:check')->run();
    }

    public function test_env_empty_lists_only_empty_variables(): void
    {
        $this->artisan('env:empty')
            ->expectsOutputToContain('EMPTY_VALUE is empty')
            ->doesntExpectOutputToContain('BOOLEAN_VALUE')
            ->doesntExpectOutputToContain('NULL_VALUE')
            ->assertSuccessful();
    }

    public function test_make_envrule_generates_class(): void
    {
        $path = $this->app->path('Env/Database/DatabaseEnvRules.php');

        File::delete($path);

        $this->artisan('make:envrule', ['name' => 'Database/DatabaseEnvRules'])->assertSuccessful();

        $this->assertFileExists($path);

        $contents = File::get($path);

        $this->assertStringContainsString('namespace App\Env\Database;', $contents);
        $this->assertStringContainsString('class DatabaseEnvRules extends EnvValidator', $contents);

        File::deleteDirectory($this->app->path('Env'));
    }
}
