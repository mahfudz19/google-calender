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
    if ($this->session->get('is_logged_in') === true) {
      $e = new AuthorizationException('RedirectIfAuthenticated');
      $e->hardRedirect();
      throw $e;
    }

    return $next($request);
  }
}
