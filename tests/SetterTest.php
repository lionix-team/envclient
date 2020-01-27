<?php

namespace Lionix\EnvClient\Tests;

use Tests\TestCase;
use Lionix\EnvSetter;

class SetterTest extends TestCase
{
    /**
     * Testing base package setter class
     *
     * @return void
     */
    public function testSetter()
    {
        $keyToCheck = "LIONIX_ENV_CLIENT_SETTER_TEST";
        $setter = new EnvSetter();
        $stringValue = "String Test";
        $booleanValue = "true";
        $integerValue = "1243";
        //
        $setter->set([ $keyToCheck => $stringValue ]);
        $setter->save();
        $this->assertSame(env($keyToCheck), $stringValue);
        //
        $setter->set([ $keyToCheck => $booleanValue ]);
        $setter->save();
        $this->assertTrue(env($keyToCheck));
        //
        $setter->set([ $keyToCheck => $integerValue ]);
        $setter->save();
        $this->assertSame(env($keyToCheck), $integerValue);
    }
}