<?php

namespace SCDS\TickSheet;

/**
 * TickSheet class for tick sheets as requested by RDASC
 */
class TickSheet
{
  private string $uuid;
  private string $colour;
  private string $name;
  private string $member;
  private string $tenant;
  private Group $component;

  /**
   * Private constructor - we will create or get objects via factories
   */
  private function __construct()
  {
    // New object
  }

  public static function create($fields)
  {
    $object = new TickSheet();
    return $object;
  }

  public static function retrieve($id)
  {
    // New empty object
    $object = new TickSheet();

    $db = app()->db;
    $tenant = app()->tenant;


    
    return $object;
  }
}
