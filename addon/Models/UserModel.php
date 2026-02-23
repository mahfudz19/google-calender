<?php

namespace Addon\Models;

use App\Core\Database\Model;

class UserModel extends Model
{
  protected ?string $connection = 'mysql';
  protected string $table = 'users';
  protected bool $timestamps = true;

  protected array $schema = [
    'id' => ['type' => 'id', 'primary' => true, 'auto_increment' => true],
    'email' => ['type' => 'string', 'nullable' => false],
    'google_id' => ['type' => 'string', 'nullable' => true],
    'name' => ['type' => 'string', 'nullable' => true],
    'avatar' => ['type' => 'string', 'nullable' => true],
    'is_active' => ['type' => 'boolean', 'nullable' => false, 'default' => true],
    'last_login_at' => ['type' => 'datetime', 'nullable' => true],
    'role' => ['type' => 'enum', 'values' => ['admin', 'approver'], 'nullable' => false, 'default' => 'approver']
  ];

  protected array $seed = [
    [
      'email' => 'mahfudz@inbitef.ac.id',
      'name' => 'Default User',
      'google_id' => null,
      'avatar' => null,
      'is_active' => 1,
      'last_login_at' => null,
      'role' => 'admin',
    ],
    [
      'email' => 'bondan@inbitef.ac.id',
      'name' => 'Default Admin',
      'google_id' => null,
      'avatar' => null,
      'is_active' => 1,
      'last_login_at' => null,
      'role' => 'approver',
    ],
  ];

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

  public function updateById(string|int $id, array $data): bool
  {
    if (empty($data)) {
      return false;
    }

    // Auto-update updated_at if not provided
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

  public function findByEmail(string $email): ?array
  {
    $stmt = $this->getDb()->prepare("SELECT * FROM {$this->table} WHERE email = :email LIMIT 1");
    $stmt->execute(['email' => $email]);
    $row = $stmt->fetch();

    return $row === false ? null : $row;
  }

  public function touchLogin(string|int $id, ?string $name, ?string $avatar, ?string $googleId): bool
  {
    $data = [
      'last_login_at' => date('Y-m-d H:i:s'),
      'updated_at' => date('Y-m-d H:i:s')
    ];

    if ($name !== null) {
      $data['name'] = $name;
    }

    if ($avatar !== null) {
      $data['avatar'] = $avatar;
    }

    if ($googleId !== null) {
      $data['google_id'] = $googleId;
    }

    return $this->updateById($id, $data);
  }

  public function createFromGoogle(array $data): bool
  {
    $now = date('Y-m-d H:i:s');

    $defaults = [
      'google_id' => null,
      'name' => null,
      'avatar' => null,
      'is_active' => 1,
      'role' => 'approver',
      'created_at' => $now,
      'updated_at' => $now
    ];

    $insertData = array_merge($defaults, $data);

    // Ensure email is present
    if (empty($insertData['email'])) {
      return false;
    }

    $columns = implode(', ', array_keys($insertData));
    $placeholders = ':' . implode(', :', array_keys($insertData));

    $sql = "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";

    return $this->getDb()->query($sql, $insertData);
  }
}
