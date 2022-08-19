<?php

$db = app()->db;
$tenant = app()->tenant;

$client = new Aws\S3\S3Client([
  'version'     => 'latest',
  'region'      => getenv('AWS_S3_REGION'),
  'visibility' => 'private',
]);
$adapter = new League\Flysystem\AwsS3V3\AwsS3V3Adapter(
  // S3Client
  $client,
  // Bucket name
  getenv('AWS_S3_BUCKET'),
  // Optional path prefix
  '',
  // Visibility converter (League\Flysystem\AwsS3V3\VisibilityConverter)
  new League\Flysystem\AwsS3V3\PortableVisibilityConverter(
    // Optional default for directories
    League\Flysystem\Visibility::PRIVATE // or ::PRIVATE
  )
);

// The FilesystemOperator
$filesystem = new League\Flysystem\Filesystem($adapter);

/***************************************************
 * Only these origins are allowed to upload images *
 ***************************************************/
$accepted_origins = [autoUrl('')];

/*********************************************
 * Change this line to set the upload folder *
 *********************************************/
$imageFolder = "images/";

if (!$tenant->getFilePath()) {
  http_response_code(500);
  return;
}

if (!getenv('AWS_S3_BUCKET')) {
  http_response_code(500);
  return;
}

$rootFilePath = $tenant->getFilePath();

if (isset($_SERVER['HTTP_ORIGIN'])) {
  // same-origin requests won't set an origin. If the origin is set, it must be valid.
  if (in_array($_SERVER['HTTP_ORIGIN'], $accepted_origins)) {
    header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
  } else {
    http_response_code(403);
    return;
  }
}

// Don't attempt to process the upload on an OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
  header("Access-Control-Allow-Methods: POST, OPTIONS");
  return;
}

reset($_FILES);
$temp = current($_FILES);
if (is_uploaded_file($temp['tmp_name'])) {
  /*
      If your script needs to receive cookies, set images_upload_credentials : true in
      the configuration and enable the following two headers.
    */
  header('Access-Control-Allow-Credentials: true');
  header('P3P: CP="There is no P3P policy."');

  $uuid = Ramsey\Uuid\Uuid::uuid4()->toString();
  $filename = $uuid . '-' . preg_replace('@[^0-9a-z\.]+@i', '-', basename($temp['name']));

  $date = (new DateTime('now', new DateTimeZone('Europe/London')))->format('Y/m/d');
  $urlPath = 'notify/public-uploads/' . $date . '/';
  $s3Path = $tenant->getId() . '/' . $urlPath;

  $filenamePath = $s3Path . $filename;
  $url = $urlPath . $filename;

  // Sanitize input
  if (preg_match("/([^\w\s\d\-_~,;:\[\]\(\).])|([\.]{2,})/", $temp['name'])) {
    http_response_code(400);
    return;
  }

  // Verify extension
  if (!in_array(strtolower(pathinfo($temp['name'], PATHINFO_EXTENSION)), array("gif", "jpg", "png"))) {
    http_response_code(400);
    return;
  }

  // Accept upload if there was no origin, or if it is an accepted origin
  $filetowrite = $filename;
  // move_uploaded_file($temp['tmp_name'], $filetowrite);

  try {
    $filesystem->write($filenamePath, file_get_contents($temp['tmp_name']), ['visibility' => 'public']);
  } catch (League\Flysystem\FilesystemException | League\Flysystem\UnableToWriteFile $exception) {
    http_response_code(500);
    return;
  }

  // Determine the base URL
  $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? "https://" : "http://";
  $baseurl = $protocol . $_SERVER["HTTP_HOST"] . rtrim(dirname($_SERVER['REQUEST_URI']), "/") . "/";

  // Respond to the successful upload with JSON.
  // Use a location key to specify the path to the saved image resource.
  // { location : '/your/uploaded/image/file'}
  echo json_encode(['location' => getUploadedAssetUrl('X-S3:' . $filenamePath)]);
} else {
  // Notify editor that the upload failed
  header("HTTP/1.1 500 Server Error");
}
