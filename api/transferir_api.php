<?php
/**
 * ====================================================================
 * GOLDHEN MANAGER V3.0 🚀 - API: TRANSFERENCIA Y RPI
 * Mantenido por AJ · Basado en proyecto original de SeBaS · RUTA: api/transferir_api.php
 * ====================================================================
 */
ob_start();
error_reporting(0);
ini_set('display_errors', 0);
set_time_limit(0); 

header('Content-Type: application/json; charset=utf-8');

$action = $_POST['action'] ?? $_GET['action'] ?? '';

// =======================================================
// AUTO-DETECTOR IP: MÉTODO SOCKET UDP + FALLBACK
// =======================================================
if ($action === 'get_phone_ip') {
    $ip = '';
    $target_ip = $_POST['host_ip'] ?? '';
    if (filter_var($target_ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) && !in_array($target_ip, ['0.0.0.0', '127.0.0.1'], true)) {
        $sock = @socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
        if ($sock && @socket_connect($sock, $target_ip, 9)) @socket_getsockname($sock, $ip);
        if ($sock) @socket_close($sock);
    }
    if (empty($ip) || $ip === '127.0.0.1' || $ip === '0.0.0.0') {
        $ip = '';
        $sock = @socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
        if ($sock) {
            @socket_connect($sock, '8.8.8.8', 53);
            @socket_getsockname($sock, $ip);
            @socket_close($sock);
        }
    }
    
    // Fallback: usar la IP del servidor (si está disponible)
    if (empty($ip) || $ip === '127.0.0.1' || $ip === '0.0.0.0') {
        $fallback = $_SERVER['SERVER_ADDR'] ?? '';
        if (filter_var($fallback, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) && $fallback !== '127.0.0.1' && $fallback !== '0.0.0.0') $ip = $fallback;
    }

    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) && $ip !== '127.0.0.1' && $ip !== '0.0.0.0') {
        ob_end_clean(); echo json_encode(['status' => 'success', 'ip' => trim($ip)]);
    } else {
        ob_end_clean(); echo json_encode(['status' => 'error', 'message' => 'No se pudo obtener la IP.']);
    }
    exit;
}

// Comprueba el puerto RPI sin enviar ninguna orden de instalación.
if ($action === 'check_rpi_port') {
    $host = $_POST['host_ip'] ?? '';
    $port = (int)($_POST['rpi_port'] ?? 12800);
    if (!filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) || !in_array($port, [12800, 12801], true)) {
        ob_end_clean(); echo json_encode(['status' => 'error', 'open' => false, 'message' => 'IP o puerto RPI no válido.']); exit;
    }
    $errno = 0; $error = '';
    $socket = @stream_socket_client("tcp://$host:$port", $errno, $error, 2, STREAM_CLIENT_CONNECT);
    if ($socket) {
        fclose($socket);
        ob_end_clean(); echo json_encode(['status' => 'success', 'open' => true, 'message' => 'Puerto RPI disponible.']); exit;
    }
    ob_end_clean(); echo json_encode(['status' => 'success', 'open' => false, 'message' => 'No responde el puerto ' . $port . '. Abre Package Installer y mantenlo en primer plano; prueba 12800 o 12801.']); exit;
}

// =======================================================
// EXPLORADOR DE CARPETAS FTP EN TIEMPO REAL
// =======================================================
if ($action === 'list_ftp_dirs') {
    $host_ip = $_POST['host_ip'] ?? '';
    $path = $_POST['path'] ?? '/';
    $port = 2121;
    
    if(!$host_ip) { ob_end_clean(); echo json_encode(['status'=>'error', 'message'=>'Falta IP']); exit; }

    $ch = curl_init();
    $partes = explode('/', trim($path, '/'));
    $partes_codificadas = array_map('rawurlencode', $partes);
    $url_path = empty($partes[0]) ? '' : implode('/', $partes_codificadas);
    
    curl_setopt($ch, CURLOPT_URL, "ftp://$host_ip:$port/$url_path/");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "LIST");
    curl_setopt($ch, CURLOPT_TIMEOUT, 6);
    $res = curl_exec($ch);
    curl_close($ch);

    $dirs = [];
    if ($res) {
        $lines = explode("\n", trim($res));
        foreach ($lines as $line) {
            if (empty(trim($line))) continue;
            $parts = preg_split('/\s+/', trim($line), 9);
            if (count($parts) >= 9) {
                $name = trim($parts[8]);
                $is_dir = (substr($parts[0], 0, 1) === 'd');
                if ($name !== '.' && $name !== '..' && $is_dir) {
                    $dirs[] = $name;
                }
            }
        }
        sort($dirs);
    }
    ob_end_clean();
    echo json_encode(['status' => 'success', 'path' => $path, 'dirs' => $dirs]);
    exit;
}

// =======================================================
// DETECTOR DE COLISIONES EN FTP
// =======================================================
if ($action === 'check_exists') {
    $host_ip = $_POST['host_ip'] ?? '';
    $file_path = $_POST['file_path'] ?? '';
    $port = 2121;

    // Convertir a ruta absoluta física si es necesario para la comprobación
    if (strpos($file_path, '/app_tmp/') === 0 || strpos($file_path, '/data/') === 0) {
        $file_path = '/user' . $file_path;
    }

    $partes = explode('/', $file_path);
    $partes_codificadas = array_map('rawurlencode', $partes);
    $url = "ftp://$host_ip:$port" . implode('/', $partes_codificadas);
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    ob_end_clean(); 
    echo json_encode(['status' => 'success', 'exists' => ($http_code == 213 || $http_code == 200)]);
    exit;
}

// =======================================================
// MODO 1: ESCANEAR JUEGOS LOCALES Y AUTO-RENOMBRAR (RPI)
// =======================================================
if ($action === 'scan_local_pkgs') {
    $remote_addr = $_SERVER['REMOTE_ADDR'] ?? '';
    if (!in_array($remote_addr, ['127.0.0.1', '::1'], true)) {
        ob_end_clean(); echo json_encode(['status' => 'error', 'message' => 'Escanea archivos desde el dispositivo que ejecuta el Manager.']); exit;
    }
    $source = $_POST['source'] ?? 'pkgs_rpi';
    $custom_path = trim($_POST['custom_path'] ?? '');
    $rpi_dir = __DIR__ . '/../user/pkgs_rpi';
    if (!is_dir($rpi_dir)) @mkdir($rpi_dir, 0777, true);
    $downloads = [];
    $home = getenv('HOME') ?: '';
    $profile = getenv('USERPROFILE') ?: '';
    foreach ([
        $home !== '' ? $home . '/storage/downloads' : '',
        '/storage/emulated/0/Download',
        '/sdcard/Download',
        $profile !== '' ? $profile . DIRECTORY_SEPARATOR . 'Downloads' : '',
    ] as $candidate) {
        if ($candidate !== '' && is_dir($candidate) && is_readable($candidate)) $downloads[] = $candidate;
    }

    $roots = [];
    if ($source === 'pkgs_rpi' || $source === 'both') $roots[] = [$rpi_dir, 'Carpeta RPI'];
    if ($source === 'downloads' || $source === 'both') {
        foreach ($downloads as $dir) $roots[] = [$dir, 'Descargas'];
    }
    if ($source === 'custom') {
        $custom_real = realpath($custom_path);
        if ($custom_real === false || !is_dir($custom_real) || !is_readable($custom_real)) {
            ob_end_clean(); echo json_encode(['status' => 'error', 'message' => 'La ruta personalizada no existe o PHP no puede leerla.']); exit;
        }
        $roots[] = [$custom_real, 'Carpeta personalizada'];
    }
    if (!$roots) {
        ob_end_clean(); echo json_encode(['status' => 'error', 'message' => 'Elige una ubicación válida para buscar los PKG.']); exit;
    }

    $cache_dir = __DIR__ . '/../user/cache';
    if (!is_dir($cache_dir)) @mkdir($cache_dir, 0777, true);
    $registry_file = $cache_dir . '/rpi_packages_registry.json';
    $registry = ['files' => []];
    if (is_file($registry_file)) {
        $old_registry = json_decode((string)@file_get_contents($registry_file), true);
        if (is_array($old_registry['files'] ?? null)) $registry['files'] = $old_registry['files'];
    }
    $registry['files'] = array_filter($registry['files'], static function ($entry) { return is_array($entry) && (int)($entry['created'] ?? 0) > time() - 21600; });

    $lista = [];
    $seen = [];
    foreach ($roots as [$root, $label]) {
        $root_real = realpath($root);
        if ($root_real === false) continue;
        $names = @scandir($root_real);
        if (!is_array($names)) continue;
        foreach ($names as $entry_name) {
            if ($entry_name === '.' || $entry_name === '..' || !preg_match('/\.pkg$/i', $entry_name)) continue;
            $path = realpath($root_real . DIRECTORY_SEPARATOR . $entry_name);
            if ($path === false || dirname($path) !== $root_real || !is_file($path) || !is_readable($path)) continue;
            $size = @filesize($path); $mtime = @filemtime($path);
            if ($size === false || $mtime === false) continue;
            $key = strtolower($path);
            if (isset($seen[$key])) continue;
            $seen[$key] = true;
            try { $id = bin2hex(random_bytes(16)); } catch (Throwable $e) { $id = hash('sha256', $path . '|' . $size . '|' . $mtime . '|' . uniqid('', true)); }
            $registry['files'][$id] = ['path' => $path, 'name' => basename($path), 'size' => (int)$size, 'mtime' => (int)$mtime, 'created' => time()];
            $lista[] = ['id' => $id, 'name' => basename($path), 'size' => (int)$size, 'source_label' => $label];
        }
    }
    usort($lista, static function ($a, $b) { return strcasecmp($a['name'], $b['name']); });
    if (@file_put_contents($registry_file, json_encode($registry, JSON_UNESCAPED_SLASHES), LOCK_EX) === false) {
        ob_end_clean(); echo json_encode(['status' => 'error', 'message' => 'No se pudo guardar el índice temporal de PKG.']); exit;
    }
    ob_end_clean(); echo json_encode(['status' => 'success', 'data' => $lista]);
    exit;
}
// MODO 2: ORDEN AL REMOTE PACKAGE INSTALLER (PS4)
// =======================================================
if ($action === 'rpi_install') {
    $ps4_ip = $_POST['host_ip'] ?? '';
    $phone_ip = $_POST['phone_ip'] ?? '';
    $file_id = (string)($_POST['file_id'] ?? '');
    $filename = (string)($_POST['filename'] ?? '');
    $server_port = (int)($_POST['server_port'] ?? 8080);
    $rpi_port = (int)($_POST['rpi_port'] ?? 12800);

    if (!filter_var($ps4_ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) || !filter_var($phone_ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) || !preg_match('/^[a-f0-9]{32,64}$/i', $file_id) || $server_port < 1 || $server_port > 65535 || !in_array($rpi_port, [12800, 12801], true)) {
        ob_end_clean(); echo json_encode(['status' => 'error', 'message' => 'IP, archivo o puerto no válido.']); exit;
    }
    $registry_file = __DIR__ . '/../user/cache/rpi_packages_registry.json';
    $registry = is_file($registry_file) ? json_decode((string)@file_get_contents($registry_file), true) : null;
    $entry = $registry['files'][$file_id] ?? null;
    $entry_path = is_array($entry) ? realpath($entry['path'] ?? '') : false;
    if (!is_array($entry) || $entry_path === false || !is_file($entry_path) || !is_readable($entry_path) || basename($entry_path) !== (string)($entry['name'] ?? '') || $filename !== (string)($entry['name'] ?? '') || !preg_match('/\.pkg$/i', $entry_path) || (int)@filesize($entry_path) !== (int)($entry['size'] ?? -1) || (int)@filemtime($entry_path) !== (int)($entry['mtime'] ?? -1) || (int)($entry['created'] ?? 0) < time() - 21600) {
        ob_end_clean(); echo json_encode(['status' => 'error', 'message' => 'El PKG ya no está disponible en esa ruta. Vuelve a escanear.']); exit;
    }
    $file_url = "http://$phone_ip:$server_port/api/rpi_file.php?id=" . rawurlencode($file_id);

    $ch = curl_init("http://$ps4_ip:$rpi_port/api/install");
    $payload = json_encode([ "type" => "direct", "packages" => [$file_url] ], JSON_UNESCAPED_SLASHES);
    
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_TIMEOUT, 25);
    
    $res = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);

    if ($res !== false && $http_code == 200) {
        $respuesta_json = json_decode($res, true);
        if (is_array($respuesta_json) && isset($respuesta_json['status']) && $respuesta_json['status'] === 'success') {
            ob_end_clean(); echo json_encode(['status' => 'success', 'message' => 'Instalación iniciada.']);
        } elseif ($res === '' || $respuesta_json === null) {
            ob_end_clean(); echo json_encode(['status' => 'success', 'message' => 'RPI aceptó la solicitud (HTTP 200). Comprueba el progreso en Package Installer.']);
        } else {
            $detail = trim(substr((string)($respuesta_json['message'] ?? $res), 0, 240));
            ob_end_clean(); echo json_encode(['status' => 'error', 'message' => 'La PS4 rechazó la solicitud RPI.' . ($detail !== '' ? ' ' . $detail : '')]);
        }
    } else {
        if ($res === false || $http_code === 0) {
            $message = "No se pudo conectar con el servicio RPI en $ps4_ip:$rpi_port. El radar solo confirma FTP; abre Package Installer en primer plano y verifica 12800/12801.";
            if ($curl_error !== '') $message .= " Detalle: $curl_error";
        } else {
            $detail = trim(substr((string)$res, 0, 240));
            $message = "El servicio RPI respondió HTTP $http_code." . ($detail !== '' ? " Respuesta: $detail" : ' Comprueba que el instalador esté abierto y acepte el enlace.');
        }
        ob_end_clean(); echo json_encode(['status' => 'error', 'message' => $message]);
    }
    exit;
}

// =======================================================
// 🔥 MODO 3: TRANSFERENCIA FTP REFORZADA (CON MEJORAS)
// =======================================================
$host_ip = $_POST['host_ip'] ?? '';
$chunk_index = (int)($_POST['chunk_index'] ?? 0);
$filename = $_POST['filename'] ?? '';
$target_dir = $_POST['target_dir'] ?? '/data/';
$port = 2121;

if (!$host_ip || !isset($_FILES['file_chunk']) || $_FILES['file_chunk']['error'] !== UPLOAD_ERR_OK) {
    $error_msg = 'Límite de memoria superado o archivo corrupto.';
    if (isset($_FILES['file_chunk']['error'])) {
        switch ($_FILES['file_chunk']['error']) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                $error_msg = 'El archivo excede el tamaño máximo permitido.';
                break;
            case UPLOAD_ERR_PARTIAL:
                $error_msg = 'El archivo se subió parcialmente.';
                break;
            case UPLOAD_ERR_NO_FILE:
                $error_msg = 'No se recibió ningún archivo.';
                break;
            case UPLOAD_ERR_NO_TMP_DIR:
                $error_msg = 'Falta la carpeta temporal.';
                break;
            case UPLOAD_ERR_CANT_WRITE:
                $error_msg = 'Error al escribir el archivo en disco.';
                break;
            case UPLOAD_ERR_EXTENSION:
                $error_msg = 'Una extensión de PHP detuvo la subida.';
                break;
        }
    }
    ob_end_clean(); echo json_encode(['status' => 'error', 'message' => $error_msg]); 
    exit;
}

// 🔥 TRUCO DEL NÚCLEO: Usar rutas absolutas para saltear bloqueos de seguridad de la PS4
if (strpos($target_dir, '/app_tmp/') === 0 || strpos($target_dir, '/data/') === 0) {
    $target_dir = '/user' . $target_dir;
}

// Normalizar la ruta para que no haya dobles barras
$ruta_completa = rtrim($target_dir, '/') . '/' . ltrim($filename, '/');
$tmp_file = $_FILES['file_chunk']['tmp_name'];

// Intentar crear el directorio destino si no existe (solo para el primer chunk)
if ($chunk_index === 0) {
    $dir_path = dirname($ruta_completa);
    $dir_parts = explode('/', trim($dir_path, '/'));
    $current_path = '';
    foreach ($dir_parts as $part) {
        $current_path .= '/' . $part;
        // Intentar crear el directorio (ignorar errores si ya existe)
        $ch = curl_init("ftp://$host_ip:$port$current_path/");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_QUOTE, ["MKD $current_path"]);
        curl_exec($ch);
        curl_close($ch);
    }
}

$partes = explode('/', $ruta_completa);
$partes_codificadas = array_map('rawurlencode', $partes);
$url = "ftp://$host_ip:$port" . implode('/', $partes_codificadas);

$fp = fopen($tmp_file, 'r');
$ch = curl_init($url);

if ($chunk_index > 0) { curl_setopt($ch, CURLOPT_FTPAPPEND, true); }
curl_setopt($ch, CURLOPT_UPLOAD, 1);
curl_setopt($ch, CURLOPT_INFILE, $fp);
curl_setopt($ch, CURLOPT_INFILESIZE, filesize($tmp_file));
curl_setopt($ch, CURLOPT_TIMEOUT, 120); // Aumentado a 120 segundos

// 🔥 LA CLAVE 2: Obligar al FTP a crear la estructura de carpetas si la Play se pone en exquisita
curl_setopt($ch, CURLOPT_FTP_CREATE_MISSING_DIRS, true); 

$res = curl_exec($ch);
$err = curl_error($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
fclose($fp);

if ($res) {
    ob_end_clean(); echo json_encode(['status' => 'success']);
} else {
    // Mensaje de error más descriptivo
    $mensaje = "Fallo FTP: $err";
    if (strpos($err, 'Could not resolve host') !== false) {
        $mensaje = "No se pudo resolver la IP de la PS4. Verifica la conexión.";
    } elseif (strpos($err, 'Connection refused') !== false) {
        $mensaje = "La PS4 rechazó la conexión FTP. ¿Está encendida y con FTP activo?";
    } elseif (strpos($err, 'Permission denied') !== false) {
        $mensaje = "Permisos insuficientes en la carpeta de destino.";
    }
    ob_end_clean(); echo json_encode(['status' => 'error', 'message' => $mensaje]);
}
exit;
?>
