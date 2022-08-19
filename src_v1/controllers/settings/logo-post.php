<?php

// pre($_POST);
// pre($_FILES);

// pre(file_get_contents($_FILES['file-upload']['tmp_name']));

$client = new Aws\S3\S3Client([
  'version'     => 'latest',
  'region'      => getenv('AWS_S3_REGION'),
  'visibility' => 'public',
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
      League\Flysystem\Visibility::PUBLIC // or ::PRIVATE
  )
);

// The FilesystemOperator
$filesystem = new League\Flysystem\Filesystem($adapter);

use Ramsey\Uuid\Uuid;
use Intervention\Image\ImageManagerStatic as Image;

try {
  $uuid = Uuid::uuid4();

  $tenant = app()->tenant;
  $url = 'logos/' . $uuid->toString() . '/';
  $relative = 'public/' . $url;
  $filePath = $tenant->getFilePath() . $relative;
  $s3FilePath = $tenant->getS3FilePath() . $relative;

  // Image::configure(array('driver' => 'imagick'));

  // to finally create image instances
  $image = Image::make($_FILES['file-upload']['tmp_name']);
  // $image = Intervention\Image\Image::make($_FILES['file-upload']['tmp_name']);

  $image->backup();

  if (!is_dir($filePath)) {
    mkdir($filePath, 0755, true);
  }

  $sizes = [
    75,
    150,
    256,
    512,
    1024,
  ];

  foreach ($sizes as $size) {
    for ($i = 1; $i < 4; $i++) {
      $image->reset();
      $image->heighten($size * $i);
      $srcset = '';
      if ($i > 1) {
        $srcset = '@' . $i . 'x';
      }
      // $image->save($filePath . 'logo-' . $size . $srcset . '.png', 80, 'png');
      $filesystem->write($s3FilePath . 'logo-' . $size . $srcset . '.png', $image->encode('png', 40), ['visibility' => 'public']);
    }
  }

  if ($_FILES['icon-upload']['error'] == 0) {
    try {
      $image = Image::make($_FILES['icon-upload']['tmp_name']);
      $image->backup();
    } catch (Exception $e) {
      $image->reset();
    }
  }

  $sizes = [
    196,
    192,
    180,
    167,
    152,
    128,
    114,
    72,
    32,
  ];

  foreach ($sizes as $size) {
    $image->reset();
    $image->resize($size, $size, function ($constraint) {
      $constraint->aspectRatio();
      $constraint->upsize();
    });
    // $image->save($filePath . 'icon-' . $size . 'x' . $size . '.png', 80, 'png');
    $filesystem->write($s3FilePath . 'icon-' . $size . 'x' . $size . '.png', $image->encode('png', 40), ['visibility' => 'public']);
  }

  $tenant->setKey('LOGO_DIR', 'X-S3:' . $s3FilePath);

  $_SESSION['TENANT-' . app()->tenant->getId()]['LOGO-SAVED'] = true;
} catch (Exception $e) {
  $_SESSION['TENANT-' . app()->tenant->getId()]['LOGO-ERROR'] = true;
  reportError($e);
}

header("location: " . autoUrl('settings/logo'));