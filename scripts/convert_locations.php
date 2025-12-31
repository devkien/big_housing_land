<?php
// Usage: php scripts/convert_locations.php path/to/locations.csv
// CSV expected columns (header): province_slug,province_name,district_slug,district_name,ward_slug,ward_name
// Outputs JSON file at data/locations.json

if ($argc < 2) {
    echo "Usage: php scripts/convert_locations.php path/to/locations.csv\n";
    exit(1);
}

$csv = $argv[1];
if (!file_exists($csv)) {
    echo "CSV file not found: $csv\n";
    exit(1);
}

$handle = fopen($csv, 'r');
if (!$handle) {
    echo "Failed to open $csv\n";
    exit(1);
}

$header = fgetcsv($handle);
if (!$header) {
    echo "Empty CSV\n";
    exit(1);
}

$mapIndex = array_flip($header);
$required = ['province_slug', 'province_name', 'district_slug', 'district_name', 'ward_slug', 'ward_name'];
foreach ($required as $r) {
    if (!isset($mapIndex[$r])) {
        echo "CSV missing required column: $r\n";
        exit(1);
    }
}

$data = [];
while (($row = fgetcsv($handle)) !== false) {
    $provSlug = $row[$mapIndex['province_slug']];
    $provName = $row[$mapIndex['province_name']];
    $distSlug = $row[$mapIndex['district_slug']];
    $distName = $row[$mapIndex['district_name']];
    $wardSlug = $row[$mapIndex['ward_slug']];
    $wardName = $row[$mapIndex['ward_name']];

    if (!isset($data[$provSlug])) {
        $data[$provSlug] = ['name' => $provName, 'districts' => []];
    }
    if (!isset($data[$provSlug]['districts'][$distSlug])) {
        $data[$provSlug]['districts'][$distSlug] = ['name' => $distName, 'wards' => []];
    }
    $data[$provSlug]['districts'][$distSlug]['wards'][] = ['id' => $wardSlug, 'name' => $wardName];
}
fclose($handle);

$outPath = __DIR__ . '/../data/locations.json';
file_put_contents($outPath, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
echo "Wrote: $outPath\n";
