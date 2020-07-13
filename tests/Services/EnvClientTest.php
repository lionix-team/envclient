<?php

namespace Lionix\EnvClient\Tests\Services;

use Lionix\EnvClient\Services\EnvClient;
use Lionix\EnvClient\Tests\EnvGetterMock;
use Lionix\EnvClient\Tests\EnvSetterMock;
use Lionix\EnvClient\Tests\TestCase;
use Lionix\EnvClient\Tests\ValidatorWithRules;
use ReflectionObject;

class EnvClientTest extends TestCase
{
    /**
     * Test resolved by the container
     *
     * @return void
     */
    public function testResolvedByTheContainer()
    {
        app()->make(EnvClient::class);

        $this->assertTrue(true);
    }

    /**
     * Test use setter chaing getter.
     *
     * @return void
     */
    public function testUseGetter()
    {
        $client = app()->make(EnvClient::class);

        $client->useGetter(new EnvGetterMock);

        $ref = new ReflectionObject($client);

        $getter = $ref->getProperty('getter');

        $getter->setAccessible(true);

        $this->assertInstanceOf(EnvGetterMock::class, $getter->getValue($client));
    }

    /**
     * Test use setter chaing setter.
     *
     * @return void
     */
    public function testUseSetter()
    {
        $client = app()->make(EnvClient::class);

        $client->useSetter(new EnvSetterMock);

        $ref = new ReflectionObject($client);

        $setter = $ref->getProperty('setter');

        $setter->setAccessible(true);

        $this->assertInstanceOf(EnvSetterMock::class, $setter->getValue($client));
    }

    /**
     * Test use setter chaing validator.
     *
     * @return void
     */
    public function testUseValidator()
    {
        $client = app()->make(EnvClient::class);

        $client->useValidator(new ValidatorWithRules);

        $ref = new ReflectionObject($client);

        $validator = $ref->getProperty('validator');

        $validator->setAccessible(true);

        $this->assertInstanceOf(ValidatorWithRules::class, $validator->getValue($client));
    }
}
