<?php

// ---- Session ---------------------------------------------------------------
$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
session_set_cookie_params([
      'httponly' => true,
      'secure' => $isHttps,   // use env flag in production
      'samesite' => 'Lax',
      'path' => '/',
  ]);
session_start();
