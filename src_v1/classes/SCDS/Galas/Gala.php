<?php

namespace SCDS\Galas;

class Gala
{

  public $id;
  public $name;
  public $course;
  public $venue;
  public $closing_time;
  public $end_date;
  public $hytek;
  public $coach_enters;
  public $requires_approval;
  public $description;
  public $payment_category;
  public $processing_fee;
  public $event_categories;
  public $events;
  public $sessions;

  public function __construct()
  {
    // Do nothing
  }

  public static function getGala($id)
  {
    $db = app()->db;
    $tenant = app()->tenant;

    $getGala = $db->prepare("SELECT `GalaID`, `GalaName`, `CourseLength`, `GalaVenue`, `ClosingDate`, `GalaDate`, `HyTek`, `CoachEnters`, `RequiresApproval`, `Description`, `PaymentCategory`, `ProcessingFee` FROM `galas` WHERE `Tenant` = ? AND `GalaID` = ?");
    $getGala->execute([
      $tenant->getId(),
      $id,
    ]);

    $gala = $getGala->fetch(\PDO::FETCH_ASSOC);

    if (!$gala) {
      http_response_code(500);
      throw new \Exception("Gala not found");
    }

    $object = new Gala();

    $object->id = (int) $gala['GalaID'];
    $object->name = $gala['GalaName'];
    $object->course = $gala['CourseLength'];
    $object->venue = $gala['GalaVenue'];
    $object->closing_time = $gala['ClosingDate'];
    $object->end_date = $gala['GalaDate'];
    $object->hytek = (bool) $gala['HyTek'];
    $object->coach_enters = (bool) $gala['CoachEnters'];
    $object->requires_approval = (bool) $gala['RequiresApproval'];
    $object->description = $gala['Description'];
    $object->payment_category = $gala['PaymentCategory'] ? (int) $gala['PaymentCategory'] : null;
    $object->processing_fee = (int) $gala['ProcessingFee'];

    $object->event_categories = [];
    $object->events = [];
    $object->sessions = [];

    return $object;
  }

  public function getAttributes()
  {
    return get_object_vars($this);
  }
}
