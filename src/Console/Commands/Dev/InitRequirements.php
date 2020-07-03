<?php

namespace AMBERSIVE\Api\Console\Commands\Dev;

use Illuminate\Console\Command;

use AMBERSIVE\Api\Helper\SchemaHelper; 

use Artisan;
use File;

class InitRequirements extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'api:init';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command will install all requirements for this package.';

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
        $content = null;

        $tasks = collect([
            'publishFiles',
            'installNodeJsRequirements',
            'updateApi',
        ]);

        $this->line("\nStart installing all requirements...");
        $this->line("=> removing conflicting config files");
        $this->line("=> nodejs toolchain");
        $this->line("=> run api update\n");

        $bar           = $this->output->createProgressBar($tasks->count());

        $bar->start();
        $tasks->each(function($task) use ($bar){
            $success = $this->{$task}();
            if ($success === true) {
                $bar->advance();
            }
        });
        $bar->finish();
        $this->info("\nConfig files and all required tools has been installed.");

    }
    
    /**
     * Publish all required config files
     *
     * @return bool
     */
    protected function publishFiles(): bool {
        shell_exec('mkdir -p config/ori');
        shell_exec('mv config/auth.php config/ori/auth.php.ori');
        shell_exec('mv config/uuid.php config/ori/uuid.php.ori');
        shell_exec('mv config/languages.php config/ori/languages.php.ori');
        shell_exec('mv config/permission.php config/ori/permission.php.ori');
        shell_exec('mv config/ambersive-api.php config/ori/ambersive-api.php.ori');
        shell_exec('mv config/ambersive-mails.php config/ori/ambersive-mails.php.ori');
        shell_exec('mv config/l5-swagger.php config/ori/l5-swagger.php.ori');
        shell_exec('php artisan vendor:publish --tag=ambersive-api-config');
        shell_exec('php artisan config:clear');
        shell_exec('php artisan vendor:publish --tag=picappipe-api-config');
        shell_exec('rm -rf database.sqlite');
        shell_exec('touch database.sqlite');
        return true;
    }
    
    /**
     * Run the default schema update
     *
     * @return bool
     */
    protected function updateApi():bool {
        shell_exec('php artisan api:update --silent');
        return true;
    }
    
    /**
     * This method will install the requirements for the api creator tool
     *
     * @return bool
     */
    protected function installNodeJsRequirements(): bool {
        if (File::exists('package.json') === true) {
            $content = (array) json_decode(File::get('package.json'), true);
            if (isset($content['scripts']) === false) {
                $content['scripts'] = [];
            }

            if (isset($content['scripts']['prettier']) === false) {
                $content['scripts']['prettier'] = 'prettier';
            }
            
            File::put('package.json', json_encode($content, JSON_PRETTY_PRINT));
        }
       
        shell_exec('npm install --save-dev --save-exact prettier '); 
        shell_exec('npm install --save-dev prettier @prettier/plugin-php'); 
        return true;
    }


}
