<?php

declare(strict_types=1);

namespace Lionix\EnvClient\Tests\Commands;

use Illuminate\Encryption\Encrypter;
use Lionix\EnvClient\Tests\Fixtures\ValidatorWithRules;
use Lionix\EnvClient\Tests\TestCase;

class FileCommandsTest extends TestCase
{
    public function test_env_get_masks_secrets(): void
    {
        file_put_contents($this->envDirectory.'/.env.secrets', "DB_PASSWORD=hunter2\nAPP_KEY=base64:abc\nEMPTY_PASSWORD=\n");

        $file = ['--file' => '.env.secrets'];

        $this->artisan('env:get', ['key' => 'DB_PASSWORD', ...$file])->expectsOutput('********')->assertSuccessful();
        $this->artisan('env:get', ['key' => 'APP_KEY', ...$file])->expectsOutput('********')->assertSuccessful();
        $this->artisan('env:get', ['key' => 'EMPTY_PASSWORD', ...$file])->expectsOutput('')->assertSuccessful();
        $this->artisan('env:get', ['key' => 'DB_PASSWORD', '--reveal' => true, ...$file])
            ->expectsOutput('hunter2')
            ->assertSuccessful();
    }

    public function test_hidden_patterns_are_configurable(): void
    {
        config()->set('env.hidden', ['APP_*']);

        $this->artisan('env:get', ['key' => 'APP_NAME'])->expectsOutput('********')->assertSuccessful();
    }

    public function test_file_option(): void
    {
        file_put_contents($this->envDirectory.'/.env.staging', "APP_NAME=Staging\n");

        $this->artisan('env:get', ['key' => 'APP_NAME', '--file' => '.env.staging'])
            ->expectsOutput('Staging')
            ->assertSuccessful();

        $this->artisan('env:set', ['key' => 'NEW_KEY', 'value' => 'x', '--file' => '.env.staging'])->assertSuccessful();

        $this->assertSame("APP_NAME=Staging\nNEW_KEY=x\n", file_get_contents($this->envDirectory.'/.env.staging'));
        $this->assertStringNotContainsString('NEW_KEY', $this->envContents());
        $this->assertSame($this->envDirectory.'/.env', $this->app->environmentFilePath());
    }

    public function test_file_option_accepts_absolute_paths_and_validates_that_file(): void
    {
        $path = $this->envDirectory.'/custom.env';
        file_put_contents($path, "APP_NAME=ab\nBOOLEAN_VALUE=true\n");

        config()->set('env.rules', [ValidatorWithRules::class]);

        $this->artisan('env:check', ['--file' => $path])
            ->expectsOutputToContain('APP_NAME')
            ->assertFailed();

        $this->artisan('env:check')->assertSuccessful();
    }

    public function test_missing_file_is_reported(): void
    {
        $this->artisan('env:get', ['key' => 'APP_NAME', '--file' => '.env.missing'])
            ->expectsOutputToContain('not found')
            ->assertFailed();
    }

    public function test_encrypted_option(): void
    {
        $key = Encrypter::generateKey('AES-256-CBC');
        $encrypter = new Encrypter($key, 'AES-256-CBC');
        $encrypted = $this->envDirectory.'/.env.production.encrypted';

        file_put_contents($encrypted, $encrypter->encrypt("APP_NAME=Production\nDB_PASSWORD=secret\n"));

        $options = ['--file' => '.env.production', '--encrypted' => true, '--key' => 'base64:'.base64_encode($key)];

        $this->artisan('env:get', ['key' => 'APP_NAME', ...$options])->expectsOutput('Production')->assertSuccessful();
        $this->artisan('env:set', ['key' => 'DB_PASSWORD', 'value' => 'rotated', ...$options])->assertSuccessful();
        $this->artisan('env:unset', ['keys' => ['APP_NAME'], ...$options])->assertSuccessful();

        $this->assertSame("DB_PASSWORD=rotated\n", $encrypter->decrypt((string) file_get_contents($encrypted)));
        $this->assertFileDoesNotExist($this->envDirectory.'/.env.production');
        $this->assertSame([], glob(sys_get_temp_dir().'/envclient*.backup') ?: []);
    }

    public function test_encrypted_option_uses_the_environment_key(): void
    {
        $key = Encrypter::generateKey('AES-128-CBC');

        file_put_contents(
            $this->envDirectory.'/.env.encrypted',
            (new Encrypter($key, 'AES-128-CBC'))->encrypt("APP_NAME=Encrypted\n"),
        );

        putenv('LARAVEL_ENV_ENCRYPTION_KEY=base64:'.base64_encode($key));
        $_ENV['LARAVEL_ENV_ENCRYPTION_KEY'] = $_SERVER['LARAVEL_ENV_ENCRYPTION_KEY'] = 'base64:'.base64_encode($key);

        $this->artisan('env:get', ['key' => 'APP_NAME', '--encrypted' => true, '--cipher' => 'AES-128-CBC'])
            ->expectsOutput('Encrypted')
            ->assertSuccessful();
    }

    public function test_encrypted_option_errors(): void
    {
        $this->artisan('env:get', ['key' => 'APP_NAME', '--encrypted' => true])
            ->expectsOutputToContain('Encrypted environment file')
            ->assertFailed();

        file_put_contents($this->envDirectory.'/.env.encrypted', 'garbage');

        $this->artisan('env:get', ['key' => 'APP_NAME', '--encrypted' => true, '--key' => str_repeat('a', 32)])
            ->expectsOutputToContain('Unable to decrypt')
            ->assertFailed();

        $this->artisan('env:get', ['key' => 'APP_NAME', '--encrypted' => true])
            ->expectsOutputToContain('A key is required')
            ->assertFailed();
    }

    public function test_env_unset(): void
    {
        $this->artisan('env:unset', ['keys' => ['app_name', 'numeric_value']])
            ->expectsOutputToContain('APP_NAME, NUMERIC_VALUE removed')
            ->assertSuccessful();

        $this->assertStringNotContainsString('APP_NAME=', $this->envContents());
        $this->assertStringNotContainsString('NUMERIC_VALUE=', $this->envContents());
    }

    public function test_env_unset_reports_missing_keys(): void
    {
        $this->artisan('env:unset', ['keys' => ['MISSING']])
            ->expectsOutputToContain('[MISSING] not found')
            ->assertFailed();

        $this->artisan('env:unset', ['keys' => ['APP_NAME', 'MISSING']])->assertFailed();

        $this->assertStringNotContainsString('APP_NAME=', $this->envContents());
    }

    public function test_env_diff(): void
    {
        file_put_contents($this->envDirectory.'/.env.example', "APP_NAME=\nNEW_FEATURE_FLAG=false\n");

        $this->artisan('env:diff')
            ->expectsOutputToContain('NEW_FEATURE_FLAG')
            ->expectsOutputToContain('NUMERIC_VALUE')
            ->assertFailed();

        $this->artisan('env:sync')->assertSuccessful();

        $this->artisan('env:diff')->assertSuccessful();

        $this->artisan('env:diff', ['--example' => '.env.nope'])
            ->expectsOutputToContain('not found')
            ->assertFailed();
    }

    public function test_env_sync_copies_values_as_written(): void
    {
        $this->writeEnv("APP_NAME=Mine\n");

        file_put_contents($this->envDirectory.'/.env.example', implode("\n", [
            'APP_NAME=Example',
            'MAIL_FROM_NAME="${APP_NAME}"',
            'QUOTED="a # b" # comment',
            'PLAIN=value # comment',
            'EMPTY=',
            'export EXPORTED=1',
        ])."\n");

        $this->artisan('env:sync')->assertSuccessful();

        $this->assertSame(implode("\n", [
            'APP_NAME=Mine',
            'MAIL_FROM_NAME="${APP_NAME}"',
            'QUOTED="a # b"',
            'PLAIN=value',
            'EMPTY=',
            'EXPORTED=1',
        ])."\n", $this->envContents());
    }

    public function test_env_sync_to_example(): void
    {
        file_put_contents($this->envDirectory.'/.env.example', "APP_NAME=\n");

        $this->artisan('env:sync', ['--to-example' => true])->assertSuccessful();

        $example = (string) file_get_contents($this->envDirectory.'/.env.example');

        $this->assertStringStartsWith("APP_NAME=\nBOOLEAN_VALUE=\n", $example);
        $this->assertStringNotContainsString('base64', $example);

        $this->artisan('env:sync')->expectsOutputToContain('Nothing to sync')->assertSuccessful();
    }

    public function test_env_restore(): void
    {
        config()->set('env.backup', true);

        $original = $this->envContents();

        $this->artisan('env:restore', ['--force' => true])->expectsOutputToContain('not found')->assertFailed();

        $this->artisan('env:set', ['key' => 'APP_NAME', 'value' => 'changed'])->assertSuccessful();

        $this->artisan('env:restore')
            ->expectsConfirmation('Replace [.env] with its backup?', 'no')
            ->assertFailed();

        $this->artisan('env:restore')
            ->expectsConfirmation('Replace [.env] with its backup?', 'yes')
            ->assertSuccessful();

        $this->assertSame($original, $this->envContents());
    }

    public function test_env_restore_encrypted_backup(): void
    {
        config()->set('env.backup', true);

        $key = Encrypter::generateKey('AES-256-CBC');
        $encrypter = new Encrypter($key, 'AES-256-CBC');
        $path = $this->envDirectory.'/.env.encrypted';

        file_put_contents($path, $original = $encrypter->encrypt("APP_NAME=Before\n"));

        $this->artisan('env:set', [
            'key' => 'APP_NAME', 'value' => 'After', '--encrypted' => true, '--key' => 'base64:'.base64_encode($key),
        ])->assertSuccessful();

        $this->assertSame("APP_NAME=After\n", $encrypter->decrypt((string) file_get_contents($path)));

        $this->artisan('env:restore', ['--encrypted' => true, '--force' => true])->assertSuccessful();

        $this->assertSame($original, file_get_contents($path));
    }
}
