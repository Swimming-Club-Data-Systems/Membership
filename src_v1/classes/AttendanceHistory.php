<?php

class AttendanceHistory
{

  private int $totalSessions = 0;
  private int $totalMandatorySessions = 0;
  private int $totalAttended = 0;
  private int $totalMandatoryAttended = 0;
  private int $totalExcused = 0;
  private int $totalMandatoryExcused = 0;
  private $startDate;
  private $endDate;
  private $data;

  private function __construct()
  {
  }

  public static function getHistory(int $member, string $from, string $until)
  {
    $object = new AttendanceHistory();
    $object->loadData($member, $from, $until);
    return $object;
  }

  private function loadData(int $member, string $from, string $until)
  {
    $db = app()->db;
    $tenant = app()->tenant;

    // Verify member
    $getMember = $db->prepare("SELECT MForename, MSurname FROM members WHERE MemberID = ? AND Tenant = ?");
    $getMember->execute([
      $member,
      $tenant->getId(),
    ]);
    $memberDetails = $getMember->fetch(PDO::FETCH_ASSOC);

    if (!$memberDetails) {
      throw new Exception('No such member');
    }

    try {

      $startDate = new DateTime($from, new DateTimeZone('Europe/London'));
      $endDate = new DateTime($until, new DateTimeZone('Europe/London'));
      $startWeekDate = clone $startDate;
      $endWeekDate = clone $endDate;

      // Work out start of week for both
      if ((int) $startWeekDate->format('N') != '7') {
        $startWeekDate->modify('last Sunday');
      }

      $startWeekPlus1Date = clone $startWeekDate;
      $startWeekPlus1Date->add(new DateInterval('P7D'));

      if ((int) $endWeekDate->format('N') != '7') {
        $endWeekDate->modify('last Sunday');
      }

      $endWeekMinus1Date = clone $endWeekDate;
      $endWeekMinus1Date->sub(new DateInterval('P7D'));

      $getSessions = $db->prepare("SELECT AttendanceBoolean, AttendanceRequired, StartTime, EndTime, SessionDay, sessionsAttendance.SessionID, WeekDateBeginning, sessionsWeek.WeekID, SessionName, VenueName, `Location` FROM sessionsAttendance INNER JOIN sessionsWeek ON sessionsAttendance.WeekID = sessionsWeek.WeekID INNER JOIN `sessions` ON sessionsAttendance.SessionID = `sessions`.`SessionID` INNER JOIN sessionsVenues ON sessionsVenues.VenueID = `sessions`.`VenueID` WHERE ((sessionsWeek.WeekDateBeginning >= ? AND sessions.SessionDay >= ?) OR (sessionsWeek.WeekDateBeginning >= ?)) AND ((sessionsWeek.WeekDateBeginning <= ? AND sessions.SessionDay <= ?) OR (sessionsWeek.WeekDateBeginning <= ?)) AND sessionsAttendance.MemberID = ? AND sessions.Tenant = ? ORDER BY WeekDateBeginning ASC, SessionDay ASC, StartTime ASC, EndTime ASC");
      $getSessions->execute([
        $startWeekDate->format('Y-m-d'),
        $startDate->format('w'),
        $startWeekPlus1Date->format('Y-m-d'),
        $endWeekDate->format('Y-m-d'),
        $endDate->format('w'),
        $endWeekMinus1Date->format('Y-m-d'),
        $member,
        $tenant->getId(),
      ]);

      $this->data = $getSessions->fetchAll(PDO::FETCH_ASSOC);

      $this->totalSessions = 0;
      $this->totalMandatorySessions = 0;
      $this->totalAttended = 0;
      $this->totalMandatoryAttended = 0;
      $this->totalExcused = 0;
      $this->totalMandatoryExcused = 0;

      foreach ($this->data as $row) {
        $required = bool($row['AttendanceRequired']);
        $attendance = (int) $row['AttendanceBoolean'];

        if ($required) {
          $this->totalMandatorySessions++;
          if ($attendance == 1) {
            $this->totalMandatoryAttended++;
          }
          if ($attendance == 2) {
            $this->totalMandatoryExcused++;
          }
        }

        $this->totalSessions++;
        if ($attendance == 1) {
          $this->totalAttended++;
        }
        if ($attendance == 2) {
          $this->totalExcused++;
        }
      }
    } catch (Exception $e) {
    }
  }

  public function getData()
  {
    return $this->data;
  }

  public function getTotalAttended(): int
  {
    return $this->totalAttended;
  }

  public function getTotalSessions(): int
  {
    return $this->totalSessions;
  }

  public function getTotalMandatorySessions(): int
  {
    return $this->totalMandatorySessions;
  }

  public function getTotalMandatoryAttended(): int
  {
    return $this->totalMandatoryAttended;
  }

  public function getTotalExcused(): int
  {
    return $this->totalExcused;
  }

  public function getTotalMandatoryExcused(): int
  {
    return $this->totalMandatoryExcused;
  }

  public function getPercentageTotal(): float
  {
    if (($this->totalMandatorySessions - $this->totalMandatoryExcused) > 0) {
      return $this->totalMandatoryAttended / (($this->totalMandatorySessions - $this->totalMandatoryExcused) / 100);
    } else {
      return 0.00;
    }
  }

  public function getPercentageMandatory(): float
  {
    if (($this->totalSessions - $this->totalExcused) > 0) {
      return $this->totalAttended / (($this->totalSessions - $this->totalExcused) / 100);
    } else {
      return 0.00;
    }
  }
}
