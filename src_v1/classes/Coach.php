<?php

/**
 * Class to represent coaches
 */
class Coach {
  /**
   * Create a coach object
   */
  public function __construct(private readonly int $id, $forename, $surname, $squad, private readonly string $type)
  {
    $this->forename = $forename;
    $this->surname = $surname;
    $this->squad = $squad;
  }

  // /**
  //  * Get the coach's first name
  //  * 
  //  * @return string first name
  //  */
  // public function getForename() {
  //   return $this->forname;
  // }

  // /**
  //  * Get the coach's first name
  //  * 
  //  * @return string first name
  //  */
  // public function getSurname() {
  //   return $this->surname;
  // }

  /**
   * Get the coach's full name
   * 
   * @return string full name
   */
  public function getFullName() {
    return $this->forename . ' ' . $this->surname;
  }

  /**
   * Get the type
   * 
   * @return string coach type
   */
  public function getType() {
    return coachTypeDescription($this->type);
  }

  /**
   * Get the type as an enum
   * 
   * @return string coach type enum
   */
  public function getTypeEnum() {
    return $this->type;
  }

}