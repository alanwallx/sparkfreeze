<?php
$_SERVER['REQUEST_URI'] = preg_replace('#^/api#', '', $_SERVER['REQUEST_URI'] ?? '') ?: '/';
require __DIR__ . '/../../api/public/index.php';