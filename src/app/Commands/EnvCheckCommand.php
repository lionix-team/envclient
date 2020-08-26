<?php

namespace Lionix\EnvClient\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Foundation\Application;
use Lionix\EnvClient\Interfaces\EnvClientInterface;

class EnvCheckCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'env:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Apply .env global validation rules';

    /**
     * Checking all .env variables with defined
     * validators in env.php configuration file at validators key
     * and print the results
     *
     * @param \Illuminate\Contracts\Foundation\Application $app
     * @param \Lionix\EnvClient\Interfaces\EnvClientInterface $client
     *
     * @return int
     */
    public function handle(Application $app, EnvClientInterface $client)
    {
        $validators = config('env.rules', []);

        if (!count($validators)) {
            $this->error('No global validation rules provided!');
            return 1;
        }

        foreach ($validators as $classname) {
            $client->useValidator($app->make($classname))->validate($client->all());
        }

        if ($client->errors()->isEmpty()) {
            $this->info('All .env variables are valid!');
            return 0;
        }

        foreach ($client->errors()->all() as $err) {
            $this->error($err);
        }

        return 1;
    }
}
