<?php

namespace AMBERSIVE\Api\Console\Commands\Dev;

use Illuminate\Console\Command;

use AMBERSIVE\Api\Helper\SchemaHelper; 

class FormatFile extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'api:format {--path= : Path to the file that should be formatted.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command will trigger via prettier.';

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
       
        $output = null;
        $path   = data_get($this->options(),'path');

        if ($path == null) {
            $this->error('Please define a file that should be formatted.');
            return;
        }

        $output = shell_exec('npm run prettier -- '.$path.' --write'); 

        $this->info($path.' has been formatted via prettier.');

    }


}
