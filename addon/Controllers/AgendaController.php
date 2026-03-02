<?php

namespace Addon\Controllers;

use App\Core\Http\Request;
use App\Core\Http\Response;
use App\Core\View\View;
use App\Core\Http\RedirectResponse;
use Addon\Models\ApprovalModel;
use Addon\Services\GoogleCalendarService;
use App\Core\Http\JsonResponse;
use App\Services\SessionService;
use Error;

class AgendaController
{
  private string $adminEmail = 'mahfudz@inbitef.ac.id';

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
    $data = $this->model->getAllApproved() ?: [];

    return $response->renderPage(
      ['total' => count($data), 'events' => $data],
      ['meta'  => ['title' => 'Dashboard | Mazu Calendar']]
    );
  }

  // Form Pengajuan Agenda Baru
  public function create(Request $request, Response $response): View
  {
    return $response->renderPage([], ['meta' => ['title' => 'Ajukan Agenda Baru']]);
  }

  // Proses Simpan Pengajuan
  public function store(Request $request, Response $response)
  {
    try {
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
    } catch (\Throwable $th) {
      return $response->redirect('/agenda/create?error=500&message=' . urlencode($th->getMessage()));
    }
  }

  // Halaman "Pengajuan Saya"
  public function myAgenda(Request $request, Response $response): View
  {
    $user = $this->session->get('user');
    $myAgendas = $this->model->getByRequester($user['email']);

    return $response->renderPage(
      ['myAgendas' => $myAgendas],
      ['meta' => ['title' => 'Pengajuan Saya']]
    );
  }

  // Detail Agenda
  public function show(Request $request, Response $response): View
  {
    $id = $request->param('id');
    $agenda = $this->model->find($id);

    return $response->renderPage(
      ['agenda' => $agenda],
      ['meta' => ['title' => 'Detail Agenda']]
    );
  }

  // Form Edit Agenda
  public function edit(Request $request, Response $response): View
  {
    $id = $request->param('id');
    $agenda = $this->model->find($id);

    // TODO: Cek apakah status masih pending & milik user ini

    return $response->renderPage(
      ['agenda' => $agenda],
      ['meta' => ['title' => 'Edit Agenda']]
    );
  }

  // Proses Update Agenda
  public function update(Request $request, Response $response): RedirectResponse
  {
    try {
      $id = $request->param('id');
      $data = $request->getBody();

      $oldAgenda = $this->model->find($id);

      // 1. Update ke Database
      $this->model->updateById($id, $data);

      // 2. Jika agenda sudah "approved", edit juga di Google Calendar
      if ($oldAgenda['status'] === 'approved' && !empty($oldAgenda['google_event_id'])) {
        $gcal = new GoogleCalendarService();

        $updatedEventData = [
          'title'       => $data['title'] ?? $oldAgenda['title'],
          'description' => $data['description'] ?? $oldAgenda['description'],
          'location'    => $data['location'] ?? $oldAgenda['location'],
          'start_time'  => isset($data['start_time']) ? date('c', strtotime($data['start_time'])) : null,
          'end_time'    => isset($data['end_time']) ? date('c', strtotime($data['end_time'])) : null,
        ];

        // Hilangkan field yang null agar method patch GCal tidak error
        $updatedEventData = array_filter($updatedEventData);

        // Update via Google API
        $gcal->impersonate($this->adminEmail)
          ->updateEvent($oldAgenda['google_event_id'], $updatedEventData, ['sendUpdates' => 'all']);
      }

      return $response->redirect('/agenda');
    } catch (\Throwable $th) {
      return $response->redirect('/agenda/edit?error=500&message=' . urlencode($th->getMessage()));
    }
  }

  // Batalkan Pengajuan
  public function cancel(Request $request, Response $response): RedirectResponse
  {
    try {
      $id = $request->param('id');
      $agenda = $this->model->find($id);

      // 1. Jika agenda sudah disetujui, HAPUS dulu dari Google Calendar
      if ($agenda['status'] === 'approved' && !empty($agenda['google_event_id'])) {
        $gcal = new GoogleCalendarService();
        // Hapus dan kirim notifikasi batal ke peserta
        $gcal->impersonate($this->adminEmail)
          ->deleteEvent($agenda['google_event_id'], ['sendUpdates' => 'all']);
      }

      // 2. Hapus dari Database Lokal
      $this->model->deleteById($id);

      return $response->redirect('/agenda');
    } catch (\Throwable $th) {
      return $response->redirect('/agenda?error=500&message=' . urlencode($th->getMessage()));
    }
  }

  public function getCalendarEvents(Request $request, Response $response): JsonResponse
  {
    try {
      $allAgendas = $this->model->all() ?: [];

      $events = [];
      foreach ($allAgendas as $agenda) {
        // Hanya tampilkan agenda yang sudah "Approved" di kalender
        if ($agenda['status'] === 'approved') {
          $events[] = [
            'id'    => $agenda['id'],
            'title' => $agenda['title'],
            // FullCalendar membutuhkan format kalender ISO8601
            'start' => date('Y-m-d\TH:i:s', strtotime($agenda['start_time'])),
            'end'   => date('Y-m-d\TH:i:s', strtotime($agenda['end_time'])),
            'extendedProps' => [
              'location' => $agenda['location'] ?? 'Virtual',
              'requester' => $agenda['requester_name'] ?? 'Sistem'
            ],
            'className' => 'fc-event-custom' // Class khusus untuk modifikasi CSS
          ];
        }
      }

      return $response->json($events);
    } catch (\Throwable $th) {
      return $response->json(['error' => $th->getMessage()], 500);
    }
  }
}
