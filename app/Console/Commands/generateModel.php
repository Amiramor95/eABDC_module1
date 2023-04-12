<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Artisan;
class generateModel extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:models';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate model & controller';


    public function handle()
    {
        include public_path('../modelGenerator.php');

        foreach($model as $key => $list){
            Artisan::call('make:model '.$list. ' -cr');
        }
        Artisan::call('generate:columns');

    }
}
