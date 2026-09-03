<?php
error_reporting(0);
ini_set('display_errors', 0);
set_time_limit(120);
header('Content-Type: application/json; charset=utf-8');

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$host = $_POST['host_ip'] ?? $_GET['host_ip'] ?? '';
$port = 2121;
$local_dirs = [__DIR__ . '/../user/plugins', __DIR__ . '/../plugins'];
$remote_dir = '/data/GoldHEN/plugins';
$ini_path = '/data/GoldHEN/plugins.ini';

function responder_plugins($data) { echo json_encode($data); exit; }
function validar_host_plugins($host) { return filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4); }
function nombre_prx($name) { $name = basename($name); return preg_match('/^[A-Za-z0-9._-]+\.prx$/i', $name) ? $name : false; }
function seccion_valida($section) { return $section === 'default' || preg_match('/^CUSA\d{5}$/i', $section); }
function ftp_url_plugins($host, $path) { return 'ftp://' . $host . ':2121' . implode('/', array_map('rawurlencode', explode('/', $path))); }
function ftp_list_plugins($host, $path) {
    $ch = curl_init(ftp_url_plugins($host, rtrim($path, '/') . '/'));
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_CUSTOMREQUEST => 'LIST', CURLOPT_TIMEOUT => 10]);
    $raw = curl_exec($ch); curl_close($ch);
    $out = [];
    foreach (explode("\n", trim((string)$raw)) as $line) {
        $parts = preg_split('/\s+/', trim($line), 9);
        if (count($parts) >= 9 && $parts[8] !== '.' && $parts[8] !== '..') $out[] = ['name' => $parts[8], 'size' => (int)$parts[4], 'is_dir' => $parts[0][0] === 'd'];
    }
    return $out;
}
function ftp_get_plugins($host, $path) {
    $ch = curl_init(ftp_url_plugins($host, $path));
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 20]);
    $data = curl_exec($ch); curl_close($ch); return $data;
}
function ftp_put_plugins($host, $path, $data) {
    $tmp = tempnam(sys_get_temp_dir(), 'ghm_'); file_put_contents($tmp, $data);
    $fp = fopen($tmp, 'r'); $ch = curl_init(ftp_url_plugins($host, $path));
    curl_setopt_array($ch, [CURLOPT_UPLOAD => true, CURLOPT_INFILE => $fp, CURLOPT_INFILESIZE => filesize($tmp), CURLOPT_FTP_CREATE_MISSING_DIRS => true, CURLOPT_TIMEOUT => 60]);
    $ok = curl_exec($ch); curl_close($ch); fclose($fp); @unlink($tmp); return $ok;
}
function parse_ini_plugins($text) {
    $sections = []; $current = null;
    foreach (preg_split('/\R/', (string)$text) as $line) {
        $line = trim($line);
        if ($line === '' || substr($line, 0, 1) === '#' || substr($line, 0, 1) === ';') continue;
        if (preg_match('/^\[([^\]]+)\]$/', $line, $m)) { $current = $m[1]; if (!isset($sections[$current])) $sections[$current] = []; continue; }
        if ($current !== null && preg_match('#^/data/GoldHEN/plugins/[A-Za-z0-9._-]+\.prx$#i', $line)) $sections[$current][] = $line;
    }
    if (!isset($sections['default'])) $sections = ['default' => []] + $sections;
    return $sections;
}
/* Modifica únicamente la sección y la ruta exactas solicitadas. Así se
   preservan comentarios, secciones desconocidas y asignaciones ajenas. */
function actualizar_ini_plugins($text, $section, $plugin_path, $enabled) {
    $lines = preg_split('/\R/', (string)$text);
    $out = []; $inside = false; $found_section = false; $inserted = false;
    foreach ($lines as $line) {
        if (preg_match('/^\s*\[([^\]]+)\]\s*$/', $line, $m)) {
            if ($inside && $enabled && !$inserted) { $out[] = $plugin_path; $inserted = true; }
            $inside = strcasecmp(trim($m[1]), $section) === 0;
            if ($inside) $found_section = true;
            $out[] = $line;
            continue;
        }
        /* Quita solo duplicados de este plugin dentro del destino elegido. */
        if ($inside && trim($line) === $plugin_path) continue;
        $out[] = $line;
    }
    if ($found_section) {
        if ($enabled && !$inserted) $out[] = $plugin_path;
    } elseif ($enabled) {
        if (count($out) && trim(end($out)) !== '') $out[] = '';
        $out[] = '[' . $section . ']';
        $out[] = $plugin_path;
    }
    return rtrim(implode("\n", $out)) . "\n";
}

if ($action === 'list_local') {
    $files = [];
    foreach ($local_dirs as $dir) {
        if (!is_dir($dir)) @mkdir($dir, 0777, true);
        foreach (glob($dir . '/*.prx') ?: [] as $file) $files[basename($file)] = ['name' => basename($file), 'size' => filesize($file), 'origin' => strpos($dir, '/user/') !== false ? 'usuario' : 'proyecto'];
    }
    responder_plugins(['status' => 'success', 'data' => array_values($files)]);
}
if ($action === 'upload_local') {
    if (!isset($_FILES['plugin']) || $_FILES['plugin']['error'] !== UPLOAD_ERR_OK || !($name = nombre_prx($_FILES['plugin']['name']))) {
        responder_plugins(['status' => 'error', 'message' => 'Selecciona un archivo .prx válido.']);
    }
    $target_dir = __DIR__ . '/../user/plugins'; @mkdir($target_dir, 0777, true);
    if (!move_uploaded_file($_FILES['plugin']['tmp_name'], $target_dir . '/' . $name)) responder_plugins(['status' => 'error', 'message' => 'No se pudo guardar el plugin.']);
    responder_plugins(['status' => 'success']);
}
if (!validar_host_plugins($host)) responder_plugins(['status' => 'error', 'message' => 'IP de PS4 inválida.']);
if ($action === 'list_remote') responder_plugins(['status' => 'success', 'data' => array_values(array_filter(ftp_list_plugins($host, $remote_dir), fn($f) => !$f['is_dir'] && nombre_prx($f['name'])))]);
if ($action === 'get_ini') responder_plugins(['status' => 'success', 'sections' => parse_ini_plugins(ftp_get_plugins($host, $ini_path) ?: '')]);
if ($action === 'upload_plugin') {
    $name = nombre_prx($_POST['name'] ?? '');
    if (!$name) responder_plugins(['status' => 'error', 'message' => 'Plugin no válido.']);
    $source = null;
    foreach ($local_dirs as $dir) if (is_file($dir . '/' . $name)) { $source = $dir . '/' . $name; break; }
    if (!$source || !ftp_put_plugins($host, "$remote_dir/$name", file_get_contents($source))) responder_plugins(['status' => 'error', 'message' => 'No se pudo subir el plugin.']);
    responder_plugins(['status' => 'success']);
}
if ($action === 'update_ini') {
    $section = strtoupper(trim($_POST['section'] ?? 'default')); if ($section === 'DEFAULT') $section = 'default';
    $name = nombre_prx($_POST['name'] ?? ''); $enabled = ($_POST['enabled'] ?? '') === 'true';
    if (!seccion_valida($section) || !$name) responder_plugins(['status' => 'error', 'message' => 'Asignación no válida.']);
    $old = ftp_get_plugins($host, $ini_path); if ($old === false) $old = '';
    $backup_dir = __DIR__ . '/../user/backups/plugins'; @mkdir($backup_dir, 0777, true);
    @file_put_contents($backup_dir . '/plugins_' . date('Ymd_His') . '.ini', $old);
    $path = "$remote_dir/$name";
    $new = actualizar_ini_plugins($old, $section, $path, $enabled);
    if (!ftp_put_plugins($host, $ini_path, $new)) responder_plugins(['status' => 'error', 'message' => 'No se pudo escribir plugins.ini.']);
    responder_plugins(['status' => 'success', 'sections' => parse_ini_plugins($new)]);
}
responder_plugins(['status' => 'error', 'message' => 'Acción no válida.']);
?>
