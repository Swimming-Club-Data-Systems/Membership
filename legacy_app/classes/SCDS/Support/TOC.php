<?php

namespace SCDS\Support;

/**
 * Class for finding the next Table of Contents in the directory hierachy
 */
class TOC
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
    // Go up from current location dir until we find the TOC file or reach BASE_PATH + support loc
    for ($i = 1; $i < 15; $i++) {
      $currentDirectory = dirname($location, $i);

      // If we reach the BASE_PATH, break and return
      if (trim($currentDirectory, '/') == trim(BASE_PATH, '/')) {
        return null;
      }

      if (file_exists($currentDirectory . '/toc.yml')) {
        $toc = new TOC();
        $toc->yamlData = \Symfony\Component\Yaml\Yaml::parseFile($currentDirectory . '/toc.yml');
        $toc->fileLocation = str_replace(BASE_PATH . 'help', '', $currentDirectory);
        $toc->currentLocation = str_replace(BASE_PATH . 'help', '', $location);

        return $toc;
      }
    }
  }

  public function getYamlData()
  {
    if (sizeof($this->yamlData) > 0) {
      return $this->yamlData;
    }
  }

  public function render() {
    return TOC::renderList($this->yamlData, $this->fileLocation);
  }

  private static function renderList($list, $currentLocation)
  {
    $output = '<ul class="list-unstyled ms-2">';

    foreach ($list as $key => $item) {

      // pre($list);

      $output .= '<li>';
      if (isset($item['href'])) {
        $output .= '<a href="' . htmlspecialchars(autoUrl('help-and-support' . $currentLocation . '/' . $item['href'])) . '">';
      } else {
        $output .= '<strong>';
      }

      $output .= htmlspecialchars($item['name']);

      if (isset($item['href'])) {
        $output .= '</a>';
      } else {
        $output .= '</strong>';
      }
      $output .= '</li>';

      if (isset($item['items']) && sizeof($item['items']) > 0) {
        $output .= '<li>' . TOC::renderList($item['items'], $currentLocation) . '</li>';
      }
    }

    $output .= '</ul>';

    return $output;
  }
}
