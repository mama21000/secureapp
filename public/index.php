<?php
// ini_set('display_errors', 1);
// error_reporting(E_ALL);
require __DIR__ . '/../app/Core/bootstrap.php';

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/';
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

use App\Controllers\HomeController;
use App\Controllers\AuthController;
use App\Controllers\TransferController;
use App\Controllers\UserController;
use App\Controllers\ProfileController;
use App\Controllers\LogsController;

switch (true) {
  case $path === '/' && $method === 'GET':
    (new HomeController())->index();
    break;

  case $path === '/register' && $method === 'GET':
    (new AuthController())->showRegister();
    break;
  case $path === '/register' && $method === 'POST':
    (new AuthController())->register();
    break;

  case $path === '/login' && $method === 'GET':
    (new AuthController())->showLogin();
    break;
  case $path === '/login' && $method === 'POST':
    (new AuthController())->login();
    break;

  case $path === '/logout' && $method === 'POST':
    (new AuthController())->logout();
    break;

  case $path === '/transfer' && $method === 'GET':
    (new TransferController())->show();
    break;

  case $path === '/transfer' && $method === 'POST':
    (new TransferController())->submit();
    break;

  case $path === '/history' && $method === 'GET':
    (new TransferController())->history();
    break;

  case $path === '/users/search' && $method === 'GET':
    // if q present => run search; else show form
    if (isset($_GET['q'])) (new UserController())->search();
    else (new UserController())->searchForm();
    break;

  case $path === '/users/profile' && $method === 'GET':
    (new UserController())->profile();
    break;
    
  case $path === '/profile/edit' && $method === 'GET':
    (new ProfileController())->edit();
    break;

  case $path === '/profile/update' && $method === 'POST':
    (new ProfileController())->update();
    break;

  case $path === '/profile/avatar' && $method === 'POST':
    (new ProfileController())->uploadAvatar();
    break;

  case $path === '/avatar' && $method === 'GET':
    (new ProfileController())->avatar();
    break;
    
  case $path === '/logs' && $method === 'GET':
    (new LogsController())->index();
    break;


  default:
    http_response_code(404);
    echo "404 Not Found";
}

