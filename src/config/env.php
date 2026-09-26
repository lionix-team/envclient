<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Environment Validation Rules
    |--------------------------------------------------------------------------
    |
    | Validator classes whose rules are applied by the `env:set`, `env:check`
    | and `env:generate` artisan commands. Generate new ones with the
    | `php artisan make:envrule` command and register them here.
    |
    | Ready-made rule sets are available in the `Lionix\EnvClient\Rules`
    | namespace: AppRules, DatabaseRules, MailRules, QueueRules,
    | RedisRules and AwsRules.
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

    /*
    |--------------------------------------------------------------------------
    | Validate On Boot
    |--------------------------------------------------------------------------
    |
    | Validate the environment against the rules above on every web request.
    | Use "log" to log a warning or "exception" to stop the application with
    | an InvalidEnvironmentException. Set to false to disable. Console
    | commands are never affected; run `env:check` in your deployments.
    |
    */

    'validate_on_boot' => env('ENV_VALIDATE_ON_BOOT', false),

    /*
    |--------------------------------------------------------------------------
    | Hidden Variables
    |--------------------------------------------------------------------------
    |
    | Variables matching these patterns are masked in the output of the
    | `env:get` and `env:generate` commands unless `--reveal` is passed.
    |
    */

    'hidden' => [
        '*PASSWORD*',
        '*SECRET*',
        '*TOKEN*',
        '*PRIVATE*',
        '*_KEY',
        '*_KEY_ID',
    ],

    /*
    |--------------------------------------------------------------------------
    | Backups
    |--------------------------------------------------------------------------
    |
    | When enabled, the environment file is copied to `<file>.backup` before
    | every change made by this package. Restore it with `env:restore`.
    |
    */

    'backup' => false,

];
