<?php

namespace SCDS;

/**
 * Class for root footer
 */
class RootFooter
{
  private $js;
  private $fluidContainer;
  private $chrome = true;

  public function __construct()
  {
    // new footer
    $this->js = [];
  }

  public function render(): void
  {
    include BASE_PATH . 'views/root/footer.php';
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

  public function chrome(bool $bool): void
  {
    $this->chrome = $bool;
  }
}
