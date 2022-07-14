<?php

/**
 * Class for representing Photo Permissions
 */
class PhotoPermission
{
  private bool $permitted;
  private string $type;
  private string $description;

  /**
   * Create a PhotoPermission object
   * 
   * @param string the type
   * @param string a description of the type
   * @param bool if photos are allowed
   */
  public function __construct(string $type, string $description, bool $permitted)
  {
    $this->type = $type;
    $this->description = $description;
    $this->permitted = $permitted;
  }

  /**
   * Get the permission type
   * 
   * @return string type
   */
  public function getType()
  {
    return $this->type;
  }

  /**
   * Get the permission description
   * 
   * @return string description
   */
  public function getDescription()
  {
    return $this->description;
  }

  /**
   * Is permitted
   * 
   * @return bool permitted
   */
  public function isPermitted()
  {
    return $this->permitted;
  }
}
