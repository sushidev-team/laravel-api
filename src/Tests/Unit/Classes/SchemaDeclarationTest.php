<?php

namespace AMBERSIVE\Api\Tests\Unit\Classes;

use Config;

use \AMBERSIVE\Api\Tests\TestPackageCase;

use AMBERSIVE\Api\Classes\SchemaDeclaration;
use AMBERSIVE\Api\Classes\SchemaEndpoint; 

class SchemaDeclarationTest extends \AMBERSIVE\Api\Tests\TestPackageCase
{
    public function setUp(): void
    {
        parent::setUp();

        Config::set('ambersive-api.collection_laravel', '');
        Config::set('ambersive-api.resource_laravel', '');
        Config::set('ambersive-api.model_laravel', '');

    }
    
    /**
     * Test if a new Schema will not return a valid class
     */
    public function testIfSchemaDeclarationIsNotEmpty():void {

        $schema = new SchemaDeclaration();
        $this->assertNotNull($schema);

    }

    /**
     * Test if the udpate method from the base class works
     */
    public function testIfSchemaDeclarationUpdateWillUpdateParams():void {

        $schema = new SchemaDeclaration();
        $schema = $schema->update(['table' => 'test']); 
        $this->assertNotNull($schema);
        $this->assertTrue($schema->table == 'test');

    }

    /**
     * Test if the toArray() function will return an array
     */
    public function testIfToArrayMethodReturnsAnArray():void {

        $schema = new SchemaDeclaration();
        $schema = $schema->toArray();

        $this->assertTrue(is_array($schema)); 

    }

    /**
     * Test if the schema declaration contains an attribute "model"
     */
    public function testIfSchemaDeclarationContainsTypeAttribute():void {

        $schema = new SchemaDeclaration(['model' => 'test']);
        $schema = $schema->toArray();

        $this->assertTrue(isset($schema['model']));
        $this->assertTrue($schema['model'] === 'test');

    }

    /**
     * Test if the SchemaDeclaration has a setDefaultEndpoints and this method
     * is chainable
     */
    public function testIfSchemaDeclarationContainsASetDefaultEndpointsMethod():void {

        $schema = new SchemaDeclaration(['table' => 'tests', 'model' => 'test']);

        $this->assertTrue(method_exists($schema, 'setDefaultEndpoints'));

        $result = $schema->setDefaultEndpoints()->toArray();
        
        $this->assertIsArray($result);

    }

    /**
     * Test if the setDefaultEndpoints will add the default endpoints to the class
     */
    public function testIfSchemaDeclarationSetDefaultEndpointsWillAddTheDefaultEndpoints():void {

        $schema = new SchemaDeclaration(['table' => 'tests', 'model' => 'test']);
        $result = $schema->setDefaultEndpoints()->toArray();
        
        $this->assertNotNull($result['endpoints']);
        $this->assertTrue(sizeOf($result['endpoints']) > 0);
        $this->assertEquals(sizeOf($result['endpoints']), 6);

    }

    /**
     * This should return seven endpoints if a new endpoint is added
     * on the fly
     */
    public function testIfSchemaDeclartionsCanAddAEndpoint(): void {

        $schema = new SchemaDeclaration(['table' => 'tests', 'model' => 'test']);
        $result = $schema->setDefaultEndpoints()->toArray();
        $this->assertEquals(sizeOf($result['endpoints']), 6);

        $schema->addEndpoint(['include' => true, 'name' => 'test']);

        $result = $schema->toArray();
        $this->assertEquals(sizeOf($result['endpoints']), 7);

    }

    /**
     * This method should check if the endpoints for a declaration can be reseted
     */
    public function testIfSchemaDeclarationEndpointsCanBeReseted(): void {

        $schema = new SchemaDeclaration(['table' => 'tests']);
        $result = $schema->setDefaultEndpoints()->toArray();
        $this->assertEquals(sizeOf($result['endpoints']), 6);

        $schema->resetEndpoints();

        $result = $schema->toArray();
        $this->assertNull($result['endpoints']);

    }

    /**
     * This method should check if the model be set automatically
     */
    public function testIfSchemaDeclarationSetModelWillSetTheModelEntry(): void {

        $schema = new SchemaDeclaration(['table' => 'tests']);

        $schema->setModel('test');
        $result = $schema->toArray();

        $this->assertNotNull($result['model']);
        $this->assertFalse($result['model'] === '');

    }

    /**
     * This test will check if the prefix helper method will always return a string
     */
    public function testIfSchemaDeclarationCreateNamespacePrefixForModelWillReturnAString():void {

        $schema = new SchemaDeclaration(['table' => 'tests']);
        $prefix = $schema->createNamespacePrefixForModel();

        $this->assertNotNull($prefix);
        $this->assertIsString($prefix);

    }

    /**
     * This method should check if the resource be set automatically even no if no
     * model is provided
     */
    public function testIfSchemaDeclarationSetResourceWillSetTheResourceEntry(): void {

        $schema = new SchemaDeclaration(['table' => 'tests']);

        $schema->setResource('test');
        $result = $schema->toArray();

        $this->assertNotNull($result['resource']);



        $this->assertNotNull($result['model']);

        $this->assertFalse($result['resource'] === '');
        $this->assertFalse($result['model'] === '');

        $this->assertEquals($result['resource'], 'App\\Http\\Resources\TestResource');

    }

    public function testIfSchemaDeclarationSetResourceEvenIfNoModelIsProvided(): void {

        $schema = new SchemaDeclaration(['table' => 'tests']);

        $schema->setResource();
        $result = $schema->toArray();

        $this->assertNotNull($result['resource']);
        $this->assertNotNull($result['model']);

        $this->assertFalse($result['resource'] === '');
        $this->assertFalse($result['model'] === '');

        $this->assertEquals($result['resource'], 'App\\Http\\Resources\TestResource');

    }

    /**
     * This method should check if the collection be set automatically 
     */
    public function testIfSchemaDeclarationSetCollectionWillSetTheCollectionEntry(): void {

        $schema = new SchemaDeclaration(['table' => 'tests']);

        $schema->setCollection('test');
        $result = $schema->toArray();

        $this->assertNotNull($result['collection']);
        $this->assertNotNull($result['model']);

        $this->assertFalse($result['collection'] === '');
        $this->assertFalse($result['model'] === '');

        $this->assertEquals($result['collection'], 'App\\Http\\Resources\TestCollection');

    }

    /**
     * This method should check if the collection be set automatically even if no model is
     * provided
     */
    public function testIfSchemaDeclarationSetCollectionEvenIfNoModelIsProvided(): void {

        $schema = new SchemaDeclaration(['table' => 'tests']);

        $schema->setCollection();
        $result = $schema->toArray();

        $this->assertNotNull($result['collection']);
        $this->assertNotNull($result['model']);

        $this->assertFalse($result['collection'] === '');
        $this->assertFalse($result['model'] === '');

        $this->assertEquals($result['collection'], 'App\\Http\\Resources\TestCollection');

    }

}
