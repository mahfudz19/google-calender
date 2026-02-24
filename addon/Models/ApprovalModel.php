<?php

namespace Addon\Models;

use App\Core\Database\Model;
use Exception;

class ApprovalModel extends Model
{
    protected ?string $connection = null; // Nama koneksi database (opsional)
    protected string $table = 'approvals';
    protected bool $timestamps = true;

    // Kolom timestamp (opsional untuk diubah)
    // protected string $createdAtColumn = 'created_at';
    // protected string $updatedAtColumn = 'updated_at';

    /**
     * Schema untuk 'php mazu migrate'
     * Tipe: id|string|int|bigint|text|datetime|date|boolean|json|decimal
     */
    protected array $schema = [
        'id' => ['type' => 'id', 'primary' => true, 'auto_increment' => true],
        'title' => ['type' => 'string', 'nullable' => false],
        'description' => ['type' => 'text', 'nullable' => true],
        'start_time' => ['type' => 'datetime', 'nullable' => false],
        'end_time' => ['type' => 'datetime', 'nullable' => false],
        'location' => ['type' => 'string', 'nullable' => true],
        'requester_name' => ['type' => 'string', 'nullable' => true],
        'requester_email' => ['type' => 'string', 'nullable' => true],
        'requester_role' => ['type' => 'string', 'nullable' => true],
        'requester_avatar' => ['type' => 'string', 'nullable' => true],
        'status' => [
            'type' => 'enum',
            'values' => ['pending', 'approved', 'rejected'],
            'nullable' => false,
            'default' => 'pending',
        ],
        'type' => ['type' => 'string', 'nullable' => true],
    ];

    protected array $seed = []; // Data awal untuk seeder

    public function all(): array
    {
        $stmt = $this->getDb()->prepare("SELECT * FROM {$this->table}");
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
        if (empty($data)) {
            return false;
        }
        $return = false;

        try {
            $columns = array_keys($data);
            $placeholders = array_map(fn($col) => ':' . $col, $columns);

            $sql = "INSERT INTO {$this->table} (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $placeholders) . ")";
            $return = $this->getDb()->prepare($sql)->execute($data);
        } catch (\Throwable $th) {
            throw new Exception($th->getMessage() ?? "Error Processing Request", 1);
            
        }

        return $return;
    }

    public function updateById(string|int $id, array $data): bool
    {
        if (empty($data)) {
            return false;
        }

        if (!isset($data['updated_at'])) {
            $data['updated_at'] = date('Y-m-d H:i:s');
        }

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

    public function getPending(): array
    {
        $stmt = $this->getDb()->prepare("SELECT * FROM {$this->table} WHERE status = :status ORDER BY start_time ASC");
        $stmt->execute(['status' => 'pending']);
        return $stmt->fetchAll();
    }

    public function getHistory(): array
    {
        $stmt = $this->getDb()->prepare("SELECT * FROM {$this->table} WHERE status != :status ORDER BY updated_at DESC");
        $stmt->execute(['status' => 'pending']);
        return $stmt->fetchAll();
    }

    public function updateStatus(string|int $id, string $status): bool
    {
        return $this->updateById($id, ['status' => $status]);
    }

    public function getByRequester(string $email): array
    {
        $stmt = $this->getDb()->prepare("SELECT * FROM {$this->table} WHERE requester_email = :email ORDER BY updated_at DESC");
        $stmt->execute(['email' => $email]);
        return $stmt->fetchAll();
    }
}
