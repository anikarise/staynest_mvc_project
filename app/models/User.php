<?php
/*
|--------------------------------------------------------------------------
| User Model
|--------------------------------------------------------------------------
| Manages user persistence, account-status filters, approval updates,
| deletion safeguards, and dashboard user statistics.
|
*/

class User extends Model
{
    public function findByEmail(string $email): ?array
    {
        // Login uses email lookup before password verification.
        $stmt = $this->db->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT user_id, name, email, phone, role, status, account_status, created_at, updated_at FROM users WHERE user_id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public function emailExists(string $email, ?int $excludeUserId = null): bool
    {
        // Registration uniqueness check prevents duplicate login identities.
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
        // Passwords are hashed before storage; account_status controls approval flow.
        $stmt = $this->db->prepare(
            'INSERT INTO users (name, email, password, phone, role, status, account_status) VALUES (:name, :email, :password, :phone, :role, :status, :account_status)'
        );

        $stmt->execute([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => password_hash($data['password'], PASSWORD_DEFAULT),
            'phone' => $data['phone'] ?? null,
            'role' => $data['role'] ?? 'customer',
            'status' => $data['status'] ?? 'active',
            'account_status' => $data['account_status'] ?? 'active',
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

    public function listAll(?string $search = null, ?string $role = null, ?string $accountStatus = null): array
    {
        // Admin filters are built with prepared parameters for safe search combinations.
        $sql = 'SELECT user_id, name, email, phone, role, status, account_status, created_at FROM users';
        $where = [];
        $params = [];

        if ($search !== null && trim($search) !== '') {
            $searchFields = [
                'CAST(user_id AS CHAR)',
                'name',
                'email',
                'phone',
                'role',
                'status',
                'account_status',
            ];
            $searchParts = [];
            foreach ($searchFields as $index => $field) {
                $key = 'search_' . $index;
                $searchParts[] = $field . ' LIKE :' . $key;
                $params[$key] = '%' . trim($search) . '%';
            }
            $where[] = '(' . implode(' OR ', $searchParts) . ')';
        }

        if ($role !== null && in_array($role, ['customer', 'host', 'staff', 'main_admin', 'booking_property_admin', 'host_location_admin'], true)) {
            $where[] = 'role = :role';
            $params['role'] = $role;
        }

        if ($accountStatus !== null) {
            if ($accountStatus === 'inactive') {
                $where[] = 'status = :status';
                $params['status'] = 'inactive';
            } elseif ($accountStatus === 'active') {
                $where[] = 'status = :status';
                $where[] = 'account_status = :account_status';
                $params['status'] = 'active';
                $params['account_status'] = 'active';
            } elseif (in_array($accountStatus, ['pending', 'rejected'], true)) {
                $where[] = 'account_status = :account_status';
                $params['account_status'] = $accountStatus;
            }
        }

        if (!empty($where)) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $sql .= ' ORDER BY user_id DESC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function updateAccountStatus(int $userId, string $accountStatus): bool
    {
        // Main Admin approval is intentionally limited to Host and Staff accounts.
        if (!in_array($accountStatus, ['active', 'pending', 'rejected'], true)) {
            return false;
        }

        $stmt = $this->db->prepare(
            'UPDATE users
             SET account_status = :account_status
             WHERE user_id = :user_id
               AND role IN ("host", "staff")'
        );

        return $stmt->execute([
            'account_status' => $accountStatus,
            'user_id' => $userId,
        ]);
    }

    public function hasLinkedRecords(int $userId): bool
    {
        // Protect related bookings, host profiles, and hosted properties before deletion.
        $checks = [
            'SELECT COUNT(*) FROM bookings WHERE user_id = :user_id',
            'SELECT COUNT(*) FROM hosts WHERE user_id = :user_id',
            'SELECT COUNT(*)
             FROM properties p
             INNER JOIN hosts h ON h.host_id = p.host_id
             WHERE h.user_id = :user_id',
        ];

        foreach ($checks as $sql) {
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['user_id' => $userId]);
            if ((int) $stmt->fetchColumn() > 0) {
                return true;
            }
        }

        return false;
    }

    public function delete(int $userId): bool
    {
        $stmt = $this->db->prepare('DELETE FROM users WHERE user_id = :user_id');
        return $stmt->execute(['user_id' => $userId]);
    }


    public function listBookableUsers(): array
    {
        // Booking forms only include active, approved customer/staff/host accounts.
        $stmt = $this->db->query('SELECT user_id, name, email, role FROM users WHERE status = "active" AND account_status = "active" AND role IN ("customer", "staff", "host") ORDER BY name ASC');
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
