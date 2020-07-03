<?php

namespace AMBERSIVE\Api\Helper;

use Config;
use Cache;
use Carbon\Carbon;

use Illuminate\Http\Request;

class LanguageHelper
{
    
    /**
     * Returns a list of supported languages by the system
     *
     * @return array
     */
    public static function list():array {

      $supportedLanguages = config('languages.supported', []);

      if (empty($supportedLanguages)) {
          return ['en', 'de'];
      }

      return $supportedLanguages;

    }

}
