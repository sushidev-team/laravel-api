<?php

namespace AMBERSIVE\Api\Tests\Unit\Console;

use \AMBERSIVE\Api\Tests\TestPackageCase;

class UpdateApiTest extends \AMBERSIVE\Api\Tests\TestPackageCase
{
    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function testIfPrepareCommandWorks()
    {
        $this->artisan('api:prepare')->assertExitCode(0);
    }
}
