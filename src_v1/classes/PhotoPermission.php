<?php

/**
 * Class for representing Photo Permissions
 */
class PhotoPermission
{
  /**
   * Create a PhotoPermission object
   * 
   * @param string the type
   * @param string a description of the type
   * @param bool if photos are allowed
   */
  public function __construct(private readonly string $type, private readonly string $description, private readonly bool $permitted)
  {
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
