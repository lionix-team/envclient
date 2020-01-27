<?php

namespace Lionix\EnvClient\Interfaces;

interface EnvSetterInterface 
{
    /**
     * Set multiple variables
     *
     * @param array $toSet
     * 
     * @return void
     */
    public function set(array $toSet) : void;

    /**
     * Apply changes by saving them into the file
     *
     * @return void
     */
    public function save() : void;
}