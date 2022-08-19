<?php

use Respect\Validation\Validator as v;
$db = app()->db;
$tenant = app()->tenant;

$getMemberAttendance = $db->prepare("SELECT AttendanceBoolean FROM `sessionsAttendance` WHERE `WeekID` = ? AND `SessionID` = ? AND `MemberID` = ?");

$markdown = new ParsedownExtra();
$markdown->setSafeMode(true);

$swimmerCount = 0;

$dateString = 'now';
if (isset($_REQUEST["date"]) && v::date()->validate($_REQUEST["date"])) {
  $dateString = $_REQUEST["date"];
}
$date = new DateTime($dateString, new DateTimeZone('Europe/London'));

if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == "Committee" || $_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == "Admin" || $_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == "Coach") {
  if ((isset($_REQUEST["squadID"]) && v::intVal()->validate($_REQUEST["squadID"])) || isset($preload) && $preload && $getSessions) {
    // get the squadID parameter from URL
    $squadID = $session = null;
    if (isset($preload) && $preload) {
      $squadID = $squad_init;
      $session = $session_init;
    } else {
      $squadID = $_REQUEST["squadID"];
      $session = $_REQUEST['selected'];
    }

    $response = "";

    if ($squadID != null) {
      $getSessions = $db->prepare("SELECT * FROM `sessions` WHERE squads.Tenant = :tenant AND squads.SquadID = :squad AND ((sessions.DisplayFrom <= :date) AND (sessions.DisplayUntil >= :date)) AND sessions.SessionDay = :dayNum ORDER BY sessions.SessionDay ASC, sessions.StartTime ASC");
      $getSessions->execute([
        'tenant' => $tenant->getId(),
        'squad' => $squadID,
        'date' => $date->format("Y-m-d"),
        'dayNum' => ((int) $date->format("N"))%7
      ]);
      $content = '<option>Choose the session from the menu</option>';

      $exists = false;

      while ($row = $getSessions->fetch(PDO::FETCH_ASSOC)) {
        $exists = true;
        $dayText = "";
        switch ($row['SessionDay']) {
          case 0:
            $dayText = "Sunday";
            break;
          case 1:
            $dayText = "Monday";
            break;
          case 2:
            $dayText = "Tuesday";
            break;
          case 3:
            $dayText = "Wednesday";
            break;
          case 4:
            $dayText = "Thursday";
            break;
          case 5:
            $dayText = "Friday";
            break;
          case 6:
            $dayText = "Saturday";
            break;
        }

        $selected = "";
        if ($session == (int) $row['SessionID']) {
          $selected = " selected ";
        }
        $content .= "<option value=\"" . $row['SessionID'] . "\" " . $selected . ">" . htmlspecialchars($row['SessionName']) . ", " . $dayText . " at " . (new DateTime($row['StartTime']))->format("H:i") . "</option>";
      }
    }

    if ($exists) {
      echo $content;
    }
    else {
      echo "<option selected value=\"0\">No sessions to show</option>";
    }
  }

  if ((isset($_REQUEST["sessionID"]) && v::intVal()->validate($_REQUEST["sessionID"]))) {

    $sessionID = $_REQUEST["sessionID"];

    $response = $content = $modalOutput = "";

    if ($sessionID != null) {
      // Get the date on sunday
      if ((int) $date->format('N') != '7') {
        $date->modify('last Sunday');
      }

      $weekBeginning = $date->format("Y-m-d");

      // Get the week id
      $getWeekId = $db->prepare("SELECT WeekID FROM sessionsWeek WHERE WeekDateBeginning = ? AND Tenant = ?");
      $getWeekId->execute([
        $date->format("Y-m-d"),
        $tenant->getId()
      ]);
      $weekId = $getWeekId->fetchColumn();

      if ($weekId == null) {
        halt(404);
      }

      // Check if the register has been done before
      $sessionRecord = $db->prepare("SELECT COUNT(*) FROM `sessionsAttendance` WHERE `WeekID` = ? AND `SessionID` = ?");
      $sessionRecord->execute([
        $weekId,
        $sessionID
      ]);
      $sessionRecordExists = $sessionRecord->fetchColumn();

      $sessionDetails = $db->prepare("SELECT * FROM (sessions INNER JOIN squads ON sessions.SquadID = squads.SquadID) WHERE sessions.SessionID = ? AND sessions.Tenant = ?");
      $sessionDetails->execute([
        $sessionID,
        $tenant->getId()
      ]);
      $row = $sessionDetails->fetch(PDO::FETCH_ASSOC);

      if (!$row) {
        halt(404);
      }

      $dayAdd = $row['SessionDay'];
      $sessionDate = strtotime($weekBeginning. ' + ' . $dayAdd . ' days');
      $date = date ('j F Y', $sessionDate);

      $dayText = "";
      switch ($row['SessionDay']) {
        case 0:
          $dayText = "Sunday";
          break;
        case 1:
          $dayText = "Monday";
          break;
        case 2:
          $dayText = "Tuesday";
          break;
        case 3:
          $dayText = "Wednesday";
          break;
        case 4:
          $dayText = "Thursday";
          break;
        case 5:
          $dayText = "Friday";
          break;
        case 6:
          $dayText = "Saturday";
          break;
      }

      $datetime1 = new DateTime($row['StartTime']);
      $datetime2 = new DateTime($row['EndTime']);
      $content .= "<div class=\"card-body\"><h2>Take register</h2><p>for " . htmlspecialchars($row['SquadName']) . " Squad, " . htmlspecialchars($row['SessionName']) . " on " . $dayText . " " . $date . " at " . $datetime1->format("H:i") . "</p>";
      $interval = $datetime1->diff($datetime2);
      $content .= "<p class=\"mb-0\">This session is ";

      if ((int) $interval->format('%h') == 1) {
        $content .= "1 hour ";
      } else if ((int) $interval->format('%h') > 1) {
        $content .= $interval->format('%h') . " hours ";
      }

      if ((int) $interval->format('%I') == 1) {
        $content .= "1 minute ";
      } else if ((int) $interval->format('%I') > 1) {
        $content .= $interval->format('%I') . " minutes ";
      }

      $content .= "long, finishing at " . $datetime2->format("H:i") . "</p>";
      $sql = "SELECT members.UserID, members.MemberID, members.MForename, members.MSurname,
      members.DateOfBirth, members.OtherNotes,
      memberPhotography.Website, memberPhotography.Social,
      memberPhotography.Noticeboard, memberPhotography.FilmTraining,
      memberPhotography.ProPhoto, memberMedical.Conditions, memberMedical.Allergies,
      memberMedical.Medication FROM (((((sessions INNER JOIN squadMembers ON sessions.SquadID = squadMembers.Squad) INNER JOIN members ON
      squadMembers.Member = members.MemberID) INNER JOIN squads ON sessions.SquadID =
      squads.SquadID) LEFT JOIN `memberPhotography` ON members.MemberID =
      memberPhotography.MemberID) LEFT JOIN `memberMedical` ON members.MemberID =
      memberMedical.MemberID) WHERE sessions.Tenant = ? AND sessions.SessionID = ? AND members.Status = '1' ORDER BY
      members.MForename, members.MSurname ASC";
      $swimmers = $db->prepare($sql);
      $swimmers->execute([
        $tenant->getId(),
        $sessionID
      ]);

      $content .= '</div><ul class="list-group list-group-flush">
      <li class="list-group-item bg-light">
        <div class="row">
          <div class="col">
            <strong>Swimmer</strong>
          </div>
          <div class="col-auto text-end">
            <strong>Notes</strong>
          </div>
        </div>
      </li>';
      while ($row = $swimmers->fetch(PDO::FETCH_ASSOC)) {
        $swimmerCount += 1;
        $age = date_diff(date_create($row['DateOfBirth']), date_create('today'))->y;
        $ageOnSession = date_diff(date_create($row['DateOfBirth']), date_create($weekBeginning. ' + ' . $dayAdd . ' days'))->y;
        $checked = "";
        if ($sessionRecordExists > 0) {
          $member = $row['MemberID'];
          $getMemberAttendance->execute([$weekId, $sessionID, $row['MemberID']]);
          $column = $getMemberAttendance->fetchColumn();

          if ($column != null && $column) {
            $checked = "checked";
          }
        }
        $no_parent = "";
        if (date("m-d", strtotime($row['DateOfBirth'])) == date("m-d", $sessionDate)) {
          $no_parent .= '<span class="visually-hidden"><em>Birthday is today</em></span><span class="badge bg-success"><i class="fa fa-birthday-cake" aria-hidden="true"></i> ' . $ageOnSession . ' today</span>';
        }
        if ($row['UserID'] == null && $age < 18) {
          $no_parent .= "<span class=\"badge bg-primary\">NO PARENT</span>";
        }
        $content .= "
        <li class=\"list-group-item\">
          <div class=\"row\">
            <div class=\"col\">
              <div class=\"form-check\">
              <input type=\"checkbox\" class=\"form-check-input\" " . $checked . " name=\"Member-" . $row['MemberID'] . "\" value=\"1\" id=\"Member-" . $row['MemberID'] . "\">
              <label class=\"form-check-label d-block\" for=\"Member-" . $row['MemberID'] . "\">" . htmlspecialchars($row['MForename'] . " " . $row['MSurname']) . " " . $no_parent . "</label>
              </div>
            </div>
            <div class=\"col-auto text-end\">";
            if ($row['Conditions'] != "" || $row['Allergies'] != "" || $row['Medication'] != "") {
              //ref=\"" . autoUrl("swimmers/" . $row['MemberID']) . "\" target=\"_blank\">
              $content .= "<a data-bs-toggle=\"modal\" href=\"#medicalModal" . $row['MemberID'] . "\"><span class=\"badge bg-danger\">MEDICAL</span></a>";
              $modalOutput .= '
              <!-- Modal -->
              <div class="modal fade" id="medicalModal' . $row['MemberID'] . '" tabindex="-1" role="dialog" aria-labelledby="medicalModalTitle' . $row['MemberID'] . '" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h3 class="modal-title" id="medicalModalTitle' . $row['MemberID'] . '">Medical Information for ' . $row['MForename'] . ' ' . $row['MSurname'] . '</h3>
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        
                      </button>
                    </div>
                    <div class="modal-body">
                    <h4>
                      Medical Conditions or Disabilities
                    </h4>';
                    if ($row["Conditions"] != "") {
                      $modalOutput .= $markdown->text($row["Conditions"]);
                    } else {
                      $modalOutput .= '<p>None</p>';
                    }

                    $modalOutput .= '
                    <h4>
                      Allergies
                    </h4>';
                    if ($row["Allergies"] != "") {
                      $modalOutput .= $markdown->text($row["Allergies"]);
                    } else {
                      $modalOutput .= '<p>None</p>';
                    }

                    $modalOutput .= '
                    <h4>
                      Medication
                    </h4>';
                    if ($row["Medication"] != "") {
                      $modalOutput .= $markdown->text($row["Medication"]);
                    } else {
                      $modalOutput .= '<p>None</p>';
                    }
                    $modalOutput .= '
                    </div>
                  </div>
                </div>
              </div>
              ';
            }
            if ($row['OtherNotes'] != "") {
              $content .= " <a data-bs-toggle=\"modal\" href=\"#notesModal" . $row['MemberID'] . "\"><span class=\"badge bg-info\">OTHER</span></a>";
              $modalOutput .= '
              <!-- Modal -->
              <div class="modal fade" id="notesModal' . $row['MemberID'] . '" tabindex="-1" role="dialog" aria-labelledby="notesModalTitle' . $row['MemberID'] . '" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h5 class="modal-title" id="notesModalTitle' . $row['MemberID'] . '">Other Notes for ' . $row['MForename'] . ' ' . $row['MSurname'] . '</h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        
                      </button>
                    </div>
                    <div class="modal-body">
                    ' . $row['OtherNotes'] . '
                    </div>
                  </div>
                </div>
              </div>
              ';
            }
            if (($row['Website'] != 1 || $row['Social'] != 1 || $row['Noticeboard'] != 1 || $row['FilmTraining'] != 1 || $row['ProPhoto'] != 1) && ($age < 18)) {
              $content .= " <a data-bs-toggle=\"modal\" href=\"#photoModal" . $row['MemberID'] . "\"><span class=\"badge bg-warning\">PHOTO</span></a>";
              $modalOutput .= '
              <!-- Modal -->
              <div class="modal fade" id="photoModal' . $row['MemberID'] . '" tabindex="-1" role="dialog" aria-labelledby="photoModalTitle' . $row['MemberID'] . '" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h5 class="modal-title" id="photoModalTitle' . $row['MemberID'] . '">Photography Permissions for ' . $row['MForename'] . ' ' . $row['MSurname'] . '</h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        
                      </button>
                    </div>
                    <div class="modal-body">
                    <p>There are limited photography permissions for this swimmer</p>
                    <ul>';
                    if ($row['Website'] != 1) {
                      $modalOutput .= '<li>Photos <strong>must not</strong> be taken of this swimmer for our website</li>';
                    }
                    if ($row['Social'] != 1) {
                      $modalOutput .= '<li>Photos <strong>must not</strong> be taken of this swimmer for our social media</li>';
                    }
                    if ($row['Noticeboard'] != 1) {
                      $modalOutput .= '<li>Photos <strong>must not</strong> be taken of this swimmer for our noticeboard</li>';
                    }
                    if ($row['FilmTraining'] != 1) {
                      $modalOutput .= '<li>This swimmer <strong>must not</strong> be filmed for the purposes of training</li>';
                    }
                    if ($row['ProPhoto'] != 1) {
                      $modalOutput .= '<li>Photos <strong>must not</strong> be taken of this swimmer by photographers</li>';
                    }
                    $modalOutput .= '
                    </ul>
                    </div>
                  </div>
                </div>
              </div>
              ';
            }
            $content .= "
            </div>
          </div>
        </li>";
      }
      $content .= '</ul><div class="card-body">
      <!--<p>
        <button class="btn btn-warning" type="button" data-bs-toggle="collapse" data-bs-target="#medicine-A-Z" aria-expanded="false" aria-controls="medicine-A-Z">
          Show/Hide Medicines A-Z
        </button>
      </p>
      <div class="collapse" id="medicine-A-Z">
        <iframe src="https://api-bridge.azurewebsites.net/medicines/?uid=Y2hyaXMuaGVwcGVsbEBjaGVzdGVybGVzdHJlZXRhc2MuY28udWs=" style="border: none; height: 450px; width: 100%;" class="mb-3"></iframe>
      </div>-->
      <p class="mb-0"><button type="submit" class="btn btn-success">Save Register</button></p></div>';
    }

    if ($swimmerCount > 0) {
      echo $content;
      echo $modalOutput;
    } else {
      echo "<p class=\"lead\">No swimmers were found for this squad and session</p>";
    }
  }

} else {
  halt(404);
}