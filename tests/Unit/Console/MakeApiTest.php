<?php

namespace AMBERSIVE\Tests\Unit\Console;

use \AMBERSIVE\Tests\TestPackageCase;

class MakeApiTest extends \AMBERSIVE\Tests\TestPackageCase
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
