<?php

namespace Addon\Controllers;

use App\Core\Http\Request;
use App\Core\Http\Response;
use Addon\Services\GoogleCalendarService;

class CalendarController
{
  private string $organizerEmail = 'mahfudz@inbitef.ac.id'; // Akun Pusat Pembuat Acara

  /**
   * CREATE: Broadcast Agenda ke banyak email sekaligus
   */
  public function broadcast(Request $request, Response $response)
  {
    try {
      $gcal = new GoogleCalendarService();
      $payload = $request->input(); // Ambil dari body JSON

      // Format minimal data yang diharapkan dari payload:
      // {
      //   "title": "Rapat Akbar",
      //   "start_time": "2026-03-01T09:00:00+08:00",
      //   "end_time": "2026-03-01T10:00:00+08:00",
      //   "attendees": [{"email": "user1@inbitef.ac.id"}, {"email": "user2@inbitef.ac.id"}]
      // }

      // Tambahkan parameter sendUpdates='all' agar notifikasi email terkirim ke peserta
      $eventId = $gcal->impersonate($this->organizerEmail)
        ->insertEvent($payload, ['sendUpdates' => 'all']);

      return $response->json([
        'status' => 'success',
        'message' => 'Agenda berhasil di-broadcast.',
        'data' => [
          'event_id' => $eventId,
          'total_attendees' => count($payload['attendees'] ?? [])
        ]
      ]);
    } catch (\Exception $e) {
      return $response->json(['status' => 'error', 'message' => $e->getMessage()], 500);
    }
  }

  /**
   * UPDATE: Mengedit agenda (misal ganti jam atau tambah peserta)
   */
  public function updateBroadcast(Request $request, Response $response)
  {
    try {
      $gcal = new GoogleCalendarService();
      $eventId = $request->param('id'); // Ambil ID dari URL, misal: /api/calendar/{id}
      $payload = $request->input(); // Ambil data yang mau diubah

      // Lakukan Update/Patch ke event ID tersebut
      $gcal->impersonate($this->organizerEmail)
        ->updateEvent($eventId, $payload, ['sendUpdates' => 'all']);

      return $response->json([
        'status' => 'success',
        'message' => 'Agenda berhasil diperbarui dan notifikasi update terkirim ke peserta.'
      ]);
    } catch (\Exception $e) {
      return $response->json(['status' => 'error', 'message' => $e->getMessage()], 500);
    }
  }

  /**
   * DELETE: Membatalkan/Menghapus agenda
   */
  public function deleteBroadcast(Request $request, Response $response)
  {
    try {
      $gcal = new GoogleCalendarService();
      $eventId = $request->param('id');

      // Hapus event dan beritahu semua peserta bahwa acara dibatalkan
      $gcal->impersonate($this->organizerEmail)
        ->deleteEvent($eventId, ['sendUpdates' => 'all']);

      return $response->json([
        'status' => 'success',
        'message' => 'Agenda berhasil dibatalkan dan ditarik dari kalender semua peserta.'
      ]);
    } catch (\Exception $e) {
      return $response->json(['status' => 'error', 'message' => $e->getMessage()], 500);
    }
  }
}
