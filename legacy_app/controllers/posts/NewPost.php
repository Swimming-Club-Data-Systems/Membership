<?php

$date = new DateTime('now', new DateTimeZone('Europe/London'));

include 'support/PostTypes.php';
$pagetitle = "New Post";
include BASE_PATH . "views/header.php";
include BASE_PATH . "views/postsMenu.php";

?>

<div class="container-xl">
	<form method="post">
		<div class="row">
			<div class="col-md-8">
				<div>
					<h1>New Post</h1>
					<div class="mb-3">
						<label class="form-label" for="title">Title</label>
						<input type="text" class="form-control" name="title" id="title" placeholder="Post Title" autocomplete="off" <?php if ($people) { ?>value="<?= getUserName($_SESSION['TENANT-' . app()->tenant->getId()]['UserID']) ?>" readonly <?php } ?>>
					</div>

					<div class="mb-3 mb-0">
						<label class="form-label" for="content">Content</label>
						<textarea class="form-control auto-grow font-monospace" id="content" name="content" oninput="autoGrow(this)">
			      </textarea>
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
						<button class="btn btn-secondary" id="submit" value="submitted" type="submit">
							Publish
						</button>
					</p>
					<p class="mb-0">We will publish this immediately.</p>
				</div>

				<div class="card card-body mb-3">
					<h3>Meta</h3>
					<div class="mb-3">
						<label class="form-label" for="path">Path</label>
						<p class="small mb-1">
							<?= htmlspecialchars(autoUrl("pages/")) ?>
						</p>
						<input type="text" class="form-control" name="path" id="path" placeholder="Leave blank to use Post ID" autocomplete="off">
					</div>
					<div class="mb-3">
						<label class="form-label" for="date">Date and time</label>
						<input type="datetime-local" class="form-control" name="date" id="date" value="<?= htmlspecialchars($date->format("c")) ?>">
					</div>
					<div class="mb-3">
						<label class="form-label" for="type">Type</label>
						<select class="form-select" name="type">
							<?php for ($i = 0; $i < sizeof($post_types); $i++) { ?>
								<option value="<?= $post_types[$i]['value'] ?>">
									<?= $post_types[$i]['description'] ?>
								</option>
							<?php } ?>
						</select>
					</div>
					<div class="mb-3 mb-0">
						<label class="form-label" for="mime">MIME Type</label>
						<select class="form-select" name="mime">
							<option value="text/html">text/html</option>
							<option value="text/plain">text/plain</option>
							<option value="text/markdown">text/markdown</option>
						</select>
					</div>
				</div>

				<div class="card card-body">
					<h3>SEO</h3>
					<div class="mb-3 mb-0">
						<label class="form-label" for="excerpt">Excerpt</label> <textarea class="form-control" name="excerpt" id="excerpt" placeholder="This is about" autocomplete="off"></textarea>
					</div>
				</div>
			</div>
		</div>
	</form>
</div>

<?php $footer = new \SCDS\Footer();
$footer->addJS("js/posts/PostEditor.js");
$footer->render();
