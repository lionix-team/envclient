<?php

namespace Lionix\EnvClient\Providers;

use Illuminate\Support\ServiceProvider;
use Lionix\EnvClient\Interfaces\EnvClientInterface;
use Lionix\EnvClient\Interfaces\EnvGetterInterface;
use Lionix\EnvClient\Interfaces\EnvSetterInterface;
use Lionix\EnvClient\Interfaces\EnvValidatorInterface;
use Lionix\EnvClient\Services\EnvClient;
use Lionix\EnvClient\Services\EnvGetter;
use Lionix\EnvClient\Services\EnvSetter;
use Lionix\EnvClient\Services\EnvValidator;

class EnvClientServiceProvider extends ServiceProvider
{
    /**
     * Publish default .env global ruleset and register commands:
     * - env:check
     * - env:empty
     * - env:get
     * - env:set
     * - make:envrule
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            dirname(__DIR__, 2) . '/config/env.php' => config_path('env.php'),
            dirname(__DIR__, 2) . '/stubs/BaseEnvValidationRules.stub' => app_path('Env/BaseEnvValidationRules.php'),
        ], 'config');

        $this->app->bind(EnvGetterInterface::class, EnvGetter::class);
        $this->app->bind(EnvSetterInterface::class, EnvSetter::class);
        $this->app->bind(EnvValidatorInterface::class, EnvValidator::class);
        $this->app->bind(EnvClientInterface::class, EnvClient::class);

        if ($this->app->runningInConsole()) {
            $this->commands([
                \Lionix\EnvClient\Commands\EnvCheckCommand::class,
                \Lionix\EnvClient\Commands\EnvEmptyCommand::class,
                \Lionix\EnvClient\Commands\EnvGetCommand::class,
                \Lionix\EnvClient\Commands\EnvSetCommand::class,
                \Lionix\EnvClient\Commands\MakeEnvRuleCommand::class,
            ]);
        }
    }
}
