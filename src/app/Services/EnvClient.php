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
     * @var EnvValidatorInterface
     */
    protected $validator;

    /**
     * Update .env variables from the file and 
     * setup default client providers
     */
    public function __construct()
    {
        $this->useGetter(new EnvGetter());
        $this->useSetter(new EnvSetter());
        $this->useValidator(new EnvValidator());
        $this->getter->update();
    }

    /**
     * Set getter property
     *
     * @param EnvGetterInterface $getter
     * 
     * @return EnvClientInterface
     */
    public function useGetter(EnvGetterInterface $getter) : EnvClientInterface
    {
        $this->getter = $getter;
        return $this;
    }

    /**
     * Set setter property
     *
     * @param EnvSetterInterface $setter
     * 
     * @return EnvClientInterface
     */
    public function useSetter(EnvSetterInterface $setter) : EnvClientInterface
    {
        $this->setter = $setter;
        return $this;
    }

    /**
     * Set validator property and merge existing errors
     * with validator errors
     *
     * @param EnvValidatorInterface $validator
     * 
     * @return EnvClientInterface
     */
    public function useValidator(EnvValidatorInterface $validator) : EnvClientInterface
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
    public function all() : array
    {
        return $this->getter->all();
    }

    /**
     * Check if .env variable exists
     *
     * @param string $key
     * @return boolean
     */
    public function has(string $key) : bool
    {
        return $this->getter->has($key);
    }

    /**
     * Get .env variable value
     *
     * @param string $key
     * 
     * @return void
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
     * @return EnvClientInterface
     */
    public function set(array $values) : EnvClientInterface
    {
        if($this->validate($values)){
            $this->setter->set($values);
        }
        return $this;
    }

    /**
     * Save the changes applied with set method and 
     * update .env variables from the file
     *
     * @return EnvClientInterface
     */
    public function save() : EnvClientInterface
    {
        $this->setter->save();
        $this->getter->update();
        return $this;
    }

    /**
     * Check given values using validator
     *
     * @param array $values
     * 
     * @return boolean
     */
    public function validate(array $values) : bool
    {
        return $this->validator->validate($values);
    }

    /**
     * Get errors
     *
     * @return MessageBag
     */
    public function errors() : MessageBag
    {
        return $this->validator ? $this->validator->errors() : new MessageBag();
    }

    /**
     * Set and save methods combined
     *
     * @param array $values
     * 
     * @return EnvClientInterface
     */
    public function update(array $values) : EnvClientInterface
    {
        $this->set($values);
        return $this->save();
    }
}