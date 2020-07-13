<?php

namespace Lionix\EnvClient\Tests\Services;

use Illuminate\Support\MessageBag;
use Lionix\EnvClient\Tests\TestCase;
use Lionix\EnvClient\Tests\ValidatorWithRules;

class EnvValidatorTest extends TestCase
{
    /**
     * Testing errors merging
     *
     * @return void
     */
    public function testMergeErrors()
    {
        $validator = new ValidatorWithRules();
        $this->assertTrue($validator->errors()->isEmpty());
        $validator->mergeErrors(
            (new MessageBag())->add('APP_NAME', 'Test message!')
        );
        $this->assertTrue($validator->errors()->has('APP_NAME'));
    }

    /**
     * Testing sample validator pass
     *
     * @return void
     */
    public function testValidationPasses()
    {
        $validator = new ValidatorWithRules();

        $this->assertTrue($validator->validate([
            'APP_NAME' => 'Hello World!',
            'NUMERIC_VALUE' => 12,
            'BOOLEAN_VALUE' => false,
        ]));

        $this->assertTrue($validator->errors()->isEmpty());
    }

    /**
     * Testing sample validator fail
     *
     * @return void
     */
    public function testValidationFails()
    {
        $validator = new ValidatorWithRules();

        $this->assertFalse($validator->validate([
            'APP_NAME' => 'Th',
            'BOOLEAN_VALUE' => true,
        ]));

        $this->assertTrue($validator->errors()->has('APP_NAME'));

        $validator = new ValidatorWithRules();

        $this->assertFalse($validator->validate([
            'APP_NAME' => 'Correct',
            'NUMERIC_VALUE' => 'NaN',
        ]));

        $this->assertTrue($validator->errors()->has('NUMERIC_VALUE'));
    }
}
