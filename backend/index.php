<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS");

$sparksFile = 'sparks.json';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    echo file_get_contents($sparksFile);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $sparks = file_exists($sparksFile) ? json_decode(file_get_contents($sparksFile), true) : [];
    $sparks[] = [
        'id' => uniqid(),
        'text' => $data['text'],
        'ignored' => false
    ];
    file_put_contents($sparksFile, json_encode($sparks, JSON_PRETTY_PRINT));
    echo json_encode(['status' => 'success']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    parse_str($_SERVER['QUERY_STRING'], $params);
    $id = $params['id'] ?? null;
    if ($id) {
        $sparks = json_decode(file_get_contents($sparksFile), true);
        foreach ($sparks as &$spark) {
            if ($spark['id'] === $id) {
                $spark['ignored'] = true;
                break;
            }
        }
        file_put_contents($sparksFile, json_encode($sparks, JSON_PRETTY_PRINT));
        echo json_encode(['status' => 'ignored']);
    } else {
        echo json_encode(['status' => 'error']);
    }
    exit;
}

echo json_encode(['status' => 'invalid request']);
