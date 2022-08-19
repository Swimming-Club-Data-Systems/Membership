<?php

http_response_code(500);

if (isset($e)) {
  try {
    reportError($e);
  } catch (Exception | Error $e) {
    // Ignore
  }
}

?>
<!doctype html>
<html lang="en">

<head>
  <!-- Required meta tags -->
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">

  <link rel="icon" sizes="800x800" href="/img/corporate/scds.png">

  <title>Service Unavailable</title>
</head>

<body style="min-height:100vh;">
  <div class="bg-light mb-3">
    <div class="container-xl">
      <div class="row">
        <div class="col-lg-8">
          <a href="https://www.myswimmingclub.uk"><img src="/img/corporate/scds.png" alt="SCDS Logo" style="max-width: 100px;"></a>
        </div>
      </div>
    </div>
  </div>
  <div class="container-xl">
    <div class="row">
      <div class="col-lg-8">
        <h1 class="mb-5">This service is currently unavailable</h1>

        <p>Please try the following:</p>

        <ul>
          <li>If this is the first time you have seen this error, try reloading the page <strong>once</strong>.</li>
          <ul>
            <li>If you are asked to confirm form resubmission, please abort and return to your tenant's homepage.</li>
          </ul>
          <li>If you see this error again, please contact Swimming Club Data Systems for support.</li>
        </ul>

        <p>
          <a href="mailto:support@myswimmingclub.uk" title="Support Hotline">Email SCDS</a> or <a href="tel:+441912494320">call SCDS on +44 191 249 4320</a> for help and support.
        </p>

        <hr>

        <p>HTTP Error 500 - Internal Server Error (Last Resort Type).</p>

        <p>&copy; Swimming Club Data Systems</p>
      </div>
    </div>
  </div>

  <!-- Optional JavaScript -->
  <!-- jQuery first, then Popper.js, then Bootstrap JS -->
  <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
</body>

</html>