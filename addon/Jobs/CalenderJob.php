<?php

declare(strict_types=1);

namespace Addon\Jobs;

use App\Core\Foundation\Application;
use App\Core\Database\DatabaseManager;

class CalenderJob
{
    private ?int $jobId = null;
    private $db;

    public function __construct(private Application $app, DatabaseManager $dbManager)
    {
        $this->db = $dbManager->connection();
    }

    /**
     * Set job ID untuk progress tracking
     */
    public function setJobId(int $jobId): void
    {
        $this->jobId = $jobId;
    }

    /**
     * Get current job ID
     */
    private function getJobId(): int
    {
        if ($this->jobId === null) {
            throw new \Exception('Job ID not set');
        }
        return $this->jobId;
    }

    /**
     * Main job execution method
     */
    public function handle(array $data = []): void
    {
        $jobId = $this->getJobId();
        $this->updateProgress($jobId, 0, 'pending', 'Starting job...');

        try {
            // Step 1: Validasi data (25%)
            $this->updateProgress($jobId, 25, 'processing', 'Validating data...');
            $this->validateData($data);

            // Step 2: Proses utama (50%)
            $this->updateProgress($jobId, 50, 'processing', 'Processing data...');
            $this->processData($data);

            // Step 3: Kirim notifikasi (75%)
            $this->updateProgress($jobId, 75, 'processing', 'Sending notifications...');
            $this->sendNotification($data);

            // Step 4: Cleanup (100%)
            $this->updateProgress($jobId, 100, 'success', 'Job success successfully');
            $this->cleanup($data);

        } catch (\Exception $e) {
            $this->updateProgress($jobId, 0, 'failed', 'Job failed: ' . $e->getMessage(), $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update progress ke database
     */
    private function updateProgress(int $jobId, int $progress, string $status, string $message, ?string $error = null): void
    {
        $data = [
            'progress' => $progress,
            'status' => $status,
            'current_step' => $message,
            'completed_at' => $status === 'completed' ? time() : null,
        ];

        if ($error) {
            $data['error_message'] = $error;
        }

        $stmt = $this->db->prepare(
            "UPDATE queues SET progress = :progress, status = :status, current_step = :step, completed_at = :completed_at, error_message = :error WHERE id = :id"
        );
        $stmt->execute([
            'progress' => $progress,
            'status' => $status,
            'step' => $message,
            'completed_at' => $data['completed_at'],
            'error' => $error ?? null,
            'id' => $jobId
        ]);
    }

    /**
     * Validasi input data
     */
    private function validateData(array $data): void
    {
        if (empty($data)) {
            throw new \Exception('Data cannot be empty');
        }
        // Add your validation logic here
    }

    /**
     * Proses utama job
     */
    private function processData(array $data): void
    {
        echo "Processing data: " . json_encode($data) . "\n";
        // Add your main processing logic here
        sleep(2); // Simulate processing time
    }

    /**
     * Kirim notifikasi
     */
    private function sendNotification(array $data): void
    {
        echo "Sending notification for job data\n";
        // Add your notification logic here
        sleep(1); // Simulate notification time
    }

    /**
     * Cleanup resources
     */
    private function cleanup(array $data): void
    {
        echo "Cleaning up after job completion\n";
        // Add your cleanup logic here
    }
}