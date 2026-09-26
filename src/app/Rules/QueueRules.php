<?php

declare(strict_types=1);

namespace Lionix\EnvClient\Rules;

use Illuminate\Validation\Rule;
use Lionix\EnvClient\Services\EnvValidator;

class QueueRules extends EnvValidator
{
    public function rules(): array
    {
        return [
            'QUEUE_CONNECTION' => ['required', Rule::in(array_keys((array) config('queue.connections')))],
            'CACHE_STORE' => ['nullable', Rule::in(array_keys((array) config('cache.stores')))],
            'SESSION_DRIVER' => [
                'nullable',
                Rule::in(['file', 'cookie', 'database', 'apc', 'memcached', 'redis', 'dynamodb', 'array']),
            ],
        ];
    }
}
