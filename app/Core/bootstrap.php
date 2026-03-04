<?php
declare(strict_types=1);

require __DIR__ . '/config.php';
require __DIR__ . '/security.php';
require __DIR__ . '/db.php';
require __DIR__ . '/csrf.php';
require __DIR__ . '/auth.php';
require __DIR__ . '/response.php';
require __DIR__ . '/logger.php';
require __DIR__ . '/request_context.php';
require __DIR__ . '/file_logger.php';


spl_autoload_register(function ($class) {
  $prefix = 'App\\';
  $baseDir = __DIR__ . '/../';
  if (str_starts_with($class, $prefix)) {
    $relative = str_replace('\\', '/', substr($class, strlen($prefix)));
    $file = $baseDir . $relative . '.php';
    if (is_file($file)) require $file;
  }
});

\App\Core\RequestContext::init();
\App\Core\Security::init();
\App\Core\Security::sendHeaders();

// Phase 4 will plug in DB activity logging here
\App\Core\Logger::requestLog();

