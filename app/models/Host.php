<?php
class Host extends Model
{
    public function createForUser(int $userId, string $companyName, ?string $description = null, ?string $contact = null): bool
    {
        $stmt = $this->db->prepare(
            'INSERT INTO hosts (user_id, company_name, company_description, contact_information) VALUES (:user_id, :company_name, :company_description, :contact_information)'
        );

        return $stmt->execute([
            'user_id' => $userId,
            'company_name' => $companyName,
            'company_description' => $description,
            'contact_information' => $contact,
        ]);
    }

    public function listAll(?string $search = null): array
    {
        $sql = 'SELECT h.*, u.name, u.email, u.phone, u.status, COUNT(p.property_id) AS property_count
                FROM hosts h
                INNER JOIN users u ON u.user_id = h.user_id
                LEFT JOIN properties p ON p.host_id = h.host_id';
        $params = [];

        if ($search !== null && trim($search) !== '') {
            $sql .= ' WHERE h.company_name LIKE :search OR h.company_description LIKE :search OR h.contact_information LIKE :search OR u.name LIKE :search OR u.email LIKE :search';
            $params['search'] = '%' . trim($search) . '%';
        }

        $sql .= ' GROUP BY h.host_id ORDER BY h.host_id DESC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT h.*, u.name, u.email, u.phone, u.status
             FROM hosts h
             INNER JOIN users u ON u.user_id = h.user_id
             WHERE h.host_id = :id
             LIMIT 1'
        );
        $stmt->execute(['id' => $id]);
        $host = $stmt->fetch();
        return $host ?: null;
    }

    public function findByUserId(int $userId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT h.*, u.name, u.email, u.phone, u.status
             FROM hosts h
             INNER JOIN users u ON u.user_id = h.user_id
             WHERE h.user_id = :user_id
             LIMIT 1'
        );
        $stmt->execute(['user_id' => $userId]);
        $host = $stmt->fetch();
        return $host ?: null;
    }

    public function create(array $data): bool
    {
        $stmt = $this->db->prepare(
            'INSERT INTO hosts (user_id, company_name, company_description, contact_information) VALUES (:user_id, :company_name, :company_description, :contact_information)'
        );

        return $stmt->execute([
            'user_id' => $data['user_id'],
            'company_name' => $data['company_name'],
            'company_description' => $data['company_description'] ?: null,
            'contact_information' => $data['contact_information'] ?: null,
        ]);
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE hosts SET company_name = :company_name, company_description = :company_description, contact_information = :contact_information WHERE host_id = :id'
        );

        return $stmt->execute([
            'company_name' => $data['company_name'],
            'company_description' => $data['company_description'] ?: null,
            'contact_information' => $data['contact_information'] ?: null,
            'id' => $id,
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM hosts WHERE host_id = :id');
        return $stmt->execute(['id' => $id]);
    }

    public function countProperties(int $id): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM properties WHERE host_id = :id');
        $stmt->execute(['id' => $id]);
        return (int) $stmt->fetchColumn();
    }

    public function availableHostUsers(?int $currentHostId = null): array
    {
        $sql = 'SELECT u.user_id, u.name, u.email
                FROM users u
                LEFT JOIN hosts h ON h.user_id = u.user_id
                WHERE u.role = :role AND (h.host_id IS NULL';
        $params = ['role' => 'host'];

        if ($currentHostId !== null) {
            $sql .= ' OR h.host_id = :current_host_id';
            $params['current_host_id'] = $currentHostId;
        }

        $sql .= ') ORDER BY u.name ASC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function userAlreadyHasHostProfile(int $userId): bool
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM hosts WHERE user_id = :user_id');
        $stmt->execute(['user_id' => $userId]);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function countAll(): int
    {
        return (int) $this->db->query('SELECT COUNT(*) FROM hosts')->fetchColumn();
    }

    public function topHosts(int $limit = 5): array
    {
        $limit = max(1, min(20, $limit));
        $sql = 'SELECT h.company_name AS label,
                       COUNT(DISTINCT p.property_id) AS properties,
                       COUNT(DISTINCT b.booking_id) AS bookings
                FROM hosts h
                LEFT JOIN properties p ON p.host_id = h.host_id
                LEFT JOIN bookings b ON b.property_id = p.property_id
                GROUP BY h.host_id, h.company_name
                ORDER BY properties DESC, bookings DESC
                LIMIT ' . $limit;
        return $this->db->query($sql)->fetchAll();
    }

}
