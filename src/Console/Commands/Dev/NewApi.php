<?php

namespace AMBERSIVE\Api\Console\Commands\Dev;

use Illuminate\Console\Command;

use Illuminate\Support\Facades\Schema;

use AMBERSIVE\Api\Helper\StubsHelper;

use Str;
use File;

use Carbon\Carbon;

class NewApi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'api:new {--table= : Table name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command will create a new database table and than run the api:make command.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // First run the prepare Command 
        $this->call('api:prepare');

        // Check for arguments
        $options = $this->options();
        $table   = data_get($options, 'table', null) == null ? $this->ask('Please insert a table name') : data_get($options, 'table', null);

        if ($table === null || $table === '' || $table === ' '){
            return $this->handle();
        }

        $table = Str::snake(Str::pluralStudly(class_basename($table)));
        $date  = date('Y_m_d_His');
        $postfix = "";

        $dbExist = $this->checkIfDatabaseExists($table);

        $dbExist ? $type = 'alter' : $type = 'create';
        $dbExist ? $postfix = "_".date('His') : $postfix = '';

        $path = base_path("database/migrations/${date}_${type}_${table}${postfix}_table.php");

        File::exists($path) ? $path = base_path("database/migrations/alter_${table}_table.php") : null;

        StubsHelper::save("Migration".ucfirst($type), $path, [
            'className' => ucfirst($table),
            'table' => $table,
            'postfix' => substr($postfix,1)
        ]);

        $this->line("<info>Created Migration:</info> {$path}");

    }

    protected function checkIfDatabaseExists($table){

        return Schema::hasTable($table);

    }

}
