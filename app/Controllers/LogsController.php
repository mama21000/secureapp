<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Response;
use App\Models\ActivityLogModel;

final class LogsController
{
  public function index(): void
  {
    Auth::requireLogin();
    $items = ActivityLogModel::latest(200);
    Response::view('logs/index', ['items' => $items]);
  }
}

