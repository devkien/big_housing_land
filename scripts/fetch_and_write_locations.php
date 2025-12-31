<?php
// Fetch provinces open API and convert to project's JSON shape
$url = 'https://provinces.open-api.vn/api/?depth=3';
$raw = @file_get_contents($url);
if ($raw === false) {
    echo "Failed to fetch $url\n";
    exit(1);
}
$data = json_decode($raw, true);
if (!is_array($data)) {
    echo "Invalid JSON from API\n";
    exit(1);
}
$out = [];
foreach ($data as $prov) {
    $pSlug = $prov['codename'] ?? preg_replace('/[^a-z0-9_]+/', '_', strtolower($prov['name']));
    $out[$pSlug] = [
        'name' => $prov['name'] ?? '',
        'districts' => []
    ];
    if (!empty($prov['districts']) && is_array($prov['districts'])) {
        foreach ($prov['districts'] as $dist) {
            $dSlug = $dist['codename'] ?? preg_replace('/[^a-z0-9_]+/', '_', strtolower($dist['name']));
            $out[$pSlug]['districts'][$dSlug] = [
                'name' => $dist['name'] ?? '',
                'wards' => []
            ];
            if (!empty($dist['wards']) && is_array($dist['wards'])) {
                foreach ($dist['wards'] as $ward) {
                    $wId = $ward['codename'] ?? preg_replace('/[^a-z0-9_]+/', '_', strtolower($ward['name']));
                    $out[$pSlug]['districts'][$dSlug]['wards'][] = [
                        'id' => $wId,
                        'name' => $ward['name'] ?? ''
                    ];
                }
            }
        }
    }
}
$path = __DIR__ . '/../data/locations.json';
file_put_contents($path, json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
echo "Wrote: $path\n";
