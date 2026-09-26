<?php

declare(strict_types=1);

namespace Lionix\EnvClient\Rules;

use Illuminate\Validation\Rule;
use Lionix\EnvClient\Services\EnvValidator;

class MailRules extends EnvValidator
{
    public function rules(): array
    {
        return [
            'MAIL_MAILER' => ['required', Rule::in(array_keys((array) config('mail.mailers')))],
            'MAIL_HOST' => ['required_if:MAIL_MAILER,smtp', 'nullable', 'string'],
            'MAIL_PORT' => ['required_if:MAIL_MAILER,smtp', 'nullable', 'integer', 'between:1,65535'],
            'MAIL_FROM_ADDRESS' => ['required', 'email'],
            'MAIL_FROM_NAME' => ['nullable', 'string'],
        ];
    }
}
