<?php
header('Content-Type: application/json; charset=utf-8');
$path = __DIR__ . '/../../data/locations.json';
if (!file_exists($path)) {
    echo json_encode((object)[]);
    exit;
}
$contents = file_get_contents($path);
if ($contents === false) {
    echo json_encode((object)[]);
    exit;
}
// Validate JSON
json_decode($contents);
if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode((object)[]);
    exit;
}

echo $contents;
