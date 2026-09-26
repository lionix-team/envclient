<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Environment Validation Rules
    |--------------------------------------------------------------------------
    |
    | Validator classes whose rules are applied by the `env:set` and
    | `env:check` artisan commands. Generate new ones with the
    | `php artisan make:envrule` command and register them here.
    |
    */

    'rules' => [
        \App\Env\BaseEnvValidationRules::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Environment Generators
    |--------------------------------------------------------------------------
    |
    | Generator classes whose values are written by the `env:generate`
    | artisan command, e.g. during deployment. Generate new ones with
    | the `php artisan make:envgenerator` command and register them here.
    |
    */

    'generators' => [
        //
    ],

];
