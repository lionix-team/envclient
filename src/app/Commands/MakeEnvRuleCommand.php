<?php

declare(strict_types=1);

namespace Lionix\EnvClient\Commands;

use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputOption;

#[AsCommand(name: 'make:envrule')]
class MakeEnvRuleCommand extends GeneratorCommand
{
    protected $name = 'make:envrule';

    protected $description = 'Create a new environment validation rules class';

    protected $type = 'Environment rule';

    protected function getStub(): string
    {
        $published = $this->laravel->basePath('stubs/envrule.stub');

        return is_file($published) ? $published : dirname(__DIR__, 2).'/stubs/envrule.stub';
    }

    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace.'\\Env';
    }

    protected function getOptions(): array
    {
        return [
            ['force', 'f', InputOption::VALUE_NONE, 'Create the class even if it already exists'],
        ];
    }
}
