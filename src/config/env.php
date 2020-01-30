<?php

return [

    /**
     * Validation classes which contain environment rules
     * applied by env artisan commands.
     * 
     * Add your validation classes created by 
     * `php artisan make:envrule` command to apply their rules
     * 
     * @var array
     */
    "rules" => [
        \App\Env\BaseEnvValidationRules::class
    ]
];