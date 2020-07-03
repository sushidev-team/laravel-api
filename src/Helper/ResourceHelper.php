<?php

namespace AMBERSIVE\Api\Helper;

use File;

class ResourceHelper
{

    protected static $exceptModelFiles = [
        'BaseModel'
    ];
    
    /**
     * This file will return the namespace for a file
     *
     * @param  mixed $file
     * @return void
     */
    public static function getNamespace($file = null){

        $namespace = null;

        if($file === null || is_string($file) === true) {
            return null;
        }

        $fileContent = File::get($file->getPathname());
        $fileBasename = $file->getBasename();

        preg_match_all('/namespace\s{1,}[a-zA-Z0-9\\\_\-]{1,}\;/', $fileContent, $match);
        
        if (sizeOf($match) === 0) {
            return null;
        }

        $namespace = str_replace('/[\s|\t]{1,}/',' ', $match[0][0]);
        $namespace = substr($namespace, 10, strlen($namespace) - 11);

        return $namespace;

    }

    /**
     * Get all classes
     *
     * @param  mixed $path
     * @param  mixed $pattern
     * @param  mixed $listOnly
     * @param  mixed $except
     * @param  mixed $withoutNamespace
     * @param  mixed $returnAsCollection
     * @return void
     */
    public static function getClasses($path = null,$pattern = null,$listOnly = false,$except = null,$withoutNamespace = false,$returnAsCollection = false){

      if($path === null){
        return false;
      }

      $finder    = new \Symfony\Component\Finder\Finder();
      $finder->files()->name('*'.$pattern.'.php')->in($path);

      $resources = [];

      foreach ($finder as $file) {

          $fileClass  = $file->getBasename('.php');

          $found = false;

          if (is_array($except) === true) {
            $found = in_array($fileClass,$except);
          }
          else  {
            $found = ($fileClass === $except);
          }

          if($found !== true) {

              $classname    = $fileClass;
              $namespace    = self::getNamespace($file);

              if($withoutNamespace === true && $listOnly === false){
                  $withoutNamespace = false;
              }

              if($withoutNamespace === false){
                $classname    = $namespace.'\\'.$classname;
              }

              if($listOnly === false){
                $resources[]  = new $classname();
              }
              else {
                $resources[]  = $classname;
              }


          }

      }

      if($returnAsCollection === true){
        return collect($resources);
      }

      return $resources;

    }

    
    /**
     * Returns a list of models
     *
     * @param  mixed $asCollection
     * @param  mixed $execept
     * @return void
     */
    public static function getModels($asCollection = false, array $execept = [])
    {
          $models = self::getClasses(app_path('Models'),'',false, array_merge(self::$exceptModelFiles,$execept),false,$asCollection);
          return $models;
    }

}
