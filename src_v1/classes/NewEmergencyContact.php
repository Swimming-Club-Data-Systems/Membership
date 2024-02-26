<?php

/**
 * New replacement emergency contact
 */
class NewEmergencyContact
{
  /**
   * Create an object
   */
  public function __construct(private $number, private $name, private $relation, private $id, private $isUser = false)
  {
  }

  /**
   * Get the relationship
   * 
   * @return string relation
   */
  public function getRelation()
  {
    if (isset($this->relation)) {
      return $this->relation;
    } else if ($this->isUser) {
      return 'Linked account';
    }
  }

  /**
   * Get the id
   * 
   * @return int user or emergency contact id
   */
  public function getId()
  {
    return $this->id;
  }

  /**
   * Get the person's name
   * 
   * @return string name
   */
  public function getName()
  {
    return $this->name;
  }

  /**
   * Get the plain contact number
   * 
   * @return string E164 phone number
   */
  public function getContactNumber()
  {
    try {
      $number = \Brick\PhoneNumber\PhoneNumber::parse($this->number);
      return $number->format(\Brick\PhoneNumber\PhoneNumberFormat::E164);
    } catch (\Brick\PhoneNumber\PhoneNumberParseException) {
      return null;
    }
  }

  /**
   * Get the formatted contact number
   * 
   * @return string National format phone number
   */
  public function getNationalContactNumber()
  {
    try {
      $number = \Brick\PhoneNumber\PhoneNumber::parse($this->number);
      return $number->format(\Brick\PhoneNumber\PhoneNumberFormat::NATIONAL);
    } catch (\Brick\PhoneNumber\PhoneNumberParseException) {
      return null;
    }
  }

  /**
   * Get the internation format contact number
   * 
   * @return string International format phone number
   */
  public function getInternationalContactNumber()
  {
    try {
      $number = \Brick\PhoneNumber\PhoneNumber::parse($this->number);
      return $number->format(\Brick\PhoneNumber\PhoneNumberFormat::INTERNATIONAL);
    } catch (\Brick\PhoneNumber\PhoneNumberParseException) {
      return null;
    }
  }

  /**
   * Get the RFC format contact number
   * 
   * @return string RFC format phone number
   */
  public function getRFCContactNumber()
  {
    try {
      $number = \Brick\PhoneNumber\PhoneNumber::parse($this->number);
      return $number->format(\Brick\PhoneNumber\PhoneNumberFormat::RFC3966);
    } catch (\Brick\PhoneNumber\PhoneNumberParseException) {
      return null;
    }
  }
}
