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


// Change spark state (e.g., ignored, open, etc.)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['id'])) {
    $id = $_GET['id'];
    $data = json_decode(file_get_contents('php://input'), true);
    $newState = $data['state'] ?? null;
    
    if ($id && $newState !== null) {
        $sparks = json_decode(file_get_contents($sparksFile), true);
        foreach ($sparks as &$spark) {
            if ($spark['id'] === $id) {
                $spark['state'] = $newState;
                break;
            }
        }
        file_put_contents($sparksFile, json_encode($sparks, JSON_PRETTY_PRINT));
        echo json_encode(['status' => 'updated']);
    } else {
        echo json_encode(['status' => 'error']);
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $sparks = file_exists($sparksFile) ? json_decode(file_get_contents($sparksFile), true) : [];
    $sparks[] = [
        'id' => uniqid(),
        'text' => $data['text'],
        'state' => 'open',
        'created_at' => date('c') // ISO 8601 format
    ];
    file_put_contents($sparksFile, json_encode($sparks, JSON_PRETTY_PRINT));
    echo json_encode(['status' => 'success']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'DELETE' && isset($_GET['id'])) {
    $id = $_GET['id'];
    $sparks = json_decode(file_get_contents($sparksFile), true);
    $sparks = array_values(array_filter($sparks, function ($spark) use ($id) {
        return $spark['id'] !== $id;
    }));
    file_put_contents($sparksFile, json_encode($sparks, JSON_PRETTY_PRINT));
    echo json_encode(['status' => 'deleted']);
    exit;
}

echo json_encode(['status' => 'invalid request']);
