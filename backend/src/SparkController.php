<?php

declare(strict_types=1);

class SparkController
{
    private SparkRepository $repo;
    
    public function __construct(SparkRepository $repo)
    {
        $this->repo = $repo;
    }
    
    // -------------------------------------------------------------------------
    // Spark endpoints
    // -------------------------------------------------------------------------
    
    public function listSparks(): void
    {
        $userId = $this->requireAuth();
        $items = $this->repo->findAllByUser($userId);
        $this->json(['items' => $items], 200);
    }
    
    public function createSpark(): void
    {
        $userId = $this->requireAuth();
        
        $body = $this->parseBody();
        $text = trim($body['text'] ?? '');
        
        if ($text === '') {
            $this->error('VALIDATION_ERROR', 'text is required', 400);
            return;
        }
        
        $spark = $this->repo->create($userId, $text);
        $this->json(['item' => $spark], 201);
    }
    
    public function updateSpark(int $id): void
    {
        $userId = $this->requireAuth();
        
        $body = $this->parseBody();
        $validStates = ['open', 'ignored', 'searched', 'finished'];
        
        $allowed = ['state', 'completed_note', 'text'];
        $fields = [];
        foreach ($allowed as $key) {
            if (array_key_exists($key, $body)) {
                $fields[$key] = $body[$key];
            }
        }
        
        if (isset($fields['state']) && !in_array($fields['state'], $validStates, true)) {
            $this->error('VALIDATION_ERROR', 'Invalid state value', 400);
            return;
        }
        
        $spark = $this->repo->update($userId, $id, $fields);
        
        if ($spark === null) {
            $this->error('NOT_FOUND', 'Spark not found', 404);
            return;
        }
        
        $this->json(['item' => $spark], 200);
    }
    
    public function deleteSpark(int $id): void
    {
        $userId = $this->requireAuth();
        
        $deleted = $this->repo->delete($userId, $id);
        
        if (!$deleted) {
            $this->error('NOT_FOUND', 'Spark not found', 404);
            return;
        }
        
        $this->json(['ok' => true], 200);
    }
    
    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------
    
    private function requireAuth(): int
    {
        $uid = $_SESSION['user_id'] ?? null;
        
        if (!is_int($uid) && !(is_string($uid) && ctype_digit($uid))) {
            $this->error('UNAUTHENTICATED', 'Login required', 401);
            exit;
        }
        
        $userId = (int) $uid;
        if ($userId < 1) {
            $this->error('UNAUTHENTICATED', 'Login required', 401);
            exit;
        }
        
        return $userId;
    }
    
    private function parseBody(): array
    {
        $raw = file_get_contents('php://input');
        if ($raw === '' || $raw === false) {
            return [];
        }
        
        $data = json_decode($raw, true);
        if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
            $this->error('INVALID_JSON', 'Request body must be valid JSON', 400);
            exit;
        }
        
        return is_array($data) ? $data : [];
    }
    
    private function json(array $data, int $status): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
    
    private function error(string $code, string $message, int $status): void
    {
        $this->json(['error' => ['code' => $code, 'message' => $message]], $status);
    }
}
