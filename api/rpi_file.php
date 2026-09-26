<?php
// Entrega únicamente PKG de user/pkgs_rpi al Remote Package Installer.
error_reporting(0);
ini_set('display_errors', 0);
$name = (string)($_GET['name'] ?? '');
if (!preg_match('/^[A-Za-z0-9._-]+\.pkg$/i', $name)) {
    http_response_code(400);
    exit;
}
$directory = realpath(__DIR__ . '/../user/pkgs_rpi');
$file = $directory === false ? false : realpath($directory . DIRECTORY_SEPARATOR . $name);
if ($directory === false || $file === false || dirname($file) !== $directory || !is_file($file) || !is_readable($file)) {
    http_response_code(404);
    exit;
}
header('Content-Type: application/octet-stream');
header('Content-Length: ' . filesize($file));
header('Content-Disposition: attachment; filename="' . $name . '"');
header('X-Content-Type-Options: nosniff');
readfile($file);
