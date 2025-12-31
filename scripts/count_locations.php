<?php
$path = __DIR__ . '/../data/locations.json';
if (!file_exists($path)) {
    echo "0\n";
    exit;
}
$contents = file_get_contents($path);
$j = json_decode($contents, true);
if (!is_array($j)) {
    echo "0\n";
    exit;
}
echo count($j) . PHP_EOL;
