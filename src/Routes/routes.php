<?php

Route::prefix('api')
             ->middleware('api')
             ->name('api.')
             ->namespace("AMBERSIVE\Api")
             ->group(__DIR__.'/api.php');

Route::middleware('web')
             ->namespace("AMBERSIVE\Web")
             ->group(__DIR__.'/web.php');