<?php

$stylesheet = "";
try {
  $hash = file_get_contents(BASE_PATH . 'cachebuster.json');
  $hash = json_decode($hash);
  $hash = $hash->resourcesHash;
  $stylesheet = url('/compiled/css/clse.' . $hash . '.min.css');
} catch (Exception $e) {
  $stylesheet = url('/compiled/css/clse.css');
}

header('Link: <' . $stylesheet . '>; rel=preload; as=style');

$container_class;
if (isset($fluidContainer) && $fluidContainer == true) {
  $container_class = "container-fluid";
} else {
  $container_class = "container";
} ?>
<!DOCTYPE html>
<!--

Copyright Chris Heppell & Chester-le-Street ASC 2016 - 2018.
Bootstrap CSS and JavaScript is Copyright Twitter Inc 2011-2018
jQuery v3.1.0 is Copyright jQuery Foundation 2016

Designed by Chris Heppell, www.chrisheppell.uk

Yes! We built this in house. Not many clubs do. We don't cheat.

Chester-le-Street ASC
Swimming Club based in Chester-le-Street, North East England
https://github.com/Chester-le-Street-ASC/

web@chesterlestreetasc.co.uk

https://corporate.myswimmingclub.co.uk

Chester-le-Street ASC is a non profit unincorporated association.

-->
<html lang="en-gb">

<head>
  <meta charset="utf-8">
  <?php if (isset($pagetitle) && ($pagetitle != "" || $pagetitle != null)) { ?>
    <title><?= $pagetitle ?> - <?= htmlspecialchars(nezamy_app()->tenant->getKey('CLUB_NAME')) ?> Membership</title>
  <?php } else { ?>
    <title><?= htmlspecialchars(nezamy_app()->tenant->getKey('CLUB_NAME')) ?> Membership</title>
  <?php } ?>
  <meta name="description" content="Your <?= htmlspecialchars(nezamy_app()->tenant->getKey('CLUB_NAME')) ?> Account lets you make gala entries online and gives you access to all your information about your swimmers, including attendance.">
  <meta name="viewport" content="width=device-width, initial-scale=1.0,
    user-scalable=no,maximum-scale=1">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="apple-mobile-web-app-title" content="CLS ASC Accounts">
  <meta name="format-detection" content="telephone=no">
  <meta name="googlebot" content="noarchive, nosnippet">
  <meta name="X-CLSE-System" content="Membership">
  <meta name="twitter:site" content="@clsasc">
  <meta name="twitter:creator" content="@clsasc">
  <meta name="og:type" content="website">
  <meta name="og:locale" content="en_GB">
  <meta name="og:site_name" content="Chester-le-Street ASC Account">
  <link rel="manifest" href="<?= autoUrl("manifest.webmanifest") ?>">
  <meta name="X-SCDS-Membership-Tracking" content="no">
  <script src="https://js.stripe.com/v3/"></script>
  <link rel="stylesheet preload" href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,400i,600,700|Roboto+Mono|Merriweather:400,600">
  <link rel="stylesheet preload" href="<?= htmlspecialchars($stylesheet) ?>">
  <link rel="icon" sizes="196x196" href="<?= htmlspecialchars(url("/img/touchicons/touch-icon-196x196.png")) ?>">
  <!-- For Chrome for Android: -->
  <link rel="icon" sizes="192x192" href="<?= url("/img/touchicons/touch-icon-192x192.png") ?>">
  <!-- For iPhone 6 Plus with @3× display: -->
  <link rel="apple-touch-icon-precomposed" sizes="180x180" href="<?= url("/img/touchicons/apple-touch-icon-180x180-precomposed.png") ?>">
  <!-- For iPad with @2× display running iOS ≥ 7: -->
  <link rel="apple-touch-icon-precomposed" sizes="152x152" href="<?= url("/img/touchicons/apple-touch-icon-152x152-precomposed.png") ?>">
  <!-- For iPad with @2× display running iOS ≤ 6: -->
  <link rel="apple-touch-icon-precomposed" sizes="144x144" href="<?= url("/img/touchicons/apple-touch-icon-144x144-precomposed.png") ?>">
  <!-- For iPhone with @2× display running iOS ≥ 7: -->
  <link rel="apple-touch-icon-precomposed" sizes="120x120" href="<?= url("/img/touchicons/apple-touch-icon-120x120-precomposed.png") ?>">
  <!-- For iPhone with @2× display running iOS ≤ 6: -->
  <link rel="apple-touch-icon-precomposed" sizes="114x114" href="<?= url("/img/touchicons/apple-touch-icon-114x114-precomposed.png") ?>">
  <!-- For the iPad mini and the first- and second-generation iPad (@1× display) on iOS ≥ 7: -->
  <link rel="apple-touch-icon-precomposed" sizes="76x76" href="<?= url("/img/touchicons/apple-touch-icon-76x76-precomposed.png") ?>">
  <!-- For the iPad mini and the first- and second-generation iPad (@1× display) on iOS ≤ 6: -->
  <link rel="apple-touch-icon-precomposed" sizes="72x72" href="<?= url("/img/touchicons/apple-touch-icon-72x72-precomposed.png") ?>">
  <!-- For non-Retina iPhone, iPod Touch, and Android 2.1+ devices: -->
  <link rel="apple-touch-icon-precomposed" href="<?= url("/img/touchicons/apple-touch-icon-precomposed.png") ?>"><!-- 57×57px -->
  <link rel="mask-icon" href="<?= url("/img/touchicons/icon-mask.svg") ?>" color="#bd0000">
  <script src="https://www.google.com/recaptcha/api.js"></script>

  <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->

</head>