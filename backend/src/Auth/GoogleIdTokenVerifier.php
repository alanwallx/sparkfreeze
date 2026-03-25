<?php

declare(strict_types=1);

final class GoogleIdTokenVerifier
{
    private string $clientId;
    private string $cacheFile;
    
    public function __construct(string $clientId, string $cacheFile)
    {
        $this->clientId = $clientId;
        $this->cacheFile = $cacheFile;
    }
    
    /** @return array<string,mixed> */
    public function verify(string $jwt, ?string $expectedNonce): array
    {
        [$headerB64, $payloadB64, $sigB64] = $this->splitJwt($jwt);
        
        $header = $this->jsonDecode($this->base64UrlDecode($headerB64));
        $payload = $this->jsonDecode($this->base64UrlDecode($payloadB64));
        
        if (($header['alg'] ?? null) !== 'RS256') {
            throw new RuntimeException('Unsupported JWT alg');
        }
        
        $kid = $header['kid'] ?? null;
        if (!is_string($kid) || $kid === '') {
            throw new RuntimeException('Missing kid');
        }
        
        $certs = $this->getGoogleCerts(); // kid => PEM
        $pem = $certs[$kid] ?? null;
        if (!is_string($pem) || $pem === '') {
            throw new RuntimeException('Unknown kid');
        }
        
        $data = $headerB64 . '.' . $payloadB64;
        $signature = $this->base64UrlDecode($sigB64);
        
        $ok = openssl_verify($data, $signature, $pem, OPENSSL_ALGO_SHA256);
        if ($ok !== 1) {
            throw new RuntimeException('Invalid signature');
        }
        
        $this->validateClaims($payload, $expectedNonce);
        
        return $payload;
    }
    
    private function validateClaims(array $payload, ?string $expectedNonce): void
    {
        $iss = $payload['iss'] ?? null;
        if ($iss !== 'accounts.google.com' && $iss !== 'https://accounts.google.com') {
            throw new RuntimeException('Invalid iss');
        }
        
        $aud = $payload['aud'] ?? null;
        if ($aud !== $this->clientId) {
            throw new RuntimeException('Invalid aud');
        }
        
        $exp = $payload['exp'] ?? null;
        if (!is_int($exp) && !(is_string($exp) && ctype_digit($exp))) {
            throw new RuntimeException('Invalid exp');
        }
        if (time() >= (int) $exp) {
            throw new RuntimeException('Token expired');
        }
        
        if ($expectedNonce !== null) {
            $nonce = $payload['nonce'] ?? null;
            if (!is_string($nonce) || !hash_equals($expectedNonce, $nonce)) {
                throw new RuntimeException('Invalid nonce');
            }
        }
    }
    
    /** @return array{0:string,1:string,2:string} */
    private function splitJwt(string $jwt): array
    {
        $parts = explode('.', $jwt);
        if (count($parts) !== 3) {
            throw new RuntimeException('Invalid JWT format');
        }
        return [$parts[0], $parts[1], $parts[2]];
    }
    
    private function base64UrlDecode(string $data): string
    {
        $remainder = strlen($data) % 4;
        if ($remainder) {
            $data .= str_repeat('=', 4 - $remainder);
        }
        $data = strtr($data, '-_', '+/');
        $decoded = base64_decode($data, true);
        if ($decoded === false) {
            throw new RuntimeException('Base64 decode failed');
        }
        return $decoded;
    }
    
    /** @return array<string,mixed> */
    private function jsonDecode(string $json): array
    {
        $data = json_decode($json, true);
        if (!is_array($data)) {
            throw new RuntimeException('JSON decode failed');
        }
        return $data;
    }
    
    /**
     * Returns kid => PEM certificate string. Cached.
     *
     * @return array<string,string>
     */
    private function getGoogleCerts(): array
    {
        $cached = $this->readCache();
        if ($cached !== null) return $cached;
        
        $url = 'https://www.googleapis.com/oauth2/v1/certs';

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 5,
            CURLOPT_HTTPHEADER     => ['Accept: application/json'],
            CURLOPT_HEADER         => true,
        ]);
        $response = curl_exec($ch);
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $curlError  = curl_error($ch);
        curl_close($ch);

        if ($response === false || $response === '') {
            throw new RuntimeException('Failed to download Google certs: ' . $curlError);
        }

        $rawHeaders = substr($response, 0, $headerSize);
        $raw        = substr($response, $headerSize);

        $headers = array_filter(
            explode("\r\n", $rawHeaders),
            fn(string $h) => stripos($h, 'Cache-Control:') === 0
        );
        $maxAge = $this->parseMaxAge($headers) ?? 300;
        
        $json = json_decode($raw, true);
        if (!is_array($json)) {
            throw new RuntimeException('Invalid certs JSON');
        }
        
        $certs = [];
        foreach ($json as $kid => $pem) {
            if (is_string($kid) && is_string($pem)) {
                $certs[$kid] = $pem;
            }
        }
        
        if ($certs === []) {
            throw new RuntimeException('No certs found');
        }
        
        $this->writeCache($certs, time() + $maxAge);
        return $certs;
    }
    
    /** @param list<string> $headers */
    private function parseMaxAge(array $headers): ?int
    {
        foreach ($headers as $h) {
            if (stripos($h, 'Cache-Control:') === 0) {
                if (preg_match('/max-age=(\d+)/i', $h, $m)) {
                    return (int) $m[1];
                }
            }
        }
        return null;
    }
    
    /** @return array<string,string>|null */
    private function readCache(): ?array
    {
        if (!is_file($this->cacheFile)) return null;
        
        $raw = @file_get_contents($this->cacheFile);
        if ($raw === false) return null;
        
        $data = json_decode($raw, true);
        if (!is_array($data)) return null;
        
        $expiresAt = $data['expires_at'] ?? 0;
        $certs = $data['certs'] ?? null;
        
        if (!is_int($expiresAt) || !is_array($certs)) return null;
        if (time() >= $expiresAt) return null;
        
        $out = [];
        foreach ($certs as $kid => $pem) {
            if (is_string($kid) && is_string($pem)) $out[$kid] = $pem;
        }
        return $out ?: null;
    }
    
    /** @param array<string,string> $certs */
    private function writeCache(array $certs, int $expiresAt): void
    {
        $dir = dirname($this->cacheFile);
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
        
        $payload = json_encode(['expires_at' => $expiresAt, 'certs' => $certs]);
        if (is_string($payload)) {
            @file_put_contents($this->cacheFile, $payload, LOCK_EX);
        }
    }
}
