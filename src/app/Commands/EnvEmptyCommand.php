<?php

namespace Lionix\EnvClient\Commands;

use Illuminate\Console\Command;
use Lionix\EnvClient;

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
    protected $description = 'Get empty .env variables';

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
     * Checking .env empty variables
     *
     * @return void
     */
    public function handle()
    {
        $noEmptyValues = false;
        foreach ((new EnvClient())->all() as $key => $value) {
            if(!boolval($value)){
                $noEmptyValues = false;
                $this->warn("$key variable is empty!");
            }
        }
        if($noEmptyValues){
            $this->info("All .env variables are set!");
        }
    }
}