<?php

namespace AMBERSIVE\Api\Console\Commands\Dev;

use Artisan;
use File;
use Str;

use Illuminate\Console\Command;

use AMBERSIVE\Api\Helper\SchemaHelper; 

class UpdateApi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'api:update {--file= : Name of schema file} {--silent}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command will update the files based on the schema files.';

    protected $isSilent = false;

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
        // First run the prepare Command 
        $this->call('api:prepare');
        $this->echoLine(""); // Keep this empty line

        $file = data_get($this->options(), 'file', null);
        $this->isSilent = data_get($this->options(), 'silent', false);

        $this->baseController();

        if ($file !== null) {
            $this->allSchemas(collect([$file]));
        }
        else {
            $this->allSchemas();
        }

        // Generate the documentation
        $this->echoLine("");
        $this->echoLine('[.] Start documentation creation...');
        Artisan::call('l5-swagger:generate');
        $this->sortSwaggerFile();
        $this->echoLine('[✔] Documentation created.'."\n");

    }

    protected function echoLine($msg){
        if ($this->isSilent === false){
            $this->line($msg);
        }
    }

    /**
     * Update the Controller.php file for 
     * the generation of the automatic documentation
     */
    protected function baseController(): void {

        $this->echoLine('[.] Update Controller.php...');
        SchemaHelper::createBaseController();
        $this->echoLine('[✔] Controller.php has been updated.'."\n");

    }

    /**
     * Create all files for the schema
     */
    protected function singleSchema($file): array{

        $modelCreated        = SchemaHelper::createModel($file);
        $controllerCreated   = SchemaHelper::createController($file);
        $testsCreated        = SchemaHelper::createTests($file);
        $policyCreated       = SchemaHelper::createPolicy($file);
        $factoryCreated      = SchemaHelper::createFactory($file);
        $resourceCreated     = SchemaHelper::createResource($file);
        $colletionCreated    = SchemaHelper::createCollection($file);

        return [
            'name'          => $file,
            'model'         => ($modelCreated === true) ? '✔' : '✘',
            'resource'      => ($resourceCreated === true) ? '✔' : '✘',
            'collection'    => ($colletionCreated === true) ? '✔' : '✘',
            'controller'    => ($controllerCreated === true) ? '✔' : '✘',
            'tests'         => ($testsCreated === true) ? '✔' : '✘',
            'policy'        => ($policyCreated === true) ? '✔' : '✘',
            'factory'       => ($factoryCreated === true) ? '✔' : '✘'
        ];

    }

    /**
     * Go through all the schemas
     */
    protected function allSchemas($schemas = null):void {

        if ($schemas === null) {
            $this->echoLine('Scan the schemas directory ('.SchemaHelper::path().') for files...'."\n");
            $schemas      = collect(SchemaHelper::listSchemas());
        }

        $schemasCount = $schemas->count();

        $bar           = $this->output->createProgressBar($schemasCount);
        $table         = [];

        if ($this->isSilent === false) {           
            $bar->start();
        }

        // Create the schema files
        $schemas->each(function($schemaName) use ($bar, &$table){
            $table[] = $this->singleSchema($schemaName);
            if ($this->isSilent === false) {
                $bar->advance();
            }
        });

        // Create a list of model/policy files
        $modelPolicyMapping = [];
        $schemas->each(function($schemaName) use (&$modelPolicyMapping){

            $schema= SchemaHelper::readSchema($schemaName);

            if (data_get($schema, 'policy', null) !== null) {
                $modelPolicyMapping[data_get($schema, 'model')] = data_get($schema, 'policy');
            }

        });

        if ($this->isSilent === false) {
            $bar->finish();
        }

        $this->echoLine("\n");

        if ($this->isSilent === false){

            $this->table(
                ['Name','Model', 'Resource', 'Collection', 'Controller', 'Tests', 'Policy', 'Factory'],
                $table
            );
            $this->info("\n\n".'Update finished. ('.$schemasCount.' schema/s found)');

        }

        // Update the AuthServiceProvider
        SchemaHelper::updateAuthServiceProvider($modelPolicyMapping);

    }
    
    /**
     * Sort the swagger file
     *
     * @return void
     */
    protected function sortSwaggerFile():void {
        
        $path = config('l5-swagger.paths.docs', storage_path('api-docs'))."/".config('l5-swagger.paths.docs_json', 'api-docs.json');

        if (File::exists($path) === false) {
            return;
        }

        $content = json_decode(File::get($path), true);
        $schemas = $content['components']['schemas'];

        uasort($schemas, function($a,$b){
            if (data_get($a, 'title', $a) == data_get($b, 'title', $b)) {
                return 0;
            }
            return (data_get($a, 'title', $a) < data_get($b, 'title', $b)) ? -1 : 1;
        });

        $content['components']['schemas'] = $schemas;

        File::put($path, json_encode($content, JSON_PRETTY_PRINT));

    }

}
