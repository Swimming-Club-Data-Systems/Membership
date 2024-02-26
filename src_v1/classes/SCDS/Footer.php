<?php

namespace SCDS;

/**
 * Class for footer
 */
class Footer
{
  private $js;
  private $fluidContainer;
  private $showContent;

  public function __construct()
  {
    // new footer
    $this->js = [];
    $this->showContent = true;
  }

  public function render(): void
  {
    include BASE_PATH . 'views/footer.php';
  }

  public function addJs($path, $module = false): void
  {
    $this->js[] = [
      'url' => autoUrl($path),
      'module' => $module,
    ];
  }

  public function addExternalJs($uri, $module = false): void
  {
    $this->js[] = [
      'url' => $uri,
      'module' => $module,
    ];
  }

  public function useFluidContainer($bool = true): void
  {
    $this->fluidContainer = $bool;
  }

  public function showContent($bool = true): void
  {
    $this->showContent = $bool;
  }
}
