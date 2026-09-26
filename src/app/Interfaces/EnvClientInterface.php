<?php

declare(strict_types=1);

namespace Lionix\EnvClient\Interfaces;

use Illuminate\Support\MessageBag;

interface EnvClientInterface
{
    /**
     * Replace the getter dependency.
     */
    public function useGetter(EnvGetterInterface $getter): static;

    /**
     * Replace the setter dependency.
     */
    public function useSetter(EnvSetterInterface $setter): static;

    /**
     * Replace the validator dependency, keeping the errors collected so far.
     */
    public function useValidator(EnvValidatorInterface $validator): static;

    /**
     * Get all variables declared in the environment file.
     *
     * @return array<string, mixed>
     */
    public function all(): array;

    /**
     * Determine if the environment file contains the given key.
     */
    public function has(string $key): bool;

    /**
     * Get the runtime value of an environment variable.
     */
    public function get(string $key): mixed;

    /**
     * Queue the given values to be saved if they pass validation.
     *
     * @param  array<string, scalar|\Stringable|null>  $values
     */
    public function set(array $values): static;

    /**
     * Write the queued values to the environment file.
     */
    public function save(): static;

    /**
     * Validate, queue and save the given values in one step.
     *
     * @param  array<string, scalar|\Stringable|null>  $values
     */
    public function update(array $values): static;

    /**
     * Determine if the given values pass the current validator rules.
     *
     * @param  array<string, mixed>  $values
     */
    public function validate(array $values): bool;

    /**
     * Get all validation errors collected during the client lifetime.
     */
    public function errors(): MessageBag;
}
