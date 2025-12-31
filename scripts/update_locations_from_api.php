<?php
$api = 'https://provinces.open-api.vn/api/v2/?depth=3';
$outPath = __DIR__ . '/../data/locations.json';
$opts = [
    "http" => [
        "method" => "GET",
        "header" => "User-Agent: Big_Housing_Land/1.0\r\n"
    ]
];
$context = stream_context_create($opts);
// Try direct fetch first; if that fails, look for a locally downloaded copy at scripts/provinces_raw.json
$contents = @file_get_contents($api, false, $context);
if ($contents === false) {
    $local = __DIR__ . '/provinces_raw.json';
    if (file_exists($local)) {
        $contents = file_get_contents($local);
        if ($contents === false) {
            fwrite(STDERR, "Failed to read local cached API file: $local\n");
            exit(1);
        }
    } else {
        fwrite(STDERR, "Failed to fetch API: $api and no local cache found\n");
        exit(1);
    }
}

$data = json_decode($contents, true);
if (!is_array($data)) {
    fwrite(STDERR, "API returned invalid JSON\n");
    exit(1);
}
$result = [];
foreach ($data as $prov) {
    $provCodename = $prov['codename'] ?? null;
    if (!$provCodename) continue;
    $provName = $prov['name'] ?? $provCodename;
    $districts = [];
    // API may supply either 'districts' (with nested wards) or a top-level 'wards' list per province.
    if (!empty($prov['districts']) && is_array($prov['districts'])) {
        $districtsRaw = $prov['districts'];
        foreach ($districtsRaw as $dist) {
            $distCodename = $dist['codename'] ?? null;
            if (!$distCodename) continue;
            $distName = $dist['name'] ?? $distCodename;
            $wardsRaw = $dist['wards'] ?? [];
            $wards = [];
            if (is_array($wardsRaw)) {
                foreach ($wardsRaw as $ward) {
                    $wardCodename = $ward['codename'] ?? ($ward['code'] ?? null);
                    $wardName = $ward['name'] ?? $wardCodename;
                    if (!$wardCodename) continue;
                    $wards[] = [
                        'id' => $wardCodename,
                        'name' => $wardName
                    ];
                }
            }
            $districts[$distCodename] = [
                'name' => $distName,
                'wards' => $wards
            ];
        }
    } elseif (!empty($prov['wards']) && is_array($prov['wards'])) {
        // Create a pseudo-district to hold all wards when API provides wards at province level
        $wards = [];
        foreach ($prov['wards'] as $ward) {
            $wardCodename = $ward['codename'] ?? ($ward['code'] ?? null);
            $wardName = $ward['name'] ?? $wardCodename;
            if (!$wardCodename) continue;
            $wards[] = ['id' => $wardCodename, 'name' => $wardName];
        }
        $pseudoDistCodename = $provCodename . '_all';
        $districts[$pseudoDistCodename] = [
            'name' => $provName,
            'wards' => $wards
        ];
    }
    $result[$provCodename] = [
        'name' => $provName,
        'districts' => $districts
    ];
}
$encoded = json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
if ($encoded === false) {
    fwrite(STDERR, "Failed to encode JSON\n");
    exit(1);
}
if (file_put_contents($outPath, $encoded) === false) {
    fwrite(STDERR, "Failed to write to $outPath\n");
    exit(1);
}
echo "Updated $outPath with " . count($result) . " provinces\n";
