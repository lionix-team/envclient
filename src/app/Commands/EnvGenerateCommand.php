<?php

declare(strict_types=1);

namespace Lionix\EnvClient\Commands;

use Closure;
use Illuminate\Console\Command;
use Illuminate\Contracts\Container\Container;
use InvalidArgumentException;
use Lionix\EnvClient\Concerns\InteractsWithEnvironmentFile;
use Lionix\EnvClient\Concerns\MasksSecrets;
use Lionix\EnvClient\Concerns\ResolvesEnvValidators;
use Lionix\EnvClient\Events\EnvironmentVariablesGenerated;
use Lionix\EnvClient\Interfaces\EnvClientInterface;
use Lionix\EnvClient\Interfaces\EnvGeneratorInterface;
use Lionix\EnvClient\Interfaces\EnvSetterInterface;
use Lionix\EnvClient\Support\EnvironmentFile;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'env:generate')]
class EnvGenerateCommand extends Command
{
    use InteractsWithEnvironmentFile;
    use MasksSecrets;
    use ResolvesEnvValidators;

    protected $signature = 'env:generate
        {--force : Overwrite variables that already have a value}
        {--dry-run : List the variables that would be written without changing the file}
        {--reveal : Print secret values instead of masking them}'.self::FILE_OPTIONS;

    protected $description = 'Fill in environment variables provided by the configured generators';

    public function handle(Container $container, EnvClientInterface $client, EnvSetterInterface $setter): int
    {
        $generators = $this->resolveConfiguredClasses($container, 'env.generators', EnvGeneratorInterface::class);

        if ($generators === []) {
            $this->components->warn('No environment generators configured.');

            return self::SUCCESS;
        }

        return $this->withEnvironmentFile(
            fn (): int => $this->generate($container, $client, $setter, $generators),
            writes: true,
            mustExist: false,
        );
    }

    /**
     * @param  list<EnvGeneratorInterface>  $generators
     */
    protected function generate(
        Container $container,
        EnvClientInterface $client,
        EnvSetterInterface $setter,
        array $generators,
    ): int {
        $current = $client->all();
        $file = EnvironmentFile::parse(EnvironmentFile::read());
        $pending = [];

        foreach ($generators as $generator) {
            foreach ($generator->values() as $key => $value) {
                $key = (string) $key;

                if (! $this->option('force') && $this->isFilled($client, $key, $file)) {
                    continue;
                }

                $pending[$key] = $value;
            }
        }

        if ($pending === []) {
            $this->components->info('All generated environment variables are already set.');

            return self::SUCCESS;
        }

        $pending = array_map(
            static fn (mixed $value): mixed => $value instanceof Closure ? $container->call($value) : $value,
            $pending,
        );

        foreach ($this->envValidators($container) as $validator) {
            $client->useValidator($validator)->validate(array_merge($current, $pending));
        }

        $errors = array_intersect_key($client->errors()->toArray(), $pending);

        if ($errors !== []) {
            foreach (array_merge(...array_values($errors)) as $error) {
                $this->components->error($error);
            }

            $this->components->error('Nothing was written.');

            return self::FAILURE;
        }

        foreach ($pending as $key => $value) {
            $this->components->twoColumnDetail(
                $key.' <fg=gray>'.$this->displayValue($key, $value, (bool) $this->option('reveal')).'</>',
                array_key_exists($key, $file) ? '<fg=yellow>overwrite</>' : '<fg=green>add</>',
            );
        }

        if ($this->option('dry-run')) {
            $this->components->info('Dry run, nothing was written.');

            return self::SUCCESS;
        }

        try {
            $setter->set($pending);
            $setter->save();
        } catch (InvalidArgumentException $e) {
            $this->components->error($e->getMessage());

            return self::FAILURE;
        }

        $this->laravel->make('events')->dispatch(
            new EnvironmentVariablesGenerated(EnvironmentFile::path(), array_keys($pending))
        );

        $this->components->info(count($pending).' environment variable(s) generated.');

        return self::SUCCESS;
    }

    /**
     * Determine if the variable already has a value, either in the loaded
     * environment or in the environment file itself.
     *
     * @param  array<string, string|null>  $file
     */
    protected function isFilled(EnvClientInterface $client, string $key, array $file): bool
    {
        return ! in_array($client->get($key), [null, ''], true)
            || ! in_array($file[$key] ?? null, [null, ''], true);
    }
}
