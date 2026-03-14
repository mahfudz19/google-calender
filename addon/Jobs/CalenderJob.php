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
    private string $adminEmail;

    public function __construct(private Application $app, private ApprovalModel $model, DatabaseManager $dbManager)
    {
        $this->db = $dbManager->connection();
        $this->adminEmail = env('GOOGLE_ADMIN', 'mahfudz@inbitef.ac.id');
    }

    /**
     * Main job execution method
     */
    public function handle(array $data): void
    {
        $jobId = $this->getJobId();
        $this->updateProgress($jobId, 0, 'processing', 'Memulai sinkronisasi...');

        try {
            $id = $data['id'] ?? null;
            if (!$id) {
                throw new \Exception("ID Agenda tidak ditemukan di dalam payload Job.");
            }

            // Step 1: Validasi data (25%)
            $this->updateProgress($jobId, 25, 'processing', 'Membaca dan memvalidasi data agenda...');
            // FIX: Gunakan validateData() agar seluruh aturan bisnis tereksekusi
            $agenda = $this->validateData($id);

            // Step 2: Ambil semua User dari Google Directory (50%)
            $this->updateProgress($jobId, 50, 'processing', 'Mengambil data Directory Users...');
            $targetEmails = $this->getProcessData($agenda);

            // Step 3: Proses Push Massal (Google Batch API) (75%)
            $this->updateProgress($jobId, 75, 'processing', 'Mengirim data via Google API Batch...');
            $this->prossessData($agenda, $id, $targetEmails);

            $this->updateProgress($jobId, 100, 'success', 'Berhasil disinkronkan ke seluruh kalender!');
        } catch (\Throwable $th) {
            $this->updateProgress($jobId, 100, 'failed', $th->getMessage());
            throw $th;
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
    private function validateData(int|string $id): array
    {
        $agenda = $this->model->find($id);

        if (!$agenda) {
            throw new \Exception("Agenda tidak ditemukan di database.");
        }

        $allowedStatuses = ['pending', 'processing'];
        if (!in_array($agenda['status'], $allowedStatuses)) {
            throw new \Exception("Job dibatalkan: Agenda ini tidak valid untuk diproses karena berstatus '{$agenda['status']}'.");
        }

        if (!empty($agenda['google_event_id'])) {
            throw new \Exception("Job dibatalkan: Agenda sudah memiliki Google Event ID.");
        }

        // FIX: Tambahkan parameter ruangan_id sebagai parameter ke-3 yang benar
        $ruanganId = isset($agenda['ruangan_id']) ? (int)$agenda['ruangan_id'] : null;
        $conflicts = $this->model->checkTimeConflict($agenda['start_time'], $agenda['end_time'], $ruanganId, (int)$id);

        if (!empty($conflicts)) {
            throw new \Exception("Conflict detected! Jadwal bertabrakan dengan agenda lain.");
        }

        // Tandai sebagai sedang diproses
        $this->model->updateStatus($agenda['id'], 'processing');

        return $agenda;
    }

    /**
     * Proses utama job
     */
    private function getProcessData(array $agenda): array
    {
        $directory = new GoogleDirectoryService();
        $users = $directory->impersonate($this->adminEmail)->getAllUsers();

        // Ekstrak emailnya saja
        $targetEmails = [];
        foreach ($users as $u) {
            // Mendukung jika $u adalah objek atau array
            $email = is_object($u) ? ($u->primaryEmail ?? null) : ($u['email'] ?? $u['primaryEmail'] ?? null);
            if ($email) {
                $targetEmails[] = $email;
            }
        }

        if (empty($targetEmails)) {
            throw new \Exception("Tidak ada user ditemukan di Google Directory.");
        }

        return $targetEmails;
    }

    /**
     * Kirim ke Google API dan Update Database
     */
    private function prossessData($agenda, int|string $id, array $targetEmails): void
    {
        $timezone = new \DateTimeZone('Asia/Makassar');
        $start = new \DateTime($agenda['start_time'], $timezone);
        $end = new \DateTime($agenda['end_time'], $timezone);

        // 1. Format array targetEmails menjadi format Attendees yang diterima service
        $attendees = [];
        foreach ($targetEmails as $email) {
            // Kita bungkus ke dalam format yang bisa dibaca oleh buildEventObject
            $attendees[] = ['email' => $email];
        }

        // 2. Siapkan data dengan key yang SESUAI dengan buildEventObject()
        $eventData = [
            'title'       => $agenda['title'],
            'description' => $agenda['description'],
            // Gabungkan nama ruangan dan lokasi spesifik agar lebih informatif
            'location'    => trim(($agenda['ruangan_name'] ?? '') . ' ' . ($agenda['location'] ?? '')),
            'start_time'  => $start->format('c'),
            'end_time'    => $end->format('c'),
            'attendees'   => $attendees
        ];

        $gcal = new GoogleCalendarService();

        // Panggil fungsi bulk insert yang sudah kita buat
        $googleEventId = $gcal->impersonate($this->adminEmail)
            ->insertEvent($eventData, ['sendUpdates' => 'all']);

        // 4. Update Database dan SIMPAN $googleEventId yang kita dapatkan
        $this->model->updateStatus($id, 'approved', 'Telah disinkronisasi ke seluruh user kampus', $googleEventId);
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
