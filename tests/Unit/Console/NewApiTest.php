<?php

namespace AMBERSIVE\Tests\Unit\Console;

use \AMBERSIVE\Tests\TestPackageCase;

use File;
use Str;

class NewApiTest extends \AMBERSIVE\Tests\TestPackageCase
{

    public function setUp(): void
    {
        parent::setUp();



    }

    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function testIfNewApiWithoutCommandFails():void
    {
        $date  = date('Y_m_d_His');

        $this->artisan('api:new', ['--table' => 'test'])->assertExitCode(0);

        $path  = base_path("database/migrations/${date}_create_tests_table.php");
        File::delete($path);

    }

    public function testIfNewApiWillCreateAMigrationFileForANewFile():void {

        $date  = date('Y_m_d_His');
        $path  = base_path("database/migrations/${date}_create_tests_table.php");

        $this->assertFalse(File::exists($path));

        $this->artisan('api:new', ['--table' => 'test'])->assertExitCode(0);

        $files = \File::files(base_path("database/migrations"));
        $found = File::exists($path);

        File::delete($path);

        $this->assertTrue($found);
    }
}
