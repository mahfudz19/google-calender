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

  /**
   * Mengambil daftar user dari Google Workspace via Service Account
   */
  private function getInbitefAkun(): array
  {
    try {
      $directory = new GoogleDirectoryService();
      // Impersonate sebagai Super Admin
      $adminEmail = env('GOOGLE_ADMIN', 'mahfudz@inbitef.ac.id');

      return $directory->impersonate($adminEmail)->getAllUsers();
    } catch (\Exception $e) {
      throw new \Exception($e->getMessage() ?? 'Gagal mengambil data user dari Google', 1);
    }
  }

  /**
   * Menggabungkan data dari Google API dengan data lokal (Database MySQL)
   */
  private function enrichUsersWithLocalDb(array $googleUsers): array
  {
    if (empty($googleUsers)) {
      return [];
    }

    $emails = array_column($googleUsers, 'email');
    $localUsersMap = $this->model->getUsersMapByEmails($emails);

    return array_map(function ($gUser) use ($localUsersMap) {
      $email = $gUser['email'];
      $local = $localUsersMap[$email] ?? null;

      return [
        'email'           => $email,
        'name'            => $gUser['name'] ?? 'Unknown',
        'google_org_unit' => $gUser['orgUnit'] ?? '/',
        'is_registered'   => !is_null($local),
        'id'              => $local['id'] ?? null,
        'role'            => $local['role'] ?? 'user',
        'is_active'       => $local['is_active'] ?? false,
        'last_login_at'   => $local['last_login_at'] ?? null,
        'avatar'          => $local['avatar'] ?? null
      ];
    }, $googleUsers);
  }

  /**
   * MENAMPILKAN HALAMAN UTAMA MANAJEMEN USER
   */
  public function index(Request $request, Response $response): View
  {
    // 1. Ambil seluruh data dari Google
    $googleUsers = $this->getInbitefAkun();

    // 2. Gabungkan dengan data dari DB Lokal
    $finalUsers = $this->enrichUsersWithLocalDb($googleUsers);

    $user_login = $this->session->get('user');

    // 3. Render ke View Mazu Engine
    return $response->renderPage(
      ['users' => $finalUsers, 'user_login' => $user_login],
      ['meta' => ['title' => 'Manajemen User | Mazu Calendar']]
    );
  }

  /**
   * Menampilkan Halaman Detail User
   */
  public function show(Request $request, Response $response): View
  {
    $id = $request->param('id');
    $item = $this->model->find($id);

    return $response->renderPage(
      ['item' => $item],
      ['meta' => ['title' => 'Detail User']]
    );
  }

  /**
   * Menampilkan Halaman Edit Role & Status User
   */
  public function edit(Request $request, Response $response): View
  {
    $id = $request->param('id');
    $item = $this->model->find($id);

    return $response->renderPage(
      ['item' => $item],
      ['meta' => ['title' => 'Edit Akses User']]
    );
  }

  /**
   * Proses Update Data User (Role / Status)
   */
  public function update(Request $request, Response $response): RedirectResponse
  {
    $id = $request->param('id');
    $data = $request->getBody();

    // Filter keamanan: Hanya role dan is_active yang boleh diedit
    $allowedFields = ['role', 'is_active'];
    $updateData = array_intersect_key($data, array_flip($allowedFields));

    $this->model->updateById($id, $updateData);

    return $response->redirect('/users');
  }

  /**
   * Mendaftarkan User dari Google ke Database Lokal secara Manual/Otomatis
   */
  public function registerFromGoogle(Request $request, Response $response): RedirectResponse
  {
    $data = $request->getBody();
    $email = $data['email'] ?? null;

    if (!$email) {
      return $response->redirect('/users');
    }

    $existing = $this->model->findByEmail($email);

    // Jika user sudah ada di DB, kita hanya update hak aksesnya
    if ($existing) {
      $this->model->updateById($existing['id'], [
        'role' => 'approver',
        'is_active' => 1,
      ]);
      return $response->redirect('/users');
    }

    // Jika user belum ada, insert ke DB lokal Mazu
    $this->model->createFromGoogle([
      'email'     => $email,
      'name'      => $data['name'] ?? 'User Sistem',
      'avatar'    => $data['avatar'] ?? null,
      'google_id' => $data['google_id'] ?? null,
      'role'      => 'approver',
      'is_active' => 1
    ]);

    return $response->redirect('/users');
  }

  /**
   * Menghapus User dari Database Lokal
   */
  public function destroy(Request $request, Response $response): RedirectResponse
  {
    $id = $request->param('id');
    $this->model->deleteById($id);

    return $response->redirect('/users');
  }
}
