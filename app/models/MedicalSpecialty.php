<?php
// app/models/MedicalSpecialty.php

class MedicalSpecialty
{
    private $db;

    public function __construct()
    {
        require_once __DIR__ . '/../db.php';
        $this->db = class_exists('Database') ? Database::get_instance() : $GLOBALS['conn'];
    }

    public function countFeatured(): int
    {
        $res = $this->db->query("SELECT COUNT(*) AS c FROM Medical_specialty WHERE is_featured = 1");
        return (int)($res ? $res->fetch_assoc()['c'] : 0);
    }

    public function getFeaturedPage(int $limit, int $offset): array
    {
        $limit = max(1, (int)$limit);
        $offset = max(0, (int)$offset);
        $res = $this->db->query("SELECT specialty_id, slug, name, blurb, image_url FROM Medical_specialty WHERE is_featured = 1 ORDER BY sort_order ASC, name ASC LIMIT $limit OFFSET $offset");
        return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }

    public static function findBySlug(mysqli $conn, string $slug): ?array
    {
        $stmt = $conn->prepare("SELECT specialty_id, slug, name, blurb, image_url FROM Medical_specialty WHERE slug = ? LIMIT 1");
        $stmt->bind_param("s", $slug);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $row ?: null;
    }

    public static function search(string $query, int $limit = 10): array
    {
        $conn = self::getConnection();
        $stmt = $conn->prepare("SELECT specialty_id, name, slug FROM Medical_specialty WHERE name LIKE CONCAT('%', ?, '%') ORDER BY sort_order, name LIMIT ?");
        $stmt->bind_param('si', $query, $limit);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        $results = [];
        foreach ($rows as $r) $results[] = ['type' => 'specialty', 'id' => (int)$r['specialty_id'], 'name' => $r['name'], 'slug' => $r['slug']];
        return $results;
    }

    private static function getConnection(): mysqli
    {
        global $conn;
        if (!isset($conn) || !($conn instanceof mysqli)) $conn = Database::get_instance();
        return $conn;
    }
}
