<?php

namespace Lionix\EnvClient\Interfaces;

interface EnvSetterInterface 
{
    /**
     * Set variables
     *
     * @param array $toSet
     * 
     * @return void
     */
    public function set(array $toSet) : void;

    /**
     * Apply changes by saving them
     *
     * @return void
     */
    public function save() : void;
}