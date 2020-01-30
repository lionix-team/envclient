<?php

namespace Lionix\EnvClient\Tests\Services;

use Lionix\EnvClient\Tests\TestCase;
use Lionix\EnvClient\Services\EnvGetter;
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
        $this->assertSame(env("APP_NAME"), $stringValue);
    }

    /**
     * Testing save
     *
     * @return void
     */
    public function testSetterSave()
    {
        $setter = new EnvSetter();
        $testValue = "Test!";
        $setter->set(["APP_NAME" => $testValue]);
        $setter->save();
        $filepath = app()->environmentFilePath();
        $contents = file_get_contents($filepath);
        $this->assertTrue(
            boolval(preg_match("/^"."APP_NAME"."=".preg_quote($testValue)."/", $contents))
        );
    }
}