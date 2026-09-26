<?php

declare(strict_types=1);

namespace Lionix\EnvClient\Providers;

use Illuminate\Support\ServiceProvider;
use Lionix\EnvClient\Commands\EnvCheckCommand;
use Lionix\EnvClient\Commands\EnvDiffCommand;
use Lionix\EnvClient\Commands\EnvEmptyCommand;
use Lionix\EnvClient\Commands\EnvGenerateCommand;
use Lionix\EnvClient\Commands\EnvGetCommand;
use Lionix\EnvClient\Commands\EnvRestoreCommand;
use Lionix\EnvClient\Commands\EnvSetCommand;
use Lionix\EnvClient\Commands\EnvSyncCommand;
use Lionix\EnvClient\Commands\EnvUnsetCommand;
use Lionix\EnvClient\Commands\MakeEnvGeneratorCommand;
use Lionix\EnvClient\Commands\MakeEnvRuleCommand;
use Lionix\EnvClient\Interfaces\EnvClientInterface;
use Lionix\EnvClient\Interfaces\EnvGetterInterface;
use Lionix\EnvClient\Interfaces\EnvSetterInterface;
use Lionix\EnvClient\Interfaces\EnvValidatorInterface;
use Lionix\EnvClient\Services\EnvClient;
use Lionix\EnvClient\Services\EnvGetter;
use Lionix\EnvClient\Services\EnvSetter;
use Lionix\EnvClient\Services\EnvValidator;
use Lionix\EnvClient\Support\EnvironmentChecker;
use Lionix\EnvClient\Support\EnvironmentFile;

class EnvClientServiceProvider extends ServiceProvider
{
    /**
     * All of the container bindings that should be registered.
     *
     * @var array<class-string, class-string>
     */
    public array $bindings = [
        EnvGetterInterface::class => EnvGetter::class,
        EnvSetterInterface::class => EnvSetter::class,
        EnvValidatorInterface::class => EnvValidator::class,
        EnvClientInterface::class => EnvClient::class,
    ];

    public function register(): void
    {
        // Remember which file the runtime environment was loaded from, so the
        // package knows when it can rely on env() and when it must read a file.
        $this->app->instance(EnvironmentFile::BOOTED_PATH, $this->app->environmentFilePath());
    }

    public function boot(): void
    {
        if (in_array($mode = config('env.validate_on_boot', false), ['log', 'exception'], true)
            && ! $this->app->runningInConsole()) {
            $this->app->booted(fn () => $this->app->make(EnvironmentChecker::class)->check($mode));
        }

        if (! $this->app->runningInConsole()) {
            return;
        }

        $root = dirname(__DIR__, 2);

        $this->publishes([
            $root.'/config/env.php' => config_path('env.php'),
            $root.'/stubs/BaseEnvValidationRules.stub' => app_path('Env/BaseEnvValidationRules.php'),
        ], ['envclient', 'envclient-config', 'config']);

        $this->publishes([
            $root.'/stubs/envrule.stub' => base_path('stubs/envrule.stub'),
            $root.'/stubs/envgenerator.stub' => base_path('stubs/envgenerator.stub'),
        ], ['envclient-stubs', 'stubs']);

        $this->commands([
            EnvCheckCommand::class,
            EnvDiffCommand::class,
            EnvEmptyCommand::class,
            EnvGenerateCommand::class,
            EnvGetCommand::class,
            EnvRestoreCommand::class,
            EnvSetCommand::class,
            EnvSyncCommand::class,
            EnvUnsetCommand::class,
            MakeEnvGeneratorCommand::class,
            MakeEnvRuleCommand::class,
        ]);
    }
}
