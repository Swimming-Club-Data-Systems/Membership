<?php

namespace SCDS\TickSheet;

/**
 * Class for ticksheet groups
 */
abstract class Component
{
  abstract public function render();
  abstract public function getName();
  abstract public function getLabel();
  abstract public function isRequired();
}
