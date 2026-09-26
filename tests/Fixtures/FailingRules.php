<?php

declare(strict_types=1);

namespace Lionix\EnvClient\Tests\Fixtures;

use Lionix\EnvClient\Services\EnvValidator;

class FailingRules extends EnvValidator
{
    public function rules(): array
    {
        return [
            'MISSING_VALUE' => 'required',
            'URL_VALUE' => 'numeric',
        ];
    }
}
