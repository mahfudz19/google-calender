<?php

namespace Addon\Controllers;

use Addon\Jobs\CalenderJob;
use App\Core\Http\Request;
use App\Core\Http\Response;
use App\Core\View\View;
use App\Core\Http\RedirectResponse;
use Addon\Models\ApprovalModel;
use Addon\Services\GoogleCalendarService;
use Addon\Services\GoogleDirectoryService;
use App\Core\Http\JsonResponse;
use App\Core\Queue\JobDispatcher;

class ApprovalController
{
  public function __construct(private ApprovalModel $model, private JobDispatcher $dispatcher) {}

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

  public function approve(Request $request, Response $response): JsonResponse
  {
    try {
      $id = $request->param('id');
      $this->dispatcher->dispatch(CalenderJob::class, ['id' => $id]);

      return $response->json(['status' => 'success', 'message' => 'Job di-dispatch']);
    } catch (\Throwable $th) {
      return $response->json(['status' => 'error', 'message' => $th->getMessage()], 500);
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

  public function checkStatus(Request $request, Response $response): JsonResponse
  {
    try {
      $id = $request->param('id');
      $agenda = $this->model->find($id);

      if (!$agenda) {
        return $response->json([
          'status' => 'error',
          'message' => 'Agenda tidak ditemukan'
        ], 404);
      }

      return $response->json([
        'status' => 'success',
        'data' => [
          'approval_status' => $agenda['status'],
          'google_event_id' => $agenda['google_event_id'] ?? null
        ]
      ]);
    } catch (\Throwable $th) {
      return $response->json([
        'status' => 'error',
        'message' => $th->getMessage()
      ], 500);
    }
  }
}
