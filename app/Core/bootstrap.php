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

set_exception_handler(function (\Throwable $e): void {
    $logFile = __DIR__ . '/../../storage/logs/php_errors.log';
    error_log(sprintf(
        "[%s] UNCAUGHT EXCEPTION: %s in %s on line %d\nStack trace:\n%s\n",
        date('Y-m-d H:i:s'), $e->getMessage(), $e->getFile(), $e->getLine(), $e->getTraceAsString()
    ), 3, $logFile);
    if (!headers_sent()) http_response_code(500);
    echo _renderErrorPage('Something went wrong.', 'An unexpected error occurred. Please try again or contact support if the problem persists.');
    exit;
});

set_error_handler(function (int $errno, string $errstr, string $errfile, int $errline): bool {
    if (!(error_reporting() & $errno)) return false;
    $logFile = __DIR__ . '/../../storage/logs/php_errors.log';
    error_log(sprintf("[%s] PHP ERROR [%d]: %s in %s on line %d\n", date('Y-m-d H:i:s'), $errno, $errstr, $errfile, $errline), 3, $logFile);
    if (in_array($errno, [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR], true))
        throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
    return true;
});

register_shutdown_function(function (): void {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        $logFile = __DIR__ . '/../../storage/logs/php_errors.log';
        error_log(sprintf("[%s] FATAL SHUTDOWN ERROR [%d]: %s in %s on line %d\n", date('Y-m-d H:i:s'), $error['type'], $error['message'], $error['file'], $error['line']), 3, $logFile);
        if (!headers_sent()) http_response_code(500);
        echo _renderErrorPage('Something went wrong.', 'An unexpected error occurred. Please try again or contact support if the problem persists.');
    }
});

function _renderErrorPage(string $title, string $message): string
{
    $t = htmlspecialchars($title,   ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    $m = htmlspecialchars($message, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{$t} – SecureApp</title>
  <style>
    body{font-family:sans-serif;background:#f8f9fa;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0}
    .box{background:#fff;border-radius:8px;padding:2rem 2.5rem;max-width:480px;text-align:center;box-shadow:0 2px 12px rgba(0,0,0,.1)}
    h1{color:#dc3545;font-size:1.5rem;margin-bottom:.5rem}
    p{color:#555}
    a{color:#0d6efd;text-decoration:none}
  </style>
</head>
<body>
  <div class="box">
    <h1>{$t}</h1>
    <p>{$m}</p>
    <p><a href="/">Return to Home</a></p>
  </div>
</body>
</html>
HTML;
}

spl_autoload_register(function (string $class): void {
    $prefix  = 'App\\';
    $baseDir = __DIR__ . '/../';
    if (str_starts_with($class, $prefix)) {
        $file = $baseDir . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
        if (is_file($file)) require $file;
    }
});

\App\Core\RequestContext::init();
\App\Core\Security::init();
\App\Core\Security::sendHeaders();
\App\Core\Logger::requestLog();