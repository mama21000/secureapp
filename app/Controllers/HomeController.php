<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Response;
use App\Core\Auth;
use App\Models\TransferModel;
use App\Models\UserModel;

final class HomeController {
    public function index(): void
    {
        if (!Auth::check()) {
            // Not logged in — just show hero page
            Response::view('home', [
                'loggedIn' => false,
                'username' => '',
            ]);
            return;
        }

        $userId = (int)Auth::userId();

        // Get user's current balance
        $user    = UserModel::findById($userId);
        $balance = (int)($user['balance'] ?? 0);

        // Get last 5 transactions for dashboard
        $recentTransactions = TransferModel::recent($userId, 5);

        Response::view('home', [
            'loggedIn'           => true,
            'username'           => Auth::username(),
            'balance'            => $balance,
            'recentTransactions' => $recentTransactions,
        ]);
    }
}