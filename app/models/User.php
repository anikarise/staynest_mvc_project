<?php
class User extends Model
{
    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT user_id, name, email, phone, role, status, created_at, updated_at FROM users WHERE user_id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public function emailExists(string $email, ?int $excludeUserId = null): bool
    {
        if ($excludeUserId !== null) {
            $stmt = $this->db->prepare('SELECT COUNT(*) FROM users WHERE email = :email AND user_id != :user_id');
            $stmt->execute(['email' => $email, 'user_id' => $excludeUserId]);
        } else {
            $stmt = $this->db->prepare('SELECT COUNT(*) FROM users WHERE email = :email');
            $stmt->execute(['email' => $email]);
        }

        return (int) $stmt->fetchColumn() > 0;
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO users (name, email, password, phone, role, status) VALUES (:name, :email, :password, :phone, :role, :status)'
        );

        $stmt->execute([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => password_hash($data['password'], PASSWORD_DEFAULT),
            'phone' => $data['phone'] ?? null,
            'role' => $data['role'] ?? 'customer',
            'status' => $data['status'] ?? 'active',
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function updateProfile(int $userId, array $data): bool
    {
        $stmt = $this->db->prepare('UPDATE users SET name = :name, phone = :phone WHERE user_id = :user_id');
        return $stmt->execute([
            'name' => $data['name'],
            'phone' => $data['phone'] ?? null,
            'user_id' => $userId,
        ]);
    }

    public function updatePassword(int $userId, string $password): bool
    {
        $stmt = $this->db->prepare('UPDATE users SET password = :password WHERE user_id = :user_id');
        return $stmt->execute([
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'user_id' => $userId,
        ]);
    }

    public function listAll(): array
    {
        $stmt = $this->db->query('SELECT user_id, name, email, phone, role, status, created_at FROM users ORDER BY user_id DESC');
        return $stmt->fetchAll();
    }


    public function listBookableUsers(): array
    {
        $stmt = $this->db->query('SELECT user_id, name, email, role FROM users WHERE status = "active" AND role IN ("customer", "staff", "host") ORDER BY name ASC');
        return $stmt->fetchAll();
    }

    public function countAll(): int
    {
        return (int) $this->db->query('SELECT COUNT(*) FROM users')->fetchColumn();
    }

    public function countByRole(): array
    {
        $stmt = $this->db->query('SELECT role AS label, COUNT(*) AS total FROM users GROUP BY role ORDER BY total DESC');
        return $stmt->fetchAll();
    }

}
