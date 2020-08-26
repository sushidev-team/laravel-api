<?php

namespace AMBERSIVE\Api\Helper;

use Yaml;
use File;
use DB;
use Str;
use Artisan;
use Version;

use Illuminate\Support\Arr;

use Ramsey\Uuid\Uuid;

use AMBERSIVE\Api\Classes\SchemaDeclaration;
use AMBERSIVE\Api\Classes\SchemaField;
use AMBERSIVE\Api\Classes\SchemaRelation;
use AMBERSIVE\Api\Classes\SchemaEndpoint;

class SchemaHelper
{
    protected static function sanitizeClasspath(string $classPath):string {

        $path = str_replace('App\\Models\\', '', $classPath);
        $path = str_replace('App\\', '', $path);
        $path = str_replace('\\', '/', $path);

        return $path;

    }
    
    /**
     * Create an imports list
     *
     * @param  mixed $schema
     * @return void
     */
    protected static function imports(array $schema = []) {
        $imports = isset($schema['imports']) ? collect($schema['imports']) : collect([]);
        $imports = $imports->map(function($import){
            return 'use '.$import.';';
        });
        return $imports;
    }
    
    /**
     * Create an traits list
     *
     * @param  mixed $schema
     * @return void
     */
    protected static function traits(array $schema = []) {
        $traits = isset($schema['traits']) ? collect($schema['traits']) : collect([]);
        $traits = $traits->map(function($trait){
            return 'use '.$trait.';';
        });
        return $traits;
    }

    protected static function pathForFile(string $classPath = null, string $prefix = null, string $fileExtension = null, string $store = 'app_path'): string {
        if ($classPath === null) {
            return "";
        }

        $path = self::sanitizeClasspath($classPath);

        if ($prefix  !== null) {
           $path = $store($prefix).(Str::endsWith($store($prefix),"/") === true ? '' :'/').$path;
        }

        if ($fileExtension !== null){
           $path .= '.'.$fileExtension;
        }

        return $path;
    }
    
    /**
     * Transform a model namespace to a policy namespace
     *
     * @param  mixed $model
     * @return string
     */
    public static function transformModelToPolicyNamespace(string $model, string $postfix = null):string {
        $namespace  = str_replace(
            'App\\Models', 
            'App\\Policies'.(config('ambersive-api.policy_laravel') != '' ? '\\'.str_replace('/', '\\', config('ambersive-api.policy_laravel')) : ''), 
            self::extractNamespace($model)
        );

        if ($postfix != null) {
            $namespace .= '\\'.$postfix;
        }

        return $namespace;
    }

    /**
     * Returns the path for the schema folder
     */
    public static function path($name = null){

        if ($name != null){
            $name        = strtolower($name);
        }   
        if (env('APP_ENV') === 'testing') {
            // Return a tmp path for testing
            return base_path('tmp').($name !== null ? "/${name}.yml" : '');
        }

        return config('ambersive-api.schema_store').($name !== null ? "/${name}.yml" : '');

    }

    /**
     * Returns a list of all available schema files
     */
    public static function listSchemas():array {

        $list  = ['users'];
        $files = File::allfiles(self::path());

        collect($files)->each(function($file) use (&$list) {
            $name = str_replace('.yml','', $file->getFilename());
            if (Str::endsWith($file->getFilename(), '.yml') && in_array($name, $list) == false) {
                $list[] = $name;
            }
        });

        return $list;

    }

    /**
     * Will create a schema yaml file
     */
    public static function createSchema(string $name = null, array $content = [], $update = false):string {
        
        if ($name === null || empty($content)){
            return null;
        }
        
        $path        = self::path($name);

        if ($update === false) {
            $fileExists = File::exists($path);
            if ($fileExists === true) {
                return null;
            }
        }

        $yamlContent = Yaml::dump($content);

        $file       = File::put($path, $yamlContent);
        $fileExists = File::exists($path);

        if ($fileExists === false) {
            return null;
        }

        return $path;

    }

    /**
     * Will delete a schema if it exists
     */
    public static function deleteSchema(string $name = null):bool {

        $path        = self::path($name);

        $fileExists = File::exists($path);

        if ($fileExists === true) {
            File::delete($path);
            return true;
        }

        return false;

    }

    /**
     * Read a schema file and return the content as array
     */
    public static function readSchema(string $name = null, string $customPath = null) {

        $name        = strtolower($name);
        $path        = self::path($name);

        if ($customPath !== null){
            $path = $customPath."/${name}.yml";
        }

        $fileExists = File::exists($path);

        if ($fileExists === false) {
            $path = base_path("vendor/AMBERSIVE/api/src/Schemas/${name}.yml");
            $fileExists = File::exists($path);
        }

        if ($fileExists === true) {
            return Yaml::parseFile($path);
        }

        return null;

    }

    /**
     * Check if a schema file exists
     */
    public static function exists(string $name = null){

        if ($name === null) {
            return false;
        }

        $path        = self::path($name);
        $fileExists  = File::exists($path);

        return $fileExists;

    }

    /**
     * Returns the default schema from the stubs folder
     */
    public static function defaultSchema(){

        $content = new SchemaDeclaration();
        return $content->toArray();

    }

    /**
     * Extracts the fields information from the database
     */
    public static function extractFieldFromDatabase($table = null){

        $columns  = null;
        $fields    = [];
        $typeField = ""; 
        $nameField = "";
        $dateField = "";
        $nullField = "";

        if (env('DB_CONNECTION') === 'mysql') {

            $columns   = DB::select(DB::raw('SHOW FIELDS FROM '.$table));
            $typeField = "Type"; 
            $nameField = "Field";
            $dateField = "timestamp";
            $nullField = "Null";
            
        }
        else if (env('DB_CONNECTION') === 'sqlite') {

            $columns   = DB::select(DB::raw('PRAGMA table_info('.$table.');'));            
            $typeField = "type";
            $nameField = "name";
            $dateField = "datetime";
            $nullField = "notnull";

        }

        // Create a list of columns
        if ($columns === null) {
            return $fields;
        }

        foreach ($columns as $key => $column) {
    
            $type      = preg_replace('/\(\d{1,}\)/i', '', $column->$typeField);
            $castAs    = null;
            $example   = null;

            switch($type){
                case 'tinyint':
                    $castAs = 'boolean';
                    $example = false;
                    break;
                case $dateField:
                    $castAs  = 'date';
                    $example = '2019-08-01 00:00:00';
                    break;
                default:
                    if ($column->$nameField === 'id' || Str::endsWith($column->$nameField, '_id') === true) {
                        $castAs  = 'uuid';
                        $example = Uuid::uuid4()->toString();
                    }
                    else {
                        $castAs  = 'string';
                        $example = '';
                    }
                    break;
            }

            $field = new SchemaField([
                'type'        => $castAs,  
                'description' => '',
                'example'     => $example
            ]);

            $fields[$column->$nameField] = $field->toArray();

        }

        return $fields;
    }

    /**
     * Extract the relations from the database
     */
    public static function extractRelations($table = null) {

        $relations = [];
        $columns   = [];

        $tableName = "";

        if (env('DB_CONNECTION') === 'mysql') {

            $columns   = collect(DB::select(DB::raw('SELECT * FROM information_schema.TABLE_CONSTRAINTS WHERE information_schema.TABLE_CONSTRAINTS.CONSTRAINT_TYPE = "FOREIGN KEY" AND information_schema.TABLE_CONSTRAINTS.TABLE_NAME = "'.$table.'"')));
            $tableName = "TABLE_NAME";

        }
        else if(env('DB_CONNECTION') === 'sqlite') {

            // Try to resolve by name resoltion
            // Be aware that this might not be possible
            $columnsFromTable = self::extractFieldFromDatabase($table);
            $tableName        = "TABLE_NAME";
            
            foreach($columnsFromTable as $key => $column){

                if (Str::endsWith($key, '_id')){

                    $columns[] = (object) [
                        'CONSTRAINT_NAME' => $table.'_'.$key.'_foreign',
                        'TABLE_NAME'      => Str::plural(str_replace('_id', '', $key))
                    ];

                }

            }

        }

        foreach ($columns as $key => $column) {

            $table    = $column->$tableName;
            $relation = new SchemaRelation();

            if (self::exists($table) == true){

                // Resolve if the table exists

                $columnId = $column->CONSTRAINT_NAME;
                $columnId = substr($columnId, strlen($table) + 1);
                $columnId = substr($columnId, 0, strlen($columnId) - 8);

                $relation->name  = strtolower(Str::plural(str_replace('_id', '', $columnId)));
                $relation->field = $columnId;

            }

            // If the name cannot be resolved do not add it to the schema relations list
            if ($relation->name !== null) {
                $relations[$relation->name] = $relation->toArray();
            }

        }

        return $relations;

    }

    /**
     * Returns the locked value from schema file
     * It will return false if the schema file does not exists
     */
    public static function isLocked($name = null):bool {

        $value = self::getValue($name, 'locked');
        return $value == null ? false : $value;

    }

    /**
     * Returns a attribute from schema file
     */
    public static function getValue($name = null, $attribute = null) {

        if ($attribute === null || $name === null) {
            return null;
        }

        $schemaFile = self::readSchema($name);
        $value      = null;

        if ($schemaFile != null){
            
            switch($attribute){
                default:
                    $value = isset($schemaFile[$attribute]) ? $schemaFile[$attribute] : null;
                    break;
            }

        }

        return $value;

    }
    
    /**
     * extract name space from a complete classpath
     *
     * @param  mixed $classPath
     * @return string
     */
    public static function extractNamespace(string $classPath = null): string {

        if ($classPath === null || strpos($classPath, '\\') === false) {
            return '';
        }

        $splitted = explode('\\', $classPath);

        array_pop($splitted);

        return implode('\\', $splitted);

    }
 
    /**
     * This command will create the path for a class file based on the namespace of the file
     * Prefix will define the folder of the classfile
     * Fileextention defines if the class should have a file extension
     *
     * @param  mixed $classPath
     * @param  mixed $prefix
     * @param  mixed $fileExtension
     * @return string
     */
    public static function extractPathForFile(string $classPath = null, string $prefix = null, string $fileExtension = null): string {
         return self::pathForFile($classPath, $prefix, $fileExtension, 'app_path');
    }
    
    /**
     * Extract the path for a factory
     *
     * @param  mixed $classPath
     * @param  mixed $prefix
     * @param  mixed $fileExtension
     * @return string
     */
    public static function extractPathForFactory(string $classPath = null, string $prefix, string $fileExtension = null): string {
        return self::pathForFile($classPath, $prefix, $fileExtension, 'base_path');
    }

    /**
     * This function will return a folder path for specific namespace
     * If the classPath parameter is null or empty it will return ""
     */
    public static function extractFolderForFile(string $classPath = null, string $prefix = null, string $store = 'app_path'): string {

        if ($classPath === null) {
            return "";
        }

        $path = self::sanitizeClasspath($classPath);

        $splitted = explode('/', $path);

        array_pop($splitted);

        $path = implode('/', $splitted);

        if ($prefix  !== null) {
            $path = $store($prefix).'/'.$path;
        }

        return $path;

    }

    /**
     * This method will return the Classname of a model based on it's
     * namespace path.
     */
    public static function extractClassName(string $classPath): string {
        
        if (strpos($classPath, '\\') === false) {
            return null;
        }

        $splitted = explode('\\', $classPath);

        return array_pop($splitted);
    }
    
    /**
     * Extract the custom ares marked with #region [CUSTOM:NAME]
     *
     * @param  mixed $path
     * @param  mixed $area
     * @return string
     */
    public static function extractCustomArea(string $path, string $area): string {

        $extracted = '';

        if (File::exists($path) === false) {
            return $extracted;
        }

        $file = File::get($path);
        $name = strtoupper($area);

        $startTag = "#region [CUSTOM:${name}]";
        $stopTag  = "#endregion [CUSTOM:${name}]";

        $tagStart = strpos($file, $startTag);
        $tagStop  = strpos($file, $stopTag);

        if ($tagStart !== false && $tagStop !== null){
            $extracted = substr($file, $tagStart + strlen($startTag));
            $tagStop  = strpos($extracted, $stopTag);
            $extracted = substr($extracted,0, $tagStop);
        }

        // Remove spaces, tags and line breaks
        $extracted = preg_replace("/\\n{1,}|\\r{1,}|\\s{2,}|\\t{1,}/", "", $extracted);

        return $extracted;

    }

    public static function transformToStringArray($data) : array {
        return collect($data)->map(function($item){
            return '"'. $item . '"';
        })->toArray();
    }

    public static function transformAssocToStringArray($data) : array {
        $result = [];
        collect($data)->each(function($data, $key) use (&$result){
            $result[] = "'".$key."' => '".$data."'";
        });
        return $result;
    }

    /**
     * Transform an array into a string 
     * @param $data: Needs to be an array (assoc)
     */
    public static function transformDeepAssocToStringArray(array $data) : string {

        $output = '[';

        foreach($data as $key => $value){
            if (is_string($value)) {
                $output .= '"'.$key.'" => "'.$value.'",';
            }
            else if($value !== null && Arr::isAssoc($value)) {
                $output .= '"'.$key.'" => '.self::transformDeepAssocToStringArray($value).',';
            }
            else if ($value !== null && is_array($value) && Arr::isAssoc($value) === false) {
                
                $output .= '"'.$key.'" => ['.implode(',', self::transformToStringArray($value)).'],';
            }
            else if ($value === null) {
                $output .= '"'.$key.'" => null,';
            }
            else {
                $output .= '"'.$key.'" => '.$value.',';
            }
        }

        $output .= ']';

        return $output;

    }
    
    /**
     * Check if the schema says that the creation is locked
     *
     * @param  mixed $path
     * @param  mixed $schema
     * @return bool
     */
    public static function handleLocked($path, array $schema): bool {
        
        // Check if the file is locked
        if (data_get($schema,'lockedHard') === true) {
            return false;
        }

        if (File::exists($path) === true && $schema['locked'] === true) {

            $file = File::get($path);

            if ($file !== null && strpos('<LOCKED>: true', $file) !== false) {
                return false;
            }

            $file = str_replace('<LOCKED>: false', '<LOCKED>: true', $file);
            File::put($path, $file);
            return false;

        }
        return true;
    }
    
    /**
     * This function will update the the base controler with all
     * the basic swagger information
     *
     * @return void
     */
    public static function createBaseController(): void {

        $version = Version::format(config('ambersive-api.swagger_version'));

        if ($version === null) {
            $version = '0.0.1';
        }

        $file = StubsHelper::replacePlaceholders('BaseController', [
            'version'     => $version,
            'title'       => config('ambersive-api.swagger_title'),
            'description' => config('ambersive-api.swagger_description'),
            'contact'     => config('ambersive-api.swagger_contact'),
            'licence'     => config('ambersive-api.swagger_licence'),
            'licenceUrl'  => config('ambersive-api.swagger_licence_url')
        ]);

        $path = app_path('Http/Controllers');

        if(File::isDirectory($path) == false) {
            File::makeDirectory($path, 0777, true);
        }

        File::put(app_path('Http/Controllers/Controller.php'), $file);

    }
  
    /**
     * This function will create the model file based on the schema file
     *
     * @param  mixed $name
     * @return bool
     */
    public static function createModel($name): bool {

        $success      = false;
        $schema       = self::readSchema($name);

        if ($schema === null) {
            return $success;
        }

        // Save file in the correct folder
        $path   = self::extractPathForFile($schema['model'], config('ambersive-api.model_laravel'), 'php');
        $folder = self::extractFolderForFile($schema['model'], config('ambersive-api.model_laravel'));

        // Check if the file is locked
        if (self::handleLocked($path, $schema) === false) {
            return $success;
        }

        if(!File::isDirectory($folder)){
            File::makeDirectory($folder, 0777, true, true);
        }

        // Define the imports
        $imports = self::imports($schema);

        // Define the imports
        $traits = self::traits($schema);

        // Documentation Block Starts
        $documentation = self::createModelDocumentation($schema);

        // Extract attribute fields
        $hidden   = [];
        $appends  = isset($schema['appends']) && is_array($schema['appends']) ? $schema['appends'] : [];
        $encrypts = [];
        $casts    = [];
        $dates    = [];
        
        collect($schema['fields'])->each(function($item,$name) use (&$hidden, &$encrypts, &$casts, &$dates, &$documentation){
            if (isset($item['hidden']) && $item['hidden'] === true) {
                $hidden[] = $name;
            }
            if (isset($item['encrypt']) && $item['encrypt'] === true) {
                $encrypts[] = $name;
            }

            switch($item['type']){
                case 'integer':
                case 'boolean':
                    $casts[$name] = $item['type'];
                    break;
                case 'date':
                    $casts[$name] = 'datetime';
                    $dates[] = $name;
                    break;
            }
        });

        // Custom casts
        $casts = array_merge($casts, isset($schema['casts']) && is_array($schema['casts']) ? $schema['casts'] : []);

        // Methods
        $methods = data_get($schema,'methods', []);

        // Relations
        $relations = [];
        collect($schema['relations'])->each(function($relation) use (&$relations){

            $withs = collect($relation['with'])->map(function($with){
                 return '->with('.$with.')';
            })->toArray();

            $orderBys = collect($relation['order'])->map(function($direction, $key){
                return '->orderBy("'.$key.'","'.($direction != "" ? $direction : 'ASC').'")';
            })->toArray();

            // Define the relationship
            // Only relations with a valid class will be created
            if ($relation['model'] == null || $relation['model'] === '') {
                return;
            }

            $relations[] = StubsHelper::replacePlaceholders('ModelRelation', [
                'name'          => $relation['name'],
                'model'         => $relation['model'],
                'type'          => $relation['type'],
                'field'         => $relation['field'],
                'field_foreign' => $relation['field_foreign'],
                'with'          => implode('', $withs),
                'orderBy'       => implode('', $orderBys)
            ]);

        });

        // Declare the placeholders
        $placeholders = [
            'namespace'         => self::extractNamespace($schema['model']),
            'modelName'         => self::extractClassName($schema['model']),
            'locked'            => $schema['locked'] == true ? 'true' : 'false',
            'imports'           => array_merge($imports->toArray()),
            'traits'            => array_merge($traits->toArray()),
            'extends'           => isset($schema['extends']) ? 'extends '.$schema['extends'] : 'extends BaseModel',
            'implements'        => isset($schema['implement']) && sizeOf($schema['implement']) > 0 ? 'implements '.implode(',', $schema['implement']) : '',
            'documentation'     => $documentation,
            'table'             => $schema['table'],
            'fillable'          => implode(',', self::transformToStringArray(array_keys($schema['fields']))),
            'hidden'            => implode(',', self::transformToStringArray($hidden)),
            'appends'           => implode(',', self::transformToStringArray($appends)),
            'casts'             => implode(',', self::transformAssocToStringArray($casts)),
            'encryptable'       => implode(',', self::transformToStringArray($encrypts)),
            'dates'             => implode(',', self::transformToStringArray($dates)),
            'customImports'     => self::extractCustomArea($path, 'IMPORTS'),
            'customTraits'      => self::extractCustomArea($path, 'TRAITS'),
            'customMethods'     => self::extractCustomArea($path, 'METHODS'),
            'relations'         => implode('', $relations),
            'methods'           => implode("\n\n", $methods),
        ];

        StubsHelper::save('Model', $path, $placeholders);

        // Run prettier command
        $exitCode = Artisan::call('api:format', [
            '--path' => $path
        ]);

        $success = $exitCode == 0;

        return $success;

    }
   
    /**
     * Create a model documentation based on the schema definition
     * This function will also create the documentation for request body entries
     *
     * @param  mixed $schema
     * @return string
     */
    public static function createModelDocumentation(array $schema):string {


        $splitted  = explode('\\', $schema['model']);
        $className = array_pop($splitted);

        $fieldsAmount    = collect($schema['fields'])->count();
        $fieldsCount     = 0;

        $properties       = [];
        $required         = [];

        collect($schema['fields'])->each(function($item,$name) use (&$properties, &$required, &$fieldsCount, $fieldsAmount){

            $example = $item['type'] !== 'integer' && $item['type'] !== 'boolean' ? '"'.$item['example'].'"' : $item['example'] ;
            
            if ($item['type'] === 'boolean') {
                $example = $item['example']  === true ? 'true' : 'false';
            }      
            
            if ($example == null || $example === '') {
                $example = '""';
            }

            $propertyData = [
                'name'        => $name,
                'type'        => $item['type'],
                'example'     => $example,
                'description' => (isset($item['description']) ? $item['description'] : '')
            ];

            $property = StubsHelper::replacePlaceholders('OpenApiProperty', $propertyData);

            $properties[] = $property;
            $fieldsCount++;

            if ($fieldsCount != $fieldsAmount) {
                $properties[sizeOf($properties) - 1] .= ',';
            }

            if ($item['required_create'] === true) {
                $required[] = $name;
            }

        });

        // Create Request bodies for this model
        $requestBodies       = data_get($schema, 'requestBodies', []);
        $requestBodiesTypes  = array_keys($requestBodies);  
        $requestBodiesExport = [];

        if (in_array('store', $requestBodiesTypes) === false) {
            $requestBodies['store'] = data_get($schema, 'fields');
        } 

        if (in_array('update', $requestBodiesTypes) === false) {
            $requestBodies['update'] = data_get($schema, 'fields');
        }      

        $requestBodiesTypes = array_keys($requestBodies);  

        $requestBodies = collect($requestBodies)->map(function($requestBodyFields, $key) use ($schema){
            
            return collect($requestBodyFields)->map(function($field, $fieldKey) use ($schema, $key) {
                $definition = data_get($schema, "fields.${fieldKey}", []);
                
                $definition['required'] = false;

                if (isset($definition["required_${key}"])){
                    $definition['required'] = $definition["required_${key}"];
                }

                unset($definition['encrypt']);
                unset($definition['hidden']);
                unset($definition['required_create']);
                unset($definition['required_update']);
                
                $field = $field != null ? array_merge($definition, $field) : $definition;

                return $field;
            })->filter(function($item){
                if ($item !== null){
                    return $item;
                }
            })->toArray();
        })->toArray();

        foreach($requestBodies as $requestBodyKey => $requestBodyFields) {

            $propertiesRequestBody = [];
            $propertiesRequired = [];

            collect($requestBodyFields)->each(function($item,$name) use (&$propertiesRequestBody, &$propertiesRequired, &$fieldsCount, $fieldsAmount){

                $example = $item['type'] !== 'integer' && $item['type'] !== 'boolean' ? '"'.$item['example'].'"' : $item['example'] ;
                
                if ($item['type'] === 'boolean') {
                    $example = $item['example']  === true ? 'true' : 'false';
                }
    
                $property = StubsHelper::replacePlaceholders('OpenApiProperty', [
                    'name'        => $name,
                    'type'        => $item['type'],
                    'example'     => $example,
                    'description' => (isset($item['description']) ? $item['description'] : '')
                ]);
    
                $propertiesRequestBody[] = $property;
                $fieldsCount++;
    
                if ($fieldsCount != $fieldsAmount) {
                    $propertiesRequestBody[sizeOf($propertiesRequestBody) - 1] .= ',';
                }
    
                if (isset($item['required']) && $item['required'] === true) {
                    $propertiesRequired[] = $name;
                }
    
            });

            $requestBodiesExport[] = StubsHelper::replacePlaceholders('OpenApiRequestBody', [
                'className'  => self::extractClassName($schema['model']),
                'method'     => ucfirst($requestBodyKey),
                'properties' => implode("\n",$propertiesRequestBody),
                'required'   => self::transformToStringArray($propertiesRequired, '"')
            ]);

        }

        // Create model schema definition
        $documentation = StubsHelper::replacePlaceholders('OpenApiSchema', [
            'properties' => implode("\n",$properties),
            'className'  => $className,
            'required'   => self::transformToStringArray($required, '"'),
            'requestBodies' => implode("\n",$requestBodiesExport)
        ]);

        return $documentation;
    }
    
    /**
     * Create a resource entry
     *
     * @param  mixed $name
     * @return bool
     */
    public static function createResource($name, string $customPath = null): bool {
        $schema    = self::readSchema($name, $customPath);
        $success   = false;

        $splitted  = explode('\\', $schema['resource'] != null ? $schema['resource'] : '');
        $collectionName = array_pop($splitted);

        $path   = self::extractPathForFile($schema['resource'], config('ambersive-api.resource_laravel'), 'php');
        $folder = self::extractFolderForFile($schema['resource'], config('ambersive-api.resource_laravel'));

        // Check if the file is locked
        if (self::handleLocked($path, $schema) === false) {
            return $success;
        }

        if(!File::isDirectory($folder)){
            File::makeDirectory($folder, 0777, true, true);
        }

        // Define the imports
        $imports = self::imports($schema);
        
        // Documentation
        $documentation = self::createResourceDocumentation($schema);

        $file = StubsHelper::replacePlaceholders('Resource', [
            'documentation'            => $documentation,
            'namespace'                => self::extractNamespace($schema['resource']),
            'className'                => self::extractClassName($schema['resource']),
            'locked'                   => $schema['locked'] == true ? 'true' : 'false',
            'resource'                 => $schema['resource'],
            'customImports'            => self::extractCustomArea($path, 'IMPORTS'),
            'customTraits'             => self::extractCustomArea($path, 'TRAITS'),
            'customMethods'            => self::extractCustomArea($path, 'METHODS'),
            'customResourceHandler'    => self::extractCustomArea($path, 'RESOURCEHANDLER'),
            'imports'                  => $imports->toArray(),
        ]);

        File::put($path, $file);

        // Run prettier command
        $exitCode = Artisan::call('api:format', [
            '--path' => $path
        ]);

        $success = $exitCode == 0;

        return $success;
    }
    
    /**
     * Create the documentation for the resource entry
     *
     * @param  mixed $schema
     * @return String
     */
    public static function createResourceDocumentation($schema): String {

        $result = "";

        $properties = [];
        $fields = data_get($schema, 'fields', []);
        $className = self::extractClassName(data_get($schema, 'resource'));
        $schemaResource = data_get($schema,'schemaResource', ['id' => []]);

        $schemaResource = collect($schemaResource)->map(function($field, $fieldKey) use ($fields){

            $definition = data_get($fields, $fieldKey, []);

            unset($definition['required_update']);
            unset($definition['required_create']);
            unset($definition['hidden']);
            unset($definition['encrypt']);

            $definition['type'] = self::transformToSwaggerParamsType(data_get($definition, 'type', 'string'));
 
            return array_merge($definition, $field !== null ? $field : []);
        })->toArray();

        foreach($schemaResource as $key => $field) {

            $example = null;
            if (isset($field['example'])){
                $example = $field['type'] !== 'integer' && $field['type'] !== 'boolean' ? '"'.$field['example'].'"' : $field['example'] ;
            }

            if ($example == null || $example === "") {
                $example = '""';
            }

            $propertyData = [
                'name'        => $key,
                'type'        => data_get($field, 'type', 'string'),
                'example'     => $example,
                'description' => data_get($field, 'description', '')
            ];

            $property = StubsHelper::replacePlaceholders('OpenApiProperty', $propertyData).",";

            $properties[] = $property;
        }

        // Create resource schema definition
        $result = StubsHelper::replacePlaceholders('OpenApiSchemaResource', [
            'properties' => implode("\n",$properties),
            'className'  => $className
        ]);

        return $result;
        
    }

    public static function createCollection($name, string $customPath = null): bool {

        $schema    = self::readSchema($name, $customPath);
        $success   = false;

        $splitted  = explode('\\', $schema['collection'] != null ? $schema['collection'] : '');
        $collectionName = array_pop($splitted);

        $path   = self::extractPathForFile($schema['collection'], config('ambersive-api.collection_laravel'), 'php');
        $folder = self::extractFolderForFile($schema['collection'], config('ambersive-api.collection_laravel'));

        // Check if the file is locked
        if (self::handleLocked($path, $schema) === false) {
            return $success;
        }

        if(!File::isDirectory($folder)){
            File::makeDirectory($folder, 0777, true, true);
        }

        // Define the imports
        $imports = self::imports($schema);

        $file = StubsHelper::replacePlaceholders('Collection', [
            'namespace'                => self::extractNamespace($schema['collection']),
            'className'                => self::extractClassName($schema['collection']),
            'locked'                   => $schema['locked'] == true ? 'true' : 'false',
            'resource'                 => $schema['resource'],
            'customImports'            => self::extractCustomArea($path, 'IMPORTS'),
            'customTraits'             => self::extractCustomArea($path, 'TRAITS'),
            'customMethods'            => self::extractCustomArea($path, 'METHODS'),
            'customCollectionHandler'  => self::extractCustomArea($path, 'COLLECTIONHANDLER'),
            'customResourceHandler'    => self::extractCustomArea($path, 'RESOURCEHANDLER'),
            'documentation'            => '',
            'imports'                  => $imports->toArray(),
        ]);

        File::put($path, $file);

        // Run prettier command
        $exitCode = Artisan::call('api:format', [
            '--path' => $path
        ]);

        $success = $exitCode == 0;

        return $success;
    }
    
    /**
     * Create a controller file. This method will return true if controller was created
     *
     * @param  mixed $name
     * @return bool
     */
    public static function createController($name, string $customPath = null): bool {
        
        $schema    = self::readSchema($name, $customPath);
        $success   = false;

        $splitted  = explode('\\', $schema['model'] != null ? $schema['model'] : '');
        $controllerName = array_pop($splitted);

        $path   = self::extractPathForFile($schema['model'].'Controller', 'Http/Controllers/'.config('ambersive-api.controller_laravel'), 'php');
        $folder = self::extractFolderForFile($schema['model'], 'Http/Controllers/'.config('ambersive-api.controller_laravel'));

        // Check if the file is locked
        if (self::handleLocked($path, $schema) === false) {
            return $success;
        }

        if(!File::isDirectory($folder)){
            File::makeDirectory($folder, 0777, true, true);
        }

        // Define the imports
        $imports = self::imports($schema);

        $file = StubsHelper::replacePlaceholders('Controller', [
            'namespace'            => 'App\\Http\\Controllers\\Api\\'.self::extractFolderForFile($schema['model']),
            'controllerName'       => $controllerName,
            'imports'              => $imports->toArray(),
            'locked'               => $schema['locked'] == true ? 'true' : 'false',
            'customImports'        => self::extractCustomArea($path, 'IMPORTS'),
            'customTraits'         => self::extractCustomArea($path, 'TRAITS'),
            'customConstructor'    => self::extractCustomArea($path, 'CONSTRUCTOR'),
            'customMethods'        => self::extractCustomArea($path, 'METHODS'),
            'controllerApiAll'     => self::createControllerMethodAll($schema, $path),
            'controllerApiIndex'   => self::createControllerMethodIndex($schema, $path),
            'controllerApiShow'    => self::createControllerMethodShow($schema, $path),
            'controllerApiUpdate'  => self::createControllerMethodUpdate($schema, $path),
            'controllerApiStore'   => self::createControllerMethodStore($schema, $path),
            'controllerApiDelete'  => self::createControllerMethodDelete($schema, $path),
            'model'                => $schema['model'],
            'resource'             => $schema['resource'],
            'collection'           => $schema['collection'],
            'policy'               => isset($schema['policy']) ? $schema['policy'] : null
        ]);

        File::put($path, $file);

        // Run prettier command
        $exitCode = Artisan::call('api:format', [
            '--path' => $path
        ]);

        $success = $exitCode == 0;

        return $success;
    }
    
    /**
     * Create a single controller function
     *
     * @param  mixed $schema
     * @param  mixed $path
     * @param  mixed $name
     * @return string
     */
    public static function createControllerMethod(array $schema, string $path, $name): string {

        // Check if the endpoint is available in the definition - if not the replacement will be empty.
        if (isset($schema['endpoints']) === false || isset($schema['endpoints'][$name]) === false) {
            return '';
        }

        // Check if the include flag is set to true
        $include = data_get($schema, "endpoints.${name}.include", false);

        if ($include === false) {
            return '';
        }

        $endpoint = data_get($schema, 'endpoints.'.$name, null);
        $permissions = data_get($schema, 'permissions', []);
        $excluded = data_get($schema, 'endpoints_exclude', []);

        // Check if the endpoint is on list of excluded elements
        if (in_array($name, $excluded)) {
            return '';
        }

        // Build the register array

        $register = [
            'fields'      => data_get($endpoint, 'fields',      ['*']),
            'where'       => data_get($endpoint, 'where',       []),
            'with'        => data_get($endpoint, 'with',        []),
            'hookPre'     => data_get($endpoint, 'hookPre',     []),
            'hookPost'    => data_get($endpoint, 'hookPost',    []),
            'permissions' => data_get($endpoint, 'permissions', []),
            'policy'      => data_get($endpoint, 'policy',      null),
            'order'       => data_get($endpoint, 'order',       []),
            'validations' => data_get($endpoint, 'validations', []),
        ];

        $result = StubsHelper::replacePlaceholders('ControllerApi'.ucfirst($name), [
            'methodRegister'      => self::transformDeepAssocToStringArray($register),
            'checkPermission'     => in_array($schema['table'].'-'.$name, $permissions) ? '->checkPermissions()' : '',
            'checkValidations'    => sizeOf(data_get($endpoint, 'validations', [])) > 0 ? '->checkValidation()' : '',
            'checkPolicy'         => data_get($endpoint, 'policy', null) != null ? '->checkPolicy()' : '',
            'customEndpointLogic' => self::extractCustomArea($path, 'ENDPOINT-'.strtoupper($name)),
            'customPreHook'       => self::extractCustomArea($path, 'PREHOOK-'.strtoupper($name)),
            'customPostHook'      => self::extractCustomArea($path, 'POSTHOOK-'.strtoupper($name)),
            'documentation'       => self::createControllerDocumentation($schema, $name)
        ]);

        return $result;

    }

    public static function createControllerMethodAll(array $schema, string $path): string {
        $result = self::createControllerMethod($schema, $path, 'all');
        return $result;
    }

    public static function createControllerMethodIndex(array $schema, string $path): string {

        $result = self::createControllerMethod($schema, $path, 'index');
        return $result;
    }

    public static function createControllerMethodShow(array $schema, string $path): string {

        $result = self::createControllerMethod($schema, $path, 'show');
        return $result;
    }

    public static function createControllerMethodUpdate(array $schema, string $path): string {

        $result = self::createControllerMethod($schema, $path, 'update');
        return $result;
    }

    public static function createControllerMethodStore(array $schema, string $path): string {

        $result = self::createControllerMethod($schema, $path, 'store');
        return $result;
    }

    public static function createControllerMethodDelete(array $schema, string $path): string {

        $result = self::createControllerMethod($schema, $path, 'destroy');

        return $result;
    }
    
    /**
     * This method will create the the documentation block for a controller
     *
     * @param  mixed $schema
     * @param  mixed $name
     * @return string
     */
    public static function createControllerDocumentation(array $schema, $name): string {

        $lines = [];

        // Define the route for this endpoint
        $route = data_get($schema, "endpoints.${name}.route", "");
        $route != "" && $route != null ? $route = "api/".$route : $route = $name;

        // Which schema will be returned
        $responseSuccess = data_get($schema, "endpoints.${name}.responseSchemaSuccess", null);
        $responseType = data_get($schema, "endpoints.${name}.responseType", null);
        $responseMode = data_get($schema, "endpoints.${name}.responseMode", null);
        $requestBody = data_get($schema, "endpoints.${name}.requestBody", null);;

        $main = Str::camel(Str::singular(data_get($schema, "table", "")));

        $tags = collect(data_get($schema, "endpoints.${name}.tags", [ucfirst(Str::plural($main))]))->map(function($tag) {
            return '"'.$tag.'"';
        })->toArray();

        $model = $responseSuccess === null || $responseSuccess === "" ? self::extractClassName($schema['model'])."Model" : $responseSuccess;

        $method = "Get";

        switch($name) {
            case 'index':
                $responseType === null ? $responseType = 'array' : null;
                $responseMode === null ? $responseMode = 'StandardApiResponsePaginated' : null;
            break;
            case 'all':
                $responseType === null ? $responseType = 'array' : null;
                $responseMode === null ? $responseMode = 'StandardApiResponse' : null;
            break;
            case 'show':
                $method = 'Get';
                $responseType === null ? $responseType = 'object' : null;
                $responseMode === null ? $responseMode = 'StandardApiResponse' : null;
            break;
            case 'store':
                $method = 'Post';
                $responseType === null ? $responseType = 'object' : null;
                $responseMode === null ? $responseMode = 'StandardApiResponse' : null;
                $requestBody === null ? $requestBody   = "${main}RequestBodyStore" : null;
            break;
            case 'update':
                $method = 'Put';
                $responseType === null ? $responseType = 'object' : null;
                $responseMode === null ? $responseMode = 'StandardApiResponse' : null;
                $requestBody === null ? $requestBody   = "${main}RequestBodyUpdate" : null;
            break;
            case 'delete':
            case 'destroy':
                $method = 'Delete';
                $responseType === null ? $responseType = 'object' : null;
                $responseMode === null ? $responseMode = 'StandardApiDestroyResponse' : null;
            break;
            default:
                $method = 'Get';
                $responseType === null ? $responseType = 'object' : null;
                $responseMode === null ? $responseMode = 'StandardApiResponse' : null;
            break;
        }

        $reference = "ref=\"#/components/schemas/${model}\",";

        switch($responseType) {
            case 'array':
            $reference = "@OA\Items(ref=\"#/components/schemas/${model}\"),";
            break;
        }

        $responseReferences = ["reference" => $reference,  'responseType' => $responseType, 'route' => $route];

        // Create the request params part
        $requestParamsBody = [];
        $requestParamFallback = new SchemaField();
        $requestParams = Arr::collapse(collect(data_get($schema, "endpoints.${name}.requestParams", []))->map(function($item) use ($schema , $name, $requestParamFallback){

            if (is_string($item)) {

                return [
                    $item => data_get($schema, "fields.${item}", [])
                ];
            }

            $key = array_keys($item)[0];
            $definition = data_get($schema, "fields.${key}", $requestParamFallback->toArray());

            $item[$key] = array_merge($definition, $item[$key]);
            return $item;

        })->toArray());

        if (!empty($requestParams)) {
            
            $requestParamsKeys = array_keys($requestParams);   

            foreach($requestParamsKeys as $param) {


                if (isset($requestParams[$param]['required']) == false) {

                     switch($name){
                         case 'store':
                            $requestParams[$param]['required'] = data_get($requestParams[$param], 'required_create', false);                            
                            break;
                        case 'update':
                            $requestParams[$param]['required'] = data_get($requestParams[$param], 'required_update', false);
                            break;
                     }

                }

                unset ($requestParams[$param]['required_create']);
                unset ($requestParams[$param]['required_update']);

                $requestParamsBody[] = StubsHelper::replacePlaceholders('OpenApiRequestParam', [
                    'where'             => data_get($requestParams[$param], 'in', 'path'),
                    'param'             => $param,
                    'required'          => data_get($requestParams[$param], 'required', null) === true ? 'true' : 'false',
                    'type'              => self::transformToSwaggerParamsType(data_get($requestParams[$param], 'type', 'string')),
                    'description'       => data_get($requestParams[$param], 'description', ''),
                    'example'           => in_array(data_get($requestParams[$param], 'type', 'string'), ['integer', 'boolean']) ? data_get($requestParams[$param], 'example') : '"'.data_get($requestParams[$param], 'example', '').'"'
                ]);

            }

        }

        // Documentation
        $documentationMain = StubsHelper::replacePlaceholders('OpenApiEndpoint', [
            'name'               => ucfirst($main),
            'function'           => ucfirst($name),
            'method'             => $method,
            'route'              => $route,
            'model'              => $model,
            'responseMode'       => $responseMode,
            'requestBodyParams'  => empty($requestParamsBody) === false ? "\n".implode("\n", $requestParamsBody) : '',
            'requestBody'        => $requestBody != null ? "\n".StubsHelper::replacePlaceholders('OpenApiEndpointRequestBody', ['requestBody' => ucfirst($requestBody)]) : '',
            'reference'          => in_array($responseMode, ['StandardApiResponse', 'StandardApiResponsePaginated']) ? StubsHelper::replacePlaceholders($responseMode === 'StandardApiResponse' ? 'OpenApiResponseDefault' : 'OpenApiPagination', $responseReferences) : '',
            'authentication'     => in_array('auth:api', data_get($schema, "endpoints.${name}.middleware", [])) ? "\n*      security={{\"bearer\":{}}}," : "",
            'summary'            => data_get($schema, "endpoints.${name}.summary", ""),
            'description'        => data_get($schema, "endpoints.${name}.description", ""),
            'descriptionSuccess' => data_get($schema, "endpoints.${name}.descriptionSuccess", "Successful response"),
            'descriptionFailed'  => data_get($schema, "endpoints.${name}.descriptionFailed", "Failed response"),
            'tags'               => implode(',', $tags),
        ]);
        
        // Build the documentation for this endpoint request
        $lines[] = "#region [Documentation]: ${route}";
        $lines[] = $documentationMain;
        $lines[] = "#endregion";

        $result = implode("\n", $lines);

        return $result;

    }

    public static function createTests($name, string $customPath = null): bool {

        $tests = [
            self::createTest($name, 'Controller', 'Feature', 'Controllers',  $customPath),
            self::createTest($name, 'Model', 'Unit', 'Models', $customPath)
        ];

        return in_array(false, $tests) === false;
    }

    /**
     * Create a single test file
     */
    public static function createTest($name, string $nameSuffix = '', string $type = 'Feature', string $prefix = null, string $customPath = null): bool {

        $schema    = self::readSchema($name, $customPath);
        $success   = false;

        $splitted  = explode('\\', $schema['model'] != null ? $schema['model'] : '');
        $className = array_pop($splitted);

        $path   = self::extractPathForFactory($schema['model'].$nameSuffix.'Test', 'tests/'.($type != null && $type != '' ? '/'.$type  : '').config('ambersive-api.tests_laravel').($prefix != null ? '/' . $prefix : ''), 'php');
        $folder = self::extractFolderForFile($schema['model'].$nameSuffix.'Test', 'tests'.($type != null && $type != '' ? '/'.$type : '').config('ambersive-api.tests_laravel').($prefix != null ? '/' . $prefix : ''), 'base_path');

        // Check if the file is locked
        if (self::handleLocked($path, $schema) === false) {
            return $success;
        }

        if(!File::isDirectory($folder)){
            File::makeDirectory($folder, 0777, true, true);
        }

        $file = StubsHelper::replacePlaceholders('Test', [
            'namespace'            => 'Tests\\'.$type.($prefix != null ? "\\".implode("\\", explode("/", $prefix)) : '').'\\'.self::extractFolderForFile($schema['model']),
            'name'                 => $className.$nameSuffix,       
            'className'            => '\\'.$schema['model'],
            'locked'               => $schema['locked'] == true ? 'true' : 'false',
            'customImports'        => self::extractCustomArea($path, 'IMPORTS'),
            'tests'                => self::extractCustomArea($path, 'TEST'),
            'imports'              => ''
        ]);

        File::put($path, $file);

        // Run prettier command
        $exitCode = Artisan::call('api:format', [
            '--path' => $path
        ]);

        $success = $exitCode == 0;

        return $success;

    }

    public static function createPolicy($name, string $customPath = null): bool {
        
        $schema    = self::readSchema($name, $customPath);
        $success   = false;

        $splitted   = explode('\\', $schema['model'] != null ? $schema['model'] : '');
        $className  = array_pop($splitted);
        $namespace  = str_replace(
            'App\\Models', 
            'App\\Policies'.(config('ambersive-api.policy_laravel') != '' ? '\\'.str_replace('/', '\\', config('ambersive-api.policy_laravel')) : ''), 
            self::extractNamespace($schema['model'])
        );

        $path   = self::extractPathForFactory($schema['model'].'Policy', 'app/Policies'. config('ambersive-api.policy_laravel'), 'php');
        $folder = self::extractFolderForFile($schema['model'].'Policy',  'Policies'. config('ambersive-api.policy_laravel'), 'app_path');

        // Check if the file is locked
        if (self::handleLocked($path, $schema) === false) {
            return $success;
        }

        if(!File::isDirectory($folder)){
            File::makeDirectory($folder, 0777, true, true);
        }

        // Define the imports
        $imports = self::imports($schema);

        $file = StubsHelper::replacePlaceholders('Policy', [
            'namespace'                   => $namespace,
            'className'                   => '\\'.$schema['model'],
            'model'                       => '\\'.$schema['model'],
            'collection'                  => '\\'.$schema['collection'],
            'name'                        => $className,
            'locked'                      => $schema['locked'] == true ? 'true' : 'false',
            'imports'                     => array_merge($imports->toArray()),           
            'customImports'               => self::extractCustomArea($path, 'IMPORTS'),
            'customMethods'               => self::extractCustomArea($path, 'METHODS'),
            'customPolicyBefore'          => self::extractCustomArea($path, 'POLICY-BEFORE'),
            'customPolicyAll'             => self::extractCustomArea($path, 'POLICY-ALL'),
            'customPolicyShow'            => self::extractCustomArea($path, 'POLICY-SHOW'),
            'customPolicyStore'           => self::extractCustomArea($path, 'POLICY-STORE'),
            'customPolicyUpdate'          => self::extractCustomArea($path, 'POLICY-UPDATE'),
            'customPolicyDestroy'         => self::extractCustomArea($path, 'POLICY-DESTROY'),
            'userClass'                   => config('auth.providers.users.model')
        ]);

        File::put($path, $file);

        // Run prettier command
        $exitCode = Artisan::call('api:format', [
            '--path' => $path
        ]);

        $success = $exitCode == 0;

        return $success;

        return $success;

    }

    public static function createFactory($name): bool {
        
        $schema    = self::readSchema($name);
        $success   = false;

        $splitted  = explode('\\', $schema['model'] != null ? $schema['model'] : '');
        $className = array_pop($splitted);

        $path   = self::extractPathForFactory($schema['model'], 'database/factories'.config('ambersive-api.factory_laravel'), 'php');
        $folder = self::extractFolderForFile($schema['model'], 'database/factories'.config('ambersive-api.factory_laravel'), 'base_path');

        // Check if the file is locked
        if (self::handleLocked($path, $schema) === false) {
            return $success;
        }

        if(!File::isDirectory($folder)){
            File::makeDirectory($folder, 0777, true, true);
        }

        $file = StubsHelper::replacePlaceholders('Factory', [
            'className'            => '\\'.$schema['model'],
            'locked'               => $schema['locked'] == true ? 'true' : 'false',
            'customImports'        => self::extractCustomArea($path, 'IMPORTS'),
            'customLogic'          => self::extractCustomArea($path, 'LOGIC')
        ]);

        File::put($path, $file);

        // Run prettier command
        $exitCode = Artisan::call('api:format', [
            '--path' => $path
        ]);

        $success = $exitCode == 0;

        return $success;

    }
    
    /**
     * Update the default laravel AuthServiceProvider to ensure
     *
     * @param  mixed $policies
     * @return bool
     */
    public static function updateAuthServiceProvider(array $policies = []):bool {

        $success = false;

        $path = app_path("Providers/AuthServiceProvider.php");

        $customImports = self::extractCustomArea($path, 'IMPORTS');
        $customCode = self::extractCustomArea($path, 'CODE');
        $customBoot = self::extractCustomArea($path, 'BOOT');
        $customPolices = self::extractCustomArea($path, 'POLICIES');

        $policiesConverted = collect($policies)->map(function($item,$key){
            return "\"${key}\" => \"${item}\",\n";
        })->flatten()->toArray();

        $file = StubsHelper::replacePlaceholders('AuthServiceProvider', [
            'customImports'        => $customImports  != null ? $customImports : "",
            'customCode'           => $customCode  != null ? $customCode : "",
            'customBoot'           => $customBoot  != null ? $customBoot : "",
            'customPolicies'       => $customPolices  != null ? $customPolices : "",
            'policies'             => $policiesConverted
        ]);

        if(File::isDirectory(app_path("Providers")) == false) {
            File::makeDirectory(app_path("Providers"), 0777, true);
        }     

        File::put($path, $file);

        // Run prettier command
        $exitCode = Artisan::call('api:format', [
            '--path' => $path
        ]);

        $success = $exitCode == 0;

        return $success;

    }
    
    /**
     * Transform a field type to a valid swagger type
     *
     * @param  mixed $type
     * @return String
     */
    public static function transformToSwaggerParamsType(String $type = null):String {

        $return = $type;

        switch($type){

            case null:
            case 'uuid':
                $return = 'string';
            break;
        }

        return $return;

    }

}