<?php
class Location extends Model
{
    public function listAll(?string $search = null): array
    {
        $sql = 'SELECT l.*, COUNT(p.property_id) AS property_count
                FROM locations l
                LEFT JOIN properties p ON p.location_id = l.location_id';
        $params = [];

        if ($search !== null && trim($search) !== '') {
            $sql .= ' WHERE l.city LIKE :search OR l.area LIKE :search OR l.country LIKE :search OR l.postal_code LIKE :search';
            $params['search'] = '%' . trim($search) . '%';
        }

        $sql .= ' GROUP BY l.location_id ORDER BY l.city ASC, l.area ASC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM locations WHERE location_id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $location = $stmt->fetch();
        return $location ?: null;
    }

    public function create(array $data): bool
    {
        $stmt = $this->db->prepare(
            'INSERT INTO locations (city, area, country, postal_code) VALUES (:city, :area, :country, :postal_code)'
        );

        return $stmt->execute([
            'city' => $data['city'],
            'area' => $data['area'] ?: null,
            'country' => $data['country'],
            'postal_code' => $data['postal_code'] ?: null,
        ]);
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE locations SET city = :city, area = :area, country = :country, postal_code = :postal_code WHERE location_id = :id'
        );

        return $stmt->execute([
            'city' => $data['city'],
            'area' => $data['area'] ?: null,
            'country' => $data['country'],
            'postal_code' => $data['postal_code'] ?: null,
            'id' => $id,
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM locations WHERE location_id = :id');
        return $stmt->execute(['id' => $id]);
    }

    public function countProperties(int $id): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM properties WHERE location_id = :id');
        $stmt->execute(['id' => $id]);
        return (int) $stmt->fetchColumn();
    }

    public function countAll(): int
    {
        return (int) $this->db->query('SELECT COUNT(*) FROM locations')->fetchColumn();
    }

    public function topLocations(int $limit = 5): array
    {
        $limit = max(1, min(20, $limit));
        $sql = 'SELECT CONCAT(l.city, COALESCE(CONCAT(" - ", l.area), "")) AS label,
                       COUNT(DISTINCT p.property_id) AS properties,
                       COUNT(DISTINCT b.booking_id) AS bookings
                FROM locations l
                LEFT JOIN properties p ON p.location_id = l.location_id
                LEFT JOIN bookings b ON b.property_id = p.property_id
                GROUP BY l.location_id, l.city, l.area
                ORDER BY properties DESC, bookings DESC
                LIMIT ' . $limit;
        return $this->db->query($sql)->fetchAll();
    }

}
