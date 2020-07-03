<?php

namespace AMBERSIVE\Api;

use Illuminate\Foundation\AliasLoader;
use Illuminate\Console\Events\CommandFinished;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Factory as EloquentFactory;

use Symfony\Component\Console\Output\ConsoleOutput;

use PragmaRX\Yaml\Package\ServiceProvider as YamlServiceProvider;

use Illuminate\Support\Collection;
use Illuminate\Pagination\Paginator;
use Illuminate\Pagination\LengthAwarePaginator;

use Laravel\Passport\Passport;

use Str;
use Config;
use Arr;

class ApiServiceProvider extends ServiceProvider
{

    public $macros = [
        \AMBERSIVE\Api\Macros\ResponseApiMacro::class
    ];

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        
        collect($this->macros)->each(function($macro){

            $cl = new $macro();
            if(method_exists($cl, 'register') === true){
                $cl->register();
            }

        });

        $this->registerEventListener();

        $this->app['events']->listen(\AMBERSIVE\Api\Events\Registered::class, \AMBERSIVE\Api\Listeners\SendActivationMail::class);

    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {

        // Config

        if ($this->app->runningInConsole()) {

            $this->publishes([
                __DIR__.'/Configs/ambersive-api.php'         => config_path('ambersive-api.php'),
                __DIR__.'/Configs/ambersive-permissions.php' => config_path('permission.php'),
                __DIR__.'/Configs/ambersive-mails.php'       => config_path('ambersive-mails.php'),
                __DIR__.'/Configs/uuid.php'                 => config_path('uuid.php'),
                __DIR__.'/Configs/auth.php'                 => config_path('auth.php'),
                __DIR__.'/Configs/languages.php'            => config_path('languages.php'),
                __DIR__.'/Configs/l5-swagger.php'           => config_path('l5-swagger.php')
            ],'ambersive-api-config');

            // User schemafile
            $this->publishes([
                __DIR__.'/Schemas/users.yml'                   => config('ambersive-api.schema_store').'/users.yml'
            ],'ambersive-user');

            // Seeds
            $this->publishes([
                __DIR__.'/Seedfiles/users.yml'          => resource_path('seedfiles/users.yml'),
                __DIR__.'/Seedfiles/roles.yml'          => resource_path('seedfiles/roles.yml'),
                __DIR__.'/Seedfiles/permissions.yml'    => resource_path('seedfiles/permissions.yml'),
            ],'ambersive-seeds');

        }

        // Routes
        $this->loadRoutesFrom(__DIR__.'/Routes/routes.php');

        // Views
        $this->loadViewsFrom(__DIR__.'/Views', 'ambersive-api');

        // Translations
        $this->loadTranslationsFrom(__DIR__.'/Lang', 'ambersive-api');

        // Controller
        $this->app->make('AMBERSIVE\Api\Controller\BaseWebController');

        // Commands
        if ($this->app->runningInConsole()) {
            $this->commands([
               \AMBERSIVE\Api\Console\Commands\Dev\InitRequirements::class,
               \AMBERSIVE\Api\Console\Commands\Dev\PrepareApi::class,
               \AMBERSIVE\Api\Console\Commands\Dev\NewApi::class,
               \AMBERSIVE\Api\Console\Commands\Dev\MakeApi::class,
               \AMBERSIVE\Api\Console\Commands\Dev\UpdateApi::class,
               \AMBERSIVE\Api\Console\Commands\Dev\FormatFile::class
            ]);
        }

        // Factories
        $this->registerEloquentFactoriesFrom(__DIR__.'/ModelFactories');

        // Migrations
        $this->loadMigrationsFrom(__DIR__.'/Migrations');

        // Seeder

        if ($this->app->runningInConsole()) {
            if ($this->isConsoleCommandContains([ 'db:seed', '--seed' ], [ '--class', 'help', '-h' ])) {
                $this->addSeedsAfterConsoleCommandFinished();
            }
        }

    }

    protected function registerEventListener():void {
        Event::listen(
            'AMBERSIVE\Api\Events\Registered',
            'AMBERSIVE\Api\Listeners\SendActivationMail'
        );

        Event::listen(
            'AMBERSIVE\Api\Events\ForgotPassword',
            'AMBERSIVE\Api\Listeners\SendPasswordResetMail'
        );
    }
    
    /**
     * Get a value that indicates whether the current command in console
     * contains a string in the specified $fields.
     *
     * @param string|array $contain_options
     * @param string|array $exclude_options
     *
     * @return bool
     */
    protected function isConsoleCommandContains($contain_options, $exclude_options = null) : bool
    {
        $args = Request::server('argv', null);
        if (is_array($args)) {
            $command = implode(' ', $args);
            if (Str::contains($command, $contain_options) && ($exclude_options == null || !Str::contains($command, $exclude_options))) {
                return true;
            }
        }
        return false;
    }

    /**
     * Add seeds from the $seed_path after the current command in console finished.
     */
    protected function addSeedsAfterConsoleCommandFinished()
    {
        Event::listen(CommandFinished::class, function(CommandFinished $event) {
            // Accept command in console only,
            // exclude all commands from Artisan::call() method.
            if ($event->output instanceof ConsoleOutput) {
                $this->addSeedsFrom(__DIR__ . '/Seeder');
            }
        });
    }

    /**
     * Register seeds.
     *
     * @param string  $seeds_path
     * @return void
     */
    protected function addSeedsFrom($seeds_path)
    {
        $file_names = glob( $seeds_path . '/*.php');
        foreach ($file_names as $filename)
        {
            $classes = $this->getClassesFromFile($filename);
            foreach ($classes as $class) {
                Artisan::call('db:seed', [ '--class' => $class, '--force' => '' ]);
            }
        }
    }

    /**
     * Get full class names declared in the specified file.
     *
     * @param string $filename
     * @return array an array of class names.
     */
    private function getClassesFromFile(string $filename) : array
    {
        // Get namespace of class (if vary)
        $namespace = "";
        $lines = file($filename);
        $namespaceLines = preg_grep('/^namespace /', $lines);
        if (is_array($namespaceLines)) {
            $namespaceLine = array_shift($namespaceLines);
            $match = array();
            preg_match('/^namespace (.*);$/', $namespaceLine, $match);
            $namespace = array_pop($match);
        }

        // Get name of all class has in the file.
        $classes = array();
        $php_code = file_get_contents($filename);
        $tokens = token_get_all($php_code);
        $count = count($tokens);
        for ($i = 2; $i < $count; $i++) {
            if ($tokens[$i - 2][0] == T_CLASS && $tokens[$i - 1][0] == T_WHITESPACE && $tokens[$i][0] == T_STRING) {
                $class_name = $tokens[$i][1];
                if ($namespace !== "") {
                    $classes[] = $namespace . "\\$class_name";
                } else {
                    $classes[] = $class_name;
                }
            }
        }

        return $classes;
    }

    /**
     * Register factories.
     *
     * @param  string  $path
     * @return void
     */
    protected function registerEloquentFactoriesFrom($path)
    {
        $this->app->make(EloquentFactory::class)->load($path);
    }

    /**
     * Merges the configs together and takes multi-dimensional arrays into account.
     *
     * @param  array  $original
     * @param  array  $merging
     * @return array
     */
    protected function mergeConfigs(array $original, array $merging)
    {
        $array = array_merge($original, $merging);

        foreach ($original as $key => $value) {
            if (! is_array($value)) {
                continue;
            }

            if (! Arr::exists($merging, $key)) {
                continue;
            }

            if (is_numeric($key)) {
                continue;
            }

            $array[$key] = $this->mergeConfigs($value, $merging[$key]);
        }

        return $array;
    }
}
