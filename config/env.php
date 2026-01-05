<?php

/**
 * Environment Configuration Loader
 *
 * Loads environment variables from .env file into $_ENV superglobal.
 * Skips comments and empty lines.
 *
 * @throws RuntimeException if .env file is not found
 */

$envPath = dirname(__DIR__) . '/.env';

/* Validate .env file exists */
if (!file_exists($envPath)) {
  throw new RuntimeException('.env file not found at: ' . $envPath);
}

/* Load and parse .env file */
$lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

foreach ($lines as $line) {
  /* Skip comments and empty lines */
  $line = trim($line);
  if (empty($line) || str_starts_with($line, '#')) {
    continue;
  }

  /* Parse KEY=VALUE pairs */
  if (strpos($line, '=') === false) {
    continue; // Skip lines without '='
  }

  [$key, $value] = explode('=', $line, 2);
  $key = trim($key);
  $value = trim($value);
  
  /* Remove quotes if present */
  if ((str_starts_with($value, '"') && str_ends_with($value, '"')) ||
      (str_starts_with($value, "'") && str_ends_with($value, "'"))) {
    $value = substr($value, 1, -1);
  }
  
  $_ENV[$key] = $value;
}
