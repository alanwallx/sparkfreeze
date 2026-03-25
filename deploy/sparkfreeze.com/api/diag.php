<?php
header('Content-Type: application/json; charset=utf-8');
$base = __DIR__ . '/../../api';
$checks = [
    'env'              => is_file("$base/.env"),
    'public/index.php' => is_file("$base/public/index.php"),
    'src/bootstrap.php'=> is_file("$base/src/bootstrap.php"),
    'src/Db.php'       => is_file("$base/src/Db.php"),
    'src/Env.php'      => is_file("$base/src/Env.php"),
    'src/AuthController.php'  => is_file("$base/src/AuthController.php"),
    'src/SparkController.php' => is_file("$base/src/SparkController.php"),
    'src/SparkRepository.php' => is_file("$base/src/SparkRepository.php"),
    'src/Auth/GoogleIdTokenVerifier.php' => is_file("$base/src/Auth/GoogleIdTokenVerifier.php"),
    'var/cache (dir)'  => is_dir("$base/var/cache"),
];
echo json_encode($checks, JSON_PRETTY_PRINT);
