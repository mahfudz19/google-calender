<?php

namespace Addon\Controllers;

use App\Core\Http\Request;
use App\Core\Http\Response;
use App\Core\View\View;
use App\Core\Http\RedirectResponse;
use Addon\Models\ApprovalModel;
use Addon\Services\GoogleCalendarService;
use Addon\Services\GoogleDirectoryService;

class ApprovalController
{
  private string $adminEmail = 'mahfudz@inbitef.ac.id';

  public function __construct(private ApprovalModel $model)
  {
    $this->model = $model;
  }

  public function index(Request $request, Response $response): View
  {
    $approvals = $this->model->getPending();

    return $response->renderPage([
      'approvals' => $approvals,
    ], ['meta' => ['title' => 'Persetujuan Agenda']]);
  }

  public function history(Request $request, Response $response): View
  {
    $approvals = $this->model->getHistory();

    return $response->renderPage([
      'approvals' => $approvals,
    ], ['meta' => ['title' => 'Riwayat Persetujuan']]);
  }

  public function approve(Request $request, Response $response): RedirectResponse
  {
    try {
      $id = $request->param('id');
      $agenda = $this->model->find($id);

      if (!$agenda) {
        throw new \Exception("Agenda tidak ditemukan");
      }

      // 1. Cek Konflik Waktu (Menggunakan fungsi di Model)
      $conflicts = $this->model->checkTimeConflict($agenda['start_time'], $agenda['end_time'], $id);
      if (!empty($conflicts)) {
        throw new \Exception("Conflict detected! Jadwal bertabrakan.");
      }

      // 2. Ambil Semua User dari Google Directory
      // $directory = new GoogleDirectoryService();
      // $users = $directory->impersonate($this->adminEmail)->getAllUsers();

      // // Mapping user ke format array attendees
      // $attendees = [];
      // foreach ($users as $u) {
      //   if (!empty($u['email'])) {
      //     $attendees[] = ['email' => $u['email']];
      //   }
      // }

      // example attendees
      $attendees = [
        ['email' => 'sultan@student.univeral.ac.id'],
        ['email' => 'mahfudz@inbitef.ac.id'],
      ];

      // 3. Insert ke Google Calendar (1x Call untuk semua user)
      $gcal = new GoogleCalendarService();
      $eventData = [
        'title'       => $agenda['title'],
        'description' => $agenda['description'],
        'location'    => $agenda['location'],
        'start_time'  => date('c', strtotime($agenda['start_time'])),
        'end_time'    => date('c', strtotime($agenda['end_time'])),
        'attendees'   => $attendees
      ];

      // Kirim event dan dapatkan ID-nya
      $googleEventId = $gcal->impersonate($this->adminEmail)
        ->insertEvent($eventData, ['sendUpdates' => 'all']);

      // 4. Update Database (Ubah status & simpan ID Event GCal)
      $this->model->updateStatus($id, 'approved', null, $googleEventId);

      return $response->redirect('/approval');
      throw new \Exception("Error Processing Request", 1);
    } catch (\Throwable $th) {
      return $response->redirect('/approval?error=500&message=' . urlencode($th->getMessage()));
    }
  }

  public function reject(Request $request, Response $response): RedirectResponse
  {
    try {
      $id = $request->param('id');
      $body = $request->getBody();
      $this->model->updateStatus($id, 'rejected', $body['comment']);

      return $response->redirect('/approval');
    } catch (\Throwable $th) {
      return $response->redirect('/approval?error=500&message=' . urlencode($th->getMessage()));
    }
  }
}
