<?php
// app/models/Office.php

class Office
{
    /**
     * Paginated search for approved offices (clinics).
     * Filters:
     *  - $q   : matches office name OR specialty name/slug
     *  - $loc : matches address
     */
    public static function searchPaginated(mysqli $conn, ?string $q, ?string $loc, int $page = 1, int $perPage = 10): array
    {
        $page    = max(1, $page);
        $perPage = max(1, min(50, $perPage));
        $offset  = ($page - 1) * $perPage;

        $where = ["o.status = 'approved'"];
        $params = [];
        $types  = "";

        if ($q !== null && $q !== '') {
            $where[] = "(o.name LIKE ? OR EXISTS (
                SELECT 1
                FROM Office_has_specialty ohs
                JOIN Medical_specialty m ON m.specialty_id = ohs.specialty_id
                WHERE ohs.office_id = o.office_id
                  AND (m.name LIKE ? OR m.slug LIKE ?)
            ))";
            $like = "%$q%";
            $params[] = $like; $types .= "s";
            $params[] = $like; $types .= "s";
            $params[] = $like; $types .= "s";
        }

        if ($loc !== null && $loc !== '') {
            $where[] = "o.address LIKE ?";
            $params[] = "%$loc%"; $types .= "s";
        }

        $whereSql = $where ? ("WHERE " . implode(" AND ", $where)) : "";

        // Total count (distinct offices)
        $sqlCount = "SELECT COUNT(DISTINCT o.office_id) AS c
                     FROM Office o
                     $whereSql";
        $stmt = $conn->prepare($sqlCount);
        if ($types !== "") $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $total = (int)($stmt->get_result()->fetch_assoc()['c'] ?? 0);
        $stmt->close();

        // Page data
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

        // Enrich each office with next slots + simple "open" heuristic
        foreach ($rows as &$r) {
            $slots = self::nextSlots($conn, (int)$r['office_id'], 6);
            $r['slots'] = $slots;

            // Heuristic: "open" if there is any available slot remaining today
            $isOpen = false;
            $todayYmd = (new DateTimeImmutable('now'))->format('Y-m-d');
            foreach ($slots as $s) {
                if ($s['day_key'] === $todayYmd) { $isOpen = true; break; }
            }
            $r['is_open'] = $isOpen;

            // Badge initials (fallback when no logo)
            $r['badge'] = self::initials($r['name'] ?? '');
        }
        unset($r);

        $pages = max(1, (int)ceil($total / $perPage));

        return [
            'rows'     => $rows,
            'total'    => $total,
            'page'     => $page,
            'perPage'  => $perPage,
            'pages'    => $pages,
        ];
    }

    /**
     * Fetch up to $limit upcoming available slots for an office (across all its doctors).
     * Returns array of ['day_label','day_key','time_label','dt']
     */
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
        $tomYmd   = $now->modify('+1 day')->format('Y-m-d');

        while ($row = $res->fetch_assoc()) {
            $dt = new DateTimeImmutable($row['start_time']);
            $ymd = $dt->format('Y-m-d');
            if ($ymd === $todayYmd)      $dayLabel = "Today";
            elseif ($ymd === $tomYmd)    $dayLabel = "Tomorrow";
            else                         $dayLabel = $dt->format('D'); // Mon/Tue…

            $out[] = [
                'dt'         => $dt->format('c'),
                'day_key'    => $ymd,
                'day_label'  => $dayLabel,
                'time_label' => strtolower($dt->format('g:ia')),
            ];
        }
        $stmt->close();
        return $out;
    }

    private static function initials(string $name): string
    {
        $name = trim(preg_replace('/\s+/', ' ', $name));
        if ($name === '') return 'CL';
        $parts = explode(' ', $name);
        $a = mb_strtoupper(mb_substr($parts[0], 0, 1));
        $b = mb_strtoupper(mb_substr(end($parts), 0, 1));
        return $a . $b;
    }
}