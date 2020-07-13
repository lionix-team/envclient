<?php

namespace Lionix\EnvClient\Tests\Services;

use Lionix\EnvClient\Services\EnvGetter;
use Lionix\EnvClient\Tests\TestCase;

class EnvGetterTest extends TestCase
{
    /**
     * Testing base package getter class
     *
     * @return void
     */
    public function testGetter()
    {
        $getter = new EnvGetter();
        foreach ($getter->all() as $key => $value) {
            $this->assertEquals($value, env($key));
        }
    }
}
