<?php

namespace Addon\Controllers;

use App\Core\Http\Request;
use App\Core\Http\Response;
use Addon\Models\QueueModel;
use App\Core\Http\JsonResponse;

class QueueController
{
  public function __construct(private QueueModel $model) {}

  // 1. Tampilkan semua antrian + status worker
  public function index(Request $request, Response $response): JsonResponse
  {
    return $response->json([
      'worker_active' => $this->isWorkerActive(),
      'stats'         => $this->model->getQueueStats(),
      'jobs'          => $this->model->all()
    ]);
  }

  // 2. Ubah status failed -> pending
  public function retry(Request $request, Response $response): JsonResponse
  {
    $id = $request->param('id');
    $success = $this->model->retryJob($id);

    if ($success) {
      return $response->json(['message' => "Job #{$id} berhasil dikembalikan ke antrian (pending)."]);
    }

    return $response->json(['error' => 'Gagal retry job. Pastikan job ada dan berstatus failed.'], 400);
  }

  // 3. Delete job
  public function destroy(Request $request, Response $response): JsonResponse
  {
    $id = $request->param('id');
    $success = $this->model->deleteById($id);

    if ($success) {
      return $response->json(['message' => "Job #{$id} berhasil dihapus."]);
    }

    return $response->json(['error' => 'Gagal menghapus job.'], 400);
  }

  /**
   * Cek apakah worker sedang berjalan
   */
  private function isWorkerActive(): bool
  {
    // Opsi 1: Mengecek file heartbeat dari worker
    $heartbeatFile = __DIR__ . '/../../../storage/logs/worker_heartbeat.json';
    if (file_exists($heartbeatFile)) {
      $data = json_decode(file_get_contents($heartbeatFile), true);

      // Jika worker mengirim sinyal kurang dari 5 menit yang lalu (300 detik), anggap aktif
      if (isset($data['last_seen_at']) && (time() - $data['last_seen_at'] < 300)) {
        return true;
      }
    }

    // Opsi 2 (Fallback): Cek langsung menggunakan process list di OS (Cocok untuk Mac/Linux)
    if (function_exists('exec')) {
      exec('pgrep -f "mazu queue:work"', $output);
      if (!empty($output)) {
        return true;
      }
    }

    return false;
  }
}
