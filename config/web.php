<?php

/**
 * Web Application Configuration
 *
 * Configures the Yii2 web application including:
 * - Application metadata (id, name)
 * - Base paths for application files
 * - Database connection parameters
 *
 * @return array Application configuration array
 */

return [
  /* Application Identity */
  'id' => $_ENV['APP_ID'],
  'name' => $_ENV['APP_NAME'],

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
  'controllerNamespace' => 'app\controllers',

  /* Error Handling */
  'components' => [
    'errorHandler' => [
      'errorAction' => 'site/error',
    ],

    /* URL Manager - Handles routing and pretty URLs */
    'urlManager' => [
      'class' => yii\web\UrlManager::class,
      'enablePrettyUrl' => true,
      'showScriptName' => false,
      'enableStrictParsing' => false,
      'rules' => [
        // Product routes
        'product' => 'product/index',
        'product/index' => 'product/index',
        'product/create' => 'product/create',
        'product/view/<id:\d+>' => 'product/view',
        'product/update/<id:\d+>' => 'product/update',
        'product/delete/<id:\d+>' => 'product/delete',

        // Default route
        '' => 'site/index',
        '<controller:\w+>/<action:\w+>' => '<controller>/<action>',
      ],
    ],

    /* Database Connection */
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

    /* Session Configuration */
    'session' => [
      'class' => yii\web\Session::class,
      'cookieParams' => [
        'httpOnly' => true,
        'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
      ],
    ],

    /* Request Configuration */
    'request' => [
      'cookieValidationKey' => $_ENV['COOKIE_VALIDATION_KEY'] ?? 'change-this-key-in-production',
      'enableCsrfValidation' => true,
      'enableCookieValidation' => true,
    ],

    /* Cache Configuration (File Cache) */
    'cache' => [
      'class' => yii\caching\FileCache::class,
    ],

    /* Asset Manager - Handles publishing of CSS, JS, and other assets */
    'assetManager' => [
      'class' => yii\web\AssetManager::class,
      'basePath' => '@webroot/assets',
      'baseUrl' => '@web/assets',
      'appendTimestamp' => true, // Append timestamp to asset URLs for cache busting
    ],

    /* Log Configuration */
    'log' => [
      'traceLevel' => (isset($_ENV['APP_DEBUG']) && $_ENV['APP_DEBUG'] == '1') ? 3 : 0,
      'targets' => [
        [
          'class' => yii\log\FileTarget::class,
          'levels' => ['error', 'warning'],
        ],
        [
          'class' => yii\log\FileTarget::class,
          'levels' => ['info'],
          'categories' => ['application'],
          'logFile' => '@runtime/logs/app.log',
        ],
      ],
    ],
  ],

  /* Params (Custom Application Parameters) */
  'params' => [
    'adminEmail' => $_ENV['ADMIN_EMAIL'] ?? 'admin@example.com',
  ],
];
