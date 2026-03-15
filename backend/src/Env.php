<?php
// A small .env loader. PHP reads a .env file itself at startup and populates getenv() / $_ENV. Then your code behaves the same everywhere:
declare(strict_types=1);

final class Env
{
    public static function load(string $path): void
    {
        if (!is_file($path)) {
            return;
        }
        
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        foreach ($lines as $line) {
            $line = trim($line);
            
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }
            
            [$key, $value] = array_pad(explode('=', $line, 2), 2, '');
            
            $key = trim($key);
            $value = trim($value);
            
            if ($key === '') {
                continue;
            }
            
            putenv("$key=$value");
            $_ENV[$key] = $value;
        }
    }
}
