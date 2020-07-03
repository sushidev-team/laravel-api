<?php

namespace AMBERSIVE\Api\Tests\Unit\Classes;

use \AMBERSIVE\Api\Tests\TestPackageCase;

use AMBERSIVE\Api\Classes\SchemaPermission; 

class SchemaPermissionTest extends \AMBERSIVE\Api\Tests\TestPackageCase
{
    
    /**
     * Test if a new Schema will not return a valid class
     */
    public function testIfSchemaPermissionIsNotEmpty():void {

        $field = new SchemaPermission();
        $this->assertNotNull($field);

    }

    /**
     * Test if the toArray() function will return an array
     */
    public function testIfToArrayMethodReturnsAnArray():void {

        $field = new SchemaPermission();
        $field = $field->toArray();

        $this->assertTrue(is_array($field)); 

    }

    /**
     * Test if the schema permission contains an attribute "name" && "description"
     */
    public function testIfSchemaPermissionContainsTypeAttribute():void {

        $field = new SchemaPermission(['name' => 'test', 'description' => 'asdf']);
        $field = $field->toArray();

        $this->assertTrue(isset($field['name']));
        $this->assertTrue($field['name'] === 'test');

        $this->assertTrue(isset($field['description']));
        $this->assertTrue($field['description'] === 'asdf');

    }

}
