<?php

namespace Addon\Controllers;

use Addon\Jobs\CalenderJob;
use App\Core\Http\Request;
use App\Core\Http\Response;
use App\Core\View\View;
use App\Core\Http\RedirectResponse;
use Addon\Models\QueueModel;
use App\Core\Http\JsonResponse;
use App\Core\Queue\JobDispatcher;

class QueueController
{
  public function __construct(private QueueModel $model, private JobDispatcher $dispatcher) {}

  // Controller hanya TRIGGER job
  public function testJob(Request $request, Response $response): JsonResponse
  {
    // 1. Data dari user (form, API, dll)
    $data = ['user_id' => 1, 'agenda_id' => 123, 'action' => 'send_notification'];

    // 2. Kirim ke queue (tidak ada logic di sini)
    $this->dispatcher->dispatch(CalenderJob::class, $data);

    return $response->json(['message' => 'Job di-dispatch']);
  }

  // Di QueueController
  public function monitor(Request $request, Response $response): JsonResponse
  {
    return $response->json([
      'failed_job' => $this->model->getFailedJobs(),
      'pending_count' => $this->model->getPendingJobsCount(),
      'queue_stats' => $this->model->getQueueStats()
    ]);
  }

  public function show(Request $request, Response $response): JsonResponse
  {
    $jobId = $request->param('id');
    $job = $this->model->find($jobId);

    if (!$job) {
      return $response->json(['error' => 'Job not found'], 404);
    }

    return $response->json($job);
  }
}
