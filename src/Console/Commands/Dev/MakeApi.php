<?php

namespace AMBERSIVE\Api\Console\Commands\Dev;

use Illuminate\Console\Command;

use AMBERSIVE\Api\Helper\SchemaHelper; 
use AMBERSIVE\Api\Classes\SchemaDeclaration;

class MakeApi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'api:make {--table= : Table name} {--model= : Model name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command will create the schema file for the api.';

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

        $schema     = new SchemaDeclaration();

        // First run the prepare Command 
        $this->call('api:prepare');

        // Check for arguments
        $options = $this->options();
        
        if (data_get($options, 'table', null) === null){
            $this->error('Option "table" is required.');
            return 1;
        }

        if (data_get($options, 'model', null) === null){
            $this->error('Option "model" is required.');
            return 1;
        }

        $table = data_get($options, 'table', null);
        $model = data_get($options, 'model', null);

        // Set the table name
        $schema->update(['table' => $table])
               ->setModel($model)
               ->setResource()
               ->setCollection()
               ->setDefaultEndpoints()
               ->update();

        // Get fields from the database
        $fields = $this->getFields($table);
        $schema->fields = $fields;

        // Get the table relations
        $relations = $this->getRelations($table);
        $schema->relations = $relations;

        if (SchemaHelper::exists($table) == true) {

            // TODO: Ask for permission to overwrite
            $this->warn('There is already a schema file.');
        }

        // Create the schema file
        SchemaHelper::createSchema($schema->table, $schema->toArray(), true);
    
        $this->line('Schema file with the name:'. $schema->table.' has been created.');

    }

    /**
     * Read the fields from the database and
     * merge it with the default schema.
     */
    protected function getFields($table = null){

       $fields        = SchemaHelper::extractFieldFromDatabase($table);
       $fieldsDefault = SchemaHelper::defaultSchema()['fields'];

       if ($fieldsDefault == null) {
           $fieldsDefault = [];
       }

       $fieldsDefault = array_merge($fieldsDefault, $fields);

       return $fieldsDefault;

    }

    protected function getRelations($table = null){

        $relations = SchemaHelper::extractRelations($table);

        if (empty($relations)){
            return null;
        }

        return $relations;

    }

}
