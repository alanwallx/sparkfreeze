<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/bootstrap.php';

/// ---- CORS -------------------------------------------------------------------
function env_list(string $key): array {
    $raw = getenv($key) ?: '';
    if ($raw === '') return [];
    $parts = array_map('trim', explode(',', $raw));
    return array_values(array_filter($parts, fn($v) => $v !== ''));
}

$allowedOrigins = env_list('APP_ALLOWED_ORIGINS');

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if ($origin !== '' && in_array($origin, $allowedOrigins, true)) {
    header("Access-Control-Allow-Origin: {$origin}");
    header('Access-Control-Allow-Credentials: true');
    header('Vary: Origin');
    header('Access-Control-Allow-Headers: Content-Type');
    header('Access-Control-Allow-Methods: GET, POST, PATCH, DELETE, OPTIONS');
}

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// ---- Bootstrap --------------------------------------------------------------
require_once __DIR__ . '/../src/Db.php';
require_once __DIR__ . '/../src/SparkRepository.php';
require_once __DIR__ . '/../src/SparkController.php';

$controller = new SparkController(
    new SparkRepository(Db::get())
);

// ---- Route parsing ----------------------------------------------------------
$uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri    = rtrim($uri, '/');
$method = $_SERVER['REQUEST_METHOD'];

$segments = array_values(
    array_filter(explode('/', $uri), fn(string $s) => $s !== '')
);

// ---- Dispatch ---------------------------------------------------------------

// GET /sparks
if ($method === 'GET' && $segments === ['sparks']) {
    $controller->listSparks();
    exit;
}

// POST /sparks
if ($method === 'POST' && $segments === ['sparks']) {
    $controller->createSpark();
    exit;
}

// PATCH /sparks/{id}
if ($method === 'PATCH' && count($segments) === 2 && $segments[0] === 'sparks') {
    $id = filter_var($segments[1], FILTER_VALIDATE_INT);
    if ($id === false || $id < 1) {
        http_response_code(400);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['error' => ['code' => 'INVALID_ID', 'message' => 'ID must be a positive integer']]);
        exit;
    }
    $controller->updateSpark((int) $id);
    exit;
}

// DELETE /sparks/{id}
if ($method === 'DELETE' && count($segments) === 2 && $segments[0] === 'sparks') {
    $id = filter_var($segments[1], FILTER_VALIDATE_INT);
    if ($id === false || $id < 1) {
        http_response_code(400);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['error' => ['code' => 'INVALID_ID', 'message' => 'ID must be a positive integer']]);
        exit;
    }
    $controller->deleteSpark((int) $id);
    exit;
}

// GET /auth/session
if ($method === 'GET' && $segments === ['auth', 'session']) {
    $controller->getSession();
    exit;
}

// POST /auth/logout
if ($method === 'POST' && $segments === ['auth', 'logout']) {
    $controller->logout();
    exit;
}

// ---- 404 Fallthrough --------------------------------------------------------
http_response_code(404);
header('Content-Type: application/json; charset=utf-8');
echo json_encode(['error' => ['code' => 'NOT_FOUND', 'message' => 'Route not found']]);
