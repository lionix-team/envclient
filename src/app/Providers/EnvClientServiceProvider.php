<?php

namespace Lionix\EnvClient\Providers;

use Illuminate\Support\ServiceProvider;

class EnvClientServiceProvider extends ServiceProvider
{
    /**
     * Publis default .env global ruleset and register commands:
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
            dirname(__DIR__, 2) . "/config/env.php" => config_path("env.php"),
            dirname(__DIR__, 2) . "/stubs/MainEnvValidator.stub" => app_path("Env/MainEnvValidator.php")
        ], "config");

        if ($this->app->runningInConsole()) {
            $this->commands([
                \Lionix\EnvClient\Commands\EnvCheckCommand::class,
                \Lionix\EnvClient\Commands\EnvEmptyCommand::class,
                \Lionix\EnvClient\Commands\EnvGetCommand::class,
                \Lionix\EnvClient\Commands\EnvSetCommand::class,
                \Lionix\EnvClient\Commands\MakeEnvRuleCommand::class
            ]);
        }
    }
}