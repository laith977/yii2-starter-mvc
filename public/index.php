<?php

/**
 * Application Entry Point
 *
 * Web application bootstrap script.
 * Loads dependencies and initializes Yii2 framework.
 *
 * Load order:
 * 1. Environment variables (.env)
 * 2. Composer autoloader
 * 3. Yii framework
 * 4. Application configuration
 * 5. Run application
 */

declare(strict_types=1);

/* Load Environment Variables - MUST BE FIRST */
require __DIR__ . '/../config/env.php';

/* Error Handling Configuration - Respect Environment */
$debug = isset($_ENV['APP_DEBUG']) && $_ENV['APP_DEBUG'] == '1';
if ($debug) {
  error_reporting(E_ALL);
  ini_set('display_errors', '1');
} else {
  error_reporting(E_ALL);
  ini_set('display_errors', '0');
  ini_set('log_errors', '1');
}

/* Load Composer Dependencies */
require __DIR__ . '/../vendor/autoload.php';

/* Load Yii Framework */
require __DIR__ . '/../vendor/yiisoft/yii2/Yii.php';

/* Load Application Configuration */
$config = require __DIR__ . '/../config/web.php';

/* Initialize and Run Application */
(new yii\web\Application($config))->run();
