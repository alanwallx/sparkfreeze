<?php

declare(strict_types=1);

require_once __DIR__ . '/Auth/GoogleIdTokenVerifier.php';

class AuthController
{
    private PDO $pdo;
    private GoogleIdTokenVerifier $verifier;
    
    public function __construct(PDO $pdo, GoogleIdTokenVerifier $verifier)
    {
        $this->pdo = $pdo;
        $this->verifier = $verifier;
    }
    
    // GET /auth/nonce
    public function nonce(): void
    {
        $_SESSION['login_nonce'] = bin2hex(random_bytes(16));
        $this->json(['nonce' => $_SESSION['login_nonce']], 200);
    }
    
    // POST /auth/google
    public function googleLogin(): void
    {
        $body = $this->parseBody();
        $credential = $body['credential'] ?? null;
        
        if (!is_string($credential) || $credential === '') {
            $this->error('VALIDATION_ERROR', 'credential is required', 400);
            return;
        }
        
        $expectedNonce = $_SESSION['login_nonce'] ?? null;
        unset($_SESSION['login_nonce']); // one-shot
        
        try {
            $claims = $this->verifier->verify(
                $credential,
                is_string($expectedNonce) ? $expectedNonce : null
            );
        } catch (Throwable $e) {
            $this->error('UNAUTHENTICATED', 'Invalid Google token', 401);
            return;
        }
        
        $googleSub = $claims['sub'] ?? null;
        $email = $claims['email'] ?? null;
        $emailVerified = $claims['email_verified'] ?? null;
        $name = $claims['name'] ?? null;
        
        if (!is_string($googleSub) || $googleSub === '') {
            $this->error('UNAUTHENTICATED', 'Missing sub claim', 401);
            return;
        }
        if (!is_string($email) || $email === '') {
            $this->error('UNAUTHENTICATED', 'Missing email claim', 401);
            return;
        }
        if ($emailVerified !== true && $emailVerified !== 'true') {
            $this->error('UNAUTHENTICATED', 'Email not verified', 401);
            return;
        }
        
        $userId = $this->findOrCreateUser($googleSub, $email, is_string($name) ? $name : null);
        
        // Prevent session fixation
        session_regenerate_id(true);
        $_SESSION['user_id'] = $userId;
        
        $this->json(['ok' => true], 200);
    }
    
    // GET /auth/session
    public function session(): void
    {
        $uid = $_SESSION['user_id'] ?? null;
        if (!is_int($uid) && !(is_string($uid) && ctype_digit($uid))) {
            $this->json(['user' => null], 200);
            return;
        }
        
        $stmt = $this->pdo->prepare('SELECT id, email, name, created_at FROM users WHERE id = ? LIMIT 1');
        $stmt->execute([(int) $uid]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        
        $this->json(['user' => $user], 200);
    }
    
    // POST /auth/logout
    public function logout(): void
    {
        $_SESSION = [];
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
        $this->json(['ok' => true], 200);
    }
    
    private function findOrCreateUser(string $googleSub, string $email, ?string $name): int
    {
        $this->pdo->beginTransaction();
        
        try {
            // 1) Prefer google_id match
            $stmt = $this->pdo->prepare('SELECT id FROM users WHERE google_id = ? LIMIT 1');
            $stmt->execute([$googleSub]);
            $userId = $stmt->fetchColumn();
            
            if ($userId) {
                $this->pdo->commit();
                return (int) $userId;
            }
            
            // 2) Link by email if exists
            $stmt = $this->pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
            $stmt->execute([$email]);
            $userId = $stmt->fetchColumn();
            
            if ($userId) {
                $stmt = $this->pdo->prepare(
                    'UPDATE users SET google_id = ?, name = COALESCE(name, ?) WHERE id = ?'
                );
                $stmt->execute([$googleSub, $name, $userId]);
                $this->pdo->commit();
                return (int) $userId;
            }
            
            // 3) Create
            $stmt = $this->pdo->prepare('INSERT INTO users (email, name, google_id) VALUES (?, ?, ?)');
            $stmt->execute([$email, $name, $googleSub]);
            $newId = (int) $this->pdo->lastInsertId();
            
            $this->pdo->commit();
            return $newId;
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
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
