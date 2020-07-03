<?php

namespace AMBERSIVE\Api\Tests\Unit\Classes;

use \AMBERSIVE\Api\Tests\TestPackageCase;

use AMBERSIVE\Api\Classes\SchemaRole; 

class SchemaRoleTest extends \AMBERSIVE\Api\Tests\TestPackageCase
{
    
    /**
     * Test if a new Schema will not return a valid class
     */
    public function testIfSchemaRoleIsNotEmpty():void {

        $field = new SchemaRole();
        $this->assertNotNull($field);

    }

    /**
     * Test if the toArray() function will return an array
     */
    public function testIfToArrayMethodReturnsAnArray():void {

        $field = new SchemaRole();
        $field = $field->toArray();

        $this->assertTrue(is_array($field)); 

    }

    /**
     * Test if the schema role contains an attribute "name" && "description"
     */
    public function testIfSchemaRoleContainsTypeAttribute():void {

        $field = new SchemaRole(['name' => 'test', 'description' => 'asdf']);
        $field = $field->toArray();

        $this->assertTrue(isset($field['name']));
        $this->assertTrue($field['name'] === 'test');

        $this->assertTrue(isset($field['description']));
        $this->assertTrue($field['description'] === 'asdf');

    }

}
