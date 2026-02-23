<?php

use \Addon\Controllers\AuthController;

$router->get('/', [AuthController::class, 'index']);

$router->get('/login', [AuthController::class, 'login']);
$router->get('/auth/callback', [AuthController::class, 'callback']);
$router->get('/logout', [AuthController::class, 'logout']);

$router->get('/dashboard', [AuthController::class, 'dashboard'], ['auth']);
$router->get('/api/super-admin-test', [AuthController::class, 'superAdminTest'], ['auth', 'role:super_admin']);
$router->get('/api/admin-test', [AuthController::class, 'adminTest'], ['auth', 'role:admin']);
