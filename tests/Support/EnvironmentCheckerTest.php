<?php

declare(strict_types=1);

namespace Lionix\EnvClient\Tests\Support;

use Illuminate\Support\Facades\Log;
use Lionix\EnvClient\Exceptions\InvalidEnvironmentException;
use Lionix\EnvClient\Providers\EnvClientServiceProvider;
use Lionix\EnvClient\Support\EnvironmentChecker;
use Lionix\EnvClient\Tests\Fixtures\FailingRules;
use Lionix\EnvClient\Tests\Fixtures\ValidatorWithRules;
use Lionix\EnvClient\Tests\TestCase;
use ReflectionProperty;

class EnvironmentCheckerTest extends TestCase
{
    public function test_valid_environment(): void
    {
        config()->set('env.rules', [ValidatorWithRules::class]);

        $this->app->make(EnvironmentChecker::class)->check('exception');

        $this->assertTrue($this->app->make(EnvironmentChecker::class)->errors()->isEmpty());
    }

    public function test_exception_mode(): void
    {
        config()->set('env.rules', [FailingRules::class]);

        try {
            $this->app->make(EnvironmentChecker::class)->check('exception');

            $this->fail('No exception thrown.');
        } catch (InvalidEnvironmentException $e) {
            $this->assertTrue($e->errors->has('MISSING_VALUE'));
            $this->assertStringContainsString('MISSING_VALUE', $e->getMessage());
        }
    }

    public function test_log_mode(): void
    {
        config()->set('env.rules', [FailingRules::class]);

        Log::shouldReceive('warning')
            ->once()
            ->withArgs(fn (string $message, array $context): bool => isset($context['errors']['MISSING_VALUE']));

        $this->app->make(EnvironmentChecker::class)->check('log');
    }

    public function test_provider_validates_web_requests_on_boot(): void
    {
        config()->set('env.rules', [FailingRules::class]);
        config()->set('env.validate_on_boot', 'exception');

        (new ReflectionProperty($this->app, 'isRunningInConsole'))->setValue($this->app, false);

        $this->expectException(InvalidEnvironmentException::class);

        (new EnvClientServiceProvider($this->app))->boot();
    }

    public function test_provider_skips_console(): void
    {
        config()->set('env.rules', [FailingRules::class]);
        config()->set('env.validate_on_boot', 'exception');

        (new EnvClientServiceProvider($this->app))->boot();

        $this->assertTrue(true);
    }
}
