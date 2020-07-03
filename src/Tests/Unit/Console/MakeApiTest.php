<?php

namespace AMBERSIVE\Api\Tests\Unit\Console;

use \AMBERSIVE\Api\Tests\TestPackageCase;

class MakeApiTest extends \AMBERSIVE\Api\Tests\TestPackageCase
{
    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function testIfMakeApiWithoutCommandFails()
    {
        $this->artisan('api:make')->assertExitCode(1);
    }
}
