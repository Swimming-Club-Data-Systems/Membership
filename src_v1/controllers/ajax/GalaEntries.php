<?php

use CLSASC\EquivalentTime\EquivalentTime;

$db = app()->db;
$tenant = app()->tenant;

$access = $_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'];
$sex = "";
if ($_REQUEST["sex"] == "m") {
  $sex = " AND `Gender` = 'Male' ";
} else if ($_REQUEST["sex"] == "f") {
  $sex = " AND `Gender` = 'Female' ";
} else {
  //halt(500);
}

// Get squad list
$getSquads = $db->prepare("SELECT `SquadName` `name` FROM squads INNER JOIN squadMembers ON squadMembers.Squad = squads.SquadID WHERE squadMembers.Member = ? ORDER BY SquadFee DESC, SquadName ASC;");

$sqlArgs = [];
$sqlArgs[] = $tenant->getId();

$count = 0;
if ($access == "Committee" || $access == "Admin" || $access == "Coach" || $access == "Galas") {
  $sql = "";
  if ((isset($_REQUEST["galaID"])) && (isset($_REQUEST["search"]))) {
    // get the galaID parameter from request
    $galaID = $_REQUEST["galaID"];
    // get the search term parameter from request
    $search = $_REQUEST["search"];

    // Search the database for the results
    if ($galaID == "all") {
      $sql = "SELECT * FROM (((galaEntries INNER JOIN members ON
      galaEntries.MemberID = members.MemberID) INNER JOIN galas ON
      galaEntries.GalaID = galas.GalaID) LEFT JOIN clubMembershipClasses ON members.NGBCategory = clubMembershipClasses.ID) WHERE galas.Tenant = ? AND galas.GalaDate >= CURDATE() " .
        $sex . " AND members.MSurname LIKE ? COLLATE utf8mb4_general_ci ORDER BY galas.ClosingDate
      ASC, galas.GalaDate DESC, members.MSurname ASC, members.MForename ASC";
      $sqlArgs[] = '%' . $search . '%';
    } else {
      $sql = "SELECT * FROM (((galaEntries INNER JOIN members ON
      galaEntries.MemberID = members.MemberID) INNER JOIN galas ON
      galaEntries.GalaID = galas.GalaID) LEFT JOIN clubMembershipClasses ON members.NGBCategory = clubMembershipClasses.ID) WHERE galas.Tenant = ? AND galas.GalaID = ? " .
        $sex . " AND members.MSurname LIKE ? COLLATE utf8mb4_general_ci ORDER BY members.MSurname ASC, members.MForename ASC";
      $sqlArgs[] = $galaID;
      $sqlArgs[] = '%' . $search . '%';
    }
  } elseif ((!isset($_REQUEST["galaID"])) && (isset($_REQUEST["search"]))) {
    // get the search term parameter from request
    $search = $_REQUEST["search"];

    // Search the database for the results
    $sql = "SELECT * FROM ((galaEntries INNER JOIN members ON
    galaEntries.MemberID = members.MemberID) INNER JOIN galas ON
    galaEntries.GalaID = galas.GalaID) WHERE galas.Tenant = ? AND galas.GalaDate >= CURDATE() " .
      $sex . " AND members.MSurname LIKE ? COLLATE utf8mb4_general_ci";
    $sqlArgs[] = '%' . $search . '%';
  } elseif ((isset($_REQUEST["galaID"])) && (!isset($_REQUEST["search"]))) {
    // get the search term parameter from request
    $galaID = $_REQUEST["galaID"];

    // Search the database for the results
    $sql = "SELECT * FROM ((galaEntries INNER JOIN members ON
    galaEntries.MemberID = members.MemberID) INNER JOIN galas ON
    galaEntries.GalaID = galas.GalaID) WHERE galas.Tenant = ? " .
      $sex . " AND galas.GalaID = ?";
    $sqlArgs[] = $galaID;
  } else {
    // Error
    halt(404);
  }

  if ($galaID == "Select a gala") {
    echo '<div class="ajaxPlaceholder"><strong>Select a Gala</strong> <br>We\'ll be able to load entries if you select a gala</div>';
  } else {

    $results = $db->prepare($sql);
    $results->execute($sqlArgs);


    $content = "";
    if (app('request')->isMobile()) {
      $content .= '<table class="table table-sm table-light">';
    } else {
      $content .= '<table class="table table-hover table-light">';
    }
    $content .= '<thead class=""><tr><th>Swimmer</th><th>Swims</th><th class="d-print-none"><abbr title="Lock entries and mark as paid">Admin</abbr></th></tr></thead><tbody>';

    // For loop iterates through the rows of the database result, producing rows for the table
    while ($row = $results->fetch(PDO::FETCH_ASSOC)) {
      $count++;
      $member = $row['MemberID'];
      $times = null;

      $hyTekPrintDate = "";
      if ($row['HyTek'] == 1) {
        $hyTekPrintDate = " <br>DoB: " . date('j F Y', strtotime($row['DateOfBirth'])) . "";
        $type;
        $typeB;
        if ($row['CourseLength'] == "SHORT") {
          $type = "SCPB";
          $typeB = "LCPB";
        } else {
          $type = "LCPB";
          $typeB = "SCPB";
        }
        // $getTimes = $db->prepare("SELECT * FROM `times` WHERE `MemberID` = ? AND `Type` = ?");

        // $getTimes->execute([$row['MemberID'], $type]);
        // $times = $getTimes->fetchAll(PDO::FETCH_ASSOC);

        // $getTimes->execute([$row['MemberID'], $typeB]);
        // $timesB = $getTimes->fetchAll(PDO::FETCH_ASSOC);
      }

      // First part of the row content
      $content .= "<tr><td><strong><a href=\"" . htmlspecialchars(autoUrl('members/' . $row['MemberID'])) . "\">" . htmlspecialchars(\SCDS\Formatting\Names::format($row['MForename'], $row['MSurname']))  . "</a></strong>" . $hyTekPrintDate . "<br><a
      class=\"d-print-none\"
      href=\"https://www.swimmingresults.org/biogs/biogs_details.php?tiref=" .
        htmlspecialchars($row['ASANumber']) . "\" target=\"_blank\" title=\"Click to see times\">" .
        htmlspecialchars($row['ASANumber']) . " <i class=\"fa fa-external-link\"
      aria-hidden=\"true\"></i></a><span class=\"d-none d-print-inline\">" . htmlspecialchars(app()->tenant->getKey('NGB_NAME')) . ": " .
        htmlspecialchars($row['ASANumber']) . "</span><br>
      <span class=\"small\">" . htmlspecialchars($row['Name']) . "<br>
      " . htmlspecialchars($row['GalaName']) . "<br><a class=\"d-print-none\" href=\"" . autoUrl('galas/entries/' . $row['EntryID']) . "\">Edit Entry</a><br><a class=\"d-print-none\" href=\"" . autoUrl('galas/entries/' . $row['EntryID']) . "/manual-time\">Set Manual Times</a></span>";

      $getSquads->execute([
        $row['MemberID']
      ]);
      $squad = $getSquads->fetchColumn();
      if ($squad) {
        $content .= '<p class="mb-0 mt-3 small"><strong>Member of</strong></p><ul class="list-unstyled small">';
        do {
          $content .= '<li>' . htmlspecialchars($squad) . '</li>';
        } while ($squad = $getSquads->fetchColumn());
        $content .= '</ul>';
      }

      $content .= '</td>';

      // Arrays of swims used to check whever to print the name of the swim entered
      // BEWARE This is in an order to ease inputting data into SportSystems, contrary to these arrays in other files
      $swimsArray = ['25Free', '50Free', '100Free', '200Free', '400Free', '800Free', '1500Free', '25Back', '50Back', '100Back', '200Back', '25Breast', '50Breast', '100Breast', '200Breast', '25Fly', '50Fly', '100Fly', '200Fly', '100IM', '150IM', '200IM', '400IM',];
      $swimsTextArray = ['25&nbsp;Free', '50&nbsp;Free', '100&nbsp;Free', '200&nbsp;Free', '400&nbsp;Free', '800&nbsp;Free', '1500&nbsp;Free', '25&nbsp;Back', '50&nbsp;Back', '100&nbsp;Back', '200&nbsp;Back', '25&nbsp;Breast', '50&nbsp;Breast', '100&nbsp;Breast', '200&nbsp;Breast', '25&nbsp;Fly', '50&nbsp;Fly', '100&nbsp;Fly', '200&nbsp;Fly', '100&nbsp;IM', '150&nbsp;IM', '200&nbsp;IM', '400&nbsp;IM',];
      $swimsTimeArray = ['25FreeTime', '50FreeTime', '100FreeTime', '200FreeTime', '400FreeTime', '800FreeTime', '1500FreeTime', '25BackTime', '50BackTime', '100BackTime', '200BackTime', '25BreastTime', '50BreastTime', '100BreastTime', '200BreastTime', '25FlyTime', '50FlyTime', '100FlyTime', '200FlyTime', '100IMTime', '150IMTime', '200IMTime', '400IMTime',];

      // Create the cell and unordered list
      $content .= "<td><ul class=\"mb-0 list-unstyled\">";

      // Print <li>Swim Name</li> for each entry
      if (!bool($row['HyTek'])) {
        for ($y = 0; $y < sizeof($swimsArray); $y++) {
          if ($row[$swimsArray[$y]] == 1) {
            $content .= "<li><strong>" . ($swimsTextArray[$y]) . '</strong>';
            if (isset($row[$swimsTimeArray[$y]]) && $row[$swimsTimeArray[$y]]) {
              $content .= '<br>' . htmlspecialchars($row[$swimsTimeArray[$y]]);
            }
            $content .= "</li>";
          }
        }
      } else {
        for ($y = 0; $y < sizeof($swimsArray); $y++) {
          $output = "";
          $mins = $secs = $hunds = 0;
          if ($row[$swimsArray[$y]] == 1) {
            $course;
            $to;
            $time;
            if ($row['CourseLength'] == "SHORT") {
              $course = "50m";
              $to = "25m";
            } else {
              $course = "25m";
              $to = "50m";
            }
            // if (isset($timesB[$swimsArray[$y]]) && $timesB[$swimsArray[$y]] != "") {
            //   $time = explode(".", $timesB[$swimsArray[$y]]);
            //   $ms = explode(":", $time[0]);
            //   $mins = $secs = $hunds =  0;
            //   if (sizeof($ms) == 1) {
            //     $secs = (int) $ms[0];
            //   } else {
            //     $mins = (int) $ms[0];
            //     $secs = (int) $ms[1];
            //   }
            //   $hunds = $time[1];
            //   $time_in = $time_double = 0;
            //   $time_in = (double) 60*$mins + $secs + ($hunds/100);

            //   $entry_times = null;
            //   if ($times[$swimsArray[$y]] != "") {
            //     $entry_times = $times[$swimsArray[$y]];
            //   } else if ($row[$swimsTimeArray[$y]]) {
            //     $entry_times = $row[$swimsTimeArray[$y]];
            //   }
            //   $time = explode(".", $entry_times);
            //   $ms = explode(":", $time[0]);
            //   $mins = $secs = $hunds =  0;
            //   if (sizeof($ms) == 1) {
            //     $secs = (int) $ms[0];
            //   } else {
            //     $mins = (int) $ms[0];
            //     $secs = (int) $ms[1];
            //   }
            //   $hunds = $time[1];
            //   $time_double_oc = (double) 60*$mins + $secs + ($hunds/100);
            //   try {
            //   	$time = new EquivalentTime($course, str_replace('&nbsp;', ' ', $swimsTextArray[$y]), $time_in);
            //     $time_double = (double) $time->getConversion($to);
            //   	$time->setOutputAsString(true);
            //     if (($time_double_oc > 0 && $time_double > 0) && $time_double < $time_double_oc) {
            //       $output = ', <abbr title="Faster converted time available">FC:</abbr> ' . htmlspecialchars($time->getConversion($to));
            //     } else {
            //       $output = null;
            //     }
            //   } catch (Exception $e) {
            //   	$output = null;
            //   }
            // }
            $content .= "<li><strong>" . $swimsTextArray[$y] . "</strong> <br>";
            if (isset($row[$swimsTimeArray[$y]]) && $row[$swimsTimeArray[$y]]) {
              $content .= htmlspecialchars($row[$swimsTimeArray[$y]]) . $output;
            } else {
              $content .= "No time available";
            }
            $content .= "</li>";
          }
        }
      }
      // End ul and cell
      $content .= "</ul></td>";

      $content .= '<td class="d-print-none">';

      // If approval not yet given and is required, show a warning
      if ($row['RequiresApproval'] && !$row['Approved']) {
        $content .= '<div class="p-3 bg-warning text-dark mb-3"><strong>This entry has not yet been approved</strong><br>Unless you are sure a squad rep will approve this entry, do not process this entry at this time.</div>';
      } else if ($row['RequiresApproval'] && $row['Approved']) {
        $content .= '<p>This entry has been approved by a rep</p>';
      }

      // If the entry has been processes, show a ticked checkbox
      $content .= "
      <div class=\"form-check\">
        <input type=\"checkbox\" value=\"1\" ";
      if ($row['EntryProcessed'] == 1) {
        $content .= ' checked ';
      }
      $content .= " data-button-action=\"mark-processed\" class=\"form-check-input\" id=\"processedEntry-" . $row['EntryID'] . "\">
        <label class=\"form-check-label\" for=\"processedEntry-" . $row['EntryID'] . "\">Processed?</label>
      </div>";

      // If the entry has been processes, show a ticked checkbox
      $content .= "
      <div class=\"form-check\">
        <input type=\"checkbox\" value=\"1\" ";
      if ($row['Charged'] || $row['PaymentID'] != null) {
        $content .= ' checked ';
      }
      if ($row['StripePayment'] && $row['Charged'] || $row['PaymentID'] != null) {
        $content .= ' disabled ';
      }
      $amount = "0.00";
      try {
        $amount = (string) (\Brick\Math\BigDecimal::of((string) $row['FeeToPay'])->toScale(2));
      } catch (Exception $e) {
        $amount = "UNKNOWN";
      }
      $content .= " data-button-action=\"mark-paid\" class=\"form-check-input\" id=\"chargedEntry-" . $row['EntryID'] . "\">
        <label class=\"form-check-label\" for=\"chargedEntry-" . $row['EntryID'] . "\">Paid? (&pound;" . htmlspecialchars($amount) . ")</label>
      </div>";

      $content .= '</td>';

      // End the row
      $content .= "</tr>";
    }
    $content .= '</tbody></table>';

    // Output to browser for AJAX
    if ($count > 0) {
      echo $content;
    } else {
      echo '<div class="alert alert-warning"><strong>We could not find any entries matching that search</strong> <br>Try another search by selecting a new gala or changing the surname you searched for</div>';
    }
  }
} else {
  halt(404);
}
