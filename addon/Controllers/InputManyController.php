<?php

namespace Addon\Controllers;

use Addon\Models\ApprovalModel;
use App\Core\Http\JsonResponse;
use App\Core\Http\Request;
use App\Core\Http\Response;
use App\Core\View\View;
use App\Core\Queue\JobDispatcher;
use Addon\Jobs\CalenderJob;

class InputManyController
{
  public function __construct(private ApprovalModel $approvalModel, private JobDispatcher $dispatcher) {}

  public function index(Request $request, Response $response): View
  {
    return $response->renderPage(
      [],
      ['meta' => ['title' => 'Input Banyak | ' . env('APP_NAME')]]
    );
  }

  public function checkDatabaseConflict(Request $request, Response $response): JsonResponse
  {
    try {
      $body = $request->getBody();
      $agendas = $body['agendas'] ?? [];

      if (empty($agendas)) {
        return $response->json([
          'status' => 'error',
          'message' => 'Data agenda kosong.'
        ], 400);
      }

      $conflicts = $this->approvalModel->checkBulkTimeConflicts($agendas);

      return $response->json([
        'status' => 'success',
        'has_conflicts' => !empty($conflicts),
        'conflicts' => $conflicts
      ]);
    } catch (\Throwable $th) {
      return $response->json([
        'status' => 'error',
        'message' => 'Terjadi kesalahan internal: ' . $th->getMessage()
      ], 500);
    }
  }

  public function upload(Request $request, Response $response): JsonResponse
  {
    // Ambil koneksi database dari model untuk memulai Transaksi
    $db = $this->approvalModel->getDb();

    try {
      $body = $request->getBody();
      $agendas = $body['agendas'] ?? [];

      if (empty($agendas)) {
        return $response->json(['status' => 'error', 'message' => 'Data agenda kosong.'], 400);
      }

      // 1. MULAI TRANSAKSI (Gembok Database)
      $db->beginTransaction();

      $dispatchedCount = 0;

      // 2. LOOPING INSERT & DISPATCH
      foreach ($agendas as $agenda) {

        // Susun data untuk di-insert ke tabel approvals
        $dataToInsert = [
          'title'            => $agenda['title'],
          'description'      => $agenda['description'] ?? null,
          'start_time'       => $agenda['start_time'],
          'end_time'         => $agenda['end_time'],
          // Optional fields
          'location'         => current(explode(' - ', $agenda['ruangan_name'] ?? '')) ?? null, // Fallback location
          'ruangan_id'       => $agenda['ruangan_id'] ?? null,
          'ruangan_name'     => $agenda['ruangan_name'] ?? null,
          'ruangan_capacity' => $agenda['ruangan_capacity'] ?? null,
          // Requester details dari session frontend
          'requester_name'   => $agenda['requester_name'] ?? null,
          'requester_email'  => $agenda['requester_email'] ?? null,
          'requester_role'   => $agenda['requester_role'] ?? null,
          'requester_avatar' => $agenda['requester_avatar'] ?? null,
          // Set status awal menjadi 'pending' (nanti Worker yang ubah jadi processing)
          'status'           => 'pending',
        ];

        // Simpan ke DB dan langsung tarik ID barunya
        $newAgendaId = $this->approvalModel->createGetId($dataToInsert);

        // Buat 1 Antrean (Tiket) untuk Agenda ini
        $this->dispatcher->dispatch(CalenderJob::class, ['id' => $newAgendaId]);

        $dispatchedCount++;
      }

      // 3. KOMIT TRANSAKSI JIKA SEMUA AMAN (Buka Gembok Database)
      $db->commit();

      // Respons secepat kilat dikembalikan ke Frontend
      return $response->json([
        'status' => 'success',
        'message' => "$dispatchedCount data agenda berhasil disimpan dan masuk ke dalam antrean sinkronisasi."
      ]);
    } catch (\Throwable $th) {
      // 4. BATALKAN SEMUA JIKA ADA SATU SAJA YANG GAGAL
      if ($db->inTransaction()) {
        $db->rollBack();
      }

      return $response->json([
        'status' => 'error',
        'message' => 'Gagal memproses data: ' . $th->getMessage()
      ], 500);
    }
  }
}
