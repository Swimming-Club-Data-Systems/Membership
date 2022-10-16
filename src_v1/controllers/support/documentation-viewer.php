<?php

$date = null;

$markdown = new ParsedownForMembership();
$markdown->setSafeMode(false);

$branch = 'main';
if (defined('SOFTWARE_BRANCH')) {
  $branch = SOFTWARE_BRANCH;
}

$path = trim($this[0], '/');

// This will do for now
$path = str_replace('.md', '', $path);

$file = null;
$pathToFile = BASE_PATH . 'help/' . $path;
$pathToFileExtension = $pathToFile;
$indexRedirect = false;

if (file_exists($pathToFile . '.md') && str_contains(mime_content_type($pathToFile . '.md'), 'text/')) {
  $pathToFileExtension = $pathToFile . '.md';
  $file = file_get_contents($pathToFileExtension);
} else if (file_exists($pathToFile . '/index.md') && str_contains(mime_content_type($pathToFile . '/index.md'), 'text/')) {
  $pathToFileExtension = $pathToFile . '/index.md';
  $file = file_get_contents($pathToFileExtension);

  if ($file) {
    $indexRedirect = true;
  }
} else if (file_exists($pathToFile)) {
  // File loader
  // $file = file_get_contents($pathToFile);
  $mime = mime_content_type($pathToFile);
  header('content-type: ' . $mime);
  header('age: 0');
  header('cache-control: max-age=31536000, private');
  header('pragma: public');
  if ($mime == 'application/pdf' || $mime == 'text/html' || $mime == 'text/css' || $mime == 'application/javascript' || mb_substr($mime, 0, mb_strlen('image')) === 'image') {
    header('content-disposition: inline');
  } else {
    header('content-disposition: attachment; filename="' . basename($pathToFile) . '"');
  }
  header("service-worker-allowed: " . autoUrl(""));
  readfile($pathToFile);
  return;
}

$helpPath = '/src/' . str_replace(BASE_PATH, '', $pathToFileExtension);

$toc = SCDS\Support\TOC::find($pathToFileExtension);
$breadcrumb = SCDS\Support\Breadcrumb::find($pathToFileExtension);

if (!$file) {
  halt(404);
} else if ($indexRedirect) {
  http_response_code(302);
  header("location: " . autoUrl(trim(app('request')->path, '/') . '/index.md'));
  return;
}
if ($date != null) {
  $date = new DateTime('@' . $date, new DateTimeZone('UTC'));
  $date->setTimezone(new DateTimeZone('Europe/London'));
}

$pagetitle = "Membership System Help - SCDS Membership";

$markdownOutput = $markdown->text($file);
$headings = $markdown->getHeadings();

$title = 'Untitled help article';
if (sizeof($headings) > 0) {
  $title = trim($headings[0], " \t\n\r\0\x0B,.");
  $pagetitle = htmlspecialchars($title . ' - Help - SCDS Membership');
}

include BASE_PATH . "views/root/header.php";

?>

<div class="">

  <div class="container-xl">
    <div class="row align-items-center mb-3">
      <div class="col">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb my-0">
            <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl("help-and-support")) ?>">Support</a></li>
            <!-- <li class="breadcrumb-item active" aria-current="page">PLACEHOLDER</li> -->
          </ol>
        </nav>
      </div>
      <div class="col-auto">
        <div class="btn-group">
          <a target="_blank" href="<?= htmlspecialchars("https://github.com/Swimming-Club-Data-Systems/Membership/blob/main$helpPath") ?>" class="btn btn-light btn-lg"><i class="fa fa-pencil" aria-hidden="true"></i> Edit</a>
          <button class="btn btn-light btn-lg d-none"><i class="fa fa-share" aria-hidden="true"></i> Share</button>
        </div>
      </div>
    </div>
  </div>

  <div class="container-xl">
    <div class="bg-scds rounded p-4 mb-4 text-white">
      <div class="row">
        <div class="col">
          <h1><?= htmlspecialchars($title) ?></h1>
          <p class="lead mb-0">Help and support documentation</p>
        </div>
      </div>
    </div>
  </div>

  <div class="container-xl">
    <div class="row justify-content-between">
      <div class="col-lg-3 border-end">
      <?php if ($toc && $data = $toc->getYamlData()) { ?>
      <?= $toc->render() ?>
      <?php } ?>
      </div>
      <div class="col">
        <main class="mb-5">
          <div>
            <?= $markdownOutput ?>
          </div>
        </main>
        <aside class="">
          <h2>Feedback</h2>
          <p>
            <a target="_blank" href="<?= htmlspecialchars("https://github.com/Swimming-Club-Data-Systems/Membership/blob/$branch$helpPath") ?>">Submit and view feedback for this page on GitHub.</a>
          </p>
        </aside>
      </div>
      <?php if (sizeof($headings) > 1) { ?>
        <nav class="col-lg-2 border-start">
          <ul class="list-unstyled">
            <?php for ($i = 1; $i < sizeof($headings); $i++) {
              $heading = $headings[$i]; ?>
              <li><a href="<?= htmlspecialchars('#' . mb_strtolower(preg_replace('@[^0-9a-z\.]+@i', '-', $heading))) ?>"><?= htmlspecialchars($heading) ?></a></li>
            <?php } ?>
          </ul>
        </nav>
      <?php } ?>
    </div>
  </div>

</div>

<?php

$footer = new \SCDS\RootFooter();
$footer->render();
