<?php

namespace Lionix\EnvClient\Commands;

use Illuminate\Console\Command;
use Lionix\EnvClient\Interfaces\EnvClientInterface;

class EnvEmptyCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'env:empty';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Print empty .env variables';

    /**
     * Print .env empty variables
     *
     * @param \Lionix\EnvClient\Interfaces\EnvClientInterface $client
     *
     * @return int
     */
    public function handle(EnvClientInterface $client)
    {
        $noEmptyValues = true;

        foreach ($client->all() as $key => $value) {
            if ($value == '') {
                $noEmptyValues = false;
                $this->warn($key . ' variable is empty!');
            }
        }

        if ($noEmptyValues) {
            $this->info('All .env variables are set!');
        }

        return 0;
    }
}
