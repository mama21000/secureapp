<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Response;
use App\Models\ActivityLogModel;

final class LogsController
{
  public function index(): void {
    // Completely disabled for production security
    http_response_code(404);
    echo "404 Not Found";
    exit;
  }
}

