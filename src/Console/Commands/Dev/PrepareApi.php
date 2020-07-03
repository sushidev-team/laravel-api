<?php

namespace AMBERSIVE\Api\Console\Commands\Dev;

use File;

use Illuminate\Console\Command;

use AMBERSIVE\Api\Helper\SchemaHelper;

class PrepareApi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'api:prepare';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command will check if all requirements for the usage of this packages are fullfilled.';

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
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $pathSchema = SchemaHelper::path();
        $pathModels = data_get(config('ambersive-api'), 'models_laravel', env('API_MODELS_LARAVEL', app_path('Models')));
        $pathSeeds  = resource_path('seedfiles');
        
        File::isDirectory($pathSchema) or File::makeDirectory($pathSchema, 0777, true, true);
        File::isDirectory($pathModels) or File::makeDirectory($pathModels, 0777, true, true);
        File::isDirectory($pathSeeds) or File::makeDirectory($pathSeeds, 0777, true, true);

        if (File::exists($pathSchema) == false){
            $this->error('The path to schema folder does not exists.');
            return 1;
        }

        if (File::exists($pathModels) == false){
            $this->error('The path to models folder does not exists.');
            return 1;
        }

        if (File::exists($pathSeeds) == false){
            $this->error('The path for seedfiles does not exists.');
            return 1;
        }

        return 0;

    }
    
}
