<?php
// app/controller/SearchController.php
require_once __DIR__ . '/../db.php';

class SearchController {
  public static function suggest(): void {
    header('Content-Type: application/json; charset=utf-8');

    $conn = Database::get_instance();
    $q   = trim($_GET['q']   ?? '');
    $loc = trim($_GET['loc'] ?? '');

    $out = ['specialties'=>[], 'offices'=>[]];

    //DOCTOR

    if ($q !== '') {
      $sql = "SELECT 
                d.doctor_id,
                d.doctor_name,
                d.specialty_id,
                ms.name AS specialty_name,
                o.office_id,
                o.name  AS office_name,
                o.address
              FROM Doctor d
              JOIN Office o ON o.office_id = d.office_id AND o.status = 'approved'
              LEFT JOIN Medical_specialty ms ON ms.specialty_id = d.specialty_id
              WHERE d.doctor_name LIKE CONCAT('%', ?, '%')
                AND (? = '' OR o.address LIKE CONCAT('%', ?, '%'))  -- lọc theo location nếu có
              ORDER BY d.doctor_name
              LIMIT 10";
      $st = $conn->prepare($sql);
      $st->bind_param('sss', $q, $loc, $loc);
      $st->execute();
      $rs = $st->get_result();
      while ($r = $rs->fetch_assoc()) {
        $out['doctors'][] = [
          'type'           => 'doctor',
          'id'             => (int)$r['doctor_id'],
          'name'           => $r['doctor_name'],
          'office_id'      => (int)$r['office_id'],
          'office_name'    => $r['office_name'],
          'address'        => $r['address'] ?? '',
          'specialty_id'   => $r['specialty_id'] ? (int)$r['specialty_id'] : null,
          'specialty_name' => $r['specialty_name'] ?? null,
        ];
      }
      $st->close();
    }

    // SPECIALTIES
    if ($q !== '') {
      $sql = "SELECT specialty_id, name, slug
              FROM Medical_specialty
              WHERE name LIKE CONCAT('%', ?, '%')
              ORDER BY sort_order, name
              LIMIT 10";
      $st = $conn->prepare($sql);
      $st->bind_param('s', $q);
      $st->execute();
      $rs = $st->get_result();
      while ($r = $rs->fetch_assoc()) {
        $out['specialties'][] = [
          'type'=>'specialty','id'=>(int)$r['specialty_id'],
          'name'=>$r['name'],'slug'=>$r['slug']
        ];
      }
      $st->close();
    }

    // OFFICES
    $sql = "SELECT office_id, name, address
            FROM Office
            WHERE status='approved'
              AND (?='' OR name    LIKE CONCAT('%', ?, '%'))
              AND (?='' OR address LIKE CONCAT('%', ?, '%'))
            ORDER BY name
            LIMIT 10";
    $st = $conn->prepare($sql);
    $st->bind_param('ssss', $q,$q,$loc,$loc);
    $st->execute();
    $rs = $st->get_result();
    while ($r = $rs->fetch_assoc()) {
      $out['offices'][] = [
        'type'=>'office','id'=>(int)$r['office_id'],
        'name'=>$r['name'],'address'=>$r['address'] ?? ''
      ];
    }
    $st->close();

    echo json_encode(['ok'=>true,'results'=>$out], JSON_UNESCAPED_UNICODE);
  }
}
