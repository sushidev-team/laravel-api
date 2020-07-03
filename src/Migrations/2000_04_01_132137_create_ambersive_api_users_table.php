<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use AMBERSIVE\Api\Helper\LanguageHelper;

class CreateAmbersiveApiUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        $languages = LanguageHelper::list();

        Schema::create('users', function (Blueprint $table) use ($languages) {
            $table->uuid('id')->unique();

            $table->string('username');
            $table->string('firstname')->nullable();
            $table->string('lastname')->nullable();

            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();

            $table->string('password');

            $table->boolean('active')->default(false);
            $table->boolean('locked')->default(false);
            
            $table->integer('loginAttempts')->default(0);
            $table->timestamp('loginAttemptTimestamp')->nullable();

            $table->enum('language', $languages)->nullable()->default($languages[0]);
            
            $table->timestamps();
            $table->softDeletes();

            $table->index('email');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
