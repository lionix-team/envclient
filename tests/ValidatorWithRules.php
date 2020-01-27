<?php

namespace Lionix\EnvClient\Tests;

use Lionix\EnvClient\Services\EnvValidator;

class ValidatorWithRules extends EnvValidator
{
    public function rules() : array
    {
        return [
            'VALIDATOR_TEST_1' => 'required|string|min:3',
            'VALIDATOR_TEST_2' => 'numeric',
            'VALIDATOR_TEST_3' => 'required|boolean'
        ];
    }
}