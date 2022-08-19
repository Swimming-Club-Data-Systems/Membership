<?php

if (!function_exists('env')) {
  function getenv($name) {
    // If a $_SERVER environment variable is set return it
    if (isset($_SERVER[$name])) {
      return $_SERVER[$name];
    }

    // If an $_ENV environment variable is set return it
    else if (isset($_ENV[$name])) {
      return $_ENV[$name];
    }

    // If an other environment variable is set return it
    else if (null !== getenv($name)) {
      return getenv($name);
    }

    // If instead a constant is defined return that
    else if (defined($name)) {
      return constant($name);
    }

    // Otherwise return null
    return null;
  }
}