<?php

declare(strict_types=1);

namespace Lionix\EnvClient\Rules;

use Illuminate\Validation\Rule;
use Lionix\EnvClient\Services\EnvValidator;

class DatabaseRules extends EnvValidator
{
    public function rules(): array
    {
        return [
            'DB_CONNECTION' => ['required', Rule::in(array_keys((array) config('database.connections')))],
            'DB_HOST' => ['exclude_if:DB_CONNECTION,sqlite', 'required', 'string'],
            'DB_PORT' => ['nullable', 'integer', 'between:1,65535'],
            'DB_DATABASE' => ['exclude_if:DB_CONNECTION,sqlite', 'required', 'string'],
            'DB_USERNAME' => ['nullable', 'string'],
            'DB_PASSWORD' => ['nullable', 'string'],
        ];
    }
}
