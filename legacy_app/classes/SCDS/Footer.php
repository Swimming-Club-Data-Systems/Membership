<?php

namespace SCDS;

/**
 * Class for footer
 */
class Footer
{
  private $js;
  private $fluidContainer;

  public function __construct()
  {
    // new footer
    $this->js = [];
  }

  public function render()
  {
    include BASE_PATH . 'views/footer.php';
  }

  public function addJs($path, $module = false)
  {
    $this->js[] = [
      'url' => autoUrl($path),
      'module' => $module,
    ];
  }

  public function addExternalJs($uri, $module = false)
  {
    $this->js[] = [
      'url' => $uri,
      'module' => $module,
    ];
  }

  public function useFluidContainer($bool = true)
  {
    $this->fluidContainer = $bool;
  }
}
