<?php

declare(strict_types=1);

namespace Lionix\EnvClient\Services;

use Illuminate\Support\MessageBag;
use Lionix\EnvClient\Interfaces\EnvClientInterface;
use Lionix\EnvClient\Interfaces\EnvGetterInterface;
use Lionix\EnvClient\Interfaces\EnvSetterInterface;
use Lionix\EnvClient\Interfaces\EnvValidatorInterface;

class EnvClient implements EnvClientInterface
{
    public function __construct(
        protected EnvGetterInterface $getter,
        protected EnvSetterInterface $setter,
        protected EnvValidatorInterface $validator,
    ) {
    }

    public function useGetter(EnvGetterInterface $getter): static
    {
        $this->getter = $getter;

        return $this;
    }

    public function useSetter(EnvSetterInterface $setter): static
    {
        $this->setter = $setter;

        return $this;
    }

    public function useValidator(EnvValidatorInterface $validator): static
    {
        $validator->mergeErrors($this->errors());

        $this->validator = $validator;

        return $this;
    }

    public function all(): array
    {
        return $this->getter->all();
    }

    public function has(string $key): bool
    {
        return $this->getter->has($key);
    }

    public function get(string $key): mixed
    {
        return $this->getter->get($key);
    }

    public function set(array $values): static
    {
        if ($this->validate($values)) {
            $this->setter->set($values);
        }

        return $this;
    }

    public function save(): static
    {
        $this->setter->save();

        return $this;
    }

    public function update(array $values): static
    {
        if ($this->validate($values)) {
            $this->setter->set($values);
            $this->setter->save();
        }

        return $this;
    }

    public function validate(array $values): bool
    {
        return $this->validator->validate($values);
    }

    public function errors(): MessageBag
    {
        return $this->validator->errors();
    }
}
