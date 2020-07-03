<?php

return [

    /*
    |--------------------------------------------------------------------------
    | MAIL VIEWS
    |--------------------------------------------------------------------------
    | This config defines which views should be used for the mails
    |
    */

    'activation_code' => env('API_MAILS_ACTIVATION_CODE', 'ambersive-api::emails.activation'),
    'reset_code' => env('API_MAILS_RESET_CODE', 'ambersive-api::emails.reset')

];
