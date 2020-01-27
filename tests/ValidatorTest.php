<?php

namespace Lionix\EnvClient\Tests;

use Tests\TestCase;
use Illuminate\Support\MessageBag;

require "ValidatorWithRules.php";

class ValidatorTest extends TestCase
{
    /**
     * Testing base package validator class
     *
     * @return void
     */
    public function testSetter()
    {
        $validator = new ValidatorWithRules();
        $this->assertTrue($validator->errors()->isEmpty());
        $validator->mergeErrors(
            (new MessageBag())->add('APP_NAME', 'Test message!')
        );
        $this->assertTrue($validator->errors()->has('APP_NAME'));
        $this->assertFalse($validator->validate([]));
        $this->assertTrue($validator->validate([
            'VALIDATOR_TEST_1' => 'Hello World!',
            'VALIDATOR_TEST_2' => 12,
            'VALIDATOR_TEST_3' => false
        ]));
        $this->assertFalse($validator->validate([
            'VALIDATOR_TEST_1' => 2,
            'VALIDATOR_TEST_3' => true
        ]));
        $this->assertFalse($validator->errors()->isEmpty());
    }
}