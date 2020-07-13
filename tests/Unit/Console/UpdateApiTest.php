<?php

namespace AMBERSIVE\Tests\Unit\Console;

use \AMBERSIVE\Tests\TestPackageCase;

class UpdateApiTest extends \AMBERSIVE\Tests\TestPackageCase
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
