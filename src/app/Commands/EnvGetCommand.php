<?php

namespace Lionix\EnvClient\Commands;

use Illuminate\Console\Command;
use Lionix\EnvClient\Interfaces\EnvClientInterface;

class EnvGetCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'env:get {key}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Print .env variable';

    /**
     * Print .env variable
     *
     * @param \Lionix\EnvClient\Interfaces\EnvClientInterface $client
     *
     * @return int
     */
    public function handle(EnvClientInterface $client)
    {
        $key = $this->argument('key');

        $result = $client->get($key);

        if (!$result) {
            $this->error('No ' . $key . ' variable found!');
            return 1;
        }

        $this->info($result);
        return 0;
    }
}
