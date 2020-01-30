<?php

namespace Lionix\EnvClient\Commands;

use Illuminate\Console\Command;
use Lionix\EnvClient\Services\EnvClient;

class EnvCheckCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = "env:check";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Apply .env global validation rules";

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Checking all .env variables with defined
     * validators in env.php configuration file at validators key
     *
     * @return void
     */
    public function handle()
    {
        $validators = config("env.rules", []);
        if (count($validators)) {
            $client = new EnvClient();
            foreach ($validators as $classname) {
                $client
                    ->useValidator(new $classname())
                    ->validate($client->all());
            }
            if ($client->errors()->isEmpty()) {
                $this->info("All .env variables are valid!");
            } else {
                foreach ($client->errors()->all() as $err) {
                    $this->error($err);
                }
            }
        } else {
            $this->error("No global validation rules provided!");
        }
    }
}