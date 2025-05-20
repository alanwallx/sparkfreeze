<?php


$sparksFile = '../../sparks.json';

$sparks = json_decode(file_get_contents($sparksFile), true);

foreach ($sparks as &$spark) {
    if (!isset($spark['created_at'])) {
        $spark['created_at'] = date('c');
    }
}

file_put_contents($sparksFile, json_encode($sparks, JSON_PRETTY_PRINT));

echo "Backfilled " . count($sparks) . " sparks.\n";
