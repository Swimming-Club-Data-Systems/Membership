<?php

$stylesheet = autoUrl(getCompiledAsset('scds.css'));

?>
<!DOCTYPE html>
<html lang="en-gb">

<head>
  <meta charset="utf-8">
  <title>Attention required</title>
  <meta name="description" content="SCDS Membership helps UK aquatics clubs run more efficiently.">
  <meta name="viewport" content="width=device-width, initial-scale=1.0,
    user-scalable=no,maximum-scale=1">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="apple-mobile-web-app-title" content="SCDS">
  <meta name="format-detection" content="telephone=no">
  <meta name="googlebot" content="noarchive, nosnippet">
  <meta name="X-CLSW-System" content="SCDS">
  <meta name="og:type" content="website">
  <meta name="og:locale" content="en_GB">
  <meta name="og:site_name" content="SCDS">
  <link rel="manifest" href="<?= autoUrl("manifest.webmanifest") ?>">
  <meta name="X-SCDS-Membership-Tracking" content="no">
  <link rel="stylesheet preload" href="<?= htmlspecialchars(autoUrl(getCompiledAsset('scds.css'))) ?>">
  <meta name="color-scheme" content="dark light">
  <!-- <link rel="stylesheet preload" href="<?= htmlspecialchars($stylesheetDarkMode) ?>" media="(prefers-color-scheme: dark)"> -->

  <!-- Generic icon -->
  <link rel="icon" href="<?= htmlspecialchars(autoUrl("img/corporate/scds.png")) ?>">

</head>

<body>

  <div class="have-full-height">
    <div class="min-vh-100 mb-n3 overflow-auto">
      <div class="bg-light">
        <div class="container">
          <div class="row justify-content-center py-5">
            <div class="col-lg-8 col-md-10">

              <img src="<?= htmlspecialchars(autoUrl('public/img/corporate/scds.png')) ?>" height="75" width="75" alt="" class="img-fluid d-block mx-auto">

            </div>
          </div>
        </div>
      </div>

      <div class="container">
        <div class="row justify-content-center py-5">
          <div class="col-lg-8 col-md-10">

            <h1>Sorry, you have been blocked</h1>

            <p class="lead">
              You are unable to visit this website.
            </p>

            <div class="row">
              <div class="col-md">
                <h2>
                  Why have I been blocked?
                </h2>

                <p>
                  This website is using a security service to protect itself from online attacks. The action you just performed triggered the security solution. There are several actions that could trigger this block including submitting a certain word or phrase, a SQL command or malformed data.
                </p>
              </div>

              <div class="col-md">
                <h2>
                  What can I do to resolve this?
                </h2>

                <p>
                  You can email your club to let them know you were blocked. Please include what you were doing when this page came up and the Cloudflare Ray ID found at the bottom of this page.
                </p>
              </div>
            </div>

            <p>
              Cloudflare Ray ID: ::RAY_ID::
            </p>

            <p>
              Location: ::GEO::
            </p>

            <footer class="small text-muted">
              &copy; Swimming Club Data Systems
            </footer>

          </div>
        </div>
      </div>
    </div>

  </div>

</body>

</html>