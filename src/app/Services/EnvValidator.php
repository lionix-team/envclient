<?php

declare(strict_types=1);

namespace Lionix\EnvClient\Services;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\MessageBag;
use Lionix\EnvClient\Interfaces\EnvValidatorInterface;

class EnvValidator implements EnvValidatorInterface
{
    protected ?MessageBag $errors = null;

    public function rules(): array
    {
        return [
            //
        ];
    }

    public function validate(array $values): bool
    {
        $rules = $this->rules();

        $validator = Validator::make($values, $rules)
            ->setAttributeNames(array_combine(array_keys($rules), array_keys($rules)));

        $passes = $validator->passes();

        $this->mergeErrors($validator->errors());

        return $passes;
    }

    public function errors(): MessageBag
    {
        return $this->errors ??= new MessageBag();
    }

    public function mergeErrors(MessageBag $errors): void
    {
        $this->errors()->merge($errors);
    }
}
