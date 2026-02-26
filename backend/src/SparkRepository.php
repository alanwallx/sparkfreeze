<?php

declare(strict_types=1);

class SparkRepository
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function findAllByUser(int $userId): array
    {
        $stmt = $this->db->prepare(
            'SELECT id, text, state, created_at, updated_at, completed_note
             FROM sparks
             WHERE user_id = :uid
             ORDER BY created_at DESC'
        );
        $stmt->execute([':uid' => $userId]);
        return $stmt->fetchAll();
    }

    public function create(int $userId, string $text): array
    {
        $stmt = $this->db->prepare(
            'INSERT INTO sparks (user_id, text, state) VALUES (:uid, :text, "open")'
        );
        $stmt->execute([':uid' => $userId, ':text' => $text]);
        return $this->findById($userId, (int) $this->db->lastInsertId());
    }

    public function update(int $userId, int $sparkId, array $fields): ?array
    {
        $allowed = ['state', 'completed_note', 'text'];
        $setClauses = [];
        $params = [':uid' => $userId, ':id' => $sparkId];

        foreach ($allowed as $col) {
            if (array_key_exists($col, $fields)) {
                $setClauses[] = "{$col} = :{$col}";
                $params[":{$col}"] = $fields[$col];
            }
        }

        if (empty($setClauses)) {
            return $this->findById($userId, $sparkId);
        }

        $sql = 'UPDATE sparks SET ' . implode(', ', $setClauses) .
               ' WHERE id = :id AND user_id = :uid';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        if ($stmt->rowCount() === 0) {
            return null;
        }

        return $this->findById($userId, $sparkId);
    }

    public function delete(int $userId, int $sparkId): bool
    {
        $stmt = $this->db->prepare(
            'DELETE FROM sparks WHERE id = :id AND user_id = :uid'
        );
        $stmt->execute([':id' => $sparkId, ':uid' => $userId]);
        return $stmt->rowCount() > 0;
    }

    private function findById(int $userId, int $sparkId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT id, text, state, created_at, updated_at, completed_note
             FROM sparks
             WHERE id = :id AND user_id = :uid'
        );
        $stmt->execute([':id' => $sparkId, ':uid' => $userId]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }
}
