<?php

$db = app()->db;
$tenant = app()->tenant;

$query = null;

$exit_edit = true;

$sql = "SELECT * FROM `posts` WHERE `ID` = ? AND Tenant = ?";
try {
	$query = $db->prepare($sql);
	$query->execute([
		$id,
		$tenant->getId()
	]);
} catch (PDOException $e) {
	halt(404);
}
$row = $query->fetch(PDO::FETCH_ASSOC);

if (!$row) {
	halt(404);
}

$date = new DateTime($row['Date'], new DateTimeZone('UTC'));
$date->setTimezone(new DateTimeZone('Europe/London'));

include 'support/PostTypes.php';
include 'support/MimeTypes.php';

$pagetitle = "Editing " . $row['Title'];

include BASE_PATH . "views/header.php";
include BASE_PATH . "views/postsMenu.php";

?>

<div class="container-xl">
	<form method="post">
		<div class="row">
			<div class="col-md-8">
				<div>
					<h1>Editing <?= htmlspecialchars($row['Title']) ?></h1>
					<div class="mb-3">
						<label class="form-label" for="title">Title</label>
						<input type="text" class="form-control" name="title" id="title" placeholder="Post Title" autocomplete="off" value="<?= htmlentities($row['Title']) ?>">
					</div>

					<div class="mb-3 mb-0">
						<label class="form-label" for="content">Content</label>
						<textarea class="form-control auto-grow font-monospace" id="content" name="content" oninput="autoGrow(this)"><?= htmlspecialchars($row['Content']) ?></textarea>
						<small id="contentHelp" class="form-text text-muted">
							Posts are written in <a href="https://www.markdownguide.org" target="_blank">Markdown</a>. HTML is not allowed for security reasons.
						</small>
					</div>

					<!--<p><button class="btn btn-secondary" id="submit" value="submitted" type="submit">Publish</button></p>-->
				</div>
			</div>
			<div class="col-12 col-md-4">
				<div class="card card-body mb-3">
					<p>
						<button class="btn btn-primary" id="submit" value="submitted" type="submit">
							Save
						</button>
					</p>
					<p class="">We will publish this update immediately.</p>
					<p class="mb-0">
						<?php if ($row['Path'] != "") { ?>
							View now at <a href="<?= htmlspecialchars(autoUrl("pages/" . $row['Path'])) ?>"><?= htmlspecialchars('/pages/' . $row['Path']) ?></a>
						<?php } else { ?>
							View now at <a href="<?= htmlspecialchars(autoUrl("pages/" . $row['ID'])) ?>"><?= htmlspecialchars('/pages/' . $row['ID']) ?></a>
						<?php } ?>
					</p>
				</div>

				<div class="card card-body mb-3">
					<h3>Meta</h3>
					<div class="mb-3">
						<label class="form-label" for="path">Path</label>
						<p class="small mb-1">
							<?= htmlspecialchars(autoUrl("pages/")) ?>
						</p>
						<input type="text" class="form-control" name="path" id="path" placeholder="Leave blank to use Page ID" autocomplete="off" value="<?= htmlentities($row['Path']) ?>">
					</div>
					<div class="mb-3">
						<label class="form-label" for="date">Date</label>
						<input type="datetime-local" class="form-control" name="date" id="date" value="<?= $date->format("c") ?>" disabled>
					</div>
					<div class="mb-3">
						<label class="form-label" for="type">Type</label>
						<select class="form-select" name="type">
							<?php for ($i = 0; $i < sizeof($post_types); $i++) {
								$s = null;
								if ($post_types[$i]['value'] == $row['Type']) {
									$s = "selected";
								} ?>
								<option value="<?= $post_types[$i]['value'] ?>" <?= $s ?>>
									<?= $post_types[$i]['description'] ?>
								</option>
							<?php } ?>
						</select>
					</div>
					<div class="mb-3 mb-0">
						<label class="form-label" for="mime">MIME Type</label>
						<select class="form-select" name="mime">
							<?php for ($i = 0; $i < sizeof($mimes); $i++) {
								$s = null;
								if ($mimes[$i]['value'] == $row['MIME']) {
									$s = "selected";
								} ?>
								<option value="<?= $mimes[$i]['value'] ?>" <?= $s ?>>
									<?= $mimes[$i]['description'] ?>
								</option>
							<?php } ?>
						</select>
					</div>
				</div>

				<div class="card card-body">
					<h3>SEO</h3>
					<div class="mb-3 mb-0">
						<label class="form-label" for="excerpt">Excerpt</label>
						<textarea class="form-control" name="excerpt" id="excerpt" placeholder="This is about" autocomplete="off"><?= htmlentities($row['Excerpt']) ?></textarea>
					</div>
				</div>
			</div>
		</div>
	</form>
</div>

<?php $footer = new \SCDS\Footer();
$footer->addJS("js/posts/PostEditor.js");
$footer->render();
