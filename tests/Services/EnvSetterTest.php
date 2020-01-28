<?php

namespace Lionix\EnvClient\Tests\Services;

use Lionix\EnvClient\Tests\TestCase;
use Lionix\EnvClient\Services\EnvSetter;

class EnvSetterTest extends TestCase
{
    /**
     * Testing boolean value set
     *
     * @return void
     */
    public function testSetterBoolean()
    {
        $setter = new EnvSetter();
        $booleanValue = "false";
        $setter->set(["APP_NAME" => $booleanValue]);
        $setter->save();
        $this->assertFalse(env("APP_NAME"));
    }

    /**
     * Testing string value set
     *
     * @return void
     */
    public function testSetterString()
    {
        $setter = new EnvSetter();
        $stringValue = "String Test!";
        $setter->set(["APP_NAME" => $stringValue]);
        $setter->save();
        $this->assertSame(env("APP_NAME"), $stringValue);
    }
}