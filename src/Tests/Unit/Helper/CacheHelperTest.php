<?php

namespace AMBERSIVE\Api\Tests\Unit\Helper;

use \AMBERSIVE\Api\Tests\TestPackageCase;

use AMBERSIVE\Api\Helper\CacheHelper; 

class CacheHelperTest extends \AMBERSIVE\Api\Tests\TestPackageCase
{
    
    /**
     * Tests if the cache status that will be returned is null 
     * if not set
     */
    public function testIfIsActiveReturnsNullIfNotSet():void {
        $active = CacheHelper::isActive();
        $this->assertNotNull($active);
    }

    /**
     * Test if setting the active status of caching is possible (with value false)
     * and returns the correct value.
     */
    public function testIfIsActiveReturnsFalseIfSetFalse():void {
        
        CacheHelper::setActive(false);
        $active = CacheHelper::isActive();
        $this->assertNotNull($active);
        $this->assertFalse($active);

    }

    /**
     * Tests if setting the active status of caching is possible (with value true)
     * and will be returned correctly.
     */
    public function testIfIsActiveReturnsTrueIfSetTrue():void {
        
        CacheHelper::setActive(true);
        $active = CacheHelper::isActive();
        $this->assertNotNull($active);
        $this->assertTrue($active);

    }

    /**
     * Tests if the active status can be reseted
     */
    public function testIfIsActiveReturnsNullIfReset():void {
        
        // First set the status to active
        CacheHelper::setActive(true);
        $active = CacheHelper::isActive();
        $this->assertNotNull($active);

        // Reset the flag
        CacheHelper::resetActive();
        $active = CacheHelper::isActive();
        $this->assertNotNull($active);

    }

    /**
     * Checks if creating a unique id out of a request is possible
     */
    public function testIfFingerprintReturnsIdIfAddUserConnectionFlagIsFalse():void {

        $id = CacheHelper::id(request());
        $this->assertNotNull($id);
        $this->assertTrue(sizeOf(explode('::', $id)) === 3);

    }

    /**
     * Checks if creating a unique id out of a request id possible, event
     * if add users connetion flag is set to true
     */
    public function testIfFingerprintReturnsIdIfAddUserConnectionFlagIsTrue():void {

        $id = CacheHelper::id(request());
        $this->assertNotNull($id);
        $this->assertTrue(sizeOf(explode('::', $id)) === 3);

    }


}
