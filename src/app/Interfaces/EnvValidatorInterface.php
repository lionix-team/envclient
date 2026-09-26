<?php

declare(strict_types=1);

namespace Lionix\EnvClient\Interfaces;

use Illuminate\Support\MessageBag;

interface EnvValidatorInterface
{
    /**
     * Get the validation rules.
     *
     * @return array<string, mixed>
     */
    public function rules(): array;

    /**
     * Determine if the given values pass the validation rules.
     *
     * @param  array<string, mixed>  $values
     */
    public function validate(array $values): bool;

    /**
     * Get the validation errors.
     */
    public function errors(): MessageBag;

    /**
     * Merge the given errors into the current ones.
     */
    public function mergeErrors(MessageBag $errors): void;
}
