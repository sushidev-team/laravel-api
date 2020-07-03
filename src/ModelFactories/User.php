<?php

use Carbon\Carbon;
use Faker\Generator as Faker;
use Illuminate\Support\Str;

 #region [CUSTOM:IMPORTS]
use AMBERSIVE\Api\Models\User;
 #endregion [CUSTOM:IMPORTS]

/*
 |
 |--------------------------------------------------------------------------
 | Generated Factory                                                     
 | Please be aware when you run the command "php artisan api:update"      
 | Cause it will automatically update this file                           
 | <LOCKED>: false                                                   
 |--------------------------------------------------------------------------
 |
 */

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(User::class, function (Faker $faker) {
    
    $modelData = [];

    #region [CUSTOM:LOGIC]
    $modelData['username']          = $faker->userName;
    $modelData['email']             = $faker->unique()->safeEmail;
    $modelData['email_verified_at'] = now();
    $modelData['password']          = bcrypt('testtest');
    $modelData['language']          = 'en';
    #endregion [CUSTOM:LOGIC]

    return $modelData;
});
