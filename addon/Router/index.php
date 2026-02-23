<?php

use Addon\Controllers\AgendaController;
use Addon\Controllers\ApprovalController;
use Addon\Controllers\AuthController;
use Addon\Controllers\UserController;

$router->get('/', [AuthController::class, 'index']);

$router->get('/login', [AuthController::class, 'login']);
$router->get('/auth/callback', [AuthController::class, 'callback']);
$router->get('/logout', [AuthController::class, 'logout']);

$router->group(['middleware' => ['auth']], function ($router) {

  // 1. Dashboard (Kalender Utama) - Bisa diakses semua role
  $router->get('/dashboard', [AgendaController::class, 'index']);

  // 2. Pengajuan Agenda (User Biasa pun bisa akses)
  $router->get('/agenda/create', [AgendaController::class, 'create']);
  $router->post('/agenda/store', [AgendaController::class, 'store']);
  $router->get('/agenda/:id', [AgendaController::class, 'show']);

  // --- Role: Approver & Admin (Gatekeeper) ---
  // Menggunakan middleware 'role:approver,admin' (artinya boleh approver ATAU admin)
  $router->group(['middleware' => ['role:approver,admin']], function ($router) {

    // Halaman antrian persetujuan
    $router->get('/approval', [ApprovalController::class, 'index']);
    $router->get('/approval/history', [ApprovalController::class, 'history']);

    // Aksi Approve/Reject
    $router->post('/approval/:id/approve', [ApprovalController::class, 'approve']);
    $router->post('/approval/:id/reject', [ApprovalController::class, 'reject']);
  });

  // --- Role: Admin Only (User Management) ---
  $router->group(['middleware' => ['role:admin']], function ($router) {

    // Manajemen User
    $router->get('/users', [UserController::class, 'index']);
    $router->post('/users/register-from-google', [UserController::class, 'registerFromGoogle']);
    $router->get('/users/:id', [UserController::class, 'show']);
    $router->get('/users/:id/edit', [UserController::class, 'edit']);
    $router->get('/users/:id/edit', [UserController::class, 'edit']);
    $router->post('/users/:id/update', [UserController::class, 'update']);
    $router->post('/users/:id/delete', [UserController::class, 'destroy']);
  });
});
