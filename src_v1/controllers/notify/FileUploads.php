<?php

$email = $date = null;

if (isset($_POST['notify_uuid'])) {
  $email = $_POST['notify_uuid'];
} else {
  $email = \Ramsey\Uuid\Uuid::uuid4();
}

if (isset($_POST['notify_date'])) {
  $date = $_POST['notify_date'];
} else {
  $date = (new DateTime('now', new DateTimeZone('Europe/London')))->format('Y/m/d');
}

$db = app()->db;
$tenant = app()->tenant;

$json = ['status' => 200];

try {

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

  if ($_FILES['file']['size'] > 10485760) {
    throw new Exception('Too large');
  }

  // The FilesystemOperator
  $filesystem = new League\Flysystem\Filesystem($adapter);

  if ($_FILES['file']['error'] != 0) {
    throw new Exception('File error');
  }

  if (!$tenant->getFilePath()) {
    throw new Exception('No filestore available');
  }

  if (!getenv('AWS_S3_BUCKET')) {
    throw new Exception('No S3 bucket available');
  }

  $rootFilePath = $tenant->getFilePath();
  // Work out filename for upload
  $urlPath = 'notify/attachments/' . $date . '/' . $email . '/';
  $s3Path = $tenant->getId() . '/' . $urlPath;

  $uuid = Ramsey\Uuid\Uuid::uuid4()->toString();
  $filename = $uuid . '-' . preg_replace('@[^0-9a-z\.]+@i', '-', basename($_FILES['file']['name']));

  $filenamePath = $s3Path . $filename;
  $url = $urlPath . $filename;

  try {
    $filesystem->write($filenamePath, file_get_contents($_FILES['file']['tmp_name']), ['visibility' => 'private']);
  } catch (League\Flysystem\FilesystemException | League\Flysystem\UnableToWriteFile $exception) {
    throw new Exception('Flysystem upload failure');
  }

  $json = [
    'status' => 200,
    'key' => $filenamePath,
    'url' => $url,
    'name' => $_FILES['file']['name'],
    'type' => $_FILES['file']['type'],
    'error' => $_FILES['file']['error'],
    'size' => $_FILES['file']['size']
  ];
} catch (Exception $e) {
  $json = [
    'status' => 500,
    'error' => $e->getMessage(),
  ];
}

header("content-type: application/json");
echo json_encode($json);

// reportError($_POST);
// reportError($_FILES);