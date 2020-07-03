<?php 

namespace AMBERSIVE\Api\Seeder;

use DB;

use AMBERSIVE\Api\Classes\SeederHelper;

use AMBERSIVE\Api\Helper\LanguageHelper;

class UserTableAttributeSeeder extends SeederHelper
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {

        if ('sqlite' === config('database.default')){
            return;
        }

        $languages = collect(LanguageHelper::list())->map(function($item){
            return '"'.$item.'"';
        })->toArray();
        
        DB::statement("ALTER TABLE users CHANGE COLUMN language language ENUM(".implode(',', $languages).")");

    }
}
