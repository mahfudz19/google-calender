<?php

namespace Addon\Controllers;

use App\Core\Http\Request;
use App\Core\Http\Response;
use App\Core\View\View;
use App\Core\Http\RedirectResponse;
use Addon\Models\ApprovalModel; // Menggunakan model yang sama dengan approval
use App\Services\SessionService;

class AgendaController
{
  public function __construct(
    private ApprovalModel $model,
    private SessionService $session
  ) {
    $this->model = $model;
    $this->session = $session;
  }

  // Dashboard Kalender Utama
  public function index(Request $request, Response $response): View
  {
    // Nanti ambil data agenda yang approved saja
    // $agendas = $this->model->getApproved(); 
    return $response->renderPage([
      // 'agendas' => $agendas
    ], ['meta' => ['title' => 'Kalender Kegiatan']]);
  }

  // Form Pengajuan Agenda Baru
  public function create(Request $request, Response $response): View
  {
    return $response->renderPage([], ['meta' => ['title' => 'Ajukan Agenda Baru']]);
  }

  // Proses Simpan Pengajuan
  public function store(Request $request, Response $response): RedirectResponse
  {
    $data = $request->getBody();
    $user = $this->session->get('user');

    // Tambahkan info requester otomatis
    $data['requester_name'] = $user['name'] ?? 'Guest';
    $data['requester_email'] = $user['email'] ?? null;
    $data['requester_role'] = $user['role'] ?? 'user';
    $data['requester_avatar'] = $user['avatar'] ?? null;
    $data['status'] = 'pending'; // Default status

    $this->model->create($data);

    return $response->redirect('/agenda');
  }

  // Halaman "Pengajuan Saya"
  public function myAgenda(Request $request, Response $response): View
  {
    $user = $this->session->get('user');
    // TODO: Filter by requester_email di model nanti
    // $myAgendas = $this->model->getByRequester($user['email']);
    $myAgendas = []; // Placeholder

    return $response->renderPage([
      'myAgendas' => $myAgendas
    ], ['meta' => ['title' => 'Pengajuan Saya']]);
  }

  // Detail Agenda
  public function show(Request $request, Response $response): View
  {
    $id = $request->param('id');
    $agenda = $this->model->find($id);

    return $response->renderPage([
      'agenda' => $agenda
    ], ['meta' => ['title' => 'Detail Agenda']]);
  }

  // Form Edit Agenda
  public function edit(Request $request, Response $response): View
  {
    $id = $request->param('id');
    $agenda = $this->model->find($id);

    // TODO: Cek apakah status masih pending & milik user ini

    return $response->renderPage([
      'agenda' => $agenda
    ], ['meta' => ['title' => 'Edit Agenda']]);
  }

  // Proses Update Agenda
  public function update(Request $request, Response $response): RedirectResponse
  {
    $id = $request->param('id');
    $data = $request->getBody();

    $this->model->updateById($id, $data);

    return $response->redirect('/agenda');
  }

  // Batalkan Pengajuan
  public function cancel(Request $request, Response $response): RedirectResponse
  {
    $id = $request->param('id');

    // Soft delete atau set status cancelled
    // $this->model->deleteById($id); 
    // Atau lebih baik:
    // $this->model->updateStatus($id, 'cancelled');

    // Sementara delete dulu sesuai model yang ada
    $this->model->deleteById($id);

    return $response->redirect('/agenda');
  }
}
