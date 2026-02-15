<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Response;
use App\Core\Auth;

final class HomeController {
  public function index(): void {
    Response::view('home', [
      'loggedIn' => Auth::check(),
      'username' => Auth::username()
    ]);
  }
}

