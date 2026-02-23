<?php

namespace Addon\Middleware;

use App\Core\Interfaces\MiddlewareInterface;
use App\Exceptions\AuthenticationException;
use App\Exceptions\AuthorizationException;
use App\Services\SessionService;

class RoleMiddleware implements MiddlewareInterface
{
  public function __construct(private SessionService $session) {}

  public function handle($request, \Closure $next, array $params = [])
  {
    if ($this->session->get('is_logged_in') !== true) {
      throw new AuthenticationException('Unauthenticated');
    }

    $user = $this->session->get('user', []);
    $role = is_array($user) ? ($user['role'] ?? null) : null;

    if (!empty($params)) {
      if (!$role || !in_array($role, $params, true)) {
        throw new AuthorizationException('Forbidden');
      }
    }

    return $next($request);
  }
}
