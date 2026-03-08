<?php

namespace Addon\Controllers;

use Addon\Models\ApprovalModel;
use App\Core\Http\Request;
use App\Core\Http\Response;
use Addon\Models\QueueModel;
use App\Core\Http\JsonResponse;

class QueueController
{
  public function __construct(private QueueModel $model, private ApprovalModel $approval_model) {}

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
    $job = $this->model->find($id);

    if (!$job) {
      return $response->json(['error' => "Job #{$id} tidak ditemukan."], 404);
    }

    // Parse payload to get approval ID
    $payload = json_decode($job['payload'], true);
    $approvalId = null;

    if (isset($payload['data']['id'])) {
      $approvalId = $payload['data']['id'];
    }

    // Reset approval status to pending if we have the ID
    if ($approvalId) {
      $approvalUpdated = $this->approval_model->updateById($approvalId, ['status' => 'pending', 'message' => null]);

      if (!$approvalUpdated) {
        return $response->json(['error' => 'Gagal mereset status approval ke pending.'], 400);
      }
    }

    // Delete the queue job
    $success = $this->model->deleteById($id);

    if ($success) {
      $message = $approvalId
        ? "Job #{$id} berhasil dihapus dan approval #{$approvalId} dikembalikan ke status pending."
        : "Job #{$id} berhasil dihapus.";

      return $response->json(['message' => $message]);
    }

    return $response->json(['error' => 'Gagal menghapus job.'], 400);
  }

  /**
   * Cek apakah worker sedang berjalan (Eksklusif & Akurat)
   */
  private function isWorkerActive(): bool
  {
    $basePath = dirname(__DIR__, 2);

    if (isProduction()) {
      if (function_exists('exec')) {
        $command = 'pgrep -f "' . $basePath . '/[m]azu queue:work"';
        exec($command, $output, $status);
        if ($status === 0 && !empty($output)) return true;
        if ($status === 1) return false;
      }
    } else {
      if (function_exists('exec')) {
        exec('pgrep -f "mazu queue:work"', $output);
        if (!empty($output)) {
          return true;
        }
      }
    }


    return false;
  }

  // Tambahkan method ini di QueueController.php
  public function show(Request $request, Response $response): JsonResponse
  {
    $id = $request->param('id');
    $job = $this->model->find($id);

    if (!$job) {
      return $response->json(['error' => "Job #{$id} tidak ditemukan."], 404);
    }

    // Parsing payload JSON agar mudah dibaca di frontend
    if (isset($job['payload'])) {
      $job['payload_parsed'] = json_decode($job['payload'], true);
    }

    return $response->json($job);
  }
}
