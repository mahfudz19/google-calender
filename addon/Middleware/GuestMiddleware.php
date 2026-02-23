<?php

namespace Addon\Middleware;

use App\Core\Interfaces\MiddlewareInterface;
use App\Services\SessionService;
use App\Exceptions\AuthorizationException;
use Closure;

/**
 * GuestMiddleware
 *
 * Middleware standar untuk memastikan pengguna BELUM login.
 * Biasanya digunakan pada halaman login/register agar user yang sudah login diredirect.
 *
 * Contoh penggunaan di router:
 *
 *   $router->get('login', [AuthController::class, 'login'], ['guest']);
 *   $router->get('register', [AuthController::class, 'register'], ['guest']);
 */
class GuestMiddleware implements MiddlewareInterface
{
  public function __construct(private SessionService $session) {}

  public function handle($request, Closure $next, array $params = [])
  {
    // Cek key session yang sama dengan AuthMiddleware
    if ($this->session->get('is_logged_in') === true) {
       // Lempar exception yang akan ditangkap oleh Handler untuk redirect ke dashboard/home
       // Pesan 'RedirectIfAuthenticated' adalah sinyal khusus untuk Exception Handler
       throw new AuthorizationException('RedirectIfAuthenticated');
    }

    return $next($request);
  }
}