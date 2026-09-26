<?php

declare(strict_types=1);

namespace Lionix\EnvClient\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Container\Container;
use Lionix\EnvClient\Concerns\ResolvesEnvValidators;
use Lionix\EnvClient\Interfaces\EnvClientInterface;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'env:check')]
class EnvCheckCommand extends Command
{
    use ResolvesEnvValidators;

    protected $signature = 'env:check';

    protected $description = 'Validate the environment file against the configured rules';

    public function handle(Container $container, EnvClientInterface $client): int
    {
        $validators = $this->envValidators($container);

        if ($validators === []) {
            $this->components->warn('No environment validation rules configured.');

            return self::SUCCESS;
        }

        $values = $client->all();

        foreach ($validators as $validator) {
            $client->useValidator($validator)->validate($values);
        }

        if ($client->errors()->isEmpty()) {
            $this->components->info('All environment variables are valid.');

            return self::SUCCESS;
        }

        foreach ($client->errors()->all() as $error) {
            $this->components->error($error);
        }

        return self::FAILURE;
    }
}
