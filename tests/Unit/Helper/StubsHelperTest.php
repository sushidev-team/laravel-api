<?php

namespace AMBERSIVE\Tests\Unit\Helper;

use \AMBERSIVE\Tests\TestPackageCase;

use AMBERSIVE\Api\Helper\StubsHelper; 

class StubsHelperTest extends \AMBERSIVE\Tests\TestPackageCase
{
   
    /**
     * Test if a existing stub file can be loaded
     */
    public function testIfLoadStubWillReturnAStubFile():void {

        $content = StubsHelper::load('Model');
        $this->assertNotNull($content);

    }

    /**
     * Test if a invalid stub file (not exsiting) will return a null as
     * value.
     */
    public function testIfAnNotExistingStubFileWillReturnNullAsValue():void {
        $content = StubsHelper::load('XXX');
        $this->assertNull($content);
    }


    public function testIfPlaceholderReplacementIsSuccessful():void {
        
        $content = StubsHelper::replacePlaceholders('Model', [
            'namespace' => 'test'
        ]);

        $this->assertNotNull($content);

        $this->assertFalse(strpos($content, 'namespace {{namespace}}'));
        $this->assertNotFalse(strpos($content, 'namespace test'));

    }

}
