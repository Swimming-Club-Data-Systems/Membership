<?php

$file = BASE_PATH . 'public/';
$file = $file . $filename;
if (file_exists($file) && mime_content_type($file) != 'directory') {
  // header('content-length: ' . filesize($file));
  header('content-description: File Transfer');
  if (strpos($file, '.css')) {
    header('content-type: text/css');
    header('age: 0');
    header('cache-control: max-age=31536000, private');
  } else if (strpos($file, '.json')) {
    header('content-type: application/json');
    header('age: 0');
    header('cache-control: max-age=31536000, private');
  } else if (strpos($file, '.js')) {
    header('content-type: application/javascript');
    header('age: 0');
    header('cache-control: max-age=31536000, private');
  } else if (strpos($file, '.woff2')) {
    header('content-type: font/woff2');
    header('age: 0');
    header('cache-control: max-age=31536000, private');
  } else if (strpos($file, '.png')) {
    header('content-type: image/png');
    header('age: 0');
    header('cache-control: max-age=31536000, private');
  } else if (strpos($file, '.jpg') || strpos($file, '.jpeg')) {
    header('content-type: image/jpeg');
    header('age: 0');
    header('cache-control: max-age=31536000, private');
  } else if (strpos($file, '.svg')) {
    header('content-type: image/svg+xml');
    header('age: 0');
    header('cache-control: max-age=31536000, private');
  } else {
    header('content-type: ' . mime_content_type($file));
    header('cache-control: must-revalidate');
  }
  if (mime_content_type($file) == 'application/pdf' || mime_content_type($file) == 'text/html' || mime_content_type($file) == 'text/css' || strpos($file, '.css') || mime_content_type($file) == 'application/javascript' || mb_substr(mime_content_type($file), 0, mb_strlen('image')) === 'image') {
    header('content-disposition: inline');
  } else {
    header('content-disposition: attachment; filename="'.basename($filename).'"');
  }
  if (strpos($file, '.css') || mb_strpos($file, '.js')) {
    header('expires: ' . date('D, d M Y H:i:s', strtotime("+1 year")) . ' GMT');
  } else {
    header('expires: 0');
  }
  header('pragma: public');
  header("service-worker-allowed: " . autoUrl(""));
  readfile($file);
  return;
} else {
  halt(404);
}
