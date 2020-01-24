<?php

namespace Lionix\EnvClient\Commands;

use Illuminate\Console\Command;
use Lionix\EnvClient;

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
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Set .env variable after checking its value by all 
     * defined global validators in env.php config file at validators key
     *
     * @return void
     */
    public function handle()
    {
        $key = $this->argument('key');
        $value = $this->argument('value');
        $validators = config('env.validators', []);
        $success = true;
        $checkerClient = new EnvClient();
        if(count($validators)){
            foreach ($validators as $classname) {
                $validator = new $classname();
                $checkerClient
                    ->useValidator($validator)
                    ->validate([ $key => $value ]);
            }
        }
        if($checkerClient->errors()->has($key)){
            foreach($checkerClient->errors()->get($key) as $err){
                $this->error($err);
            }
        } else {
            (new EnvClient())->update([ $key => $value ]);
            $this->info("{$key} successfully set!");
        }
    }
}