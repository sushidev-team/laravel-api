<?php

namespace AMBERSIVE\Api\Helper;

use Config;
use Cache;
use Carbon\Carbon;
use File;

use Illuminate\Http\Request;

class StubsHelper
{
   
    /**
     * Returns the path for the stub folde
     *
     * @param  mixed $name
     * @return string
     */
    public static function path($name = null):string {

      $path = config('ambersive-api.stubs_store');

      if (File::exists($path) === false) {
          $path = base_path('vendor/AMBERSIVE/api/src/Stubs');
      }

      if ($name != null) {
         $path .= '/'.$name.'.stub';
      }

      return $path;

    }
 
    /**
     * Will load the stub file from folder
     * Method will return null if the stub file cannot be loaded.
     *
     * @param  mixed $name
     * @return string / null
     */
    public static function load(string $name = null) {

        if ($name === null) {
           return null;
        }
        
        $path = self::path($name);

        if (File::exists($path) === false) {
           return null;
        }

        $content = File::get($path);

        if ($content !== null) {
           return $content;
        }

        return null;

    }
    
    /**
     * Transform an array to string
     *
     * @param  mixed $array
     * @return string
     */
    public static function transformArrayWith($array):string {
        return implode('', $array);
    }
    
    /**
     * replacePlaceholders
     *
     * @param  mixed $name
     * @param  mixed $placeholders
     * @return string
     */
    public static function replacePlaceholders($name, $placeholders):string {

       $content = self::load($name);

       if ($content != null) {

         collect($placeholders)->each(function($placeholder, $placeholderKey) use (&$content) {

            if (is_array($placeholder) === false) {
               $content = str_replace('{{'.$placeholderKey.'}}', $placeholder, $content);
            }
            else {
               $content = str_replace('{{'.$placeholderKey.'}}', self::transformArrayWith($placeholder), $content);
            }
            
         });

       }      

       return $content == null ? "" : $content;

    }
    
    /**
     * Load a stub an replace the placeholders and save it
     *
     * @param  mixed $name
     * @param  mixed $path
     * @param  mixed $placeholder
     * @return bool
     */
    public static function save(String $name, String $path, array $placeholder = []):bool {

       $content = self::replacePlaceholders($name, $placeholder);

       if ($content !== null ){
            File::put($path, $content);
            return true;
       }

       return false;

    }

}
