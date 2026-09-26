<?php

declare(strict_types=1);

namespace Lionix\EnvClient\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'env:restore')]
class EnvRestoreCommand extends Command
{
    protected $signature = 'env:restore
        {--file= : Restore this environment file instead of .env}
        {--encrypted : Restore the encrypted copy of the file (<file>.encrypted)}
        {--force : Restore without asking for confirmation}';

    protected $description = 'Restore the environment file from the backup made before the last change';

    public function handle(): int
    {
        $path = $this->laravel->environmentFilePath();

        if (is_string($file = $this->option('file')) && $file !== '') {
            $path = Str::startsWith($file, ['/', '\\']) || preg_match('/^[A-Za-z]:[\\\\\/]/', $file) === 1
                ? $file
                : $this->laravel->environmentPath().DIRECTORY_SEPARATOR.$file;
        }

        if ($this->option('encrypted')) {
            $path .= '.encrypted';
        }

        $backup = $path.'.backup';

        if (! is_file($backup)) {
            $this->components->error("Backup [{$backup}] not found. Enable backups with the `env.backup` option.");

            return self::FAILURE;
        }

        if (! $this->option('force') && ! $this->confirm('Replace ['.basename($path).'] with its backup?', true)) {
            return self::FAILURE;
        }

        copy($backup, $path);

        $this->components->info('['.basename($path).'] restored from its backup.');

        return self::SUCCESS;
    }
}
