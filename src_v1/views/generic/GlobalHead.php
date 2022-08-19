<?php

$tenant = app()->tenant;

$stylesheet = autoUrl(getCompiledAsset('generic.css'), false);
$stylesheetDarkMode = autoUrl(getCompiledAsset('generic-dark-mode.css'), false);

header('Link: <' . $stylesheet . '>; rel=preload; as=style', false);
header('Link: <' . $stylesheetDarkMode . '>; rel=preload; as=style', false);
// header('Link: <' . autoUrl("public/css/colour.css") . '>; rel=preload; as=style', false);

$container_class;
if (isset($fluidContainer) && $fluidContainer == true) {
  $container_class = "container-fluid";
} else {
  $container_class = "container-xl";
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
    <title><?= $pagetitle ?> - <?= htmlspecialchars(app()->tenant->getKey('CLUB_NAME')) ?> Membership</title>
  <?php } else { ?>
    <title><?= htmlspecialchars(app()->tenant->getKey('CLUB_NAME')) ?> Membership</title>
  <?php } ?>
  <meta name="description" content="Your <?= app()->tenant->getKey('CLUB_NAME') ?> Account lets you make gala entries online and gives you access to all your information about your swimmers, including attendance.">
  <meta name="viewport" content="width=device-width, initial-scale=1.0,
    user-scalable=no,maximum-scale=1">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="apple-mobile-web-app-title" content="<?= htmlspecialchars(app()->tenant->getKey('CLUB_SHORT_NAME')) ?> Accounts">
  <meta name="format-detection" content="telephone=no">
  <meta name="googlebot" content="noarchive, nosnippet">
  <meta name="X-CLSW-System" content="Membership">
  <meta name="og:type" content="website">
  <meta name="og:locale" content="en_GB">
  <meta name="og:site_name" content="<?= htmlspecialchars(app()->tenant->getKey('CLUB_NAME')) ?> Account">
  <link rel="manifest" href="<?= autoUrl("manifest.webmanifest") ?>">
  <meta name="X-SCDS-Membership-Tracking" content="no">
  <script src="https://js.stripe.com/v3/"></script>
  <link rel="stylesheet preload" href="<?= htmlspecialchars($stylesheet) ?>">
  <meta name="color-scheme" content="dark light">
  <link rel="stylesheet preload" href="<?= htmlspecialchars($stylesheetDarkMode) ?>" media="(prefers-color-scheme: dark)">
  <?php if (app()->tenant->getKey('SYSTEM_COLOUR')) { ?>
    <meta name="theme-color" content="<?= htmlspecialchars(app()->tenant->getKey('SYSTEM_COLOUR')) ?>" media="(prefers-color-scheme: light)">
  <?php } else { ?>
    <meta name="theme-color" content="#1d2124" media="(prefers-color-scheme: light)">
  <?php } ?>
  <meta name="theme-color" content="#1d2124" media="(prefers-color-scheme: dark)">

  <?php include 'colour.php'; ?>

  <?php if (isset($pagetitle)) { ?>
    <meta property="og:title" content="<?= $pagetitle ?>" />
  <?php } ?>

  <!-- Generic icon first -->
  <?php if ($logos = $tenant->getKey('LOGO_DIR')) { ?>
    <link rel="icon" sizes="196x196" href="<?= htmlspecialchars(getUploadedAssetUrl($logos . 'icon-196x196.png')) ?>">
    <link rel="icon" sizes="192x192" href="<?= htmlspecialchars(getUploadedAssetUrl($logos . 'icon-192x192.png')) ?>">
    <link rel="apple-touch-icon-precomposed" sizes="180x180" href="<?= getUploadedAssetUrl($logos . 'icon-180x180.png') ?>">
    <link rel="apple-touch-icon-precomposed" sizes="167x167" href="<?= getUploadedAssetUrl($logos . 'icon-167x167.png') ?>">
    <link rel="apple-touch-icon-precomposed" sizes="152x152" href="<?= getUploadedAssetUrl($logos . 'icon-152x152.png') ?>">
    <link rel="apple-touch-icon-precomposed" sizes="128x128" href="<?= getUploadedAssetUrl($logos . 'icon-128x128.png') ?>">
    <link rel="apple-touch-icon-precomposed" sizes="114x114" href="<?= getUploadedAssetUrl($logos . 'icon-114x114.png') ?>">
    <link rel="apple-touch-icon-precomposed" sizes="72x72" href="<?= getUploadedAssetUrl($logos . 'icon-72x72.png') ?>">
    <link rel="apple-touch-icon-precomposed" sizes="32x32" href="<?= getUploadedAssetUrl($logos . 'icon-32x32.png') ?>">
    <link rel="apple-touch-icon-precomposed" sizes="196x196" href="<?= getUploadedAssetUrl($logos . 'icon-196x196.png') ?>">
    <meta property="og:image" content="<?= getUploadedAssetUrl($logos . 'logo-512.png') ?>" />
  <?php } else { ?>
    <!-- For iPhone 6 Plus with @3× display: -->
    <link rel="apple-touch-icon-precomposed" sizes="180x180" href="<?= autoUrl("img/corporate/icons/apple-touch-icon-180x180.png", false) ?>">
    <!-- For iPad with @2× display running iOS ≥ 7: -->
    <link rel="apple-touch-icon-precomposed" sizes="152x152" href="<?= autoUrl("img/corporate/icons/apple-touch-icon-152x152.png", false) ?>">
    <!-- For iPad with @2× display running iOS ≤ 6: -->
    <link rel="apple-touch-icon-precomposed" sizes="144x144" href="<?= autoUrl("img/corporate/icons/apple-touch-icon-144x144.png", false) ?>">
    <!-- For iPhone with @2× display running iOS ≥ 7: -->
    <link rel="apple-touch-icon-precomposed" sizes="120x120" href="<?= autoUrl("img/corporate/icons/apple-touch-icon-120x120.png", false) ?>">
    <!-- For iPhone with @2× display running iOS ≤ 6: -->
    <link rel="apple-touch-icon-precomposed" sizes="114x114" href="<?= autoUrl("img/corporate/icons/apple-touch-icon-114x114.png", false) ?>">
    <!-- For the iPad mini and the first- and second-generation iPad (@1× display) on iOS ≥ 7: -->
    <link rel="apple-touch-icon-precomposed" sizes="76x76" href="<?= autoUrl("img/corporate/icons/apple-touch-icon-76x76.png", false) ?>">
    <!-- For the iPad mini and the first- and second-generation iPad (@1× display) on iOS ≤ 6: -->
    <link rel="apple-touch-icon-precomposed" sizes="72x72" href="<?= autoUrl("img/corporate/icons/apple-touch-icon-72x72.png", false) ?>">
    <!-- For non-Retina iPhone, iPod Touch, and Android 2.1+ devices: -->
    <link rel="apple-touch-icon-precomposed" href="<?= autoUrl("img/corporate/icons/apple-touch-icon.png", false) ?>"><!-- 57×57px -->
  <?php } ?>
  <!-- <link rel="mask-icon" href="https://www.chesterlestreetasc.co.uk/wp-content/themes/chester/img/chesterIcon.svg"
    color="#bd0000"> -->
  <script src="https://www.google.com/recaptcha/api.js"></script>

  <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->

</head>