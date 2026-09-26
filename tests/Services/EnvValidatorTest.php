<?php

declare(strict_types=1);

namespace Lionix\EnvClient\Tests\Services;

use Illuminate\Support\MessageBag;
use Lionix\EnvClient\Tests\Fixtures\ValidatorWithRules;
use Lionix\EnvClient\Tests\TestCase;

class EnvValidatorTest extends TestCase
{
    public function test_merge_errors(): void
    {
        $validator = new ValidatorWithRules;

        $this->assertTrue($validator->errors()->isEmpty());

        $validator->mergeErrors((new MessageBag)->add('APP_NAME', 'Test message!'));

        $this->assertTrue($validator->errors()->has('APP_NAME'));
    }

    public function test_validation_passes(): void
    {
        $validator = new ValidatorWithRules;

        $this->assertTrue($validator->validate([
            'APP_NAME' => 'Hello World!',
            'NUMERIC_VALUE' => 12,
            'BOOLEAN_VALUE' => false,
        ]));

        $this->assertTrue($validator->errors()->isEmpty());
    }

    public function test_validation_fails(): void
    {
        $validator = new ValidatorWithRules;

        $this->assertFalse($validator->validate([
            'APP_NAME' => 'Th',
            'BOOLEAN_VALUE' => true,
        ]));

        $this->assertTrue($validator->errors()->has('APP_NAME'));

        $validator = new ValidatorWithRules;

        $this->assertFalse($validator->validate([
            'APP_NAME' => 'Correct',
            'BOOLEAN_VALUE' => true,
            'NUMERIC_VALUE' => 'NaN',
        ]));

        $this->assertTrue($validator->errors()->has('NUMERIC_VALUE'));
    }

    public function test_error_messages_use_the_variable_name(): void
    {
        $validator = new ValidatorWithRules;

        $validator->validate(['APP_NAME' => 'Th', 'BOOLEAN_VALUE' => true]);

        $this->assertStringContainsString('APP_NAME', $validator->errors()->first('APP_NAME'));
    }
}
