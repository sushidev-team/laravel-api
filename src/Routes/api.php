<?php

Route::get('/status',        '\AMBERSIVE\Api\Controller\System\StatusController@status')->name('status');
Route::post('/status',        '\AMBERSIVE\Api\Controller\System\StatusController@status')->name('status');

/**
 * Authentication via Json Web Token
 */

Route::prefix('auth')->name('auth.')->middleware('api')->group(function () {
    Route::post('login',               '\AMBERSIVE\Api\Controller\Auth\LoginController@login')->name('login');
    Route::post('register',            '\AMBERSIVE\Api\Controller\Auth\RegisterController@register')->name('register');
    Route::get('refresh',              '\AMBERSIVE\Api\Controller\Auth\LoginController@refreshToken')->name('refresh.token')->middleware('auth:api');
    Route::get('activation/{code}',    '\AMBERSIVE\Api\Controller\Auth\RegisterController@activation')->name('register.activation');
    Route::post('password/forgotten',  '\AMBERSIVE\Api\Controller\Auth\ForgotPasswordController@forgotPassword')->name('password.forgot');
    Route::post('password',            '\AMBERSIVE\Api\Controller\Auth\ForgotPasswordController@setPassword')->name('password.set');
});

/**
 * Users
 */

Route::get('/users/current-refresh','\AMBERSIVE\Api\Controller\Users\UserController@currentRefresh')->name('users.current.refresh');
Route::get('/users/current',        '\AMBERSIVE\Api\Controller\Users\UserController@current')->name('users.current');
Route::api('users',                 '\AMBERSIVE\Api\Controller\Users\UserController', [], 'users', []);