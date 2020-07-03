<?php

namespace AMBERSIVE\Api\Tests\Unit\Helper;

use \AMBERSIVE\Api\Tests\TestPackageCase;

use AMBERSIVE\Api\Helper\LanguageHelper; 

class LanguageHelperTest extends \AMBERSIVE\Api\Tests\TestPackageCase
{

    /**
     * Test if there is a list of possible languages returned
     */
    public function testIfLanguageHelperListReturnsValues():void {

        $languages = LanguageHelper::list();
        $this->assertFalse(empty($languages));

    }

}
