<?php
// app/models/Office.php

class Office
{
    private static function colExists(mysqli $conn, string $table, string $col): bool
    {
        // Works with prepared statements; no identifier interpolation
        $sql = "SELECT 1
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME   = ?
              AND COLUMN_NAME  = ?
            LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $table, $col);
        $stmt->execute();
        $ok = $stmt->get_result()->num_rows > 0;
        $stmt->close();
        return $ok;
    }

    private static function tableExists(mysqli $conn, string $table): bool
    {
        $sql = "SELECT 1
            FROM INFORMATION_SCHEMA.TABLES
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME   = ?
            LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $table);
        $stmt->execute();
        $ok = $stmt->get_result()->num_rows > 0;
        $stmt->close();
        return $ok;
    }
    /** Search any approved office by name/specialty text + location (your original method) */
    public static function searchPaginated(mysqli $conn, ?string $q, ?string $loc, int $page = 1, int $perPage = 10): array
    {
        $page = max(1, $page);
        $perPage = max(1, min(50, $perPage));
        $offset = ($page - 1) * $perPage;

        $where = ["o.status = 'approved'"];
        $params = [];
        $types = "";

        if ($q !== null && $q !== '') {
            $where[] = "(o.name LIKE ? OR EXISTS (
                SELECT 1
                FROM Office_has_specialty ohs
                JOIN Medical_specialty m ON m.specialty_id = ohs.specialty_id
                WHERE ohs.office_id = o.office_id
                  AND (m.name LIKE ? OR m.slug LIKE ?)
            ))";
            $like = "%$q%";
            $params[] = $like;
            $types .= "s";
            $params[] = $like;
            $types .= "s";
            $params[] = $like;
            $types .= "s";
        }

        if ($loc !== null && $loc !== '') {
            $where[] = "o.address LIKE ?";
            $params[] = "%$loc%";
            $types .= "s";
        }

        $whereSql = $where ? ("WHERE " . implode(" AND ", $where)) : "";

        // total
        $sqlCount = "SELECT COUNT(DISTINCT o.office_id) AS c
                     FROM Office o
                     $whereSql";
        $stmt = $conn->prepare($sqlCount);
        if ($types !== "") {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $total = (int) ($stmt->get_result()->fetch_assoc()['c'] ?? 0);
        $stmt->close();

        // page
        $sql = "SELECT o.office_id, o.name, o.address, o.logo
                FROM Office o
                $whereSql
                ORDER BY o.name ASC
                LIMIT ? OFFSET ?";
        $stmt = $conn->prepare($sql);

        $types2 = $types . "ii";
        $params2 = $params;
        $params2[] = $perPage;
        $params2[] = $offset;

        $stmt->bind_param($types2, ...$params2);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        // decorate
        foreach ($rows as &$r) {
            $slots = self::nextSlots($conn, (int) $r['office_id'], 6);
            $r['slots'] = $slots;
            $r['is_open'] = self::hasTodaySlot($slots);
            $r['badge'] = self::initials($r['name'] ?? '');
        }
        unset($r);

        return [
            'rows' => $rows,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'pages' => max(1, (int) ceil($total / $perPage)),
        ];
    }

    /** NEW: list approved offices for a REQUIRED specialty slug (+optional q/loc) */
    public static function searchBySpecialtyPaginated(
        mysqli $conn,
        string $specialtySlug,
        ?string $q,
        ?string $loc,
        int $page = 1,
        int $perPage = 10
    ): array {
        $page = max(1, $page);
        $perPage = max(1, min(50, $perPage));
        $offset = ($page - 1) * $perPage;

        $where = ["o.status = 'approved'", "ms.slug = ?"];
        $params = [$specialtySlug];
        $types = "s";

        if ($q !== null && $q !== '') {
            $where[] = "(o.name LIKE ? OR m.name LIKE ?)";
            $like = "%$q%";
            $params[] = $like;
            $types .= "s";
            $params[] = $like;
            $types .= "s";
        }
        if ($loc !== null && $loc !== '') {
            $where[] = "o.address LIKE ?";
            $params[] = "%$loc%";
            $types .= "s";
        }

        $whereSql = "WHERE " . implode(" AND ", $where);

        // total
        $sqlCount = "SELECT COUNT(DISTINCT o.office_id) AS c
                     FROM Office o
                     JOIN Office_has_specialty ohs ON ohs.office_id = o.office_id
                     JOIN Medical_specialty ms     ON ms.specialty_id = ohs.specialty_id
                     $whereSql";
        $stmt = $conn->prepare($sqlCount);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $total = (int) ($stmt->get_result()->fetch_assoc()['c'] ?? 0);
        $stmt->close();

        // page
        $sql = "SELECT DISTINCT o.office_id, o.name, o.address, o.logo
                FROM Office o
                JOIN Office_has_specialty ohs ON ohs.office_id = o.office_id
                JOIN Medical_specialty ms     ON ms.specialty_id = ohs.specialty_id
                $whereSql
                ORDER BY o.name ASC
                LIMIT ? OFFSET ?";
        $stmt = $conn->prepare($sql);

        $types2 = $types . "ii";
        $params2 = $params;
        $params2[] = $perPage;
        $params2[] = $offset;

        $stmt->bind_param($types2, ...$params2);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        foreach ($rows as &$r) {
            $slots = self::nextSlots($conn, (int) $r['office_id'], 6);
            $r['slots'] = $slots;
            $r['is_open'] = self::hasTodaySlot($slots);
            $r['badge'] = self::initials($r['name'] ?? '');
        }
        unset($r);

        return [
            'rows' => $rows,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'pages' => max(1, (int) ceil($total / $perPage)),
        ];
    }

    /** shared helpers */
    public static function nextSlots(mysqli $conn, int $officeId, int $limit = 6): array
    {
        $sql = "SELECT s.start_time
                FROM Appointment_slot s
                JOIN Doctor d ON d.doctor_id = s.doctor_id
                WHERE d.office_id = ?
                  AND s.status = 'available'
                  AND s.start_time >= NOW()
                ORDER BY s.start_time ASC
                LIMIT ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $officeId, $limit);
        $stmt->execute();
        $res = $stmt->get_result();

        $out = [];
        $now = new DateTimeImmutable('now');
        $todayYmd = $now->format('Y-m-d');
        $tomYmd = $now->modify('+1 day')->format('Y-m-d');

        while ($row = $res->fetch_assoc()) {
            $dt = new DateTimeImmutable($row['start_time']);
            $ymd = $dt->format('Y-m-d');
            $dayLabel =
                $ymd === $todayYmd ? "Today" :
                ($ymd === $tomYmd ? "Tomorrow" : $dt->format('D'));

            $out[] = [
                'dt' => $dt->format('c'),
                'day_key' => $ymd,
                'day_label' => $dayLabel,
                'time_label' => strtolower($dt->format('g:ia')),
            ];
        }
        $stmt->close();
        return $out;
    }

    private static function hasTodaySlot(array $slots): bool
    {
        $todayYmd = (new DateTimeImmutable('now'))->format('Y-m-d');
        foreach ($slots as $s) {
            if (($s['day_key'] ?? '') === $todayYmd)
                return true;
        }
        return false;
    }

    private static function initials(string $name): string
    {
        $name = trim(preg_replace('/\s+/', ' ', $name));
        if ($name === '')
            return 'CL';
        $parts = explode(' ', $name);
        $a = mb_strtoupper(mb_substr($parts[0], 0, 1));
        $b = mb_strtoupper(mb_substr(end($parts), 0, 1));
        return $a . $b;
    }
    public static function countBySpecialtySlug(mysqli $conn, string $slug): int
    {
        $sql = "
          SELECT COUNT(*) AS c
          FROM Office o
          JOIN Office_has_specialty ohs ON ohs.office_id = o.office_id
          JOIN Medical_specialty ms     ON ms.specialty_id = ohs.specialty_id
          WHERE ms.slug = ? AND o.status = 'approved'
        ";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $slug);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        return (int) ($res['c'] ?? 0);
    }

    public static function listBySpecialtySlug(
        mysqli $conn,
        string $slug,
        int $limit,
        int $offset
    ): array {
        $sql = "
          SELECT o.office_id, o.name, o.address, o.website, o.logo, o.status
          FROM Office o
          JOIN Office_has_specialty ohs ON ohs.office_id = o.office_id
          JOIN Medical_specialty ms     ON ms.specialty_id = ohs.specialty_id
          WHERE ms.slug = ? AND o.status = 'approved'
          ORDER BY o.name ASC
          LIMIT ? OFFSET ?
        ";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('sii', $slug, $limit, $offset);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    public static function listPaginatedAll(mysqli $conn, int $page = 1, int $perPage = 10): array
    {
        $page = max(1, $page);
        $perPage = max(1, min(50, $perPage));
        $offset = ($page - 1) * $perPage;

        // Count
        $sqlC = "SELECT COUNT(*) AS c FROM Office WHERE status = 'approved'";
        $total = (int) ($conn->query($sqlC)->fetch_assoc()['c'] ?? 0);

        // Page
        $sql = "SELECT office_id, name, address, logo
                 FROM Office
                 WHERE status = 'approved'
                 ORDER BY name ASC
                 LIMIT ? OFFSET ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $perPage, $offset);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        // Add slots + open + badge
        foreach ($rows as &$r) {
            $r['slots'] = self::nextSlots($conn, (int) $r['office_id'], 6);
            $r['is_open'] = self::hasSlotToday($r['slots']);
            $r['badge'] = self::initials($r['name'] ?? '');
            $r['status'] = 'approved'; // for the view’s open/closed pill
        }
        unset($r);

        return [
            'rows' => $rows,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'pages' => max(1, (int) ceil($total / $perPage)),
        ];
    }

    /** List approved offices under a specialty (by id) */
    public static function bySpecialtyPaginated(mysqli $conn, int $specialtyId, int $page = 1, int $perPage = 10): array
    {
        $page = max(1, $page);
        $perPage = max(1, min(50, $perPage));
        $offset = ($page - 1) * $perPage;

        // Count (distinct offices for this specialty)
        $sqlC = "SELECT COUNT(DISTINCT o.office_id) AS c
                 FROM Office o
                 JOIN Office_has_specialty ohs ON ohs.office_id = o.office_id
                 WHERE o.status = 'approved' AND ohs.specialty_id = ?";
        $stmt = $conn->prepare($sqlC);
        $stmt->bind_param("i", $specialtyId);
        $stmt->execute();
        $total = (int) ($stmt->get_result()->fetch_assoc()['c'] ?? 0);
        $stmt->close();

        // Page
        $sql = "SELECT DISTINCT o.office_id, o.name, o.address, o.logo
                 FROM Office o
                 JOIN Office_has_specialty ohs ON ohs.office_id = o.office_id
                 WHERE o.status = 'approved' AND ohs.specialty_id = ?
                 ORDER BY o.name ASC
                 LIMIT ? OFFSET ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iii", $specialtyId, $perPage, $offset);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        foreach ($rows as &$r) {
            $r['slots'] = self::nextSlots($conn, (int) $r['office_id'], 6);
            $r['is_open'] = self::hasSlotToday($r['slots']);
            $r['badge'] = self::initials($r['name'] ?? '');
            $r['status'] = 'approved';
        }
        unset($r);

        return [
            'rows' => $rows,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'pages' => max(1, (int) ceil($total / $perPage)),
        ];
    }

    public static function hasSlotToday(array $slots): bool
    {
        if (!$slots)
            return false;
        $todayYmd = (new DateTimeImmutable('now'))->format('Y-m-d');
        foreach ($slots as $s) {
            if (($s['day_key'] ?? '') === $todayYmd)
                return true;
        }
        return false;
    }
    // app/models/Office.php
    public static function findOne(mysqli $conn, int $officeId): ?array
    {
        $sql = "SELECT 
              o.office_id, o.name, o.address, o.logo, o.status,
              o.website, o.description, o.googleMap, o.rating,
              MIN(op.phone) AS phone
            FROM Office o
            LEFT JOIN Office_phone op ON op.office_id = o.office_id
            WHERE o.office_id = ? AND o.status = 'approved'
            GROUP BY o.office_id, o.name, o.address, o.logo, o.status, o.website, o.description, o.googleMap, o.rating
            LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $officeId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if (!$row)
            return null;

        // 2-letter badge
        $parts = preg_split('/\s+/', trim($row['name']));
        $row['code'] = strtoupper(
            mb_substr($parts[0] ?? '', 0, 1) .
            mb_substr($parts ? end($parts) : '', 0, 1)
        );
        $slotsForOpen = self::nextSlots($conn, (int) $row['office_id'], 1);
        $row['is_open'] = self::hasTodaySlot($slotsForOpen);   // reuse the helper below
        $row['hours_label'] = $row['is_open'] ? 'Slots today' : null;

        return $row;
    }

    public static function doctorsWithNext(mysqli $conn, int $officeId): array
    {
        // ----- Decide how to read doctor name based on existing columns -----
        $nameExpr = null;
        $nameParts = [];
        if (self::colExists($conn, 'Doctor', 'name')) {
            $nameExpr = "d.name";
        } else {
            if (self::colExists($conn, 'Doctor', 'first_name'))
                $nameParts[] = "d.first_name";
            if (self::colExists($conn, 'Doctor', 'last_name'))
                $nameParts[] = "d.last_name";
            if ($nameParts) {
                $nameExpr = "TRIM(CONCAT_WS(' ', " . implode(',', $nameParts) . "))";
            } elseif (self::colExists($conn, 'Doctor', 'full_name')) {
                $nameExpr = "d.full_name";
            } elseif (self::colExists($conn, 'Doctor', 'doctor_name')) {
                $nameExpr = "d.doctor_name";
            } elseif (self::colExists($conn, 'Doctor', 'display_name')) {
                $nameExpr = "d.display_name";
            } else {
                $nameExpr = "CONCAT('Doctor #', d.doctor_id)";
            }
        }

        // ----- Decide how to read specialty -----
        $specExpr = "'Doctor'";
        $joinSpec = "";
        if (self::colExists($conn, 'Doctor', 'specialty')) {
            $specExpr = "d.specialty";
        } elseif (
            self::colExists($conn, 'Doctor', 'specialty_id')
            && self::tableExists($conn, 'Medical_specialty')
            && self::colExists($conn, 'Medical_specialty', 'name')
        ) {
            $specExpr = "ms.name";
            $joinSpec = "LEFT JOIN Medical_specialty ms ON ms.specialty_id = d.specialty_id";
        }
        $degreeExpr = "''";
        if (self::colExists($conn, 'Doctor', 'degree')) {
            $degreeExpr = "COALESCE(d.degree, '')";
        }
        
        $photoExpr = "NULL";
        if (self::colExists($conn, 'Doctor', 'photo')) {
            $photoExpr = "d.photo";
        }
        
        // ----- Fetch doctors -----
        $sql = "SELECT 
                d.doctor_id,
                $nameExpr   AS doc_name,
                $specExpr   AS specialty,
                $degreeExpr AS degree,
                $photoExpr  AS photo
            FROM Doctor d
            $joinSpec
            WHERE d.office_id = ?
            ORDER BY doc_name ASC";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $officeId);
        $stmt->execute();
        $res = $stmt->get_result();

        $docs = [];
        while ($r = $res->fetch_assoc()) {
            // Normalize keys for your view
            $r['id'] = (int) $r['doctor_id'];
            $r['name'] = (string) $r['doc_name'];
            $r['spec'] = (string) $r['specialty'];
            $r['degree'] = (string) ($r['degree'] ?? '');

            // Initials
            $p = preg_split('/\s+/', trim($r['name']));
            $r['init'] = strtoupper(
                mb_substr($p[0] ?? '', 0, 1) .
                mb_substr($p ? end($p) : '', 0, 1)
            );

            $docs[] = $r;
        }
        $stmt->close();

        // ----- One "next" slot per doctor -----
        if (!empty($docs) && self::tableExists($conn, 'Appointment_slot') && self::colExists($conn, 'Appointment_slot', 'start_time')) {
            $sqlNext = "SELECT start_time
                    FROM Appointment_slot
                    WHERE doctor_id = ?
                      AND status = 'available'
                      AND start_time >= NOW()
                    ORDER BY start_time ASC
                    LIMIT 1";
            $stmt = $conn->prepare($sqlNext);
            foreach ($docs as &$d) {
                $stmt->bind_param("i", $d['id']);
                $stmt->execute();
                $r = $stmt->get_result()->fetch_assoc();
                $d['next'] = $r
                    ? (new DateTimeImmutable($r['start_time']))->format('D, g:ia')
                    : '—';
            }
            $stmt->close();
        } else {
            foreach ($docs as &$d) {
                $d['next'] = '—';
            }
        }

        return $docs;
    }
}