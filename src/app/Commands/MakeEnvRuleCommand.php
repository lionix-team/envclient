<?php

namespace Lionix\EnvClient\Commands;

use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputArgument;

class MakeEnvRuleCommand extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = "make:envrule {name}";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Create a new .env validator rules";

	/**
	 * The type of class being generated.
	 *
	 * @var string
	 */
    protected $type = "Environment Rule";
    
    /**
     * Replace the class name for the given stub.
     *
     * @param  string  $stub
     * @param  string  $name
     * 
     * @return string
     */
    protected function replaceClass($stub, $name)
    {
        $stub = parent::replaceClass($stub, $name);
        return str_replace("MainEnvValidator", $this->argument("name"), $stub);
    }

	/**
	 * Get the stub file for the generator.
	 *
	 * @return string
	 */
	protected function getStub()
	{
		return dirname(__DIR__, 2)."/stubs/MainEnvValidator.stub";
    }
    
	/**
	 * Get the default namespace for the class.
	 *
	 * @param  string  $rootNamespace
     * 
	 * @return string
	 */
	protected function getDefaultNamespace($rootNamespace)
	{
		return $rootNamespace."\Env";
	}

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ["name", InputArgument::REQUIRED, "The name of the .env rule."],
        ];
    }
}