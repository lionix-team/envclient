<?php

declare(strict_types=1);

namespace Lionix\EnvClient\Commands;

use Illuminate\Console\Command;
use Lionix\EnvClient\Concerns\ComparesWithExampleFile;
use Lionix\EnvClient\Concerns\InteractsWithEnvironmentFile;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'env:diff')]
class EnvDiffCommand extends Command
{
    use ComparesWithExampleFile;
    use InteractsWithEnvironmentFile;

    protected $signature = 'env:diff
        {--example= : The example file to compare with (defaults to .env.example)}'.self::FILE_OPTIONS;

    protected $description = 'Compare the environment file with the example file';

    public function handle(): int
    {
        return $this->withEnvironmentFile(function (): int {
            $example = $this->examplePath();

            if (! is_file($example)) {
                $this->components->error("Example file [{$example}] not found.");

                return self::FAILURE;
            }

            [$missing, $extra] = $this->compareWithExample($example);

            if ($missing === [] && $extra === []) {
                $this->components->info('The environment file is in sync with '.basename($example).'.');

                return self::SUCCESS;
            }

            if ($missing !== []) {
                $this->components->warn('Missing from the environment file:');
                $this->components->bulletList($missing);
            }

            if ($extra !== []) {
                $this->components->info('Not declared in '.basename($example).':');
                $this->components->bulletList($extra);
            }

            return $missing === [] ? self::SUCCESS : self::FAILURE;
        });
    }
}
