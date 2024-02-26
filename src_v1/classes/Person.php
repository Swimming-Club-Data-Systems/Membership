<?php

/**
 * Person superclass
 */
class Person
{
  public function __construct(protected int $id, protected string $forename, protected string $surname)
  {
  }

  public function getForename()
  {
    return $this->forename;
  }

  public function getSurname()
  {
    return $this->surname;
  }

  /**
   * Get the person's full name
   * 
   * @return string full name
   */
  public function getFullName()
  {
    return \SCDS\Formatting\Names::format($this->forename, $this->surname);
  }

  /**
   * Get the person's full name without optional formatting
   * 
   * @return string full name
   */
  public function getFullNameUnformatted()
  {
    return $this->forename . ' ' . $this->surname;
  }

  public function getId()
  {
    return $this->id;
  }
}
