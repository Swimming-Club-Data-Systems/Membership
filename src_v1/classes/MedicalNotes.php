<?php

class MedicalNotes
{
  private int $id;
  private $conditions;
  private $allergies;
  private $medication;
  private $hasInfo;
  private $gpName;
  private $gpAddress;
  private $gpPhone;
  private $withholdConsent;

  public function __construct($id)
  {
    $this->id = $id;
    $db = app()->db;

    $getDetails = $db->prepare("SELECT Conditions, Allergies, Medication, `GPName`, `GPAddress`, `GPPhone`, `WithholdConsent` FROM memberMedical WHERE MemberID = ?");
    $getDetails->execute([
      $this->id
    ]);

    $row = $getDetails->fetch(PDO::FETCH_ASSOC);
    if ($row) {
      $this->conditions = $row['Conditions'];
      $this->allergies = $row['Allergies'];
      $this->medication = $row['Medication'];
    } else {
      $this->conditions = null;
      $this->allergies = null;
      $this->medication = null;
    }

    if (isset($row['GPName']) && $row['GPName']) {
      $this->gpName = $row['GPName'];
    }

    if (isset($row['GPPhone']) && $row['GPPhone']) {
      $this->gpPhone = $row['GPPhone'];
    }

    if (isset($row['GPAddress']) && $row['GPAddress']) {
      try {
        $this->gpAddress = json_decode($row['GPAddress']);
      } catch (\Exception | \Error $e) {
        // Ignore
      }
    }

    $this->withholdConsent = (isset($row['WithholdConsent']) && !bool($row['WithholdConsent']));

    $this->hasInfo = mb_strlen($this->conditions) > 0 || mb_strlen($this->allergies) > 0 || mb_strlen($this->medication) > 0;
  }

  public function getRawConditions()
  {
    if (mb_strlen($this->conditions) > 0) {
      return $this->conditions;
    }
    return 'N/A';
  }

  public function getRawAllergies()
  {
    if (mb_strlen($this->allergies) > 0) {
      return $this->allergies;
    }
    return 'N/A';
  }

  public function getRawMedication()
  {
    if (mb_strlen($this->medication) > 0) {
      return $this->medication;
    }
    return 'N/A';
  }

  public function getConditions()
  {
    $md = $this->getRawConditions();
    $markdown = new \ParsedownExtra();
    $markdown->setSafeMode(true);
    return $markdown->text($md);
  }

  public function getAllergies()
  {
    $md = $this->getRawAllergies();
    $markdown = new \ParsedownExtra();
    $markdown->setSafeMode(true);
    return $markdown->text($md);
  }

  public function getMedication()
  {
    $md = $this->getRawMedication();
    $markdown = new \ParsedownExtra();
    $markdown->setSafeMode(true);
    return $markdown->text($md);
  }

  public function hasMedicalNotes()
  {
    return $this->hasInfo;
  }

  public function getGpPhone()
  {
    return $this->gpPhone;
  }

  public function getGpAddress()
  {
    return $this->gpAddress;
  }

  public function getGpName()
  {
    return $this->gpName;
  }

  public function hasConsent()
  {
    if (!$this->withholdConsent) {
      return 'NO CONSENT HAS BEEN GIVEN for emergency medical treatment';
    }

    return 'Consent has been given for emergency medical treatment';
  }
}
