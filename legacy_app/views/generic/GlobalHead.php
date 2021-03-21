<?php

$tenant = nezamy_app()->tenant;

$stylesheet = "";
try {
  $hash = file_get_contents(BASE_PATH . 'cachebuster.json');
  $hash = json_decode($hash);
  $hash = $hash->resourcesHash;
  $stylesheet = autoUrl('public/compiled/css/generic.' . $hash . '.min.css');
} catch (Exception $e) {
  $stylesheet = autoUrl('public/compiled/css/generic.css');
}

if (getenv('CUSTOM_CSS_PATH')) {
  $stylesheet = getenv('CUSTOM_CSS_PATH');
}

header('Link: <' . $stylesheet . '>; rel=preload; as=style', false);
header('Link: <' . autoUrl("public/css/colour.css") . '>; rel=preload; as=style', false);

$container_class;
if (isset($fluidContainer) && $fluidContainer == true) {
  $container_class = "container-fluid";
} else {
  $container_class = "container";
} ?>
<!DOCTYPE html>
<!--

Copyright Swimming Club Data Systems / Chris Heppell / Chester-le-Street ASC 2016 - 2020.

Bootstrap CSS and JavaScript is Copyright Twitter Inc 2011-2018
jQuery is Copyright jQuery Foundation

Designed by SCDS / Chris Heppell

support@myswimmingclub.uk

https://myswimmingclub.co.uk

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
  <meta name="description" content="Your <?= nezamy_app()->tenant->getKey('CLUB_NAME') ?> Account lets you make gala entries online and gives you access to all your information about your swimmers, including attendance.">
  <meta name="viewport" content="width=device-width, initial-scale=1.0,
    user-scalable=no,maximum-scale=1">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="apple-mobile-web-app-title" content="<?= htmlspecialchars(nezamy_app()->tenant->getKey('CLUB_SHORT_NAME')) ?> Accounts">
  <meta name="format-detection" content="telephone=no">
  <meta name="googlebot" content="noarchive, nosnippet">
  <meta name="X-CLSW-System" content="Membership">
  <meta name="og:type" content="website">
  <meta name="og:locale" content="en_GB">
  <meta name="og:site_name" content="<?= htmlspecialchars(nezamy_app()->tenant->getKey('CLUB_NAME')) ?> Account">
  <link rel="manifest" href="<?= autoUrl("manifest.webmanifest") ?>">
  <meta name="X-SCDS-Membership-Tracking" content="no">
  <script src="https://js.stripe.com/v3/"></script>
  <link rel="stylesheet preload" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:400,400i,700|Roboto+Mono|Merriweather:400,600">
  <link rel="stylesheet preload" href="<?= htmlspecialchars($stylesheet) ?>">
  <link rel="stylesheet preload" href="<?= htmlspecialchars(autoUrl("public/css/colour.css")) ?>">

  <meta property="og:title" content="<?= $pagetitle ?>" />

  <!-- Generic icon first -->
  <?php if ($logos = $tenant->getKey('LOGO_DIR')) { ?>
    <link rel="icon" sizes="196x196" href="<?= htmlspecialchars(autoUrl($logos . 'icon-196x196.png')) ?>">
    <link rel="icon" sizes="192x192" href="<?= htmlspecialchars(autoUrl($logos . 'icon-192x192.png')) ?>">
    <link rel="apple-touch-icon-precomposed" sizes="180x180" href="<?= autoUrl($logos . 'icon-180x180.png') ?>">
    <link rel="apple-touch-icon-precomposed" sizes="167x167" href="<?= autoUrl($logos . 'icon-167x167.png') ?>">
    <link rel="apple-touch-icon-precomposed" sizes="152x152" href="<?= autoUrl($logos . 'icon-152x152.png') ?>">
    <link rel="apple-touch-icon-precomposed" sizes="128x128" href="<?= autoUrl($logos . 'icon-128x128.png') ?>">
    <link rel="apple-touch-icon-precomposed" sizes="114x114" href="<?= autoUrl($logos . 'icon-114x114.png') ?>">
    <link rel="apple-touch-icon-precomposed" sizes="72x72" href="<?= autoUrl($logos . 'icon-72x72.png') ?>">
    <link rel="apple-touch-icon-precomposed" sizes="32x32" href="<?= autoUrl($logos . 'icon-32x32.png') ?>">
    <link rel="apple-touch-icon-precomposed" sizes="196x196" href="<?= autoUrl($logos . 'icon-196x196.png') ?>">
    <meta property="og:image" content="<?= autoUrl($logos . 'logo-512.png') ?>" />
  <?php } else { ?>
    <!-- For iPhone 6 Plus with @3× display: -->
    <link rel="apple-touch-icon-precomposed" sizes="180x180" href="<?= autoUrl("public/img/corporate/icons/apple-touch-icon-180x180.png") ?>">
    <!-- For iPad with @2× display running iOS ≥ 7: -->
    <link rel="apple-touch-icon-precomposed" sizes="152x152" href="<?= autoUrl("public/img/corporate/icons/apple-touch-icon-152x152.png") ?>">
    <!-- For iPad with @2× display running iOS ≤ 6: -->
    <link rel="apple-touch-icon-precomposed" sizes="144x144" href="<?= autoUrl("public/img/corporate/icons/apple-touch-icon-144x144.png") ?>">
    <!-- For iPhone with @2× display running iOS ≥ 7: -->
    <link rel="apple-touch-icon-precomposed" sizes="120x120" href="<?= autoUrl("public/img/corporate/icons/apple-touch-icon-120x120.png") ?>">
    <!-- For iPhone with @2× display running iOS ≤ 6: -->
    <link rel="apple-touch-icon-precomposed" sizes="114x114" href="<?= autoUrl("public/img/corporate/icons/apple-touch-icon-114x114.png") ?>">
    <!-- For the iPad mini and the first- and second-generation iPad (@1× display) on iOS ≥ 7: -->
    <link rel="apple-touch-icon-precomposed" sizes="76x76" href="<?= autoUrl("public/img/corporate/icons/apple-touch-icon-76x76.png") ?>">
    <!-- For the iPad mini and the first- and second-generation iPad (@1× display) on iOS ≤ 6: -->
    <link rel="apple-touch-icon-precomposed" sizes="72x72" href="<?= autoUrl("public/img/corporate/icons/apple-touch-icon-72x72.png") ?>">
    <!-- For non-Retina iPhone, iPod Touch, and Android 2.1+ devices: -->
    <link rel="apple-touch-icon-precomposed" href="<?= autoUrl("public/img/corporate/icons/apple-touch-icon.png") ?>"><!-- 57×57px -->
  <?php } ?>
  <!-- <link rel="mask-icon" href="https://www.chesterlestreetasc.co.uk/wp-content/themes/chester/img/chesterIcon.svg"
    color="#bd0000"> -->
  <script src="https://www.google.com/recaptcha/api.js"></script>

  <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->

</head>