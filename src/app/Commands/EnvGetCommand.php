<?php

declare(strict_types=1);

namespace Lionix\EnvClient\Commands;

use Illuminate\Console\Command;
use Lionix\EnvClient\Concerns\InteractsWithEnvironmentFile;
use Lionix\EnvClient\Concerns\MasksSecrets;
use Lionix\EnvClient\Interfaces\EnvClientInterface;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'env:get')]
class EnvGetCommand extends Command
{
    use InteractsWithEnvironmentFile;
    use MasksSecrets;

    protected $signature = 'env:get
        {key : The environment variable name}
        {--reveal : Print secret values instead of masking them}'.self::FILE_OPTIONS;

    protected $description = 'Print the value of an environment variable';

    public function handle(EnvClientInterface $client): int
    {
        return $this->withEnvironmentFile(function () use ($client): int {
            $key = $this->stringArgument('key');

            if (! $client->has($key)) {
                $this->components->error("Environment variable [{$key}] not found.");

                return self::FAILURE;
            }

            $this->line($this->displayValue($key, $client->get($key), (bool) $this->option('reveal')));

            return self::SUCCESS;
        });
    }
}
