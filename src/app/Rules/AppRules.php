<?php

declare(strict_types=1);

namespace Lionix\EnvClient\Rules;

use Closure;
use Illuminate\Support\Str;
use Lionix\EnvClient\Services\EnvValidator;

class AppRules extends EnvValidator
{
    public function rules(): array
    {
        return [
            'APP_NAME' => ['required', 'string'],
            'APP_ENV' => ['required', 'string'],
            'APP_KEY' => ['required', 'string', $this->validKey(...)],
            'APP_DEBUG' => ['nullable', 'boolean', 'declined_if:APP_ENV,production'],
            'APP_URL' => ['nullable', 'url'],
        ];
    }

    /**
     * Ensure the key has a length supported by Laravel's encrypter (16 or 32 bytes).
     */
    protected function validKey(string $attribute, mixed $value, Closure $fail): void
    {
        $key = (string) $value;

        if (Str::startsWith($key, 'base64:')) {
            $key = base64_decode(Str::after($key, 'base64:'), true);
        }

        if (! is_string($key) || ! in_array(strlen($key), [16, 32], true)) {
            $fail("The {$attribute} is not a valid encryption key. Run `php artisan key:generate`.");
        }
    }
}
