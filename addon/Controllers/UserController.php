<?php

namespace Addon\Controllers;

use App\Core\Http\Request;
use App\Core\Http\Response;
use App\Core\View\View;
use App\Core\Http\RedirectResponse;
use Addon\Models\UserModel;
use Addon\Services\GoogleDirectoryService;
use App\Services\SessionService;

class UserController
{

  public function __construct(
    private UserModel $model,
    private SessionService $session,
  ) {
    $this->model = $model;
  }

  private function getInbitefAkun()
  {
    try {
      // 1. Inisialisasi Service
      $directory = new GoogleDirectoryService();

      // 2. Impersonate sebagai SUPER ADMIN (Wajib!)
      // Menggunakan email Pak Mahfudz (Super Admin)
      $adminEmail = 'mahfudz@inbitef.ac.id';

      // 3. Ambil Semua User
      $users = $directory->impersonate($adminEmail)->getAllUsers();

      return $users;
    } catch (\Exception $e) {
      return false;
    }
  }

  public function index(Request $request, Response $response): View
  {
    $params = $this->getQueryParams($request);

    // 1. Ambil Data dari Google Directory (Raw Array)
    $googleUsers = $this->getInbitefAkun();
    if ($googleUsers === false) {
      $googleUsers = [];
    }

    // 2. Filtering (Search)
    if (!empty($params['search'])) {
      $googleUsers = $this->filterUsers($googleUsers, $params['search']);
    }

    // 3. Sorting
    $googleUsers = $this->sortUsers($googleUsers, $params['sort'], $params['order']);

    // 4. Pagination (Manual array_slice)
    $total = count($googleUsers);
    $totalPages = ceil($total / $params['limit']);
    $offset = ($params['page'] - 1) * $params['limit'];

    $paginatedUsers = array_slice($googleUsers, $offset, $params['limit']);

    // 5. Enrichment (Gabungkan dengan Data DB Lokal)
    $finalUsers = $this->enrichUsersWithLocalDb($paginatedUsers);

    return $response->renderPage([
      'users' => $finalUsers,
      'user_loggin' => $this->session->get('user'),
      'pagination' => [
        'current_page' => $params['page'],
        'per_page' => $params['limit'],
        'total_items' => $total,
        'total_pages' => $totalPages,
        'has_next' => $params['page'] < $totalPages,
        'has_prev' => $params['page'] > 1,
        'search' => $params['search'],
        'sort' => $params['sort'],
        'order' => $params['order']
      ]
    ], ['meta' => ['title' => 'Manajemen User']]);
  }

  private function getQueryParams(Request $request): array
  {
    return [
      'page' => max(1, (int)($request->query['page'] ?? 1)),
      'limit' => max(1, (int)($request->query['limit'] ?? 10)),
      'search' => strtolower(trim($request->query['search'] ?? '')),
      'sort' => $request->query['sort'] ?? 'name',
      'order' => $request->query['order'] ?? 'asc'
    ];
  }

  private function filterUsers(array $users, string $search): array
  {
    return array_filter($users, function ($user) use ($search) {
      return str_contains(strtolower($user['name'] ?? ''), $search) ||
        str_contains(strtolower($user['email'] ?? ''), $search);
    });
  }

  private function sortUsers(array $users, string $sort, string $order): array
  {
    usort($users, function ($a, $b) use ($sort, $order) {
      $valA = strtolower($a[$sort] ?? '');
      $valB = strtolower($b[$sort] ?? '');

      if ($valA == $valB) return 0;

      $result = ($valA < $valB) ? -1 : 1;
      return ($order === 'desc') ? -$result : $result;
    });
    return $users;
  }

  private function enrichUsersWithLocalDb(array $googleUsers): array
  {
    $emails = array_column($googleUsers, 'email');
    $localUsersMap = [];

    if (!empty($emails)) {
      $placeholders = implode(',', array_fill(0, count($emails), '?'));
      $stmt = $this->model->getDb()->prepare("SELECT * FROM users WHERE email IN ($placeholders)");
      $stmt->execute($emails);
      $localUsers = $stmt->fetchAll();

      foreach ($localUsers as $local) {
        $localUsersMap[$local['email']] = $local;
      }
    }

    return array_map(function ($gUser) use ($localUsersMap) {
      $email = $gUser['email'];
      $local = $localUsersMap[$email] ?? null;

      return [
        'email' => $email,
        'name' => $gUser['name'],
        'google_org_unit' => $gUser['orgUnit'] ?? '/',
        'is_registered' => !is_null($local),
        'id' => $local['id'] ?? null,
        'role' => $local['role'] ?? 'user',
        'is_active' => $local['is_active'] ?? false,
        'last_login_at' => $local['last_login_at'] ?? null,
        'avatar' => $local['avatar'] ?? null
      ];
    }, $googleUsers);
  }

  public function show(Request $request, Response $response): View
  {
    $id = $request->param('id');
    $item = $this->model->find($id);

    return $response->renderPage(['item' => $item], ['meta' => ['title' => 'Detail User']]);
  }

  public function edit(Request $request, Response $response): View
  {
    $id = $request->param('id');
    $item = $this->model->find($id);

    return $response->renderPage(['item' => $item], ['meta' => ['title' => 'Edit User']]);
  }

  public function update(Request $request, Response $response): RedirectResponse
  {
    $id = $request->param('id');
    $data = $request->getBody();

    // Filter allowed fields
    $allowedFields = ['role', 'is_active'];
    $updateData = array_intersect_key($data, array_flip($allowedFields));

    $this->model->updateById($id, $updateData);

    return $response->redirect('/users');
  }

  public function registerFromGoogle(Request $request, Response $response): RedirectResponse
  {
    $data = $request->getBody();
    $email = $data['email'] ?? null;

    if (!$email) {
      return $response->redirect('/users');
    }

    $existing = $this->model->findByEmail($email);

    if ($existing) {
      $this->model->updateById($existing['id'], [
        'role' => 'approver',
        'is_active' => 1,
      ]);

      return $response->redirect('/users');
    }

    $this->model->createFromGoogle([
      'email' => $email,
      'name' => $data['name'] ?? null,
      'avatar' => $data['avatar'] ?? null,
      'google_id' => $data['google_id'] ?? null,
    ]);

    return $response->redirect('/users');
  }

  public function destroy(Request $request, Response $response): RedirectResponse
  {
    $id = $request->param('id');
    $this->model->deleteById($id);

    return $response->redirect('/users');
  }
}
