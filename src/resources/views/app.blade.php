<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="min-h-full bg-gray-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0,user-scalable=no,maximum-scale=1">

    <title
        inertia>{{ tenant() ? tenant()->getOption("CLUB_NAME") . " Membership" : config('app.name', 'Laravel') }}</title>

    <meta name="description"
          content="{{ tenant() ? "Your " . tenant()->getOption("CLUB_NAME") . " Account lets you make gala entries online and gives you access to all your information about your swimmers, including attendance." : "SCDS provides membership management software to UK swimming clubs." }} ">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="apple-mobile-web-app-title"
          content="{{ tenant() ? tenant()->getOption("CLUB_SHORT_NAME") . " Membership" : "SCDS" }}">
    <meta name="format-detection" content="telephone=no">
    <meta name="googlebot" content="noarchive, nosnippet">
    <meta name="og:type" content="website">
    <meta name="og:locale" content="en_GB">
    <meta name="og:site_name"
          content="{{ tenant() ? tenant()->getOption("CLUB_NAME") . " Membership" : "Swimming Club Data Systems" }}">

    <!-- Fonts -->
    <link rel="stylesheet" href="https://rsms.me/inter/inter.css">

    @if (tenant() && tenant()->getOption('LOGO_DIR'))
        @php($logos = tenant()->getOption('LOGO_DIR'))
        <link rel="icon" sizes="196x196"
              href="{{getUploadedAssetUrl($logos . 'icon-196x196.png')}}">
        <link rel="icon" sizes="192x192"
              href="{{getUploadedAssetUrl($logos . 'icon-192x192.png')}}">
        <link rel="apple-touch-icon-precomposed" sizes="180x180"
              href="{{getUploadedAssetUrl($logos . 'icon-180x180.png')}}">
        <link rel="apple-touch-icon-precomposed" sizes="167x167"
              href="{{getUploadedAssetUrl($logos . 'icon-167x167.png')}}">
        <link rel="apple-touch-icon-precomposed" sizes="152x152"
              href="{{getUploadedAssetUrl($logos . 'icon-152x152.png')}}">
        <link rel="apple-touch-icon-precomposed" sizes="128x128"
              href="{{getUploadedAssetUrl($logos . 'icon-128x128.png')}}">
        <link rel="apple-touch-icon-precomposed" sizes="114x114"
              href="{{getUploadedAssetUrl($logos . 'icon-114x114.png')}}">
        <link rel="apple-touch-icon-precomposed" sizes="72x72"
              href="{{getUploadedAssetUrl($logos . 'icon-72x72.png')}}">
        <link rel="apple-touch-icon-precomposed" sizes="32x32"
              href="{{getUploadedAssetUrl($logos . 'icon-32x32.png')}}">
        <link rel="apple-touch-icon-precomposed" sizes="196x196"
              href="{{getUploadedAssetUrl($logos . 'icon-196x196.png')}}">
        <meta property="og:image" content="{{getUploadedAssetUrl($logos . 'logo-512.png')}}" />
    @else
        <!-- For iPhone 6 Plus with @3× display: -->
        <link rel="apple-touch-icon-precomposed" sizes="180x180"
              href="{{asset("img/corporate/icons/apple-touch-icon-180x180.png")}}">
        <!-- For iPad with @2× display running iOS ≥ 7: -->
        <link rel="apple-touch-icon-precomposed" sizes="152x152"
              href="{{asset("img/corporate/icons/apple-touch-icon-152x152.png")}}">
        <!-- For iPad with @2× display running iOS ≤ 6: -->
        <link rel="apple-touch-icon-precomposed" sizes="144x144"
              href="{{asset("img/corporate/icons/apple-touch-icon-144x144.png")}}">
        <!-- For iPhone with @2× display running iOS ≥ 7: -->
        <link rel="apple-touch-icon-precomposed" sizes="120x120"
              href="{{asset("img/corporate/icons/apple-touch-icon-120x120.png")}}">
        <!-- For iPhone with @2× display running iOS ≤ 6: -->
        <link rel="apple-touch-icon-precomposed" sizes="114x114"
              href="{{asset("img/corporate/icons/apple-touch-icon-114x114.png")}}">
        <!-- For the iPad mini and the first- and second-generation iPad (@1× display) on iOS ≥ 7: -->
        <link rel="apple-touch-icon-precomposed" sizes="76x76"
              href="{{asset("img/corporate/icons/apple-touch-icon-76x76.png")}}">
        <!-- For the iPad mini and the first- and second-generation iPad (@1× display) on iOS ≤ 6: -->
        <link rel="apple-touch-icon-precomposed" sizes="72x72"
              href="{{asset("img/corporate/icons/apple-touch-icon-72x72.png")}}">
        <!-- For non-Retina iPhone, iPod Touch, and Android 2.1+ devices: -->
        <link rel="apple-touch-icon-precomposed" href="{{asset("img/corporate/icons/apple-touch-icon.png")}}">
        <!-- 57×57px -->
    @endif

    <!-- Scripts -->
    @routes
    @viteReactRefresh
    @vite('resources/js/app.jsx')
    @inertiaHead
</head>
<body class="font-sans antialiased min-h-full">
@inertia
</body>
</html>
