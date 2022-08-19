<?php

if (!isset($_POST["action"])) {
	halt(404);
}

$db = app()->db;
$tenant = app()->tenant;

$access = $_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'];
$count = 0;

// A function is used to produce the View/Edit and Add Sections Stuff
// This is because we will call it when a squad is selected, and after a session is added

$user = app()->user;

function sessionManagement($squadID, $old = null)
{
	ob_start();
	$db = app()->db;
	$tenant = app()->tenant;
	$output = $content = $modals = "";

	$date = new DateTime('now', new DateTimeZone('Europe/London'));

	$getSessions = $db->prepare("SELECT * FROM ((`sessions` INNER JOIN sessionsSquads ON sessions.SessionID = sessionsSquads.Session) INNER JOIN sessionsVenues ON sessions.VenueID = sessionsVenues.VenueID) WHERE `sessionsSquads`.`Squad` = :squad AND sessions.Tenant = :tenant AND (ISNULL(sessions.DisplayFrom) OR (sessions.DisplayFrom <= :today)) AND (ISNULL(sessions.DisplayUntil) OR (sessions.DisplayUntil >= :today)) ORDER BY `SessionDay` ASC, `StartTime` ASC");
	$getSessions->execute([
		'squad' => $squadID,
		'tenant' => $tenant->getId(),
		'today' => $date->format('Y-m-d'),
	]);

	$getFutureSessions = $db->prepare("SELECT * FROM ((`sessions` INNER JOIN sessionsSquads ON sessions.SessionID = sessionsSquads.Session) INNER JOIN sessionsVenues ON sessions.VenueID = sessionsVenues.VenueID) WHERE `sessionsSquads`.`Squad` = :squad AND sessions.Tenant = :tenant AND sessions.DisplayFrom > :today AND sessions.DisplayUntil > :today ORDER BY sessions.DisplayFrom ASC, `StartTime` ASC");
	$getFutureSessions->execute([
		'squad' => $squadID,
		'tenant' => $tenant->getId(),
		'today' => $date->format('Y-m-d'),
	]);

	$venues = $db->prepare("SELECT VenueName `name`, VenueID id FROM `sessionsVenues` WHERE Tenant = ? ORDER BY VenueName ASC");
	$venues->execute([
		$tenant->getId()
	]);

	$row = $getSessions->fetch(PDO::FETCH_ASSOC);

?>

	<div class="row">
		<div class="col-md-6">
			<div class="card mb-3">
				<div class="card-body">
					<h2 class="card-title">Sessions</h2>
					<?php if ($row != null) { ?>
						<p class="card-text">
							Sessions are ordered by day of week and time
						</p>
				</div>
				<ul class="list-group list-group-flush">
					<?php
						do {

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
							$interval = $datetime1->diff($datetime2);

							$oneOff = $row['DisplayFrom'] == $row['DisplayUntil'];
							$startDate = new DateTime($row['DisplayFrom'], new DateTimeZone('Europe/London'));
							$endDate = new DateTime($row['DisplayUntil'], new DateTimeZone('Europe/London'));

					?>
						<li class="list-group-item">
							<p class="mb-0">
								<a data-bs-toggle="modal" href="#sessionModal<?= $row['SessionID'] ?>">
									<strong class="text-gray-dark">
										<?= htmlspecialchars($row['SessionName']) ?>, <?= $dayText ?> at <?= htmlspecialchars($datetime1->format("H:i")) ?>
									</strong>
								</a>
							</p>

							<p class="mb-0">
								<?= htmlspecialchars($row['VenueName']) ?>
							</p>

							<p class="mb-0">
								<em><?php if ($oneOff) { ?>One-off session on <?= htmlspecialchars($startDate->format('j F Y')) ?><?php } else { ?>Recurring weekly until <?= htmlspecialchars($endDate->format('j F Y')) ?><?php } ?></em>
							</p>
						</li>

					<?php
							$modals .= '
			<!-- Modal -->
			<div class="modal fade" id="sessionModal' . $row['SessionID'] . '" tabindex="-1" role="dialog" aria-labelledby="sessionModalTitle' . $row['SessionID'] . '" aria-hidden="true">
				<div class="modal-dialog modal-dialog-centered" role="document">
					<div class="modal-content">
						<div class="modal-header">
							<h5 class="modal-title" id="sessionModalTitle' . $row['SessionID'] . '">' . htmlspecialchars($row['SessionName']) . ', ' . $dayText . ' at ' . $row['StartTime'] . '</h5>
							<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
								
							</button>
						</div>
						<div class="modal-body">
							<dl>
								<dt>Session Name</dt>
								<dd>' . htmlspecialchars($row['SessionName']) . '</dd>
								<dt>Include in attendance calculations</dt>';
							if ($row['ForAllMembers']) {
								$modals .= '<dd>This session is included in attendance calculations</dd>';
							} else {
								$modals .= '<dd>This session is <strong>not included</strong> in attendance calculations</dd>';
							}
							$modals .= '<dt>Venue</dt>
								<dd>' . htmlspecialchars($row['VenueName']) . '</dd>
								<dt>Start Time</dt>
								<dd>' . htmlspecialchars($datetime1->format("H:i")) . '</dd>
								<dt>Finish Time</dt>
								<dd>' . htmlspecialchars($datetime2->format("H:i")) . '</dd>
								<dt>Session Duration</dt>';
							$modals .= '
								<dd>' . $interval->format('%h hours %I minutes') . '</dd>
								<dt>Display Until</dt>
								<dd>';
							if ($row['DisplayUntil'] != null) {
								$modals .= (new DateTime($row['DisplayUntil']))->format("j F Y");
							} else {
								$modals .= "Not set";
							}
							$modals .= '
								<a class="btn btn-dark-l btn-outline-light-d" href="sessions/' . $row['SessionID'] . '">Edit End Date</a></dd>
							</dl>
							<strong>You can\'t edit a session once it has been created</strong>  <br>Sessions are immutable. This is because swimmers may be marked as present at a session in the past, changing the session in any way, such as altering the start or finish time would distort the attendance records. Instead, set a DisplayUntil date for the session, after which it will not appear in the register, but will still be visible in attendance history
						</div>
					</div>
				</div>
			</div>';
						} while ($row = $getSessions->fetch(PDO::FETCH_ASSOC)); ?>
				</ul>
			<?php
					} else { ?>
				<div class="alert alert-warning mb-0">
					There aren't any sessions for this squad yet. Try adding one
				</div>
			</div>
		<?php } ?>
		</div>
	</div>

	<?php $row = $getFutureSessions->fetch(PDO::FETCH_ASSOC); ?>

	<div class="col-md-6">
		<div class="card mb-3">
			<div class="card-body">
				<h2 class="card-title">Future Sessions</h2>
				<?php if ($row != null) { ?>
					<p class="card-text">
						Sessions starting in the future for this squad, ordered by start date and time
					</p>
			</div>
			<ul class="list-group list-group-flush">
				<?php
					do {

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
						$interval = $datetime1->diff($datetime2);

						$oneOff = $row['DisplayFrom'] == $row['DisplayUntil'];
						$startDate = new DateTime($row['DisplayFrom'], new DateTimeZone('Europe/London'));
						$endDate = new DateTime($row['DisplayUntil'], new DateTimeZone('Europe/London'));

				?>
					<li class="list-group-item">
						<p class="mb-0">
							<a data-bs-toggle="modal" href="#sessionModal<?= $row['SessionID'] ?>">
								<strong class="text-gray-dark">
									<?= htmlspecialchars($row['SessionName']) ?>, <?= $dayText ?> at <?= htmlspecialchars($datetime1->format("H:i")) ?>
								</strong>
							</a>
						</p>

						<p class="mb-0">
							<?= htmlspecialchars($row['VenueName']) ?>
						</p>

						<p class="mb-0">
							<em><?php if ($oneOff) { ?>One-off session on <?= htmlspecialchars($startDate->format('j F Y')) ?><?php } else { ?>From <?= htmlspecialchars($startDate->format('j F Y')) ?> until <?= htmlspecialchars($endDate->format('j F Y')) ?><?php } ?></em>
						</p>
					</li>

				<?php
						$modals .= '
			<!-- Modal -->
			<div class="modal fade" id="sessionModal' . $row['SessionID'] . '" tabindex="-1" role="dialog" aria-labelledby="sessionModalTitle' . $row['SessionID'] . '" aria-hidden="true">
				<div class="modal-dialog modal-dialog-centered" role="document">
					<div class="modal-content">
						<div class="modal-header">
							<h5 class="modal-title" id="sessionModalTitle' . $row['SessionID'] . '">' . htmlspecialchars($row['SessionName']) . ', ' . $dayText . ' at ' . $row['StartTime'] . '</h5>
							<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
								
							</button>
						</div>
						<div class="modal-body">
							<dl>
								<dt>Session Name</dt>
								<dd>' . htmlspecialchars($row['SessionName']) . '</dd>
								<dt>Include in attendance calculations</dt>';
						if ($row['ForAllMembers']) {
							$modals .= '<dd>This session is included in attendance calculations</dd>';
						} else {
							$modals .= '<dd>This session is <strong>not included</strong> in attendance calculations</dd>';
						}
						$modals .= '<dt>Venue</dt>
								<dd>' . htmlspecialchars($row['VenueName']) . '</dd>
								<dt>Start Time</dt>
								<dd>' . htmlspecialchars($datetime1->format("H:i")) . '</dd>
								<dt>Finish Time</dt>
								<dd>' . htmlspecialchars($datetime2->format("H:i")) . '</dd>
								<dt>Session Duration</dt>';
						$modals .= '
								<dd>' . $interval->format('%h hours %I minutes') . '</dd>
								<dt>Display Until</dt>
								<dd>';
						if ($row['DisplayUntil'] != null) {
							$modals .= (new DateTime($row['DisplayUntil']))->format("j F Y");
						} else {
							$modals .= "Not set";
						}
						$modals .= '
								<a class="btn btn-dark-l btn-outline-light-d" href="sessions/' . $row['SessionID'] . '">Edit End Date</a></dd>
							</dl>
							<strong>You can\'t edit a session once it has been created</strong>  <br>Sessions are immutable. This is because swimmers may be marked as present at a session in the past, changing the session in any way, such as altering the start or finish time would distort the attendance records. Instead, set a DisplayUntil date for the session, after which it will not appear in the register, but will still be visible in attendance history
						</div>
					</div>
				</div>
			</div>';
					} while ($row = $getFutureSessions->fetch(PDO::FETCH_ASSOC)); ?>
			</ul>
		<?php
				} else { ?>
			<div class="alert alert-warning mb-0">
				There aren't any sessions starting in the future for this squad.
			</div>
		</div>
	<?php } ?>
	</div>
	</div>

	<div class="col-md-6 d-none">

		<div class="card mb-3">
			<div class="card-body">
				<h2>Add Session</h2>

				<div id="status-message"></div>

				<div class="mb-3">
					<label class="form-label" for="newSessionName">Session Name</label>
					<input type="text" class="form-control" name="newSessionName" id="newSessionName" placeholder="Name">
					<small id="newSessionStartDateHelp" class="form-text text-muted">
						e.g. <em>Swimming</em>, <em>Land Training</em>
					</small>
				</div>
				<div class="row">
					<div class="col">
						<div class="mb-3">
							<label class="form-label" for="newSessionDay">Session Day</label>
							<select class="form-select" name="newSessionDay" id="newSessionDay">
								<option value="9" selected>Select a Day</option>
								<option value="0">Sunday</option>
								<option value="1">Monday</option>
								<option value="2">Tuesday</option>
								<option value="3">Wednesday</option>
								<option value="4">Thursday</option>
								<option value="5">Friday</option>
								<option value="6">Saturday</option>
							</select>
						</div>
					</div>
					<div class="col">
						<div class="mb-3">
							<label class="form-label" for="newSessionVenue">Session Venue</label>
							<select class="form-select" name="newSessionVenue" id="newSessionVenue">
								<option selected value="0">Select a Venue</option>
								<?php while ($venue = $venues->fetch(PDO::FETCH_ASSOC)) { ?>
									<option value="<?= $venue['id'] ?>">
										<?= htmlspecialchars($venue['name']) ?>
									</option>
								<?php } ?>

							</select>
						</div>
					</div>
				</div>
				<div class="mb-3">
					<label class="form-label" for="newSessionMS">Include in attendance count</label>
					<div class="form-check">
						<input type="radio" id="newSessionMSYes" name="newSessionMS" class="form-check-input" value="1" checked>
						<label class="form-check-label" for="newSessionMSYes">
							Yes, the session is for the full squad
						</label>
					</div>
					<div class="form-check">
						<input type="radio" id="newSessionMSNo" name="newSessionMS" class="form-check-input" value="0">
						<label class="form-check-label" for="newSessionMSNo">
							No, this session is only for selected swimmers
						</label>
					</div>
				</div>
				<div class="row">
					<div class="col">
						<div class="mb-3">
							<label class="form-label" for="newSessionStartTime">Start Time</label>
							<input type="time" class="form-control" name="newSessionStartTime" id="newSessionStartTime" placeholder="0" value="18:00">
							<small id="newSessionStartTimeHelp" class="form-text text-muted">
								Make sure to use 24 Hour Time
							</small>
						</div>
					</div>
					<div class="col">
						<div class="mb-3">
							<label class="form-label" for="newSessionEndTime">End Time</label>
							<input type="time" class="form-control" name="newSessionEndTime" id="newSessionEndTime" placeholder="0" value="18:30">
							<small id="newSessionEndTimeHelp" class="form-text text-muted">
								Make sure to use 24 Hour Time
							</small>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col">
						<div class="mb-3">
							<label class="form-label" for="newSessionStartDate">Show From</label>
							<input type="date" aria-labelledby="newSessionStartDateHelp" class="form-control" name="newSessionStartDate" id="newSessionStartDate" placeholder="<?= date("Y-m-d") ?>" value="<?= date("Y-m-d") ?>">
							<small id="newSessionStartDateHelp" class="form-text text-muted">
								The date from which the session will appear in the registers
							</small>
						</div>
					</div>
					<div class="col">
						<div class="mb-3">
							<label class="form-label" for="newSessionEndDate">Show Until</label>
							<input type="date" aria-labelledby="newSessionStartDateHelp" class="form-control" name="newSessionEndDate" id="newSessionEndDate" placeholder="<?= date("Y-m-d", strtotime('+1 year')) ?>" value="<?= date("Y-m-d", strtotime('+1 year')) ?>">
							<small id="newSessionEndDateHelp" class="form-text text-muted">
								If you know when this session will stop running, enter the last date here
							</small>
						</div>
					</div>
				</div>
				<p class="mb-0"><button class="btn btn-success" id="newSessionAction" onclick="addSession();">Add Session</button>
				</p>

			</div>
		</div>
	</div>
	</div>

<?php

	$html = ob_get_clean();
	return $html . $modals;
}

if ($user->hasPermissions(['Admin', 'Coach', 'Committee'])) {
	// Get the action to work out what we're going to do
	if (isset($_POST["action"]) && $_POST["action"] == "getSessions" && isset($_POST["squadID"]) && $_POST["squadID"] != null) {
		echo sessionManagement($_POST["squadID"]);
	}
} else {
	halt(404);
}
