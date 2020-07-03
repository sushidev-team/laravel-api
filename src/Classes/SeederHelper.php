<?php

namespace AMBERSIVE\Api\Classes;

use Illuminate\Database\Seeder;

use File;
use Yaml;
use Illuminate\Support\Collection;

class SeederHelper extends Seeder
{
  /**
   * Load yaml file from seederfiles
   *
   * @return array
   */
  public function loadYaml(string $filename = null): array {

    $arr = [];

    $path = resource_path("seedfiles/${filename}.yml");
    $pathInPackage = dirname(__DIR__)."/Seedfiles/${filename}.yml";

    if ($filename === null || File::exists($path) == false) {

        if (File::exists($pathInPackage) == true) {
            $arr = Yaml::parseFile($pathInPackage);
        }

        return $arr;
    }

    

    $arr = Yaml::parseFile($path);
    return $arr;

  }
  
  /**
   * Load a yaml file and return it as collection
   *
   * @param  mixed $filename
   * @return Illuminate\Support\Collection
   */
  public function loadYamlAsCollection(string $filename = null) : Collection {
      return collect($this->loadYaml($filename));
  }

  /**
   * Load yaml file from seederfiles
   *
   * @return array
   */
  public function updateYaml(string $filename = null, $data = []): bool {

    if ($filename === null || $data === null) {
        return false;
    }
    
    $path    = resource_path("seedfiles/${filename}.yml");
    $content = Yaml::dump($data);

    if (File::exists($path) == false) {
      return false;
    }

    File::put($path, $content);
    return true;

  }

}
