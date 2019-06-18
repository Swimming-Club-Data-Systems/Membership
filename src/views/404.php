<?php

$json = file_get_contents('https://opentdb.com/api.php?amount=1');
$trivia = json_decode($json)->results[0];

$possible_answers = $trivia->incorrect_answers;
$possible_answers[] = $trivia->correct_answer;

if (sizeof($possible_answers) > 2) {
  sort($possible_answers, SORT_STRING);
}

http_response_code(404);
$pagetitle = "Error 404 - Page not found";
global $currentUser;
if ($currentUser == null) {
	include BASE_PATH . "views/head.php";
} else {
	include BASE_PATH . "views/header.php";
}
?>

<div class="container">
	<h1>The page you requested cannot be found</h1>
	<p class="lead">The page you are looking for might have been removed, had its name changed, or is temporarily unavailable. You may also not be authorised to view the page.</p>
  <hr>

  <!-- Trivia Section Woo -->
  <aside class="cell pb-0">
    <h2 class="h4 mb-0">Trivia</h2>
    <p class="small text-muted mb-2"><?=$trivia->category?></p>
    <table class="table table-borderless table-sm">
      <tr>
        <td><span class="mono">Q:</span></td>
        <td><strong><?=$trivia->question?></strong></td>
      </tr>
    <?php if (sizeof($possible_answers) > 2) { ?>
    <?php for ($i = 0; $i < sizeof($possible_answers); $i++) { ?>
      <tr>
        <td></td>
        <td><?=$possible_answers[$i]?></td>
      </tr>
    <?php } ?>
    <?php } ?>
    </table>

    <p class="mb-0">
      <button class="btn btn-primary" type="button" data-toggle="collapse" data-target="#page-404-show-answer" aria-expanded="false" aria-controls="page-404-show-answer">
        Reveal answer
      </button>
    </p>
    <div class="pb-3">
      <div class="collapse" id="page-404-show-answer">
        <div class="card card-body mt-3">
          <em><?=$trivia->correct_answer?></em>
        </div>
      </div>
    </div>
  </aside>
  <!-- Trivia API by opentdb.com -->

	<hr>
	<p>Please try the following:</p>
	<ul>
		<li>Make sure that the Web site address displayed in the address bar of your browser is spelled and formatted correctly.</li>
		<li>If you reached this page by clicking a link, contact the Web site administrator to alert them that the link is incorrectly formatted.</li>
		<li>Click the <a href="javascript:history.back(1)">Back</a> button to try another link.</li>
	</ul>
	<p>HTTP Error 404 - File or directory not found.</p>
	<hr>
	<p class="mt-2">Contact our <a href="mailto:support@chesterlestreetasc.co.uk" title="Support Hotline">support address</a> if the issue persists.</p>
</div>

<?php include BASE_PATH . "views/footer.php"; ?>
