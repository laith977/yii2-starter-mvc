<?php

/**
 * Console Application Configuration
 *
 * Configures the Yii2 console application for running CLI commands,
 * migrations, and other console tasks.
 *
 * @return array Console application configuration array
 */

return [
  /* Application Identity */
  'id' => $_ENV['APP_ID'] . '-console',
  'name' => $_ENV['APP_NAME'] . ' Console',

  /* Base Paths */
  'basePath' => dirname(__DIR__),
  'runtimePath' => dirname(__DIR__) . '/runtime',
  'vendorPath' => dirname(__DIR__) . '/vendor',

  /* Path Aliases */
  'aliases' => [
    '@bower' => '@vendor/bower-asset',
    '@npm' => '@vendor/npm-asset',
    '@webroot' => dirname(__DIR__) . '/public',
    '@web' => '/',
    '@runtime' => dirname(__DIR__) . '/runtime',
    '@vendor' => dirname(__DIR__) . '/vendor',
    '@app' => dirname(__DIR__),
  ],

  /* Controller Namespace */
  'controllerNamespace' => 'app\commands',

  /* Application Components */
  'components' => [
    'db' => [
      'class' => yii\db\Connection::class,
      /* Database Connection String */
      'dsn' => sprintf(
        '%s:host=%s;port=%s;dbname=%s',
        $_ENV['DB_DRIVER'],
        $_ENV['DB_HOST'],
        $_ENV['DB_PORT'],
        $_ENV['DB_NAME']
      ),
      /* Database Credentials */
      'username' => $_ENV['DB_USER'],
      'password' => $_ENV['DB_PASSWORD'],
      /* Character Encoding */
      'charset' => 'utf8mb4',
    ],

    /* Cache Configuration (File Cache) */
    'cache' => [
      'class' => yii\caching\FileCache::class,
    ],
  ],
];

