<?php

declare(strict_types=1);

class SparkController
{
    private SparkRepository $repo;

    // TODO: replace with session user_id once OAuth is wired up
    private const DEV_USER_ID = 1;

    public function __construct(SparkRepository $repo)
    {
        $this->repo = $repo;
    }

    // -------------------------------------------------------------------------
    // Spark endpoints
    // -------------------------------------------------------------------------

    public function listSparks(): void
    {
        $items = $this->repo->findAllByUser(self::DEV_USER_ID);
        $this->json(['items' => $items], 200);
    }

    public function createSpark(): void
    {
        $body = $this->parseBody();
        $text = trim($body['text'] ?? '');

        if ($text === '') {
            $this->error('VALIDATION_ERROR', 'text is required', 400);
            return;
        }

        $spark = $this->repo->create(self::DEV_USER_ID, $text);
        $this->json(['item' => $spark], 201);
    }

    public function updateSpark(int $id): void
    {
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

        $spark = $this->repo->update(self::DEV_USER_ID, $id, $fields);

        if ($spark === null) {
            $this->error('NOT_FOUND', 'Spark not found', 404);
            return;
        }

        $this->json(['item' => $spark], 200);
    }

    public function deleteSpark(int $id): void
    {
        $deleted = $this->repo->delete(self::DEV_USER_ID, $id);

        if (!$deleted) {
            $this->error('NOT_FOUND', 'Spark not found', 404);
            return;
        }

        $this->json(['ok' => true], 200);
    }

    // -------------------------------------------------------------------------
    // Auth stubs (OAuth deferred)
    // -------------------------------------------------------------------------

    public function getSession(): void
    {
        $this->json(['user' => null], 200);
    }

    public function logout(): void
    {
        $this->json(['ok' => true], 200);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function parseBody(): array
    {
        $raw = file_get_contents('php://input');
        if ($raw === '' || $raw === false) {
            return [];
        }
        return json_decode($raw, true) ?? [];
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
