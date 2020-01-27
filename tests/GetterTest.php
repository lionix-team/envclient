<?php

namespace Lionix\EnvClient\Tests;

use Tests\TestCase;
use Lionix\EnvGetter;

class GetterTest extends TestCase
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
            $this->assertEquals($getter->get($key), env($key));
        }
    }
}