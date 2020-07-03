<?php

use Illuminate\Http\Request;

Route::get('/license',        '\AMBERSIVE\Api\Controller\System\LicenseController@license')->name('license');