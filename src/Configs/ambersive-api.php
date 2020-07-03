<?php

return [

    /*
    |--------------------------------------------------------------------------
    | API - Basic settings
    |--------------------------------------------------------------------------
    |
    */

    // Standard response type of api
    'default' => env('API_DEFAULT_RESPONSE','json'),

    // Entries per pagination site
    'per_site' => env('API_DEFAULT_ENTRIES_PER_PAGE', 25),

    // GZIP active
    'gzip' => env('API_DEFAULT_GZIP', true),

    // Append the url to every request
    'append_url' => env('API_APPEND_URL', false),

    // Except routes from creation
    'except' => explode(',', env('API_DEFAULT_EXCEPT', 'create,edit')),

    // Path for VUEJS Models
    'models_vue' => env('API_MODELS_VUE', null),

    // Path for VUEJS Stores
    'stores_vue' => env('API_STORES_VUE', null),

    // Gzip compression for api calls active?
    'gzip' => env('API_GZIP_ACTIVE', true),

    // Models - Based on app_path()
    'model_laravel' => env('API_MODELS_LARAVEL', 'Models'),

    // Resource - Bases on app_path('Http/Resources')
    'resource_laravel' => env('API_RESOURCES_LARAVEL', ''),

    // Collection - Bases on app_path('Http/Resources')
    'collection_laravel' => env('API_COLLECTIONS_LARAVEL', ''),

    // Controller - Bases on app_path('Http/Controllers')
    'controller_laravel' => env('API_CONTROLLER_LARAVEL', 'Api'),

    // Factories - Bases on base_path('database/factories')
    'factory_laravel' => env('API_FACTORY_LARAVEL', ''),

    // Factories - Bases on app_path('Policies')
    'policy_laravel' => env('API_POLICY_LARAVEL', ''),

    // Path for Schema files
    'schema_store' => env('API_SCHEMA_STORE', app_path('Schemas')),

    // Path for Stubs files
    'stubs_store' => env('API_STUBS', base_path('resources/api-stubs')),

    // Test - Bases on base_path('tests')
    'tests_laravel' => env('API_TESTS', ''),

    // Swagger version nummer
    'swagger_version' => env('API_SWAGGER_VERSION', 'compact'),

    // Swagger documentation title
    'swagger_name' => env('API_SWAGGER_TITLE', env('APP_NAME')),

    // Swagger documentation description
    'swagger_description' => env('API_SWAGGER_DESCRIPTION', ''),

    // Swagger documentation contact email
    'swagger_contact' => env('API_SWAGGER_CONTACT', 'office@AMBERSIVE.com'),

    // Swagger documentation Licence Name
    'swagger_licence' => env('API_SWAGGER_LICENCENAME', 'AMBERSIVE GmbH - Standard Software Licence Agreement'),

    // Swagger documentation Licence URL
    'swagger_licence_url' => env('API_SWAGGER_LICENCEURL', 'https://AMBERSIVE.com/licences/standard'),

    // Custom Seeder Files
    // Pass a valid path with the files, otherwise false. If false the seeds from the package will be used
    'custom_seeder_folder' => env('API_SEEDER_FOLDER', false),

    /*
    |--------------------------------------------------------------------------
    | Helper Settings
    |--------------------------------------------------------------------------
    |
    */

    'cache_active' => env('API_CACHE_ACTIVE', false),

    /*
    |--------------------------------------------------------------------------
    | USERS Settings
    |--------------------------------------------------------------------------
    |
    */

    'login_attempts' => env('API_LOGIN_ATTEMPTS_MAX', 3),
    'login_locked'   => env('API_LOGIN_LOCKED_WAIT', 5),

    'allow_register' => env('API_REGISTER_ALLOW', true),

    'automatic_active'              => env('API_ACTIVATION_AUTO', false), // User will be set active on registration,
    'activation_redirect_success'   => env('API_ACTIVATION_REDIRECT_SUCCESS', ''),
    'activation_redirect_failure'   => env('API_ACTIVATION_REDIRECT_FAILURE', ''),

    /*
    |--------------------------------------------------------------------------
    | MODEL Settings
    |--------------------------------------------------------------------------
    |
    */

    'models' => [
        'user_model'       => AMBERSIVE\Api\Models\User::class,
        'role_model'       => AMBERSIVE\Api\Models\Role::class,
        'permission_model' => AMBERSIVE\Api\Models\Permission::class
    ],

    /*
    |--------------------------------------------------------------------------
    | PASSWORD Settings
    |--------------------------------------------------------------------------
    |
    */

    'password' =>  [
        'minlength' => 8,
        'reset_expires_minues' => 1440
    ],

];