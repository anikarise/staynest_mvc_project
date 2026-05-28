<?php
class Property extends Model
{
    public function listForPublic(?string $search = null, ?int $locationId = null, ?string $availability = null): array
    {
        [$where, $params] = $this->buildSearchFilters($search, $locationId, $availability, 'approved');

        $sql = $this->baseSelect() . ' WHERE ' . implode(' AND ', $where) . ' ORDER BY p.created_at DESC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function listForManager(?string $search = null, ?int $locationId = null, ?string $availability = null, ?string $status = null): array
    {
        [$where, $params] = $this->buildSearchFilters($search, $locationId, $availability, $status);
        $sql = $this->baseSelect();

        if (!empty($where)) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $sql .= ' ORDER BY FIELD(p.status, "pending", "approved", "rejected"), p.created_at DESC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function listForHostUser(int $userId, ?string $search = null, ?int $locationId = null, ?string $availability = null, ?string $status = null): array
    {
        [$where, $params] = $this->buildSearchFilters($search, $locationId, $availability, $status);
        $where[] = 'h.user_id = :host_user_id';
        $params['host_user_id'] = $userId;

        $sql = $this->baseSelect() . ' WHERE ' . implode(' AND ', $where) . ' ORDER BY p.created_at DESC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }


    public function listApprovedAvailable(): array
    {
        $stmt = $this->db->query($this->baseSelect() . ' WHERE p.status = "approved" AND p.availability = "available" ORDER BY p.title ASC');
        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare($this->baseSelect() . ' WHERE p.property_id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $property = $stmt->fetch();
        return $property ?: null;
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO properties (host_id, location_id, title, description, image, price, category, availability, status)
             VALUES (:host_id, :location_id, :title, :description, :image, :price, :category, :availability, :status)'
        );

        $stmt->execute([
            'host_id' => $data['host_id'],
            'location_id' => $data['location_id'],
            'title' => $data['title'],
            'description' => $data['description'] ?: null,
            'image' => $data['image'] ?: null,
            'price' => $data['price'],
            'category' => $data['category'],
            'availability' => $data['availability'],
            'status' => $data['status'],
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE properties
             SET host_id = :host_id,
                 location_id = :location_id,
                 title = :title,
                 description = :description,
                 image = :image,
                 price = :price,
                 category = :category,
                 availability = :availability,
                 status = :status
             WHERE property_id = :id'
        );

        return $stmt->execute([
            'host_id' => $data['host_id'],
            'location_id' => $data['location_id'],
            'title' => $data['title'],
            'description' => $data['description'] ?: null,
            'image' => $data['image'] ?: null,
            'price' => $data['price'],
            'category' => $data['category'],
            'availability' => $data['availability'],
            'status' => $data['status'],
            'id' => $id,
        ]);
    }

    public function updateStatus(int $id, string $status): bool
    {
        $stmt = $this->db->prepare('UPDATE properties SET status = :status WHERE property_id = :id');
        return $stmt->execute(['status' => $status, 'id' => $id]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM properties WHERE property_id = :id');
        return $stmt->execute(['id' => $id]);
    }

    public function countBookings(int $id): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM bookings WHERE property_id = :id');
        $stmt->execute(['id' => $id]);
        return (int) $stmt->fetchColumn();
    }

    public function userOwnsProperty(int $propertyId, int $userId): bool
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM properties p
             INNER JOIN hosts h ON h.host_id = p.host_id
             WHERE p.property_id = :property_id AND h.user_id = :user_id'
        );
        $stmt->execute(['property_id' => $propertyId, 'user_id' => $userId]);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function countAll(): int
    {
        return (int) $this->db->query('SELECT COUNT(*) FROM properties')->fetchColumn();
    }

    public function countByStatus(string $status): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM properties WHERE status = :status');
        $stmt->execute(['status' => $status]);
        return (int) $stmt->fetchColumn();
    }

    private function baseSelect(): string
    {
        return 'SELECT p.*, h.company_name, h.user_id AS host_user_id, u.name AS host_user_name, u.email AS host_email,
                       l.city, l.area, l.country, l.postal_code,
                       (SELECT COUNT(*) FROM bookings b WHERE b.property_id = p.property_id) AS booking_count
                FROM properties p
                INNER JOIN hosts h ON h.host_id = p.host_id
                INNER JOIN users u ON u.user_id = h.user_id
                INNER JOIN locations l ON l.location_id = p.location_id';
    }

    private function buildSearchFilters(?string $search, ?int $locationId, ?string $availability, ?string $status): array
    {
        $where = [];
        $params = [];

        if ($search !== null && trim($search) !== '') {
            $where[] = '(p.title LIKE :search OR p.description LIKE :search OR p.address LIKE :search OR p.property_type LIKE :search OR p.category LIKE :search OR h.company_name LIKE :search OR l.city LIKE :search OR l.area LIKE :search)';
            $params['search'] = '%' . trim($search) . '%';
        }

        if ($locationId !== null && $locationId > 0) {
            $where[] = 'p.location_id = :location_id';
            $params['location_id'] = $locationId;
        }

        if ($availability !== null && in_array($availability, ['available', 'unavailable'], true)) {
            $where[] = 'p.availability = :availability';
            $params['availability'] = $availability;
        }

        if ($status !== null && in_array($status, ['pending', 'approved', 'rejected'], true)) {
            $where[] = 'p.status = :status';
            $params['status'] = $status;
        }

        return [$where, $params];
    }

    public function countApprovedAvailable(): int
    {
        return (int) $this->db->query('SELECT COUNT(*) FROM properties WHERE status = "approved" AND availability = "available"')->fetchColumn();
    }

    public function countForHostUser(int $userId): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM properties p INNER JOIN hosts h ON h.host_id = p.host_id WHERE h.user_id = :user_id');
        $stmt->execute(['user_id' => $userId]);
        return (int) $stmt->fetchColumn();
    }

    public function countForHostUserByStatus(int $userId, string $status): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM properties p INNER JOIN hosts h ON h.host_id = p.host_id WHERE h.user_id = :user_id AND p.status = :status');
        $stmt->execute(['user_id' => $userId, 'status' => $status]);
        return (int) $stmt->fetchColumn();
    }

    public function statusBreakdown(): array
    {
        return $this->breakdown('SELECT status AS label, COUNT(*) AS total FROM properties GROUP BY status ORDER BY total DESC');
    }

    public function statusBreakdownForHostUser(int $userId): array
    {
        $stmt = $this->db->prepare('SELECT p.status AS label, COUNT(*) AS total FROM properties p INNER JOIN hosts h ON h.host_id = p.host_id WHERE h.user_id = :user_id GROUP BY p.status ORDER BY total DESC');
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }

    public function availabilityBreakdown(): array
    {
        return $this->breakdown('SELECT availability AS label, COUNT(*) AS total FROM properties GROUP BY availability ORDER BY total DESC');
    }

    public function availabilityBreakdownForHostUser(int $userId): array
    {
        $stmt = $this->db->prepare('SELECT p.availability AS label, COUNT(*) AS total FROM properties p INNER JOIN hosts h ON h.host_id = p.host_id WHERE h.user_id = :user_id GROUP BY p.availability ORDER BY total DESC');
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }

    public function categoryBreakdown(): array
    {
        return $this->breakdown('SELECT category AS label, COUNT(*) AS total FROM properties GROUP BY category ORDER BY total DESC, category ASC');
    }

    public function recent(int $limit = 5): array
    {
        $limit = max(1, min(20, $limit));
        return $this->db->query($this->baseSelect() . ' ORDER BY p.created_at DESC LIMIT ' . $limit)->fetchAll();
    }

    public function recentApproved(int $limit = 5): array
    {
        $limit = max(1, min(20, $limit));
        return $this->db->query($this->baseSelect() . ' WHERE p.status = "approved" ORDER BY p.created_at DESC LIMIT ' . $limit)->fetchAll();
    }

    public function recentForHostUser(int $userId, int $limit = 5): array
    {
        $limit = max(1, min(20, $limit));
        $stmt = $this->db->prepare($this->baseSelect() . ' WHERE h.user_id = :user_id ORDER BY p.created_at DESC LIMIT ' . $limit);
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }

    private function breakdown(string $sql): array
    {
        return $this->db->query($sql)->fetchAll();
    }

}
