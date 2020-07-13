<?php

namespace Lionix\EnvClient\Services;

use Illuminate\Support\MessageBag;
use Lionix\EnvClient\Interfaces\EnvClientInterface;
use Lionix\EnvClient\Interfaces\EnvGetterInterface;
use Lionix\EnvClient\Interfaces\EnvSetterInterface;
use Lionix\EnvClient\Interfaces\EnvValidatorInterface;

class EnvClient implements EnvClientInterface
{
    /**
     * Env Getter
     *
     * @var Lionix\EnvClient\Interfaces\EnvGetterInterface
     */
    protected $getter;

    /**
     * Env Setter
     *
     * @var Lionix\EnvClient\Interfaces\EnvSetterInterface
     */
    protected $setter;

    /**
     * Env Validator
     *
     * @var Lionix\EnvClient\Interfaces\EnvValidatorInterface
     */
    protected $validator;

    /**
     * Setup default client providers
     *
     * @param EnvGetterInterface $getter
     * @param EnvSetterInterface $setter
     * @param EnvValidatorInterface $validator
     */
    public function __construct(
        EnvGetterInterface $getter,
        EnvSetterInterface $setter,
        EnvValidatorInterface $validator
    ) {
        $this->getter = $getter;
        $this->setter = $setter;
        $this->validator = $validator;
    }

    /**
     * Set getter property
     *
     * @param Lionix\EnvClient\Interfaces\EnvGetterInterface $getter
     *
     * @return self
     */
    public function useGetter(EnvGetterInterface $getter): EnvClientInterface
    {
        $this->getter = $getter;

        return $this;
    }

    /**
     * Set setter property
     *
     * @param Lionix\EnvClient\Interfaces\EnvSetterInterface $setter
     *
     * @return self
     */
    public function useSetter(EnvSetterInterface $setter): EnvClientInterface
    {
        $this->setter = $setter;

        return $this;
    }

    /**
     * Set validator property and merge existing errors
     * with validator errors
     *
     * @param Lionix\EnvClient\Interfaces\EnvValidatorInterface $validator
     *
     * @return self
     */
    public function useValidator(EnvValidatorInterface $validator): EnvClientInterface
    {
        $errorsToMerge = $this->errors();

        $this->validator = $validator;

        $this->validator->mergeErrors($errorsToMerge);

        return $this;
    }

    /**
     * Get all .env variables
     *
     * @return array
     */
    public function all(): array
    {
        return $this->getter->all();
    }

    /**
     * Check if .env variable exists
     *
     * @param string $key
     *
     * @return boolean
     */
    public function has(string $key): bool
    {
        return $this->getter->has($key);
    }

    /**
     * Get .env variable value
     *
     * @param string $key
     *
     * @return mixed
     */
    public function get(string $key)
    {
        return $this->getter->get($key);
    }

    /**
     * Set .env variable value if its validated successfully
     *
     * @param array $values
     *
     * @return self
     */
    public function set(array $values): EnvClientInterface
    {
        if ($this->validate($values)) {
            $this->setter->set($values);
        }

        return $this;
    }

    /**
     * Save the changes applied with set method
     *
     * @return self
     */
    public function save(): EnvClientInterface
    {
        $this->setter->save();

        return $this;
    }

    /**
     * Set and save methods combined
     *
     * @param array $values
     *
     * @return self
     */
    public function update(array $values): EnvClientInterface
    {
        if ($this->validate($values)) {
            $this->setter->set($values);
            $this->setter->save();
        }
        return $this;
    }

    /**
     * Check given values using validator
     *
     * @param array $values
     *
     * @return boolean
     */
    public function validate(array $values): bool
    {
        return $this->validator->validate($values);
    }

    /**
     * Get errors
     *
     * @return Illuminate\Support\MessageBag
     */
    public function errors(): MessageBag
    {
        return $this->validator ? $this->validator->errors() : new MessageBag();
    }
}
