<?php

function getISOAlpha2Countries() {
	// Get the preferred locale
	$prefLocales = array_reduce(
    explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']), 
      function ($res, $el) { 
        list($l, $q) = array_merge(explode(';q=', $el), [1]); 
        $res[$l] = (float) $q; 
        return $res; 
      }, []);
	arsort($prefLocales);

	// Check if we have a list for a locale (order of priority) and return that if we do
	foreach($prefLocales as $locale => $priority) {
		$path = BASE_PATH . 'vendor/umpirsky/country-list/data/' . str_replace('-', '_', $locale) . '/country.php';
		if (file_exists($path)) {
			return require $path;
		}
	}
	
	// Else return the default
	return require BASE_PATH . 'vendor/umpirsky/country-list/data/en_GB/country.php';
}

function getISOAlpha2CountriesWithHomeNations($noGreatBritain = true) {

	$homeNations = [
		'GB-ENG' => 'England',
		'GB-NIR' => 'Northern Ireland (Ulster Swimming)',
		'GB-SCT' => 'Scotland',
		'GB-WLS' => 'Wales'
	];
	
	// Get all countries
	$countries = getISOAlpha2Countries();

	if ($noGreatBritain) {
		unset($countries['GB']);
	}

	$array = $homeNations + $countries;

	return $array;

}