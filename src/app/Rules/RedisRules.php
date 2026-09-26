<?php

declare(strict_types=1);

namespace Lionix\EnvClient\Rules;

use Lionix\EnvClient\Services\EnvValidator;

class RedisRules extends EnvValidator
{
    public function rules(): array
    {
        return [
            'REDIS_CLIENT' => ['nullable', 'in:phpredis,predis'],
            'REDIS_HOST' => ['required', 'string'],
            'REDIS_PORT' => ['nullable', 'integer', 'between:1,65535'],
            'REDIS_PASSWORD' => ['nullable', 'string'],
            'REDIS_DB' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
