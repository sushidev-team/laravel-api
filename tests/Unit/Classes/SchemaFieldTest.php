<?php

namespace AMBERSIVE\Tests\Unit\Classes;

use \AMBERSIVE\Tests\TestPackageCase;

use AMBERSIVE\Api\Classes\SchemaField; 

class SchemaFieldTest extends \AMBERSIVE\Tests\TestPackageCase
{
    
    /**
     * Test if a new Schema will not return a valid class
     */
    public function testIfSchemaFieldIsNotEmpty():void {

        $field = new SchemaField();
        $this->assertNotNull($field);

    }

    /**
     * Test if the toArray() function will return an array
     */
    public function testIfToArrayMethodReturnsAnArray():void {

        $field = new SchemaField();
        $field = $field->toArray();

        $this->assertTrue(is_array($field)); 

    }

    /**
     * Test if the schema field contains an attribute "type"
     */
    public function testIfSchemaFieldContainsTypeAttribute():void {

        $field = new SchemaField(['type' => 'uuid']);
        $field = $field->toArray();

        $this->assertTrue(isset($field['type']));
        $this->assertEquals($field['type'], 'uuid');

    }

}
