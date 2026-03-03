<?php

namespace Addon\Controllers;

use Addon\Middleware\RoleMiddleware;
use App\Core\Http\Request;
use App\Core\Http\Response;
use App\Core\View\View;
use App\Core\Http\RedirectResponse;
use Addon\Models\ApprovalModel;
use Addon\Services\GoogleCalendarService;
use App\Core\Http\JsonResponse;
use App\Core\Queue\JobDispatcher;
use App\Exceptions\AuthorizationException;
use App\Services\SessionService;
use Error;

class AgendaController
{
  private string $adminEmail;

  public function __construct(
    private ApprovalModel $model,
    private SessionService $session,
    private ApiController $apiController,
    private JobDispatcher $dispatcher,
    private RoleMiddleware $roleMiddleware
  ) {
    $this->model = $model;
    $this->session = $session;
    $this->adminEmail = env('GOOGLE_ADMIN', 'mahfudz@inbitef.ac.id');
  }

  // Dashboard Kalender Utama
  public function index(Request $request, Response $response)
  {
    // Ambil sesi user saat ini
    $userSession = $this->session->get('user') ?? [];
    $role = $userSession['role'] ?? 'user';
    $userEmail = $userSession['email'] ?? '';

    // Ambil seluruh data agenda
    $allAgendas = $this->model->all() ?: [];

    // 1. Data untuk Widget: Pengajuan Terakhir Saya (Max 5)
    $userAgendas = array_filter($allAgendas, function ($a) use ($userEmail) {
      return ($a['requester_email'] ?? '') === $userEmail;
    });
    // Sortir dari yang terbaru
    usort($userAgendas, fn($a, $b) => strtotime($b['created_at'] ?? 'now') <=> strtotime($a['created_at'] ?? 'now'));
    $recentAgendas = array_slice($userAgendas, 0, 5);

    // 2. Data untuk Widget: Tugas Approval Tertunda (Hanya untuk Admin/Approver)
    $pendingTasks = [];
    if (in_array($role, ['admin', 'approver'])) {
      $pendingTasks = array_filter($allAgendas, fn($a) => $a['status'] === 'pending');
      usort($pendingTasks, fn($a, $b) => strtotime($b['created_at'] ?? 'now') <=> strtotime($a['created_at'] ?? 'now'));
      $pendingTasks = array_slice($pendingTasks, 0, 5);
    }

    // 3. semua agenda dengan status approved
    $approvedAgendas = array_filter($allAgendas, fn($a) => $a['status'] === 'approved');
    usort($approvedAgendas, fn($a, $b) => strtotime($b['created_at'] ?? 'now') <=> strtotime($a['created_at'] ?? 'now'));

    // Render ke engine Mazu
    return $response->renderPage([
      'role'  => $role,
      'recentAgendas' => $recentAgendas,
      'pendingTasks'  => $pendingTasks,
      'approvedAgendas' => $approvedAgendas
    ], [
      'meta'  => ['title' => 'Dashboard | Mazu Calendar']
    ]);
  }

  // Form Pengajuan Agenda Baru
  public function create(Request $request, Response $response): View
  {
    $ruangan = $this->apiController->getRuanganApi(['perPage' => 9999]);

    return $response->renderPage(['ruangan' => $ruangan['data']], ['meta' => ['title' => 'Ajukan Agenda Baru']]);
  }

  // Proses Simpan Pengajuan
  public function store(Request $request, Response $response)
  {
    try {
      $data = $request->getBody();
      $user = $this->session->get('user');

      // Tambahkan info requester otomatis
      $body = [];
      $body['title'] = $data['title'] ?? null;
      $body['description'] = $data['description'] ?? null;
      $body['start_time'] = $data['start_time'] ?? null;
      $body['end_time'] = $data['end_time'] ?? null;
      $body['location'] = $data['location'] ?? null;
      $body['requester_name'] = $user['name'] ?? 'Guest';
      $body['requester_email'] = $user['email'] ?? null;
      $body['requester_role'] = $user['role'] ?? 'user';
      $body['requester_avatar'] = $user['avatar'] ?? null;
      $body['status'] = 'pending';

      $body['ruangan_id'] = $data['ruangan_id'] ?? null;
      $body['ruangan_name'] = $data['ruangan_name'] ?? null;
      $body['ruangan_location'] = $data['ruangan_location'] ?? null;
      $body['ruangan_capacity'] = $data['ruangan_capacity'] ?? null;
      $this->model->create($body);


      return $response->redirect('/agenda');
    } catch (\Throwable $th) {
      return $response->redirect('/agenda/create?error=500&message=' . urlencode($th->getMessage()));
    }
  }

  // Halaman "Pengajuan Saya"
  public function myAgenda(Request $request, Response $response): View
  {
    $userEmail = $_SESSION['user']['email'] ?? '';

    // 1. Ambil Parameter dari Query String URL (Contoh: /agenda?status=approved&page=2)
    $status = $request->get('status');
    $page = (int) ($request->get('page') ?? 1);
    if ($page < 1) $page = 1;

    // 2. Setup Limit dan Offset untuk Paginasi
    $limit = 10;
    $offset = ($page - 1) * $limit;

    // Validasi filter status
    if (!in_array($status, ['pending', 'approved', 'rejected'])) {
      $status = null;
    }

    // 3. Eksekusi Data dari Model
    $agendas = $this->model->getByRequester($userEmail, $status, $limit, $offset);
    $totalAgendas = $this->model->countByRequester($userEmail, $status);
    $totalPages = ceil($totalAgendas / $limit);

    // 4. Render ke Mazu View
    return $response->renderPage(
      [
        'agendas' => $agendas,
        'currentStatus' => $status,
        'currentPage' => $page,
        'totalPages' => $totalPages,
        'totalAgendas' => $totalAgendas
      ],
      ['meta' => ['title' => 'Agenda Saya | Mazu Calendar']]
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
    $ruangan = $this->apiController->getRuanganApi(['perPage' => 9999]);
    if ($agenda['status'] !== 'pending') {
      $this->roleMiddleware->isCanAccess(['admin']);
    }

    return $response->renderPage(
      ['agenda' => $agenda, 'ruangan' => $ruangan['data']],
      ['meta' => ['title' => 'Edit Agenda']]
    );
  }

  // Proses Update Agenda
  public function update(Request $request, Response $response): RedirectResponse
  {
    try {
      $id = $request->param('id');
      $data = $request->getBody();

      $body = [];
      $body['title'] = $data['title'] ?? null;
      $body['description'] = $data['description'] ?? null;
      $body['start_time'] = $data['start_time'] ?? null;
      $body['end_time'] = $data['end_time'] ?? null;
      $body['location'] = $data['location'] ?? null;

      $body['ruangan_id'] = $data['ruangan_id'] ?? null;
      $body['ruangan_name'] = $data['ruangan_name'] ?? null;
      $body['ruangan_location'] = $data['ruangan_location'] ?? null;
      $body['ruangan_capacity'] = $data['ruangan_capacity'] ?? null;

      $oldAgenda = $this->model->find($id);
      // 1. Cek Konflik Waktu (Menggunakan fungsi di Model)
      if ($oldAgenda['status'] === 'approved') {
        $this->roleMiddleware->isCanAccess(['admin']);

        $conflicts = $this->model->checkTimeConflict($body['start_time'], $body['end_time'], $body['ruangan_id'],  $id);
        if (!empty($conflicts)) {
          throw new \Exception("Conflict detected! Jadwal bertabrakan.");
        }
      }

      // 2. Update ke Database
      logger()->log('2. Update ke Database');
      $this->model->updateById($id, $body);

      // 3. Jika agenda sudah "approved", edit juga di Google Calendar
      logger()->log('3.1 Jika agenda sudah "approved", edit juga di Google Calendar');
      if ($oldAgenda['status'] === 'approved' && !empty($oldAgenda['google_event_id'])) {
        $timezone = new \DateTimeZone('Asia/Makassar');
        $start = new \DateTime($body['start_time'], $timezone);
        $end = new \DateTime($body['end_time'], $timezone);

        $gcal = new GoogleCalendarService();

        $updatedEventData = [
          'title'       => $body['title'] ?? $oldAgenda['title'],
          'description' => $body['description'] ?? $oldAgenda['description'],
          'location'    => $body['location'] ?? $oldAgenda['location'],
          'start_time'  => $start->format('c'),
          'end_time'    => $end->format('c'),
        ];

        // Hilangkan field yang null agar method patch GCal tidak error
        $updatedEventData = array_filter($updatedEventData);

        // Update via Google API
        logger()->log('3.2 Update via Google API');
        $gcal->impersonate($this->adminEmail)
          ->updateEvent($oldAgenda['google_event_id'], $updatedEventData, ['sendUpdates' => 'all']);
      }

      if ($oldAgenda['status'] === 'approved') {
        logger()->log('4 redirect /approval/history');
        return $response->redirect('/approval/history');
      }
      logger()->log('4 redirect /agenda');
      return $response->redirect('/agenda');
    } catch (\Throwable $th) {
      return $response->redirect('/agenda/' . $id . '/edit?error=500&message=' . urlencode($th->getMessage()));
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
