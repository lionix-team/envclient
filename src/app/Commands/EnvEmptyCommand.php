<?php

declare(strict_types=1);

namespace Lionix\EnvClient\Commands;

use Illuminate\Console\Command;
use Lionix\EnvClient\Interfaces\EnvClientInterface;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'env:empty')]
class EnvEmptyCommand extends Command
{
    protected $signature = 'env:empty';

    protected $description = 'List the environment variables that have no value';

    public function handle(EnvClientInterface $client): int
    {
        $empty = array_keys(array_filter($client->all(), static fn (mixed $value): bool => $value === ''));

        if ($empty === []) {
            $this->components->info('All environment variables are set.');

            return self::SUCCESS;
        }

        foreach ($empty as $key) {
            $this->components->warn("{$key} is empty.");
        }

        return self::SUCCESS;
    }
}
