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
     * @var EnvGetterInterface
     */
    protected $getter;

    /**
     * Env Setter
     *
     * @var EnvSetterInterface
     */
    protected $setter;

    /**
     * Env Validator
     *
     * @var EnvValidatorInterface[]
     */
    protected $validator;

    public function __construct()
    {
        $this->useGetter(new EnvGetter());
        $this->useSetter(new EnvSetter());
        $this->useValidator(new EnvValidator());
    }

    public function useGetter(EnvGetterInterface $getter) : EnvClientInterface
    {
        $this->getter = $getter;
        return $this;
    }

    public function useSetter(EnvSetterInterface $setter) : EnvClientInterface
    {
        $this->setter = $setter;
        return $this;
    }

    public function useValidator(EnvValidatorInterface $validator) : EnvClientInterface
    {
        $errorsToMerge = $this->errors();
        $this->validator = $validator;
        $this->validator->mergeErrors($errorsToMerge);
        return $this;
    }

    public function all() : array
    {
        return $this->getter->all();
    }

    public function has(string $key) : bool
    {
        return $this->getter->has($key);
    }

    public function get(string $key)
    {
        return $this->getter->get($key);
    }

    public function set(array $values) : EnvClientInterface
    {
        if($this->validate($values)){
            $this->setter->set($values);
        }
        return $this;
    }

    public function save() : EnvClientInterface
    {
        $this->setter->save();
        return $this;
    }

    public function validate(array $values) : bool
    {
        return $this->validator->validate($values);
    }

    public function errors() : MessageBag
    {
        return $this->validator ? $this->validator->errors() : new MessageBag();
    }

    public function update(array $values) : EnvClientInterface
    {
        $this->set($values);
        return $this->save();
    }

    public function rules() : array
    {
        return $this->validator->rules();
    }
}