<?php

namespace AMBERSIVE\Api\Macros;

use Route;
use Response;

use AMBERSIVE\Api\Abstracts\MacroAbstract;

class ResponseApiMacro extends MacroAbstract
{

    public function register(){

      Route::macro('api', function($url, $controller, $except = [], $name = null, $middleware = ['auth:api'])
      {

        $except = array_merge($except, ['create', 'edit']);

        Route::group(['middleware' => $middleware], function()  use($controller, $except, $url, $name){

          if(in_array('all', $except) === false){
            Route::get('/'.$url.'/all', $controller.'@all')->name($url.'.all');
          }

          Route::resource($url, $controller, ['except' => $except]);

        });

      });

    }

}
