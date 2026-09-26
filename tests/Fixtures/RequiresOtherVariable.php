<?php

declare(strict_types=1);

namespace Lionix\EnvClient\Tests\Fixtures;

use Lionix\EnvClient\Services\EnvValidator;

class RequiresOtherVariable extends EnvValidator
{
    public function rules(): array
    {
        return [
            'URL_VALUE' => 'required|url',
            'NUMERIC_VALUE' => 'required|numeric',
        ];
    }
}
