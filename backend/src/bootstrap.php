<?php

require_once __DIR__ . '/Env.php';

Env::load(__DIR__ . '/../.env');

// ---- Session ---------------------------------------------------------------
$appEnv = getenv('APP_ENV') ?: 'development';
$secure = $appEnv === 'production';

session_set_cookie_params([
  'httponly' => true,
  'secure'   => $secure,
  'samesite' => 'Lax',
  'path'     => '/',
]);

session_start();
