<?php

use Carbon\Carbon;
use Faker\Generator as Faker;
use Illuminate\Support\Str;

#region [CUSTOM:IMPORTS]
use AMBERSIVE\Api\Models\UserActivation;
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
$factory->define(UserActivation::class, function (Faker $faker) {
    
    $modelData = [];

    #region [CUSTOM:LOGIC]
    $modelData['code']              = $faker->password;
    $modelData['used']              = false;
    #endregion [CUSTOM:LOGIC]

    return $modelData;
});
