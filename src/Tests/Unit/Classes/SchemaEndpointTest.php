<?php

namespace AMBERSIVE\Api\Tests\Unit\Classes;

use \AMBERSIVE\Api\Tests\TestPackageCase;

use AMBERSIVE\Api\Classes\SchemaEndpoint; 

class SchemaEndpointTest extends \AMBERSIVE\Api\Tests\TestPackageCase
{
    
    /**
     * Test if a new Schema will not return a valid class
     */
    public function testIfSchemaEndpointIsNotEmpty():void {

        $field = new SchemaEndpoint();
        $this->assertNotNull($field);

    }

    /**
     * Test if the toArray() function will return an array
     */
    public function testIfToArrayMethodReturnsAnArray():void {

        $field = new SchemaEndpoint();
        $field = $field->toArray();

        $this->assertTrue(is_array($field)); 

    }

    /**
     * Test if the schema endpoint contains an attribute "model"
     */
    public function testIfSchemaEndpointContainsTypeAttribute():void {

        $field = new SchemaEndpoint([
            'model'     => 'App\\Models\\Users\\User',
            'resource'  => 'TEST'
        ]);
        
        $field = $field->toArray();

        $this->assertTrue(isset($field['model']));
        $this->assertTrue($field['model'] === 'App\\Models\\Users\\User');
        $this->assertTrue($field['resource'] === 'TEST');

    }

}
