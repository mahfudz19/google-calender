<?php

namespace Addon\Models;

use App\Core\Database\Model;

class QueueModel extends Model
{
    protected ?string $connection = null;
    protected string $table = 'queues';
    protected bool $timestamps = true;

    protected array $schema = [
        // Field wajib untuk queue system - TIDAK BOLEH DIHAPUS
        'id' => ['type' => 'id', 'primary' => true, 'auto_increment' => true],
        'queue' => ['type' => 'string', 'nullable' => false, 'default' => 'default'],
        'payload' => ['type' => 'longtext', 'nullable' => false],
        'attempts' => ['type' => 'int', 'nullable' => false, 'default' => 0],
        'reserved_at' => ['type' => 'bigint', 'nullable' => true],
        'available_at' => ['type' => 'bigint', 'nullable' => false],

        // Field opsional untuk progress tracking - BOLEH DIHAPUS jika tidak perlu
        'status' => ['type' => 'enum', 'values' => ['pending', 'processing', 'success', 'failed'], 'nullable' => false, 'default' => 'pending'],
        'progress' => ['type' => 'int', 'nullable' => false, 'default' => 0],
        'current_step' => ['type' => 'string', 'nullable' => true],
        'error_message' => ['type' => 'text', 'nullable' => true],
        'completed_at' => ['type' => 'bigint', 'nullable' => true],

        // Tambahkan custom fields untuk project Anda di sini
        // Contoh:
        // 'priority' => ['type' => 'enum', 'values' => ['low', 'medium', 'high'], 'default' => 'medium'],
        // 'retry_count' => ['type' => 'int', 'default' => 0],
        // 'duration' => ['type' => 'bigint', 'nullable' => true],
    ];


    protected array $seed = [];

    public function getPendingJobsCount(string $queue = 'default'): int
    {
        $stmt = $this->getDb()->prepare(
            "SELECT COUNT(*) FROM {$this->table} 
             WHERE queue = :queue AND reserved_at IS NULL AND available_at <= :now"
        );
        $stmt->execute(['queue' => $queue, 'now' => time()]);
        return (int)$stmt->fetchColumn();
    }

    public function getFailedJobs(): array
    {
        $stmt = $this->getDb()->prepare(
            "SELECT * FROM {$this->table} 
             WHERE attempts >= 3 ORDER BY created_at DESC"
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getQueueStats(): array
    {
        $sql = "SELECT 
                    queue,
                    COUNT(*) as total,
                    SUM(CASE WHEN reserved_at IS NULL THEN 1 ELSE 0 END) as pending,
                    SUM(CASE WHEN reserved_at IS NOT NULL THEN 1 ELSE 0 END) as processing,
                    SUM(CASE WHEN attempts >= 3 THEN 1 ELSE 0 END) as failed
                FROM {$this->table} 
                GROUP BY queue";

        $stmt = $this->getDb()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Basic CRUD methods
    public function all(): array
    {
        $stmt = $this->getDb()->prepare("SELECT * FROM {$this->table} ORDER BY created_at DESC");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function find(string|int $id): ?array
    {
        $stmt = $this->getDb()->prepare("SELECT * FROM {$this->table} WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    public function create(array $data): bool
    {
        if (empty($data)) return false;

        $columns = array_keys($data);
        $placeholders = array_map(fn($col) => ':' . $col, $columns);

        $sql = "INSERT INTO {$this->table} (" . implode(', ', $columns) . ")
                VALUES (" . implode(', ', $placeholders) . ")";

        return $this->getDb()->query($sql, $data);
    }

    public function updateById(string|int $id, array $data): bool
    {
        if (empty($data)) return false;

        $setParts = [];
        foreach ($data as $column => $value) {
            $setParts[] = "{$column} = :{$column}";
        }

        $sql = "UPDATE {$this->table} SET " . implode(', ', $setParts) . " WHERE id = :id";
        $data['id'] = $id;

        return $this->getDb()->query($sql, $data);
    }

    public function deleteById(string|int $id): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        return $this->getDb()->query($sql, ['id' => $id]);
    }
}
