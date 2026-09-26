<?php

declare(strict_types=1);

namespace Lionix\EnvClient\Rules;

use Lionix\EnvClient\Services\EnvValidator;

class AwsRules extends EnvValidator
{
    public function rules(): array
    {
        return [
            'AWS_ACCESS_KEY_ID' => ['required', 'string'],
            'AWS_SECRET_ACCESS_KEY' => ['required', 'string'],
            'AWS_DEFAULT_REGION' => ['required', 'regex:/^[a-z]{2}(-[a-z]+)+-\d+$/'],
            'AWS_BUCKET' => ['nullable', 'string'],
            'AWS_USE_PATH_STYLE_ENDPOINT' => ['nullable', 'boolean'],
        ];
    }
}
