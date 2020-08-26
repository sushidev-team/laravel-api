<?php

namespace AMBERSIVE\Tests\Unit\Helper;

use \AMBERSIVE\Tests\TestPackageCase;

use AMBERSIVE\Api\Helper\SchemaHelper; 

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use File;
use Str;
use Yaml;

class SchemaHelperTest extends \AMBERSIVE\Tests\TestPackageCase
{

   public $testMigrationTable = "tests";
   
   protected function setUp(): void
   {
        parent::setUp();

        /**
         * Run test migrations
         */
        Schema::dropIfExists($this->testMigrationTable);
      
        Schema::create($this->testMigrationTable, function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')
               ->references('id')
               ->on('users')
               ->onDelete('cascade');
        });

        /**
         * Make sure that there is no schema file at all in the environment
         */
        $path = SchemaHelper::path();

        if ($path !== null) {
            File::deleteDirectory($path);
            $this->artisan('api:prepare');
        }

   }

   /**
    * Test if the path function return a path and not null
    */
   public function testIfThePathFunctionReturnAPath():void {

         $path   = SchemaHelper::path();
         $pathTmp = base_path('tmp');

         $this->assertNotNull($path);
         $this->assertEquals($path, $pathTmp);

   }

   /**
    * Test if the method results in an exception if a wrong parameter is provided
    */
   public function testIfCreateSchemaThrowExceptionCauseFirstParamIsInvalid():void {

        $this->expectException("Error");
        $success = SchemaHelper::createSchema([]);

   }

   /**
    * Test if the createSchema will throw an exception if the Content is null
    */
   public function testIfCreateSchemaWillThrowExceptionIfContentIsNull():void {
      $this->expectException("Error");
      $path = SchemaHelper::createSchema("Test", null);
   }

   /**
    * Test if the createSchema will throw an exception if the Content is an empty array
    */
   public function testIfCreateSchemaWillThrowExceptionIfContentIsEmptyArray():void {
      $this->expectException("Error");
      $path = SchemaHelper::createSchema("Test", [], true);
   }

   /**
    * Test if the create schema static method return a path
    * if all params are set.
    */
   public function testIfCreateSchemaWillCreateASchema():void {

      $path = SchemaHelper::createSchema("Test", ["Test" => true], true);
      $this->assertNotNull($path);

   }

   /**
    * This test should test if schema file can be deleted
    */
   public function testIfDeleteSchemaWillDeleteASchema():void {
         $path = SchemaHelper::createSchema("Test", ["Test" => true], true);
         $result = SchemaHelper::deleteSchema("Test");
         $this->assertTrue($result);
   }

   /**
    * This test should test if deleting a schema file which is not there fail.
    */
   public function testIfDeleteSchemaWillReturnFalseIfThereIsNoSchemaFile():void {
      
      // Do a creation and delete process before testing
      SchemaHelper::createSchema("Test", ["Test" => true], true);
      SchemaHelper::deleteSchema("Test");

      // Should return false
      $result = SchemaHelper::deleteSchema("Test");
      $this->assertFalse($result);
   }

   /**
    * This test will test if the readSchema static function will return
    * an array.
    */
   public function testIfReadSchemaWillReturnAnArray(): void {

      SchemaHelper::createSchema("Test", ["Test" => true], true);

      $result = SchemaHelper::readSchema("Test");
      $this->assertNotNull($result);
      $this->assertTrue(is_array($result));
      $this->assertFalse(empty($result));

   }

   /**
    * This test will test if the readSchema static function will return a
    * "null" as return value if the schema cannot be found.
    */
   public function testIfReadSchemaWillReturnNullIfSchemaNotExists(): void {

      $result = SchemaHelper::readSchema("XXX");
      $this->assertNull($result);

   }

   /**
    * This test will check if the readSchema static fucntion accepts a custom
    * path and is capable to read from the given path.
    */
   public function testIfRestSchemaAcceptCustomPath():void {

      $result = SchemaHelper::readSchema("test", __DIR__.'/../../Testfiles');
      $this->assertNotNull($result);

   }

   /**
    * This test will test if the exists static function will return false
    * if the Schema does not exist.
    */
   public function testIfExistsWillReturnFalse():void {

      $result = SchemaHelper::exists("XXX");
      $this->assertFalse($result);

   }

   /**
    * This test will check if the "exists" static function will return true 
    * if the Schema exists.
    */
   public function testIfExistsWillReturnTrueIfSchemaExists():void {

      SchemaHelper::createSchema("Test", ["Test" => true], true);
      $result = SchemaHelper::exists("Test");
      $this->assertTrue($result);

   }

   /**
    * This test will check if the defaultSchema static function will not return
    * a null - cause it should return an default schema
    */
   public function testIfDefaultSchemaWillNotReturnNull():void {

      $result = SchemaHelper::defaultSchema();
      $this->assertNotNull($result);

   }

   /**
    * This test will check if the extractFieldFromDatabase static function
    * will extract basic information from the database
    */
   public function testIfExtractColumnsWillReturnColumns():void {

      $result = SchemaHelper::extractFieldFromDatabase("users");
      $this->assertNotNull($result);
      $this->assertTrue(is_array($result));
      $this->assertFalse(empty($result));

   }

   /**
    * This test will check if the read input of a simple file will result in the same
    * yaml output
    */
   public function testIfReadAndCreateWillEndInSameResultSimple():void {

         $result1 = SchemaHelper::readSchema("test", __DIR__.'/../../Testfiles');

         SchemaHelper::createSchema("test", $result1, true);

         $result2 = SchemaHelper::readSchema("test");

         $this->assertTrue(json_encode($result1) == json_encode($result2));

   }

   /**
    * This test will check if the yaml output will be excapt the same
    */
   public function testIfReadCreateWillEndInSameResultForDefaultSchema():void {

        $result1 = SchemaHelper::defaultSchema();

        SchemaHelper::createSchema("test", $result1, true);

        $result2 = SchemaHelper::readSchema("test");

        $this->assertTrue(json_encode($result1) == json_encode($result2));

   }

   /**
    * This test should test if the static function extractRelations
    * can extract the relations
    */
   public function testIfExtractRelationsWillReturnRelations(): void {

       $relations = SchemaHelper::extractRelations('users');
       
       $this->assertNotNull($relations);
       $this->assertTrue(is_array($relations));

   }

   /**
    * This test should check if the resolution of relations works
    */
   public function testIfExtractRelationsWillReturnRelationsBetweentTwoTables(): void {

      $result1 = SchemaHelper::defaultSchema();

      SchemaHelper::createSchema("users", $result1, true);

      $users = SchemaHelper::readSchema("users");

      $this->assertNotNull($users);

      $relations = SchemaHelper::extractRelations('tests');

      $this->assertFalse(empty($relations));

   }

   /**
    * Test if the IsLocked static method will return a boolena value
    */
   public function testIfIsLockedWillReturnABooleanValue(): void {

      $schema = SchemaHelper::defaultSchema();

      SchemaHelper::createSchema("test", $schema, true);

      $locked = SchemaHelper::isLocked("test");

      $this->assertIsBool($locked);
      $this->assertFalse($locked);

   }

   /**
    * Test if the IsLocked static function will return true
    * if the schema file has the locked value: true
    */
   public function testIfIsLockedReturnTrueIfSchemaFileIsLocked(): void {

      $schema = SchemaHelper::defaultSchema();
      $schema['locked'] = true;

      SchemaHelper::createSchema("test", $schema, true);

      $locked = SchemaHelper::isLocked("test");

      $this->assertIsBool($locked);
      $this->assertTrue($locked);

   }

   /**
    * Tests if extracting a specific attribute from schema
    * file will return a specific value
    */
   public function testIfGetValueWillReturnAValue(): void {

      $schema = SchemaHelper::defaultSchema();
      SchemaHelper::createSchema("test", $schema, true);

      $value = SchemaHelper::getValue('test', 'locked');

      $this->assertNotNull($value);

   }

   /***
    * Test if getValue will return null if the searched attribute
    * is not present in the schema file.
    */
   public function testIfGetValueWillReturnNullIfAttributeDoesNotExists(): void {

      $schema = SchemaHelper::defaultSchema();
      SchemaHelper::createSchema("test", $schema, true);

      $value = SchemaHelper::getValue('test', 'XXXX');

      $this->assertNull($value);

   }

   /**
    * Test if listSchemas will return a list of schema files
    */
   public function testIfListSchemasWillReturnAListOfSchemaFiles(): void {

      // Prepare
      $schema = SchemaHelper::defaultSchema();
      SchemaHelper::createSchema("test", $schema, true);

      $list = SchemaHelper::listSchemas();

      $this->assertTrue(is_array($list));

   }

   /**
    * Test if the Create Model Method will create a model file
    */
   public function testIfCreateModelCreateAModelFile(): void {

      $schema = SchemaHelper::readSchema("test", __DIR__.'/../../Testfiles');
      SchemaHelper::createSchema("test", $schema, true);

      $success = SchemaHelper::createModel('test');
      $this->assertTrue($success);

   }

   /**
    * Test if the createModel static function will not create model file
    */
    public function testIfCreateModelWillNotCreateAModelFileIfSchemaFileDoesNotExists(): void {

      $result = SchemaHelper::createModel('XXX');
      $this->assertFalse($result);

   }

   /**
    * Test if the createModelDocumentation will create a valid documentation
    */
   public function testIfCreateModelDocumentationReturnsADocumentation():void {

      $schema = SchemaHelper::readSchema("test", __DIR__.'/../../Testfiles');

      $result = SchemaHelper::createModelDocumentation($schema);

      $this->assertNotNull($result);
      $this->assertTrue(Str::startsWith($result, "#region [Documentation]: TestModel\n/**"));
      $this->assertNotFalse(strpos( $result, '@OA\Schema(schema="TestModel", required={}, title="Model: Test"'));

   }

   /**
    * Test if exractNamespace will return the correct Namespace for a model
    */
   public function testIfExtractNamespaceWillReturnACorrectNamespace(): void {
         // Prepare 
         $schema = SchemaHelper::readSchema("test", __DIR__.'/../../Testfiles');
         $model  = $schema['model'];

         // Actual test
         $namespace = SchemaHelper::extractNamespace($model);
         $this->assertNotNull($namespace);
         $this->assertNotEquals($namespace, $model);
         $this->assertEquals($namespace, "App\Models\Test");
   }

   /**
    * Test if the extractNamespace function can handle empty requests
    */
   public function testIfNullValueWillBeHandledInExtractNameSpace():void {
      $namespace = SchemaHelper::extractNamespace(null);
      $this->assertNotNull($namespace);
      $this->assertEquals($namespace, "");

      $namespace = SchemaHelper::extractNamespace("");
      $this->assertNotNull($namespace);
      $this->assertEquals($namespace, "");
   }

   /**
    * Test if the ExtractPathForFile can create a correct path
    */
   public function testIfExtractPathForFileWillReturnAValidPath(): void {
         
      // Prepare 
      $schema = SchemaHelper::readSchema("test", __DIR__.'/../../Testfiles');
      $model  = $schema['model'];
      
      // Check the result
      $result = SchemaHelper::extractPathForFile($model);
      $this->assertNotNull($result);
      $this->assertNotEquals($result,"");
      $this->assertFalse(strpos($result,"\\"));
      $this->assertEquals($result, "Test/Test");

      $result = SchemaHelper::extractPathForFile($model, "XXX");
      $this->assertNotNull($result);
      $this->assertNotEquals($result,"");
      $this->assertFalse(strpos($result,"\\"));
      $this->assertEquals($result, app_path("XXX/Test/Test"));
      $this->assertTrue(Str::startsWith($result, app_path('XXX')));
   }

   /**
    * Test if the extractPathForFile can handle null as parameter
    */
   public function testIfExtractPathForFileCanHandleNullAsParameter():void {

      $result = SchemaHelper::extractPathForFile(null);
      $this->assertNotNull($result);
      $this->assertEquals($result, "");

      $result = SchemaHelper::extractPathForFile("");
      $this->assertNotNull($result);
      $this->assertEquals($result, "");

   }

   /**
    * This test will check if the extractFolderForFile will return
    * a valid path the folder
    */
   public function testIfExtractFolderForFileWillReturnFolderPath(): void {
      // Prepare 
      $schema = SchemaHelper::readSchema("test", __DIR__.'/../../Testfiles');
      $model  = $schema['model'];
      
      // Check the result
      $result = SchemaHelper::extractFolderForFile($model);
      $this->assertNotNull($result);
      $this->assertNotEquals($result,"");
      $this->assertFalse(strpos($result,"\\"));
      $this->assertEquals($result, "Test");

      // Check the result with prefix
      $result = SchemaHelper::extractFolderForFile($model, 'XXX');
      $this->assertNotNull($result);
      $this->assertNotEquals($result,"");
      $this->assertFalse(strpos($result,"\\"));
      $this->assertEquals($result, app_path('XXX/Test'));
   }

   /**
    * Test if the classname of a namespace can be extracted
    */
   public function testIfExtractClassNameWillReturnAStringWithoutTheNamespace(): void {
      
      // Prepare 
      $schema = SchemaHelper::readSchema("test", __DIR__.'/../../Testfiles');
      $model  = $schema['model'];
      
      // Check the result
      $result = SchemaHelper::extractClassName($model);
      $this->assertEquals($result, "Test");

   }

   /**
    * Test if a Custom area can be extracted
    */
   public function testIfExtractCustomAreaWillScrapeTheContentFromFile(): void {
      
      // Check the result

      if (File::exists(app_path('Models/Test/Test.php')) == true) {
         File::delete(app_path('Models/Test/Test.php'));
      }

      $schema = SchemaHelper::readSchema("test", __DIR__.'/../../Testfiles');
      SchemaHelper::createSchema("test", $schema, true);
 
      $success = SchemaHelper::createModel('test');
      $this->assertTrue($success);

      $model   = $schema['model'];
      $path    = SchemaHelper::extractPathForFile($model, config('ambersive-api.model_laravel'), 'php');
      $content = SchemaHelper::extractCustomArea($path,'METHODS');

      $this->assertNotNull($content);

      $content = str_replace("\n", "", $content);

      $this->assertEquals($content, "");

   }

   /**
    * Check if the extractCustomArea can handle the useage of a invalid area.
    */
   public function testIfExtractCustomAreaWillReturnEmptyIfAreaDoesNotExists(): void {
      // Check the result
      $schema = SchemaHelper::readSchema("test", __DIR__.'/../../Testfiles');
      SchemaHelper::createSchema("test", $schema, true);
 
      $success = SchemaHelper::createModel('test');
      $this->assertTrue($success);

      $model   = $schema['model'];
      $path    = SchemaHelper::extractPathForFile($model, config('ambersive-api.model_laravel'), 'php');
      $content = SchemaHelper::extractCustomArea($path,'XXX');

      $this->assertNotNull($content);
      $this->assertEquals($content, "");
   }

   /**
    * Test if a controller file will be created 
    */
   public function testIfCreateBaseControllerWillCreateAUpdatedControllerFile(): void {
      
       SchemaHelper::createBaseController();

       $file = File::get(app_path('Http/Controllers/Controller.php'));

       $this->assertNotNull($file);
       $this->assertNotFalse(strpos($file, "* @OA\Info("));
       $this->assertFalse(strpos($file, "{{version}}"));
       $this->assertFalse(strpos($file, "{{contact}}"));
       $this->assertFalse(strpos($file, "{{licence}}"));
       $this->assertFalse(strpos($file, "{{licenceUrl}}"));
       $this->assertFalse(strpos($file, "{{title}}"));
       $this->assertFalse(strpos($file, "{{description}}"));

   }

   /**
    * Test if a resource will be created
    */
   public function testIfCreateResourceWillCreateAResourceFile(): void {

      $success = SchemaHelper::createResource("test_underlined", __DIR__.'/../../Testfiles');    
      $this->assertTrue($success);
      $this->assertTrue(File::exists(app_path("Http/Resources/TestResource.php")));

   }

   /**
    * Test if the collection will be created
    */
   public function testIfCreateCollectionWillCreateACollectionFile(): void {
      
      $success = SchemaHelper::createCollection("test_underlined", __DIR__.'/../../Testfiles');    
      $this->assertTrue($success);
      $this->assertTrue(File::exists(app_path("Http/Resources/TestCollection.php")));

   }

   /**
    * Test if the controller will be created
    */
   public function testIfCreateControllerWillCreateAControllerFile(): void {
      
      SchemaHelper::createController("test_underlined", __DIR__.'/../../Testfiles');      
      $this->assertTrue(File::exists(app_path("Http/Controllers/Test/TestController.php")));

   }

   /**
    * Test if the controller documentation can handle the underlines and will transform it to camel case
    *
    * @return void
    */
   public function testIfCreateControllerDocumentationCanHandleTablesWithUnderline():void {

      $schema = SchemaHelper::readSchema("test_underlined", __DIR__.'/../../Testfiles');
      $documentation = SchemaHelper::createControllerDocumentation($schema,'update');

     $this->assertNotFalse(strpos($documentation, "ref=\"#/components/schemas/UserMoreRequestBodyUpdate\""));

   }

   /**
    * Test if the create controller will create an correct method
    */
   public function testIfCreateControllerMethodWillReturnValidControllerFunction(): void {
      
      $schema = SchemaHelper::readSchema("test_underlined", __DIR__.'/../../Testfiles');
      $path   = app_path(SchemaHelper::extractPathForFile($schema['model'].'Controller', 'Http/Controllers/'.config('ambersive-api.controller_laravel'), 'php'));
      $method = SchemaHelper::createControllerMethod($schema, $path, 'all');

      $this->assertNotFalse(strpos($method, 'public function all(Request $request){'));
      $this->assertNotFalse(strpos($method, 'return $api->respond(\'collection\');'));

   }

   /**
    * Test if the test files will be created
    */
   public function testIfCreateTestWillCreateATestFile(): void {
      
      $tests = SchemaHelper::createTests("test_underlined", __DIR__.'/../../Testfiles');

      $this->assertTrue($tests);
      $this->assertTrue(File::exists(base_path("tests/Unit/Models/Test/TestModelTest.php")));
      $this->assertTrue(File::exists(base_path("tests/Feature/Controllers/Test/TestControllerTest.php")));


   }

   /**
    * Test if policy files will be created
    */
   public function testIfCreatePolicyWillCreateAPolicyFile(): void {
      
      $polices = SchemaHelper::createPolicy("test_underlined", __DIR__.'/../../Testfiles');

      $this->assertTrue($polices);
      $this->assertTrue(File::exists(app_path("Policies/Test/TestPolicy.php")));

   }

   public function testIfUpdateAuthServiceProviderWillUpdateTheFile():void {
      
      $schema = SchemaHelper::readSchema("test_underlined", __DIR__.'/../../Testfiles');
      SchemaHelper::updateAuthServiceProvider([]);
      

      $polices = SchemaHelper::createPolicy("test_underlined", __DIR__.'/../../Testfiles');

      $modelPolicyMapping = [];
      $modelPolicyMapping[data_get($schema, 'model')] = data_get($schema, 'policy');

      $fingerprint = hash_file('md5',app_path("Providers/AuthServiceProvider.php"));

      SchemaHelper::updateAuthServiceProvider($modelPolicyMapping);

      $fingerprintCompare = hash_file('md5',app_path("Providers/AuthServiceProvider.php"));

      SchemaHelper::updateAuthServiceProvider($modelPolicyMapping);

      $fingerprintCompare2 = hash_file('md5',app_path("Providers/AuthServiceProvider.php"));
      
      // Do the assertions
      $this->assertTrue($polices);
      $this->assertNotEquals($fingerprintCompare, $fingerprint);
      $this->assertEquals($fingerprintCompare, $fingerprintCompare2);

   }

   public function testIfTransformDeepAssocToStringArrayReturnAValidResult(): void {

      $test = [];

      $result = SchemaHelper::transformDeepAssocToStringArray($test);

      $this->assertTrue(is_string($result));
      $this->assertNotNull($result);
      $this->assertEquals($result, "[]");

   }

   public function testIfTransformDeepAssocToStringArrayReturnAValidResultWithValidInformation(): void {

      $test = ["fields" => ['*']];

      $result = SchemaHelper::transformDeepAssocToStringArray($test);

      $this->assertTrue(is_string($result));
      $this->assertNotNull($result);
      $this->assertNotEquals($result, "[]");
      $this->assertEquals($result, '["fields" => ["*"],]');

   }

}
