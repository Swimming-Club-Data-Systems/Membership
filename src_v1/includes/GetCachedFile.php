<?php

function getCachedFile($cacheFile, $url, $maxAge) {
  $httpClient = new GuzzleHttp\Client();
  $file = null;

  if(file_exists($cacheFile) && time() - filemtime($cacheFile) < $maxAge) {
    // Use the cached file
    $file = file_get_contents($cacheFile);
  } else {
    try {
      $res = $httpClient->request('GET', $url, [
      ]);
      if ($res->getStatusCode() == "200") {
        $cache = $res->getBody();
        try {
          file_put_contents($cacheFile, $cache);
        } catch (Error $e) {

        }
        $file = $cache;
      } else if (file_exists($cacheFile)) {
        $file = file_get_contents($cacheFile);
      }
    } catch (Exception $e) {
      // Do nothing
    }
  }

  return $file;
}