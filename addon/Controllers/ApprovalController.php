<?php

namespace Addon\Controllers;

use App\Core\Http\Request;
use App\Core\Http\Response;
use App\Core\View\View;
use App\Core\Http\RedirectResponse;
use Addon\Models\ApprovalModel;

class ApprovalController
{
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
    $id = $request->param('id');
    $this->model->updateStatus($id, 'approved');

    return $response->redirect('/approval');
  }

  public function reject(Request $request, Response $response): RedirectResponse
  {
    try {
      $id = $request->param('id');
      $body = $request->getBody();
      $this->model->updateStatus($id, 'rejected', $body['comment']);
      // throw new \Exception("Error Processing Request", 1);
      

      return $response->redirect('/approval');
    } catch (\Throwable $th) {
      return $response->redirect('/approval?error=500&message=' . urlencode($th->getMessage()));
    }
  }
}
