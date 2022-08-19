<?php

function getUploadedAssetUrl($asset)
{
  if (substr($asset, 0, 5) === "X-S3:") {
    $asset = ltrim(substr($asset, 5), '/');
    if (env('AWS_CLOUDFRONT_ROOT')) {
      return env('AWS_CLOUDFRONT_ROOT') . $asset;
    } else if (env('AWS_S3_REGION') && env('AWS_S3_BUCKET')) {
      return 'https://' . env('AWS_S3_BUCKET') . '.s3.' . env('AWS_S3_REGION') . '.amazonaws.com/' . $asset;
    }
  }

  return asset($asset);
}
