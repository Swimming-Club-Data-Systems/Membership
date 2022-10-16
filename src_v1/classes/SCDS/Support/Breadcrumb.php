<?php

namespace SCDS\Support;

/**
 * Class for finding the next BREADCRUM data file in the directory hierachy
 */
class Breadcrumb
{

  private $yamlData;
  private $fileLocation;
  private $currentLocation;

  private function __construct()
  {
    // Empty object
  }

  public static function find($location)
  {
    // Go up from current location dir until we find the BREADCRUM file or reach BASE_PATH + support loc
    for ($i = 1; $i < 15; $i++) {
      $currentDirectory = dirname($location, $i);

      // If we reach the BASE_PATH, break and return
      if (trim($currentDirectory, '/') == trim(BASE_PATH, '/')) {
        return null;
      }

      if (file_exists($currentDirectory . '/breadcrumb/toc.yml')) {
        return file_get_contents($currentDirectory . '/breadcrumb/toc.yml');
      }
    }
  }
}
