<?php

declare(strict_types=1);

namespace Lionix\EnvClient\Commands;

use Illuminate\Console\Command;
use InvalidArgumentException;
use Lionix\EnvClient\Concerns\ComparesWithExampleFile;
use Lionix\EnvClient\Concerns\InteractsWithEnvironmentFile;
use Lionix\EnvClient\Interfaces\EnvSetterInterface;
use Lionix\EnvClient\Support\EnvironmentFile;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'env:sync')]
class EnvSyncCommand extends Command
{
    use ComparesWithExampleFile;
    use InteractsWithEnvironmentFile;

    protected $signature = 'env:sync
        {--example= : The example file to sync with (defaults to .env.example)}
        {--to-example : Also add the variables missing from the example file to it, with empty values}'.self::FILE_OPTIONS;

    protected $description = 'Add the variables declared in the example file to the environment file';

    public function handle(EnvSetterInterface $setter): int
    {
        return $this->withEnvironmentFile(function () use ($setter): int {
            $example = $this->examplePath();

            if (! is_file($example)) {
                $this->components->error("Example file [{$example}] not found.");

                return self::FAILURE;
            }

            [$missing, $extra] = $this->compareWithExample($example);

            if ($missing !== []) {
                $lines = EnvironmentFile::lines(EnvironmentFile::read($example));

                try {
                    $setter->set(array_combine($missing, array_map(
                        fn (string $key): string => $this->rawValue($lines[$key]),
                        $missing,
                    )));
                    $setter->save();
                } catch (InvalidArgumentException $e) {
                    $this->components->error($e->getMessage());

                    return self::FAILURE;
                }

                $this->components->info('Added to the environment file:');
                $this->components->bulletList($missing);
            }

            if ($extra !== [] && $this->option('to-example')) {
                $contents = EnvironmentFile::read($example);

                if ($contents !== '' && ! str_ends_with($contents, "\n")) {
                    $contents .= PHP_EOL;
                }

                file_put_contents($example, $contents.implode(PHP_EOL, array_map(
                    static fn (string $key): string => $key.'=',
                    $extra,
                )).PHP_EOL, LOCK_EX);

                $this->components->info('Added to '.basename($example).':');
                $this->components->bulletList($extra);
            }

            if ($missing === [] && ($extra === [] || ! $this->option('to-example'))) {
                $this->components->info('Nothing to sync.');
            }

            return self::SUCCESS;
        }, writes: true, mustExist: false);
    }

    /**
     * Extract the value exactly as written in the example file, without any inline comment.
     */
    protected function rawValue(string $line): string
    {
        $value = trim((string) preg_replace(EnvironmentFile::LINE_PATTERN, '', $line, 1));

        if (preg_match('/^(["\'])(?:\\\\.|(?!\1).)*\1/', $value, $matches) === 1) {
            return $matches[0];
        }

        return trim((string) preg_replace('/\s+#.*$/', '', $value));
    }
}
