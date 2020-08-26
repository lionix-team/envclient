<?php

namespace Lionix\EnvClient\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Foundation\Application;
use Lionix\EnvClient\Interfaces\EnvClientInterface;

class EnvSetCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'env:set {key} {value}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set .env variable';

    /**
     * Set .env variable after checking its value by all defined
     * global validators in env configuration at validators key.
     *
     * @param \Illuminate\Contracts\Foundation\Application $app
     * @param \Lionix\EnvClient\Interfaces\EnvClientInterface $client
     *
     * @return int
     */
    public function handle(Application $app, EnvClientInterface $client)
    {
        $key = strtoupper($this->argument('key'));
        $value = $this->argument('value');
        $validators = config('env.rules', []);

        if (count($validators)) {
            foreach ($validators as $classname) {
                $validator = $app->make($classname);
                $client->useValidator($validator)->validate([$key => $value]);
            }
        }

        if ($client->errors()->has($key)) {
            foreach ($client->errors()->get($key) as $err) {
                $this->error($err);
            }
            return 1;
        }

        $client->update([$key => $value]);
        $this->info($key . ' successfully set!');
        return 0;
    }
}
