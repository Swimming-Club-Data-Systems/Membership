<?php

http_response_code(404);
$pagetitle = "Error 404 - Page not found";

include BASE_PATH . "views/root/header.php";

?>

<div class="container-xl">
  <div class="row">
    <div class="col-lg-8">
      <h1>The page you requested cannot be found</h1>
      <p class="lead">The page you are looking for might have been removed, had its name changed, or is temporarily
        unavailable. You may also not be authorised to view the page.</p>

      <hr>
      <p>Please try the following:</p>
      <ul>
        <li>Make sure that the Web site address displayed in the address bar of your browser is spelled and formatted
          correctly.</li>
        <li>If you reached this page by clicking a link, contact the Web site administrator to alert them that the link
          is incorrectly formatted.</li>
        <li>Click the <a href="javascript:history.back(1)">Back</a> button to try another link.</li>
      </ul>
      <p>HTTP Error 404 - File or directory not found.</p>
      <hr>

      <p class="mt-2"><a href="mailto:support@myswimmingclub.uk" title="Support Hotline">Email us</a> or <a href="tel:+441912494320">call us on +44 191 249 4320</a> for help and support if the issue persists.</p>
    </div>
  </div>
</div>

<?php $footer = new \SCDS\RootFooter();
$footer->render(); ?>