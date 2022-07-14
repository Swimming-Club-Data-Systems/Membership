<?php

$date = null;

$markdown = new ParsedownForMembership();
$markdown->setSafeMode(false);

$redirectIndex = false;

$path = ltrim($this[0], '/');
$file = file_get_contents(BASE_PATH . 'help/' . $path . '.md');
$date = filemtime(BASE_PATH . 'help/' . $path . '.md');

if ($file === false) {
  // Redirect to this path with /index.md
  $file = file_get_contents(BASE_PATH . 'help/' . $path . '/index.md');
  $date = filemtime(BASE_PATH . 'help/' . $path . '/index.md');

  if ($file) {
    $redirectIndex = true;
  }
}

if ($file === false) {
  halt(404);
}

// pre(app('request'));

if ($redirectIndex) {
  // http_response_code(302);
  // header('location: ' . );
  // return;
}

if ($date != null) {
  $date = new DateTime('@' . $date, new DateTimeZone('UTC'));
  $date->setTimezone(new DateTimeZone('Europe/London'));
}

$pagetitle = "Help and Support Documentation";

$markdownOutput = $markdown->text($file);
$headings = $markdown->getHeadings();

if (sizeof($headings) > 0) {
  $pagetitle = htmlspecialchars($headings[0]);
}

include BASE_PATH . 'views/header.php';

?>

<main class="mt-n3">

  <div class="bg-dark py-3 mb-4 text-white">
    <div class="container-xl">
      <div class="row">
        <div class="col-lg-8 col-md-10">
          <h1><?=htmlspecialchars($pagetitle)?></h1>
          <?php if (false) { ?>
          <p class="lead mb-0">Last modified at <?=$date->format('H:i \o\\n j F Y')?></p>
          <?php } else { ?>
          <p class="lead mb-0">Help and support documentation</p>
          <?php } ?>
        </div>
      </div>
    </div>
  </div>

  <div class="container-xl">
    <div class="row">
      <div class="col-lg-8 col-md-10">
        <main class="mb-5">
          <div>
            <?= $markdownOutput ?>
          </div>
        </main>

        <!--
        <aside class="cell">
          <h2>Onward Content</h2>
        </aside>
        -->
      </div>
    </div>
  </div>

</main>

<?php

$footer = new \SCDS\Footer();
$footer->render();