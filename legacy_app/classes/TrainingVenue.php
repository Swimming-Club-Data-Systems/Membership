<?php

/**
 * Class to represent a training venue
 */
class TrainingVenue
{
  private int $tenant;
  protected int $id;
  protected string $name;
  protected $address;

  /**
   * Create an empty training venue object
   */
  function __construct()
  {
  }

  /**
   * Returns a training venue object by ID
   * 
   * @param id The training venue id
   * @return Squad object
   */
  public static function get(int $id)
  {
    $venue = new TrainingVenue();
    $venue->id = $id;
    $venue->revalidate();
    return $venue;
  }

  /**
   * Returns a list of training venues
   * 
   * @param Array args
   * @return Array array of training venues
   */
  public static function list($args = [])
  {
    $list = [];



    return $list;
  }

  /**
   * Refetch stored information from the database
   */
  public function revalidate()
  {
    $db = app()->db;
    $tenant = app()->tenant;

    $this->tenant = $tenant->getId();

    $getVenue = $db->prepare("SELECT `VenueName`, `Location` FROM `sessionsVenues` WHERE VenueID = ? AND Tenant = ?");
    $getVenue->execute([
      $this->id,
      $this->tenant
    ]);
    $venue = $getVenue->fetch(PDO::FETCH_ASSOC);

    if (!$venue) {
      throw new Exception('No venue');
    }

    $this->name = $venue['VenueName'];
    $this->address = $venue['Location'];
  }

  /**
   * Get the name of a training venue
   * 
   * @return string training venue name
   */
  public function getName()
  {
    return $this->name;
  }

  /**
   * Get the training venue id
   * 
   * @return int id
   */
  public function getId(): int
  {
    return $this->id;
  }

  /**
   * Get raw location
   * 
   * @return string location
   */
  public function getRawLocation()
  {
    return $this->location;
  }
}
