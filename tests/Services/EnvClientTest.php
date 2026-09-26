<?php

declare(strict_types=1);

namespace Lionix\EnvClient\Tests\Services;

use Lionix\EnvClient\Facades\EnvClient as EnvClientFacade;
use Lionix\EnvClient\Interfaces\EnvClientInterface;
use Lionix\EnvClient\Services\EnvClient;
use Lionix\EnvClient\Services\EnvGetter;
use Lionix\EnvClient\Services\EnvSetter;
use Lionix\EnvClient\Tests\Fixtures\ValidatorWithRules;
use Lionix\EnvClient\Tests\TestCase;
use ReflectionProperty;

class EnvClientTest extends TestCase
{
    public function test_resolved_by_the_container(): void
    {
        $this->assertInstanceOf(EnvClient::class, $this->app->make(EnvClientInterface::class));
        $this->assertInstanceOf(EnvClient::class, $this->app->make(EnvClient::class));
    }

    public function test_use_getter_setter_and_validator(): void
    {
        $client = $this->app->make(EnvClient::class);

        $getter = new EnvGetter();
        $setter = new EnvSetter();
        $validator = new ValidatorWithRules();

        $this->assertSame($client, $client->useGetter($getter)->useSetter($setter)->useValidator($validator));

        $this->assertSame($getter, (new ReflectionProperty($client, 'getter'))->getValue($client));
        $this->assertSame($setter, (new ReflectionProperty($client, 'setter'))->getValue($client));
        $this->assertSame($validator, (new ReflectionProperty($client, 'validator'))->getValue($client));
    }

    public function test_errors_are_kept_when_switching_validators(): void
    {
        $client = $this->app->make(EnvClient::class);

        $client->useValidator(new ValidatorWithRules())->validate(['APP_NAME' => 'x']);
        $client->useValidator(new ValidatorWithRules());

        $this->assertTrue($client->errors()->has('APP_NAME'));
        $this->assertTrue($client->errors()->has('BOOLEAN_VALUE'));
    }

    public function test_update_saves_valid_values(): void
    {
        $client = $this->app->make(EnvClient::class)->useValidator(new ValidatorWithRules());

        $client->update(['APP_NAME' => 'Updated', 'BOOLEAN_VALUE' => '1']);

        $this->assertTrue($client->errors()->isEmpty());
        $this->assertStringContainsString("APP_NAME=Updated\n", $this->envContents());
    }

    public function test_update_skips_invalid_values(): void
    {
        $original = $this->envContents();

        $client = $this->app->make(EnvClient::class)->useValidator(new ValidatorWithRules());

        $client->update(['APP_NAME' => 'x', 'BOOLEAN_VALUE' => '1']);

        $this->assertTrue($client->errors()->has('APP_NAME'));
        $this->assertSame($original, $this->envContents());
    }

    public function test_set_then_save(): void
    {
        $client = $this->app->make(EnvClient::class);

        $client->set(['FIRST' => '1'])->set(['SECOND' => '2'])->save();

        $this->assertStringEndsWith("FIRST=1\nSECOND=2\n", $this->envContents());
    }

    public function test_facade(): void
    {
        $this->assertTrue(EnvClientFacade::has('APP_NAME'));
        $this->assertSame('lionix/envclient', EnvClientFacade::get('APP_NAME'));

        $client = EnvClientFacade::useValidator(new ValidatorWithRules())->update(['APP_NAME' => 'x']);

        $this->assertTrue($client->errors()->has('APP_NAME'));
        $this->assertTrue(EnvClientFacade::errors()->isEmpty(), 'Facade calls must not share state.');
    }
}
