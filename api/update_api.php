<?php
error_reporting(0);
ini_set('display_errors', 0);
header('Content-Type: application/json; charset=utf-8');

$action = $_POST['action'] ?? 'check';
$root = realpath(__DIR__ . '/..');
if (!$root || !is_dir($root . '/.git')) { echo json_encode(['status' => 'error', 'message' => 'Esta instalación no contiene metadatos Git. Ejecuta goldhen.sh para reinstalar.']); exit; }

function ejecutar_git_update($root, $command, &$status = null) {
    $output = []; $status = 1;
    @exec('cd ' . escapeshellarg($root) . ' && ' . $command . ' 2>&1', $output, $status);
    return implode("\n", $output);
}

$fetch = ejecutar_git_update($root, 'git fetch --quiet origin main', $fetch_status);
if ($fetch_status !== 0) { echo json_encode(['status' => 'error', 'message' => 'No se pudo consultar GitHub.']); exit; }
$local = trim(ejecutar_git_update($root, 'git rev-parse HEAD', $local_status));
$remote = trim(ejecutar_git_update($root, 'git rev-parse FETCH_HEAD', $remote_status));
if ($local_status || $remote_status) { echo json_encode(['status' => 'error', 'message' => 'No se pudo leer la versión instalada.']); exit; }

if ($action === 'check') { echo json_encode(['status' => 'success', 'update_available' => $local !== $remote, 'current' => substr($local, 0, 7), 'remote' => substr($remote, 0, 7)]); exit; }
if ($action === 'apply') {
    if ($local === $remote) { echo json_encode(['status' => 'success', 'updated' => false]); exit; }
    $result = ejecutar_git_update($root, 'git pull --ff-only origin main', $apply_status);
    if ($apply_status !== 0) { echo json_encode(['status' => 'error', 'message' => 'La actualización no pudo aplicarse; revisa cambios locales con Termux.']); exit; }
    echo json_encode(['status' => 'success', 'updated' => true]); exit;
}
echo json_encode(['status' => 'error', 'message' => 'Acción no válida.']);
?>
