<?php

declare(strict_types=1);

namespace Lionix\EnvClient\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Container\Container;
use InvalidArgumentException;
use Lionix\EnvClient\Concerns\ResolvesEnvValidators;
use Lionix\EnvClient\Interfaces\EnvClientInterface;
use Lionix\EnvClient\Interfaces\EnvSetterInterface;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'env:set')]
class EnvSetCommand extends Command
{
    use ResolvesEnvValidators;

    protected $signature = 'env:set
        {key : The environment variable name}
        {value : The value to set}';

    protected $description = 'Set an environment variable if it passes the configured rules';

    public function handle(Container $container, EnvClientInterface $client, EnvSetterInterface $setter): int
    {
        $key = strtoupper((string) $this->argument('key'));
        $value = (string) $this->argument('value');

        // Validate against the whole file so rules referencing other variables keep working,
        // but only block on errors related to the variable being set.
        $values = array_merge($client->all(), [$key => $value]);

        foreach ($this->envValidators($container) as $validator) {
            $client->useValidator($validator)->validate($values);
        }

        if ($client->errors()->has($key)) {
            foreach ($client->errors()->get($key) as $error) {
                $this->components->error($error);
            }

            return self::FAILURE;
        }

        try {
            $setter->set([$key => $value]);
            $setter->save();
        } catch (InvalidArgumentException $e) {
            $this->components->error($e->getMessage());

            return self::FAILURE;
        }

        $this->components->info("{$key} successfully set.");

        return self::SUCCESS;
    }
}
