<?php
/**
 * ====================================================================
 * GOLDHEN MANAGER V3.0 🚀 - API: TRANSFERENCIA Y RPI
 * DEVELOPED By SeBaS - RUTA: api/transferir_api.php
 * ====================================================================
 */
ob_start();
error_reporting(0);
ini_set('display_errors', 0);
set_time_limit(0); 

header('Content-Type: application/json; charset=utf-8');

$action = $_POST['action'] ?? $_GET['action'] ?? '';

// =======================================================
// AUTO-DETECTOR IP: MÉTODO SOCKET UDP
// =======================================================
if ($action === 'get_phone_ip') {
    $ip = '';
    $sock = @socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
    if ($sock) {
        @socket_connect($sock, "8.8.8.8", 53);
        @socket_getsockname($sock, $ip);
        @socket_close($sock);
    }
    
    if (!empty($ip) && $ip !== '127.0.0.1') {
        ob_end_clean(); echo json_encode(['status' => 'success', 'ip' => trim($ip)]);
    } else {
        ob_end_clean(); echo json_encode(['status' => 'error', 'message' => 'No se pudo forzar la lectura.']);
    }
    exit;
}

// =======================================================
// EXPLORADOR DE CARPETAS FTP EN TIEMPO REAL
// =======================================================
if ($action === 'list_ftp_dirs') {
    $host_ip = $_POST['host_ip'] ?? '';
    $path = $_POST['path'] ?? '/';
    $port = 2121;
    
    if(!$host_ip) { ob_end_clean(); echo json_encode(['status'=>'error']); exit; }

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
    $dir = '../user/pkgs_rpi';
    if (!is_dir($dir)) { @mkdir($dir, 0777, true); }
    
    $archivos = glob("$dir/*.pkg");
    $archivos = array_merge($archivos, glob("$dir/*.PKG"));
    $archivos = array_unique($archivos);

    $lista = [];
    if ($archivos) {
        foreach($archivos as $f) {
            $base = basename($f);
            $clean = preg_replace('/[^a-zA-Z0-9.\-_]/', '_', $base);
            
            if ($base !== $clean) {
                rename($f, "$dir/$clean");
                $base = $clean;
            }
            $lista[] = [ 'name' => $base, 'size' => filesize("$dir/$base") ];
        }
    }
    ob_end_clean(); echo json_encode(['status' => 'success', 'data' => $lista]);
    exit;
}

// =======================================================
// MODO 2: ORDEN AL REMOTE PACKAGE INSTALLER (PS4)
// =======================================================
if ($action === 'rpi_install') {
    $ps4_ip = $_POST['host_ip'] ?? '';
    $file_url = $_POST['file_url'] ?? ''; 

    if (!$ps4_ip || !$file_url) {
        ob_end_clean(); echo json_encode(['status' => 'error', 'message' => 'Faltan datos de IP o URL.']); exit;
    }

    $ch = curl_init("http://$ps4_ip:12800/api/install");
    $payload = json_encode([ "type" => "direct", "packages" => [$file_url] ], JSON_UNESCAPED_SLASHES);
    
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    
    $res = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($res !== false && $http_code == 200) {
        $respuesta_json = json_decode($res, true);
        if (isset($respuesta_json['status']) && $respuesta_json['status'] === 'success') {
            ob_end_clean(); echo json_encode(['status' => 'success', 'message' => 'Instalación iniciada.']);
        } else {
            ob_end_clean(); echo json_encode(['status' => 'error', 'message' => 'La PS4 rechazó el enlace de instalación.']);
        }
    } else {
        ob_end_clean(); echo json_encode(['status' => 'error', 'message' => "La PS4 no pudo descargar: $file_url (HTTP $http_code)"]);
    }
    exit;
}

// =======================================================
// 🔥 MODO 3: TRANSFERENCIA FTP REFORZADA
// =======================================================
$host_ip = $_POST['host_ip'] ?? '';
$chunk_index = (int)($_POST['chunk_index'] ?? 0);
$filename = $_POST['filename'] ?? '';
$target_dir = $_POST['target_dir'] ?? '/data/';
$port = 2121;

if (!$host_ip || !isset($_FILES['file_chunk']) || $_FILES['file_chunk']['error'] !== UPLOAD_ERR_OK) {
    ob_end_clean(); echo json_encode(['status' => 'error', 'message' => 'Límite de memoria superado o archivo corrupto.']); exit;
}

// 🔥 TRUCO DEL NÚCLEO: Usar rutas absolutas para saltear bloqueos de seguridad de la PS4
if (strpos($target_dir, '/app_tmp/') === 0 || strpos($target_dir, '/data/') === 0) {
    $target_dir = '/user' . $target_dir;
}

// Normalizar la ruta para que no haya dobles barras
$ruta_completa = rtrim($target_dir, '/') . '/' . ltrim($filename, '/');
$tmp_file = $_FILES['file_chunk']['tmp_name'];
$partes = explode('/', $ruta_completa);
$partes_codificadas = array_map('rawurlencode', $partes);
$url = "ftp://$host_ip:$port" . implode('/', $partes_codificadas);

$fp = fopen($tmp_file, 'r');
$ch = curl_init($url);

if ($chunk_index > 0) { curl_setopt($ch, CURLOPT_FTPAPPEND, true); }
curl_setopt($ch, CURLOPT_UPLOAD, 1);
curl_setopt($ch, CURLOPT_INFILE, $fp);
curl_setopt($ch, CURLOPT_INFILESIZE, filesize($tmp_file));
curl_setopt($ch, CURLOPT_TIMEOUT, 60); 

// 🔥 LA CLAVE 2: Obligar al FTP a crear la estructura de carpetas si la Play se pone en exquisita
curl_setopt($ch, CURLOPT_FTP_CREATE_MISSING_DIRS, true); 

$res = curl_exec($ch);
$err = curl_error($ch);
curl_close($ch);
fclose($fp);

if ($res) {
    ob_end_clean(); echo json_encode(['status' => 'success']);
} else {
    // Te muestro el error exacto para debugear más fácil si vuelve a fallar
    ob_end_clean(); echo json_encode(['status' => 'error', 'message' => "Fallo FTP: $err"]);
}
exit;
?>
