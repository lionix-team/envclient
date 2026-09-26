<?php

declare(strict_types=1);

namespace Lionix\EnvClient\Tests\Fixtures;

use Lionix\EnvClient\Services\EnvValidator;

class ValidatorWithRules extends EnvValidator
{
    public function rules(): array
    {
        return [
            'APP_NAME' => 'required|string|min:4',
            'BOOLEAN_VALUE' => 'required|boolean',
            'NUMERIC_VALUE' => 'numeric',
        ];
    }
}
