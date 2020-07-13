<?php

namespace AMBERSIVE\Tests\Unit\Helper;

use \AMBERSIVE\Tests\TestPackageCase;

use AMBERSIVE\Api\Helper\LanguageHelper; 

class LanguageHelperTest extends \AMBERSIVE\Tests\TestPackageCase
{

    /**
     * Test if there is a list of possible languages returned
     */
    public function testIfLanguageHelperListReturnsValues():void {

        $languages = LanguageHelper::list();
        $this->assertFalse(empty($languages));

    }

}
