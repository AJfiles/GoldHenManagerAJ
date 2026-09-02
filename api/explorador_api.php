<?php
/**
 * ====================================================================
 * GOLDHEN MANAGER V3.0 🚀 - API: MOTOR DEL EXPLORADOR Y DESCOMPRESIÓN
 * DEVELOPED By SeBaS - RUTA: api/explorador_api.php
 * ====================================================================
 */
error_reporting(0);
ini_set('display_errors', 0);
set_time_limit(0);

header('Content-Type: application/json; charset=utf-8');

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$host_ip = $_POST['host_ip'] ?? $_GET['host_ip'] ?? '';
$port = 2121;

if (!$host_ip && $action !== 'descargar_directo') {
    echo json_encode(['status' => 'error', 'message' => 'Falta la IP de la consola.']);
    exit;
}

// =======================================================
// FUNCIÓN AUXILIAR: COMANDOS FTP PUROS
// =======================================================
function enviar_comando_raw_ftp($host, $port, $comandos) {
    $ch = curl_init("ftp://$host:$port/");
    $res = false;
    if ($ch) {
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_QUOTE, $comandos);
        $res = curl_exec($ch);
        curl_close($ch);
    }
    return $res;
}

// =======================================================
// MOTOR DE BORRADO RECURSIVO (DEEP DELETE)
// =======================================================
function borrar_recursivo_ftp($host_ip, $port, $path) {
    $ch = curl_init();
    $url_path = implode('/', array_map('rawurlencode', explode('/', trim($path, '/'))));
    curl_setopt($ch, CURLOPT_URL, "ftp://$host_ip:$port/$url_path/");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "LIST");
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    $res = curl_exec($ch);
    curl_close($ch);

    if ($res) {
        $lines = explode("\n", trim($res));
        foreach ($lines as $line) {
            if (empty(trim($line))) continue;
            $parts = preg_split('/\s+/', trim($line), 9);
            if (count($parts) >= 9) {
                $name = trim($parts[8]);
                if ($name === '.' || $name === '..') continue;
                
                $is_dir = (substr($parts[0], 0, 1) === 'd');
                $item_path = rtrim($path, '/') . '/' . $name;
                
                if ($is_dir) {
                    borrar_recursivo_ftp($host_ip, $port, $item_path);
                } else {
                    enviar_comando_raw_ftp($host_ip, $port, ["DELE $item_path"]);
                }
            }
        }
    }
    enviar_comando_raw_ftp($host_ip, $port, ["RMD " . rtrim($path, '/')]);
}

// =======================================================
// ACCIÓN 1: LISTAR DIRECTORIO
// =======================================================
if ($action === 'listar_directorio') {
    $path = $_POST['path'] ?? '/';
    if (substr($path, -1) !== '/') $path .= '/';

    $url_path = trim($path, '/');
    $partes = $url_path === '' ? [] : explode('/', $url_path);
    $partes_cod = array_map('rawurlencode', $partes);
    $final_path = implode('/', $partes_cod);
    
    $ftp_url = "ftp://$host_ip:$port/" . ($final_path !== '' ? $final_path . '/' : '');

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $ftp_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "LIST");
    curl_setopt($ch, CURLOPT_TIMEOUT, 8);
    $res = curl_exec($ch);
    curl_close($ch);

    $carpetas = [];
    $archivos = [];

    if ($res) {
        $lines = explode("\n", trim($res));
        foreach ($lines as $line) {
            if (empty(trim($line))) continue;
            $parts = preg_split('/\s+/', trim($line), 9);
            if (count($parts) >= 9) {
                $name = trim($parts[8]);
                if ($name === '.' || $name === '..') continue;

                $is_dir = (substr($parts[0], 0, 1) === 'd');
                $size = (int)$parts[4];
                $date = $parts[5] . ' ' . $parts[6] . ' ' . $parts[7];

                $item = ['name' => $name, 'size' => $size, 'date' => $date];

                if ($is_dir) $carpetas[] = $item;
                else $archivos[] = $item;
            }
        }
        usort($carpetas, function($a, $b){ return strcasecmp($a['name'], $b['name']); });
        usort($archivos, function($a, $b){ return strcasecmp($a['name'], $b['name']); });
    }

    echo json_encode([
        'status' => 'success',
        'current_path' => $path,
        'data' => ['carpetas' => $carpetas, 'archivos' => $archivos]
    ]);
    exit;
}

// =======================================================
// ACCIÓN 2: SUBIDA DE ARQUITECTURAS
// =======================================================
if ($action === 'upload_explorer_chunk') {
    $filename = $_POST['filename'] ?? '';
    $target_dir = $_POST['target_dir'] ?? '/';
    $chunk_index = (int)($_POST['chunk_index'] ?? 0);
    $is_folder_zip = ($_POST['is_folder_zip'] ?? '') === 'true';

    if (!$filename || !isset($_FILES['file_chunk'])) {
        echo json_encode(['status' => 'error', 'message' => 'Lote incompleto.']);
        exit;
    }

    $tmp_file = $_FILES['file_chunk']['tmp_name'];
    $remote_path = rtrim($target_dir, '/') . '/' . rawurlencode($filename);
    
    $fp = fopen($tmp_file, 'r');
    $ch = curl_init("ftp://$host_ip:$port$remote_path");

    if ($chunk_index > 0) { curl_setopt($ch, CURLOPT_FTPAPPEND, true); }
    curl_setopt($ch, CURLOPT_UPLOAD, 1);
    curl_setopt($ch, CURLOPT_INFILE, $fp);
    curl_setopt($ch, CURLOPT_INFILESIZE, filesize($tmp_file));
    curl_setopt($ch, CURLOPT_FTP_CREATE_MISSING_DIRS, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);

    $res = curl_exec($ch);
    curl_close($ch);
    fclose($fp);

    if (!$res) {
        echo json_encode(['status' => 'error', 'message' => 'Error inyección fragmento.']);
        exit;
    }

    if ($is_folder_zip && $_FILES['file_chunk']['size'] < (1.5 * 1024 * 1024)) {
        $full_zip_path = rtrim($target_dir, '/') . '/' . $filename;
        enviar_comando_raw_ftp($host_ip, $port, [
            "SITE UNZIP " . $full_zip_path,
            "DELE " . $full_zip_path
        ]);
    }

    echo json_encode(['status' => 'success']);
    exit;
}

// =======================================================
// ACCIONES SECUNDARIAS
// =======================================================
if ($action === 'crear_carpeta') {
    $target = $_POST['target'] ?? '';
    $res = enviar_comando_raw_ftp($host_ip, $port, ["MKD $target"]);
    echo json_encode(['status' => $res ? 'success' : 'error']);
    exit;
}

if ($action === 'crear_archivo') {
    $target = $_POST['target'] ?? '';
    $ch = curl_init("ftp://$host_ip:$port$target");
    curl_setopt($ch, CURLOPT_UPLOAD, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, "");
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    $res = curl_exec($ch);
    curl_close($ch);
    echo json_encode(['status' => $res ? 'success' : 'error']);
    exit;
}

if ($action === 'eliminar') {
    $target = $_POST['target'] ?? '';
    $is_dir = ($_POST['is_dir'] ?? '') === 'true';
    if ($is_dir) {
        borrar_recursivo_ftp($host_ip, $port, $target);
        echo json_encode(['status' => 'success']);
    } else {
        $res = enviar_comando_raw_ftp($host_ip, $port, ["DELE $target"]);
        echo json_encode(['status' => $res !== false ? 'success' : 'error']);
    }
    exit;
}

if ($action === 'renombrar_mover') {
    $old = $_POST['old_path'] ?? '';
    $new = $_POST['new_path'] ?? '';
    $res = enviar_comando_raw_ftp($host_ip, $port, ["RNFR $old", "RNTO $new"]);
    echo json_encode(['status' => $res ? 'success' : 'error']);
    exit;
}

// 🔥 FIX: COPIA VIA DESCARGA/SUBIDA PARA PS4 (Evita el SITE CP que no existe)
if ($action === 'copiar') {
    $source = $_POST['source_path'] ?? '';
    $dest = $_POST['dest_path'] ?? '';
    
    try {
        $local_tmp = sys_get_temp_dir() . '/' . uniqid() . '.tmp';
        
        // 1. Descargamos el archivo a Termux
        $fp = fopen($local_tmp, 'w+');
        $source_url = "ftp://$host_ip:$port" . implode('/', array_map('rawurlencode', explode('/', $source)));
        $ch = curl_init($source_url);
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_TIMEOUT, 300);
        $res = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);
        fclose($fp);

        if (!$res || !file_exists($local_tmp) || filesize($local_tmp) === 0) {
            @unlink($local_tmp);
            throw new Exception("No se pudo leer el archivo original: $err");
        }

        // 2. Lo inyectamos en la nueva ruta
        $fp = fopen($local_tmp, 'r');
        $dest_url = "ftp://$host_ip:$port" . implode('/', array_map('rawurlencode', explode('/', $dest)));
        $ch = curl_init($dest_url);
        curl_setopt($ch, CURLOPT_UPLOAD, 1);
        curl_setopt($ch, CURLOPT_INFILE, $fp);
        curl_setopt($ch, CURLOPT_INFILESIZE, filesize($local_tmp));
        curl_setopt($ch, CURLOPT_FTP_CREATE_MISSING_DIRS, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 300);
        $res_up = curl_exec($ch);
        $err_up = curl_error($ch);
        curl_close($ch);
        fclose($fp);
        
        @unlink($local_tmp); // Limpiamos

        if (!$res_up) throw new Exception("Error al escribir el archivo destino: $err_up");

        echo json_encode(['status' => 'success']);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}

if ($action === 'descargar_directo') {
    $path = $_GET['path'] ?? '';
    if (!$path) exit;
    $filename = basename($path);
    header("Content-Disposition: attachment; filename=\"" . $filename . "\"");
    header("Content-Type: application/octet-stream");
    $url = "ftp://$host_ip:$port" . implode('/', array_map('rawurlencode', explode('/', $path)));
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_TIMEOUT, 300);
    curl_exec($ch);
    curl_close($ch);
    exit;
}
?>
