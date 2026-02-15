<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Response;
use App\Models\UserModel;

final class UserController
{
  public function searchForm(): void
  {
    Auth::requireLogin();
    Response::view('users/search');
  }

  public function search(): void
  {
    Auth::requireLogin();

    $q = trim((string)($_GET['q'] ?? ''));
    $mode = (string)($_GET['mode'] ?? 'username'); // 'username' or 'id'

    $results = [];
    $error = null;

    if ($q === '') {
      $error = 'Enter a search value.';
      Response::view('users/search', compact('results', 'error', 'q', 'mode'));
      return;
    }

    if ($mode === 'id') {
      if (!ctype_digit($q)) {
        $error = 'User ID must be numeric.';
        Response::view('users/search', compact('results', 'error', 'q', 'mode'));
        return;
      }
      $user = UserModel::findById((int)$q);
      if ($user) {
        $results = [['id' => (int)$user['id'], 'username' => (string)$user['username']]];
      } else {
        $results = [];
      }
    } else {
      // username mode
      if (mb_strlen($q) < 2) {
        $error = 'Type at least 2 characters.';
        Response::view('users/search', compact('results', 'error', 'q', 'mode'));
        return;
      }
      $results = UserModel::searchByUsername($q, 20);
    }

    Response::view('users/search', compact('results', 'error', 'q', 'mode'));
  }

  public function profile(): void
  {
    Auth::requireLogin();

    $idStr = (string)($_GET['id'] ?? '');
    if (!ctype_digit($idStr)) {
      http_response_code(400);
      echo "Invalid user id.";
      return;
    }

    $user = UserModel::findById((int)$idStr);
    if (!$user) {
      http_response_code(404);
      echo "User not found.";
      return;
    }

    Response::view('users/profile', ['u' => $user]);
  }
}

