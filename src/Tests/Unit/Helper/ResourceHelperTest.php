<?php

namespace AMBERSIVE\Api\Tests\Unit\Helper;

use \AMBERSIVE\Api\Tests\TestPackageCase;
use AMBERSIVE\Api\Helper\ResourceHelper; 

use File;

class ResourceHelperTest extends \AMBERSIVE\Api\Tests\TestPackageCase
{
    
    /**
     * This test will test if the ResourceHelper::getClasses static function returns 
     * false as return value when null is provided as path.
     */
    public function testIfGetClassesReturnsFalseIfPathIsNull():void {
        $classes = ResourceHelper::getClasses(null);
        $this->assertFalse($classes);
    }

    /**
     * This test will test if the ResourceHelper::getClasses static function returns 
     * false if no path is provided.
     */
    public function testIfGetClassesReturnsFalseIfPathIsNotSetAtAll():void {
        $classes = ResourceHelper::getClasses();
        $this->assertFalse($classes);
    }

    /**
     * Tests if all files will ba part of the returned array
     */
    public function testIfGetClassesWillListAllFiles():void {
       // $this->expectException("Error");
        $classes = ResourceHelper::getClasses(__DIR__);
        $files   = File::files(__DIR__);

        $this->assertTrue(sizeOf($files) === sizeOf($classes));
    }

    /**
     * This test will test if no exception will triggered and
     * if a array 
     */
    public function testIfResourceHelperDoesNotThrowAnErrorIfRemoveNameSpaceIsProvided():void {
        $classes = ResourceHelper::getClasses(__DIR__, 'Test', true);
        $this->assertNotNull($classes);
        $this->assertNotFalse($classes);
        $this->assertTrue(is_array($classes));
        $this->assertTrue(sizeOf($classes) > 0);
    }

    /**
     * Test if this methods return null if the passed parameter is a string
     */
    public function testIfGetNameSpaceReturnsNullIfA(): void {

        $namespace = ResourceHelper::getNamespace(__DIR__);
        $this->assertNull($namespace);

    }

    /**
     * Test if getClasses will return an array (not empty) of class names
     */
    public function testIfGetClassesWillReturnAnArrayOfInstances(): void {

        $classes = ResourceHelper::getClasses(__DIR__, 'Test', false);
        $this->assertNotNull($classes);
        $this->assertTrue(is_array($classes));
        $this->assertTrue(sizeOf($classes) > 0);

    }

    /**
     * Test if the getClasses will return an empty array, cause there where no
     * matching files found.
     */
    public function testIfGetClassesWillReturnAnEmptyArrayIfPatternNotMatching():void {
        $classes = ResourceHelper::getClasses(__DIR__, 'XXXX', false);
        $this->assertNotNull($classes);
        $this->assertTrue(is_array($classes));
        $this->assertTrue(sizeOf($classes) === 0);
    }

    /**
     * Test if the getClasses will accept the $listOnly Flag (boolean)
     */
    public function testIfGetClassesAcceptsTheListOnlyFlag():void {
        $classes = ResourceHelper::getClasses(__DIR__, 'Test', true);
        $this->assertNotNull($classes);
        $this->assertTrue(is_array($classes));
        $this->assertTrue(is_string($classes[0]));
    }

    /**
     * This test will check if the getClasses function accepts an
     * array or string as fourth parameter to except entries 
     * from listing
     */
    public function testIfGetClassesAcceptsTheExceptAttribute():void {

        $compare           = ResourceHelper::getClasses(__DIR__, 'Test', true);
        $reducedWithArray  = ResourceHelper::getClasses(__DIR__, 'Test', true, ['ResourceHelperTest']);
        $reducedWithString = ResourceHelper::getClasses(__DIR__, 'Test', true, 'ResourceHelperTest');

        $this->assertTrue(sizeOf($compare) != sizeOf($reducedWithArray));
        $this->assertTrue(sizeOf($compare) != sizeOf($reducedWithString));

    }

    /**
     * This test will check if getClasses function accepts a withoutNameSpace 
     * boolean flag
     */
    public function testIfGetClassesAcceptsTheWithoutNameSpaceFlag():void {

        $compare            = ResourceHelper::getClasses(__DIR__, 'Test', true, null, false);
        $compareNoNamespace = ResourceHelper::getClasses(__DIR__, 'Test', true, null, true);

        $this->assertTrue(sizeOf($compare) == sizeOf($compareNoNamespace));
        $this->assertFalse($compare[0] == $compareNoNamespace[0]);

    }

    /***
     * This tets will check if the getClasses functions will transform the
     * result to a collection
     */
    public function testIfGetClassesAcceptsTheReturnAsCollectionFlag():void {
        $compare             = ResourceHelper::getClasses(__DIR__, 'Test', true, null, false, false);
        $compareAsCollection = ResourceHelper::getClasses(__DIR__, 'Test', true, null, false, true);

        $this->assertTrue(is_array($compare));
        $this->assertFalse(is_array($compareAsCollection));
        $this->assertTrue(method_exists($compareAsCollection,'count'));

    }

    /**
     * Test if getModels function will return a array
     */
    public function testIfGetModelsReturnsAListOfModels():void {
        $models = ResourceHelper::getModels(false, []);
        $this->assertTrue(is_array($models));
    }

    /**
     * Test if getModels function will return a collection
     * if the first parameter is set to true
     */
    public function testIfGetModelsReturnsACollectionOfModels():void {
        $models = ResourceHelper::getModels(true, []);
        $this->assertFalse(is_array($models));
        $this->assertTrue(method_exists($models,'count'));
    }

}
