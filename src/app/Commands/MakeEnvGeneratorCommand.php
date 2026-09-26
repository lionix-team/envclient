<?php

declare(strict_types=1);

namespace Lionix\EnvClient\Commands;

use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputOption;

#[AsCommand(name: 'make:envgenerator')]
class MakeEnvGeneratorCommand extends GeneratorCommand
{
    protected $name = 'make:envgenerator';

    protected $description = 'Create a new environment generator class';

    protected $type = 'Environment generator';

    protected function getStub(): string
    {
        $published = $this->laravel->basePath('stubs/envgenerator.stub');

        return is_file($published) ? $published : dirname(__DIR__, 2).'/stubs/envgenerator.stub';
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
