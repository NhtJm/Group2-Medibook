<?php
// app/models/MedicalSpecialty.php
class MedicalSpecialty
{
  /** @var mysqli */
  private $db;

  public function __construct()
  {
    require_once __DIR__ . '/../db.php';
    if (class_exists('Database')) {
      $this->db = Database::get_instance();
    } else {
      // fallback if your db.php exposes $conn
      global $conn;
      $this->db = $conn;
    }
  }

  public function countFeatured(): int
  {
    $sql = "SELECT COUNT(*) AS c FROM Medical_specialty WHERE is_featured = 1";
    $res = $this->db->query($sql);
    if (!$res) return 0;
    $row = $res->fetch_assoc();
    $res->free();
    return (int)$row['c'];
  }

  /** @return array<int,array<string,mixed>> */
  public function getFeaturedPage(int $limit, int $offset): array
  {
    $limit  = max(1, (int)$limit);
    $offset = max(0, (int)$offset);
    // ints are safe to inline once cast
    $sql = "
      SELECT specialty_id, slug, name, blurb, image_url
      FROM Medical_specialty
      WHERE is_featured = 1
      ORDER BY sort_order ASC, name ASC
      LIMIT $limit OFFSET $offset";
    $res = $this->db->query($sql);
    if (!$res) return [];
    $rows = $res->fetch_all(MYSQLI_ASSOC);
    $res->free();
    return $rows;
  }
}