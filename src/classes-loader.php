<?php

function loadObjects($className)
{
  $path = BASE_PATH . 'classes/';
  $className = str_replace("\\", DIRECTORY_SEPARATOR, $className);
  $filename = $path . $className . '.php';
  if (file_exists($filename)) {
    require_once $filename;
  }
}

function loadLaravelModelObjects($className)
{
  $path = BASE_PATH . 'v2/';
  $className = lcfirst(str_replace("\\", DIRECTORY_SEPARATOR, $className));
  $filename = $path . $className . '.php';
  if (file_exists($filename)) {
    require_once $filename;
  }
}

spl_autoload_register('loadObjects');
spl_autoload_register('loadLaravelModelObjects');