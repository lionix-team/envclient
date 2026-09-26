<?php

declare(strict_types=1);

namespace Lionix\EnvClient\Commands;

use Illuminate\Console\Command;
use Lionix\EnvClient\Interfaces\EnvClientInterface;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'env:get')]
class EnvGetCommand extends Command
{
    protected $signature = 'env:get {key : The environment variable name}';

    protected $description = 'Print the value of an environment variable';

    public function handle(EnvClientInterface $client): int
    {
        $key = (string) $this->argument('key');

        if (! $client->has($key)) {
            $this->components->error("Environment variable [{$key}] not found.");

            return self::FAILURE;
        }

        $this->line(match ($value = $client->get($key)) {
            true => 'true',
            false => 'false',
            null => 'null',
            default => (string) $value,
        });

        return self::SUCCESS;
    }
}
