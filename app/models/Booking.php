<?php
/*
|--------------------------------------------------------------------------
| Booking Model
|--------------------------------------------------------------------------
| Encapsulates booking persistence, relationship queries, search filters,
| conflict checks, and dashboard statistics.
|
*/

class Booking extends Model
{
    public const MAX_BOOKING_NIGHTS = 60;

    public function listForManager(?string $search = null, ?string $status = null): array
    {
        [$where, $params] = $this->buildFilters($search, $status);
        $sql = $this->baseSelect();

        if (!empty($where)) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $sql .= ' ORDER BY FIELD(b.booking_status, "pending", "confirmed", "rejected", "cancelled"), b.created_at DESC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function listForCustomer(int $userId, ?string $search = null, ?string $status = null): array
    {
        [$where, $params] = $this->buildFilters($search, $status);
        // Scope customer queries to records owned by the current session user.
        $where[] = 'b.user_id = :current_user_id';
        $params['current_user_id'] = $userId;

        $sql = $this->baseSelect() . ' WHERE ' . implode(' AND ', $where) . ' ORDER BY b.created_at DESC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function listForHostUser(int $userId, ?string $search = null, ?string $status = null): array
    {
        [$where, $params] = $this->buildFilters($search, $status);
        // Scope host queries through the linked host profile relationship.
        $where[] = 'h.user_id = :host_user_id';
        $params['host_user_id'] = $userId;

        $sql = $this->baseSelect() . ' WHERE ' . implode(' AND ', $where) . ' ORDER BY b.created_at DESC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare($this->baseSelect() . ' WHERE b.booking_id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $booking = $stmt->fetch();
        return $booking ?: null;
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO bookings (user_id, property_id, booking_date, check_in_date, check_out_date, total_price, booking_status)
             VALUES (:user_id, :property_id, CURDATE(), :check_in_date, :check_out_date, :total_price, :booking_status)'
        );

        $stmt->execute([
            'user_id' => $data['user_id'],
            'property_id' => $data['property_id'],
            'check_in_date' => $data['check_in_date'],
            'check_out_date' => $data['check_out_date'],
            'total_price' => $data['total_price'],
            'booking_status' => $data['booking_status'],
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE bookings
             SET user_id = :user_id,
                 property_id = :property_id,
                 check_in_date = :check_in_date,
                 check_out_date = :check_out_date,
                 total_price = :total_price,
                 booking_status = :booking_status
             WHERE booking_id = :id'
        );

        return $stmt->execute([
            'user_id' => $data['user_id'],
            'property_id' => $data['property_id'],
            'check_in_date' => $data['check_in_date'],
            'check_out_date' => $data['check_out_date'],
            'total_price' => $data['total_price'],
            'booking_status' => $data['booking_status'],
            'id' => $id,
        ]);
    }

    public function updateStatus(int $id, string $status): bool
    {
        $stmt = $this->db->prepare('UPDATE bookings SET booking_status = :status WHERE booking_id = :id');
        return $stmt->execute(['status' => $status, 'id' => $id]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM bookings WHERE booking_id = :id');
        return $stmt->execute(['id' => $id]);
    }

    public function userOwnsBooking(int $bookingId, int $userId): bool
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM bookings WHERE booking_id = :booking_id AND user_id = :user_id');
        $stmt->execute(['booking_id' => $bookingId, 'user_id' => $userId]);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function hostOwnsBooking(int $bookingId, int $userId): bool
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM bookings b
             INNER JOIN properties p ON p.property_id = b.property_id
             INNER JOIN hosts h ON h.host_id = p.host_id
             WHERE b.booking_id = :booking_id AND h.user_id = :user_id'
        );
        $stmt->execute(['booking_id' => $bookingId, 'user_id' => $userId]);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function hasDateConflict(int $propertyId, string $checkIn, string $checkOut, ?int $excludeBookingId = null, array $statuses = ['pending', 'confirmed']): bool
    {
        // Overlap check compares active bookings on the same property date range.
        $statusPlaceholders = [];
        $params = [
            'property_id' => $propertyId,
            'check_in_date' => $checkIn,
            'check_out_date' => $checkOut,
        ];

        foreach ($statuses as $index => $status) {
            $key = 'status_' . $index;
            $statusPlaceholders[] = ':' . $key;
            $params[$key] = $status;
        }

        $sql = 'SELECT COUNT(*) FROM bookings
                WHERE property_id = :property_id
                  AND booking_status IN (' . implode(',', $statusPlaceholders) . ')
                  AND check_in_date < :check_out_date
                  AND check_out_date > :check_in_date';

        if ($excludeBookingId !== null) {
            $sql .= ' AND booking_id != :exclude_booking_id';
            $params['exclude_booking_id'] = $excludeBookingId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function nightsBetween(string $checkIn, string $checkOut): int
    {
        return (int) (new DateTime($checkIn))->diff(new DateTime($checkOut))->days;
    }

    public function isDurationAllowed(string $checkIn, string $checkOut): bool
    {
        // Business rule: StayNest bookings may not exceed 60 nights.
        $nights = $this->nightsBetween($checkIn, $checkOut);
        return $nights >= 1 && $nights <= self::MAX_BOOKING_NIGHTS;
    }

    public function countAll(): int
    {
        return (int) $this->db->query('SELECT COUNT(*) FROM bookings')->fetchColumn();
    }

    public function countByStatus(string $status): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM bookings WHERE booking_status = :status');
        $stmt->execute(['status' => $status]);
        return (int) $stmt->fetchColumn();
    }

    public function totalConfirmedRevenue(): float
    {
        // Revenue totals only confirmed bookings so pending/rejected requests do not inflate income.
        return (float) $this->db->query('SELECT COALESCE(SUM(total_price), 0) FROM bookings WHERE booking_status = "confirmed"')->fetchColumn();
    }

    private function baseSelect(): string
    {
        // Shared relationship query supplies table views and dashboards with joined booking context.
        return 'SELECT b.*, u.name AS customer_name, u.email AS customer_email, u.phone AS customer_phone,
                       p.title AS property_title, p.price AS property_price, p.category, p.availability AS property_availability, p.status AS property_status,
                       h.company_name, h.user_id AS host_user_id,
                       l.city, l.area, l.country, l.postal_code
                FROM bookings b
                INNER JOIN users u ON u.user_id = b.user_id
                INNER JOIN properties p ON p.property_id = b.property_id
                INNER JOIN hosts h ON h.host_id = p.host_id
                INNER JOIN locations l ON l.location_id = p.location_id';
    }

    private function buildFilters(?string $search, ?string $status): array
    {
        $where = [];
        $params = [];

        if ($search !== null && trim($search) !== '') {
            // Search filters are parameterized to keep table filtering safe and composable.
            $searchFields = [
                'CAST(b.booking_id AS CHAR)',
                'u.name',
                'u.email',
                'p.title',
                'h.company_name',
                'l.city',
                'l.area',
                'l.country',
                'l.postal_code',
                'b.booking_status',
            ];
            $searchParts = [];
            foreach ($searchFields as $index => $field) {
                $key = 'search_' . $index;
                $searchParts[] = $field . ' LIKE :' . $key;
                $params[$key] = '%' . trim($search) . '%';
            }
            $where[] = '(' . implode(' OR ', $searchParts) . ')';
        }

        if ($status !== null && in_array($status, ['pending', 'confirmed', 'rejected', 'cancelled'], true)) {
            $where[] = 'b.booking_status = :status';
            $params['status'] = $status;
        }

        return [$where, $params];
    }

    public function countForCustomer(int $userId): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM bookings WHERE user_id = :user_id');
        $stmt->execute(['user_id' => $userId]);
        return (int) $stmt->fetchColumn();
    }

    public function countForCustomerByStatus(int $userId, string $status): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM bookings WHERE user_id = :user_id AND booking_status = :status');
        $stmt->execute(['user_id' => $userId, 'status' => $status]);
        return (int) $stmt->fetchColumn();
    }

    public function countForHostUser(int $userId): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM bookings b INNER JOIN properties p ON p.property_id = b.property_id INNER JOIN hosts h ON h.host_id = p.host_id WHERE h.user_id = :user_id');
        $stmt->execute(['user_id' => $userId]);
        return (int) $stmt->fetchColumn();
    }

    public function statusBreakdown(): array
    {
        return $this->db->query('SELECT booking_status AS label, COUNT(*) AS total FROM bookings GROUP BY booking_status ORDER BY total DESC')->fetchAll();
    }

    public function statusBreakdownForCustomer(int $userId): array
    {
        $stmt = $this->db->prepare('SELECT booking_status AS label, COUNT(*) AS total FROM bookings WHERE user_id = :user_id GROUP BY booking_status ORDER BY total DESC');
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }

    public function statusBreakdownForHostUser(int $userId): array
    {
        $stmt = $this->db->prepare('SELECT b.booking_status AS label, COUNT(*) AS total FROM bookings b INNER JOIN properties p ON p.property_id = b.property_id INNER JOIN hosts h ON h.host_id = p.host_id WHERE h.user_id = :user_id GROUP BY b.booking_status ORDER BY total DESC');
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }

    public function revenueForHostUser(int $userId): float
    {
        $stmt = $this->db->prepare('SELECT COALESCE(SUM(b.total_price), 0) FROM bookings b INNER JOIN properties p ON p.property_id = b.property_id INNER JOIN hosts h ON h.host_id = p.host_id WHERE h.user_id = :user_id AND b.booking_status = "confirmed"');
        $stmt->execute(['user_id' => $userId]);
        return (float) $stmt->fetchColumn();
    }

    public function monthlyRevenue(int $months = 6): array
    {
        // Monthly revenue powers dashboard trend cards and charts.
        $months = max(1, min(12, $months));
        $sql = 'SELECT DATE_FORMAT(booking_date, "%Y-%m") AS label,
                       COUNT(*) AS bookings,
                       COALESCE(SUM(CASE WHEN booking_status = "confirmed" THEN total_price ELSE 0 END), 0) AS revenue
                FROM bookings
                WHERE booking_date >= DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL ' . ($months - 1) . ' MONTH), "%Y-%m-01")
                GROUP BY DATE_FORMAT(booking_date, "%Y-%m")
                ORDER BY label ASC';
        return $this->db->query($sql)->fetchAll();
    }

    public function recent(int $limit = 5): array
    {
        $limit = max(1, min(20, $limit));
        return $this->db->query($this->baseSelect() . ' ORDER BY b.created_at DESC LIMIT ' . $limit)->fetchAll();
    }

    public function recentForCustomer(int $userId, int $limit = 5): array
    {
        $limit = max(1, min(20, $limit));
        $stmt = $this->db->prepare($this->baseSelect() . ' WHERE b.user_id = :user_id ORDER BY b.created_at DESC LIMIT ' . $limit);
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }

    public function recentForHostUser(int $userId, int $limit = 5): array
    {
        $limit = max(1, min(20, $limit));
        $stmt = $this->db->prepare($this->baseSelect() . ' WHERE h.user_id = :user_id ORDER BY b.created_at DESC LIMIT ' . $limit);
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }

}
