<?php

namespace AMBERSIVE\Api\Tests\Unit\Classes;

use \AMBERSIVE\Api\Tests\TestPackageCase;

use AMBERSIVE\Api\Classes\SchemaRelation; 

class SchemaRelationTest extends \AMBERSIVE\Api\Tests\TestPackageCase
{
    
    /**
     * Test if a new Schema will not return a valid class
     */
    public function testIfSchemaRelationIsNotEmpty():void {

        $field = new SchemaRelation();
        $this->assertNotNull($field);

    }

    /**
     * Test if the toArray() function will return an array
     */
    public function testIfToArrayMethodReturnsAnArray():void {

        $field = new SchemaRelation();
        $field = $field->toArray();

        $this->assertTrue(is_array($field)); 

    }

    /**
     * Test if the schema relation contains an attribute "name"
     */
    public function testIfSchemaRelationContainsTypeAttribute():void {

        $field = new SchemaRelation(['name' => 'test']);
        $field = $field->toArray();

        $this->assertTrue(isset($field['name']));
        $this->assertTrue($field['name'] === 'test');

    }

}
