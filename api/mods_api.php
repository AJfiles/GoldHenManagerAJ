<?php
/**
 * ====================================================================
 * GOLDHEN MANAGER V3.0 🚀 - API: MOTOR DE MODS Y BÓVEDA (TERMUX)
 * DEVELOPED By SeBaS - RUTA: api/mods_api.php
 * ====================================================================
 */
error_reporting(0);
ini_set('display_errors', 0);
set_time_limit(0);

header('Content-Type: application/json; charset=utf-8');

$action = $_POST['action'] ?? '';
$host_ip = $_POST['host_ip'] ?? '';
$cusa = strtoupper(trim($_POST['cusa'] ?? ''));
$port = 2121;

if (!$host_ip || !$cusa) {
    echo json_encode(['status' => 'error', 'message' => 'Faltan datos de conexión o CUSA.']);
    exit;
}

// Rutas originales
$vault_base = "/data/GoldHEN/Mods_Vault/$cusa";
$app_tmp_base = "/app_tmp/$cusa/user325801247/games/com.mojang";

// =======================================================
// FUNCIONES AUXILIARES Y MOTOR DE LOTES
// =======================================================
function enviar_lote_socket($host, $port, $comandos) {
    if (empty($comandos)) return true;
    
    $sock = @fsockopen($host, $port, $errno, $errstr, 5);
    if (!$sock) return false;
    
    stream_set_timeout($sock, 5);
    
    while ($line = fgets($sock)) { if (preg_match('/^\d{3}\s/', $line)) break; }
    
    fwrite($sock, "USER anonymous\r\n");
    while ($line = fgets($sock)) { if (preg_match('/^\d{3}\s/', $line)) break; }
    
    foreach ($comandos as $cmd) {
        fwrite($sock, "$cmd\r\n");
        while ($line = fgets($sock)) { if (preg_match('/^\d{3}\s/', $line)) break; }
    }
    
    fwrite($sock, "QUIT\r\n");
    fclose($sock);
    return true;
}

function enviar_comando_raw_ftp($host, $port, $comandos) {
    $ch = curl_init("ftp://$host:$port/");
    if ($ch) {
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_QUOTE, $comandos);
        $res = curl_exec($ch);
        curl_close($ch);
        return $res;
    }
    return false;
}

function borrar_recursivo_ftp($host_ip, $port, $path) {
    $ch = curl_init();
    $url_path = implode('/', array_map('rawurlencode', explode('/', trim($path, '/'))));
    curl_setopt($ch, CURLOPT_URL, "ftp://$host_ip:$port/$url_path/");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "LIST");
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
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
                
                if ($is_dir) borrar_recursivo_ftp($host_ip, $port, $item_path);
                else enviar_comando_raw_ftp($host_ip, $port, ["DELE $item_path"]);
            }
        }
    }
    enviar_comando_raw_ftp($host_ip, $port, ["RMD " . rtrim($path, '/')]);
}

function leer_archivo_ftp($host, $port, $path) {
    $ch = curl_init("ftp://$host:$port" . implode('/', array_map('rawurlencode', explode('/', $path))));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 3);
    $res = curl_exec($ch);
    curl_close($ch);
    return $res;
}

// =======================================================
// 1. ENSAMBLADOR Y SUBIDA
// =======================================================
if ($action === 'upload_chunk') {
    $mod_name = trim($_POST['mod_name'] ?? '');
    $target_folder = trim($_POST['target_folder'] ?? '');
    $filename = trim($_POST['filename'] ?? '');
    $chunk_index = (int)($_POST['chunk_index'] ?? 0);

    if (!$mod_name || !$filename || !isset($_FILES['file_chunk'])) {
        echo json_encode(['status' => 'error', 'message' => 'Paquete incompleto.']); exit;
    }

    $tmp_file = $_FILES['file_chunk']['tmp_name'];
    $folder_encoded = $target_folder ? '/' . implode('/', array_map('rawurlencode', explode('/', $target_folder))) : '';
    $remote_path = "$vault_base/" . rawurlencode($mod_name) . $folder_encoded . "/" . rawurlencode($filename);
    
    $fp = fopen($tmp_file, 'r');
    $ch = curl_init("ftp://$host_ip:$port$remote_path");

    if ($chunk_index > 0) curl_setopt($ch, CURLOPT_FTPAPPEND, true);
    curl_setopt($ch, CURLOPT_UPLOAD, 1);
    curl_setopt($ch, CURLOPT_INFILE, $fp);
    curl_setopt($ch, CURLOPT_INFILESIZE, filesize($tmp_file));
    curl_setopt($ch, CURLOPT_FTP_CREATE_MISSING_DIRS, true); 
    curl_setopt($ch, CURLOPT_TIMEOUT, 60); 

    $res = curl_exec($ch);
    curl_close($ch);
    fclose($fp);

    echo json_encode(['status' => $res ? 'success' : 'error']);
    exit;
}

if ($action === 'guardar_indice') {
    $mod_name = trim($_POST['mod_name'] ?? '');
    if (!$mod_name || !isset($_FILES['file'])) { echo json_encode(['status' => 'error']); exit; }

    $tmp_file = $_FILES['file']['tmp_name'];
    $remote_path = "$vault_base/" . rawurlencode($mod_name) . "/index.json";
    
    $fp = fopen($tmp_file, 'r');
    $ch = curl_init("ftp://$host_ip:$port$remote_path");
    curl_setopt($ch, CURLOPT_UPLOAD, 1);
    curl_setopt($ch, CURLOPT_INFILE, $fp);
    curl_setopt($ch, CURLOPT_INFILESIZE, filesize($tmp_file));
    curl_setopt($ch, CURLOPT_FTP_CREATE_MISSING_DIRS, true);
    curl_exec($ch);
    curl_close($ch);
    fclose($fp);
    
    echo json_encode(['status' => 'success']);
    exit;
}

// =======================================================
// 2. ESCÁNER: BÓVEDA CON LECTURA INSTANTÁNEA
// =======================================================
if ($action === 'listar_boveda') {
    $ch = curl_init("ftp://$host_ip:$port$vault_base/");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "LIST");
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    $res = curl_exec($ch);
    curl_close($ch);

    $mods_encontrados = [];

    if ($res) {
        $lines = explode("\n", trim($res));
        foreach ($lines as $line) {
            if (empty(trim($line))) continue;
            $parts = preg_split('/\s+/', trim($line), 9);
            if (count($parts) >= 9 && substr($parts[0], 0, 1) === 'd') {
                $mod_name = trim($parts[8]);
                if ($mod_name !== '.' && $mod_name !== '..') {
                    $index_json = leer_archivo_ftp($host_ip, $port, "$vault_base/$mod_name/index.json");
                    $index_data = $index_json ? json_decode($index_json, true) : null;

                    $mods_encontrados[] = [
                        'id' => md5($mod_name),
                        'name' => $mod_name,
                        'total_files' => $index_data['total_files'] ?? '?',
                        'file_registry' => $index_data['file_registry'] ?? []
                    ];
                }
            }
        }
    }
    echo json_encode(['status' => 'success', 'data' => $mods_encontrados]);
    exit;
}

// =======================================================
// 3. ELIMINAR MOD
// =======================================================
if ($action === 'eliminar_mod') {
    $mod_name = trim($_POST['mod_name'] ?? '');
    $estado_activo = ($_POST['estado'] ?? '') === 'activar';
    $file_registry = json_decode($_POST['file_registry'] ?? '[]', true);

    if (!$mod_name) { echo json_encode(['status' => 'error']); exit; }
    $vault_mod_dir = "$vault_base/$mod_name";

    // Si está encendido, manda comandos DELE por lotes para fulminar los archivos inyectados al instante
    if ($estado_activo && !empty($file_registry)) {
        $comandos_dele = [];
        foreach ($file_registry as $f) {
            $folder = trim($f['folder']); 
            $nombre = trim($f['name']); 
            $sub = $folder ? "/$folder" : "";
            $comandos_dele[] = "DELE $app_tmp_base$sub/$nombre";
        }
        enviar_lote_socket($host_ip, $port, $comandos_dele);
    }
    
    borrar_recursivo_ftp($host_ip, $port, $vault_mod_dir);
    echo json_encode(['status' => 'success']);
    exit;
}

// =======================================================
// 4. INTERRUPTOR: COPIA REAL (ACTIVAR) Y ELIMINAR (DESACTIVAR)
// =======================================================
if ($action === 'conmutar_mod') {
    $mod_name = trim($_POST['mod_name'] ?? '');
    $estado = $_POST['estado'] ?? '';
    $file_registry = json_decode($_POST['file_registry'] ?? '[]', true);

    if (!$mod_name || empty($file_registry)) { 
        echo json_encode(['status' => 'error', 'message' => 'El índice está corrupto o vacío. Reensambla el mod.']); exit; 
    }

    try {
        if ($estado === 'activar') {
            // 1. Armar primero la estructura de carpetas en Minecraft
            $dirs_to_make = [];
            foreach ($file_registry as $f) {
                $folder = trim($f['folder']); 
                $sub = $folder ? "/$folder" : "";
                $dirs_to_make["$app_tmp_base$sub"] = true;
            }
            
            $mkd_comandos = [];
            $rutas_creadas = [];
            foreach (array_keys($dirs_to_make) as $dir) {
                $parts = explode('/', trim($dir, '/'));
                $path = '';
                foreach ($parts as $part) {
                    if (empty($part)) continue;
                    $path .= '/' . $part;
                    if (!isset($rutas_creadas[$path])) {
                        $mkd_comandos[] = "MKD $path";
                        $rutas_creadas[$path] = true;
                    }
                }
            }
            // Envia las carpetas vacías a la consola
            enviar_lote_socket($host_ip, $port, $mkd_comandos);

            // 2. Realizar la COPIA EXACTA de cada archivo
            foreach ($file_registry as $f) {
                $folder = trim($f['folder']);
                $nombre = trim($f['name']);
                $sub = $folder ? "/$folder" : "";
                
                $path_vault = "$vault_base/$mod_name$sub/$nombre";
                $path_tmp = "$app_tmp_base$sub/$nombre";

                $local_tmp = sys_get_temp_dir() . '/' . uniqid() . '.tmp';
                
                // Extrae el archivo de la bóveda hacia tu celular
                $fp = fopen($local_tmp, 'w+');
                $ch_down = curl_init("ftp://$host_ip:$port" . implode('/', array_map('rawurlencode', explode('/', $path_vault))));
                curl_setopt($ch_down, CURLOPT_FILE, $fp);
                curl_setopt($ch_down, CURLOPT_TIMEOUT, 30);
                curl_exec($ch_down);
                curl_close($ch_down);
                fclose($fp);
                
                // Sube la copia recién descargada a la ruta de Minecraft
                if (file_exists($local_tmp) && filesize($local_tmp) > 0) {
                    $fp_up = fopen($local_tmp, 'r');
                    $ch_up = curl_init("ftp://$host_ip:$port" . implode('/', array_map('rawurlencode', explode('/', $path_tmp))));
                    curl_setopt($ch_up, CURLOPT_UPLOAD, 1);
                    curl_setopt($ch_up, CURLOPT_INFILE, $fp_up);
                    curl_setopt($ch_up, CURLOPT_INFILESIZE, filesize($local_tmp));
                    curl_setopt($ch_up, CURLOPT_TIMEOUT, 30);
                    curl_exec($ch_up);
                    curl_close($ch_up);
                    fclose($fp_up);
                }
                @unlink($local_tmp); // Limpia la caché temporal
            }

        } else {
            // DESACTIVAR: En lugar de mover, manda una orden de destrucción masiva
            // para limpiar la carpeta de Minecraft y dejar intacta la bóveda
            $comandos_dele = [];
            foreach ($file_registry as $f) {
                $folder = trim($f['folder']); 
                $nombre = trim($f['name']); 
                $sub = $folder ? "/$folder" : "";
                $comandos_dele[] = "DELE $app_tmp_base$sub/$nombre";
            }
            enviar_lote_socket($host_ip, $port, $comandos_dele);
        }

        echo json_encode(['status' => 'success']);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}
?>
