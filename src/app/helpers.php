<?php

function getUploadedAssetUrl($asset)
{
  if (substr($asset, 0, 5) === "X-S3:") {
    $asset = ltrim(substr($asset, 5), '/');
    if (config('filesystems.disks.s3.cloudfront_url')) {
      return config('filesystems.disks.s3.cloudfront_url') . $asset;
    } else if (config('filesystems.disks.s3.region') && config('filesystems.disks.s3.bucket')) {
      return 'https://' . config('filesystems.disks.s3.bucket') . '.s3.' . config('filesystems.disks.s3.region') . '.amazonaws.com/' . $asset;
    }
  }

  return asset($asset);
}
