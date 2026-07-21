<?php
// Shared bill -> group/level mapping and docket-matching logic.
// Used by every endpoint that needs to know "is this bill in this
// official's docket" (dashboard display, comment actions, proposals).
// Edit bill assignments HERE ONLY — do not copy these arrays elsewhere.

$GLOBALS['billGroups'] = [
    'finance-bill-2026'       => 'Finance',
    'appropriation-bill-2026' => 'Finance',
    'supp-approp-2026'        => 'Finance',
    'division-revenue-2026'   => 'Finance',
    'county-alloc-2026'       => 'Finance',
    'infra-fund-2026'         => 'Infrastructure',
    'food-feed-safety'        => 'Agriculture',
    'plant-protection'        => 'Agriculture',
    'forest-conservation'     => 'Environment',
    'competition-amendment'   => 'Trade',
    'procurement-amendment'   => 'Procurement',
    'culture-bill'            => 'Culture',
    'health-amendment'        => 'Health',
];

$GLOBALS['billLevels'] = [
    'finance-bill-2026'       => 'national',
    'appropriation-bill-2026' => 'national',
    'supp-approp-2026'        => 'national',
    'division-revenue-2026'   => 'national',
    'county-alloc-2026'       => 'national',
    'infra-fund-2026'         => 'national',
    'food-feed-safety'        => 'national',
    'plant-protection'        => 'national',
    'forest-conservation'     => 'national',
    'competition-amendment'   => 'national',
    'procurement-amendment'   => 'national',
    'culture-bill'            => 'national',
    'health-amendment'        => 'national',
];

function departmentMatchesGroup(?string $department, ?string $group): bool {
    if (!$department || !$group) return false;
    if ($department === 'All') return true;
    return $department === $group;
}

function levelMatches(?string $officialLevel, ?string $billLevel): bool {
    if (!$officialLevel || !$billLevel) return false;
    return $officialLevel === $billLevel;
}

/**
 * Fetches an official's department/level from the users table and checks
 * whether they own the given bill_slug's docket. Returns true/false.
 * Pass an open $conn (mysqli) and the official's user_id.
 */
function officialOwnsBill(mysqli $conn, int $user_id, string $bill_slug): bool {
    $billGroups = $GLOBALS['billGroups'];
    $billLevels = $GLOBALS['billLevels'];

    $stmt = $conn->prepare("SELECT office_department, government_level FROM users WHERE id = ?");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $officialDepartment = $row['office_department'] ?? null;
    $officialLevel = $row['government_level'] ?? null;

    return departmentMatchesGroup($officialDepartment, $billGroups[$bill_slug] ?? null)
        && levelMatches($officialLevel, $billLevels[$bill_slug] ?? null);
}