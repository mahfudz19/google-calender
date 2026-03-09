<?php

namespace Addon\Controllers;

use Addon\Models\ApprovalModel;
use App\Core\Http\JsonResponse;
use App\Core\Http\Request;
use App\Core\Http\Response;
use App\Core\View\View;

class InputManyController
{
  public function __construct(private ApprovalModel $approvalModel) {}

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
      // Ambil payload JSON dari request
      $body = $request->getBody();
      $agendas = $body['agendas'] ?? [];

      if (empty($agendas)) {
        return $response->json([
          'status' => 'error',
          'message' => 'Data agenda kosong.'
        ], 400);
      }

      // Lakukan pengecekan via Model
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
    try {
      $body = $request->getBody();
      $agendas = $body['agendas'] ?? [];

      if (empty($agendas)) return $response->json(['status' => 'error', 'message' => 'Data agenda kosong.'], 400);

      return $response->json(['status' => 'success', 'data' => $agendas]);
    } catch (\Throwable $th) {
      return $response->json(['status' => 'error', 'message' => 'Terjadi kesalahan internal: ' . $th->getMessage()], 500);
    }
  }
}
