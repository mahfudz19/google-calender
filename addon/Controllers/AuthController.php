<?php

namespace Addon\Controllers;

use App\Core\Http\Request;
use App\Core\Http\Response;
use App\Core\View\View;
use Addon\Services\GoogleAuthService;
use Addon\Models\UserModel;
use App\Exceptions\HttpException;
use App\Services\SessionService;
use Exception;

class AuthController
{
  public function __construct(
    private GoogleAuthService $googleAuth,
    private UserModel $users,
    private SessionService $session,
  ) {}

  public function index(Request $request, Response $response)
  {
    return $response->renderPage([], ['path' => '/login']);
  }

  public function login(Request $request, Response $response)
  {
    try {
      $url = $this->googleAuth->getAuthUrl();
      return $response->redirect($url);
    } catch (Exception $e) {
      throw new HttpException(500, $e->getMessage());
    }
  }

  public function callback(Request $request, Response $response)
  {
    $code = $request->query['code'] ?? null;
    $errorCode = 500;

    try {
      if (!$code) {
        $errorCode = 400;
        throw new Exception('Authorization code not found');
      }

      $userData = $this->googleAuth->handleCallback($code);

      // Cek user di database (untuk menentukan apakah dia Admin/Privileged)
      $user = $this->users->findByEmail($userData['email']);

      $role = 'user';
      $dbId = null;

      if ($user) {
        // User ditemukan di DB (Admin/Super Admin/Staff)
        if (isset($user['is_active']) && !$user['is_active']) {
          $errorCode = 403;
          throw new Exception('Akun dinonaktifkan');
        }

        $this->users->touchLogin($user['id'], $userData['name'] ?? null, $userData['picture'] ?? null, $userData['google_id'] ?? null);
        $dbId = $user['id'];
        $role = $user['role'] ?? 'admin';
      }

      // Simpan ke session
      $this->session->set('user', [
        'id' => $dbId,
        'email' => $userData['email'],
        'name' => $userData['name'],
        'avatar' => $userData['picture'],
        'google_id' => $userData['google_id'] ?? null,
        'role' => $role,
      ]);
      $this->session->set('is_logged_in', true);

      return $response->redirect('/dashboard');
    } catch (Exception $e) {
      throw new HttpException($errorCode, 'Login Failed: ' . $e->getMessage());
    }
  }

  public function logout(Request $request, Response $response)
  {
    $this->session->destroy();
    return $response->redirect('/');
  }

  public function dashboard(Request $request, Response $response): View
  {
    $user = $this->session->get('user', []);
    $role = $user['role'] ?? 'User';

    return $response->renderPage(
      ['user' => $user, 'role' => $role],
      ['meta' => ['title' => 'Dashboard']]
    );
  }
}
