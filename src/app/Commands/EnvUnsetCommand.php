<?php

declare(strict_types=1);

namespace Lionix\EnvClient\Commands;

use Illuminate\Console\Command;
use InvalidArgumentException;
use Lionix\EnvClient\Concerns\InteractsWithEnvironmentFile;
use Lionix\EnvClient\Interfaces\EnvClientInterface;
use Lionix\EnvClient\Interfaces\EnvForgetterInterface;
use Lionix\EnvClient\Interfaces\EnvSetterInterface;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'env:unset')]
class EnvUnsetCommand extends Command
{
    use InteractsWithEnvironmentFile;

    protected $signature = 'env:unset
        {keys* : The environment variable names to remove}'.self::FILE_OPTIONS;

    protected $description = 'Remove environment variables from the environment file';

    public function handle(EnvClientInterface $client, EnvSetterInterface $setter): int
    {
        if (! $setter instanceof EnvForgetterInterface) {
            $this->components->error('The bound environment setter does not support removing variables.');

            return self::FAILURE;
        }

        return $this->withEnvironmentFile(function () use ($client, $setter): int {
            $keys = array_map(strtoupper(...), (array) $this->argument('keys'));
            $missing = array_values(array_filter($keys, static fn (string $key): bool => ! $client->has($key)));
            $existing = array_values(array_diff($keys, $missing));

            foreach ($missing as $key) {
                $this->components->warn("Environment variable [{$key}] not found.");
            }

            if ($existing === []) {
                return self::FAILURE;
            }

            try {
                $setter->forget($existing);
                $setter->save();
            } catch (InvalidArgumentException $e) {
                $this->components->error($e->getMessage());

                return self::FAILURE;
            }

            $this->components->info(implode(', ', $existing).' removed.');

            return $missing === [] ? self::SUCCESS : self::FAILURE;
        }, writes: true);
    }
}
