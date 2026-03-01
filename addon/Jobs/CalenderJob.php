<?php

declare(strict_types=1);

namespace Addon\Jobs;

use Addon\Models\ApprovalModel;
use Addon\Services\GoogleCalendarService;
use Addon\Services\GoogleDirectoryService;
use App\Core\Foundation\Application;
use App\Core\Database\DatabaseManager;

class CalenderJob
{
    private ?int $jobId = null;
    private $db;
    private string $adminEmail = 'mahfudz@inbitef.ac.id';


    public function __construct(private Application $app, private ApprovalModel $model, DatabaseManager $dbManager)
    {
        $this->db = $dbManager->connection();
    }

    /**
     * Main job execution method
     */
    public function handle(array $data): void
    {
        $jobId = $this->getJobId();
        $this->updateProgress($jobId, 0, 'pending', 'Starting job...');

        try {
            // Step 1: Validasi data (25%)
            $this->updateProgress($jobId, 25, 'processing', 'Validating data...');
            $agenda = $this->validateData($data['id']);

            // Step 2: Proses utama (50%)
            $this->updateProgress($jobId, 50, 'processing', 'Processing data...');
            $eventData = $this->getProcessData($agenda);

            // Step 3: Kirim notifikasi (75%)
            $this->updateProgress($jobId, 75, 'processing', 'Sending notifications...');
            $this->prossessData($eventData, $agenda['id']);

            // Step 4: Cleanup (100%)
            $this->updateProgress($jobId, 100, 'success', 'Job success successfully');
            $this->cleanup($agenda);
        } catch (\Exception $e) {
            $this->updateProgress($jobId, 0, 'failed', 'Job failed: ' . $e->getMessage(), $e->getMessage());
            throw $e;
        }
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
    private function validateData(string $id): array
    {
        $agenda = $this->model->find($id);

        if (!$agenda) {
            throw new \Exception("Agenda tidak ditemukan");
        }
        if ($agenda['status'] !== 'pending') {
            throw new \Exception("Status agenda tidak valid");
        }
        $this->model->updateStatus($agenda['id'], 'processing');

        // 1. Cek Konflik Waktu (Menggunakan fungsi di Model)
        $conflicts = $this->model->checkTimeConflict($agenda['start_time'], $agenda['end_time'], $agenda['id']);
        if (!empty($conflicts)) {
            throw new \Exception("Conflict detected! Jadwal bertabrakan.");
        }

        return $agenda;
    }

    /**
     * Proses utama job
     */
    private function getProcessData(array $agenda): array
    {

        // 2. Ambil Semua User dari Google Directory
        $directory = new GoogleDirectoryService();
        $users = $directory->impersonate($this->adminEmail)->getAllUsers();

        // Mapping user ke format array attendees
        $attendees = [];
        foreach ($users as $u) {
            if (!empty($u['email'])) {
                $attendees[] = ['email' => $u['email']];
            }
        }

        // // example attendees
        // $attendees = [
        //     ['email' => 'sultan@student.univeral.ac.id'],
        //     ['email' => 'mahfudz@inbitef.ac.id'],
        // ];

        // 3. Insert ke Google Calendar (1x Call untuk semua user)
        return [
            'title'       => $agenda['title'],
            'description' => $agenda['description'],
            'location'    => $agenda['location'],
            'start_time'  => date('c', strtotime($agenda['start_time'])),
            'end_time'    => date('c', strtotime($agenda['end_time'])),
            'attendees'   => $attendees
        ];
    }

    /**
     * Kirim notifikasi
     */
    private function prossessData($eventData, $id): void
    {
        // Kirim event dan dapatkan ID-nya
        echo ('Kirim data approvals dengan id =' . $id . ', dan dapatkan ID-nya');
        $gcal = new GoogleCalendarService();
        $googleEventId = $gcal->impersonate($this->adminEmail)->insertEvent($eventData, ['sendUpdates' => 'all']);

        // 4. Update Database (Ubah status & simpan ID Event GCal)
        $this->model->updateStatus($id, 'approved', null, $googleEventId);
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
