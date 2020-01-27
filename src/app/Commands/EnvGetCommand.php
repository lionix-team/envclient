<?php

namespace Lionix\EnvClient\Commands;

use Illuminate\Console\Command;
use Lionix\EnvClient;

class EnvGetCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = "env:get {key}";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Get .env variable";

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
     * Get .env variable
     *
     * @return void
     */
    public function handle()
    {
        $client = new EnvClient();
        $key = $this->argument("key");
        return !is_null($result = $client->get($key)) ?
            $this->info($result) : $this->error("No {$key} variable found!");
    }
}