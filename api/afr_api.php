<?php
/**
 * ====================================================================
 * GOLDHEN MANAGER V3.0 🚀 - API: GESTOR AFR V3 + SISTEMA PLUGINS
 * RUTA: api/afr_api.php
 * ====================================================================
 */
ob_start();
error_reporting(0);
ini_set('display_errors', 0);
set_time_limit(0); 

header('Content-Type: application/json; charset=utf-8');

if (empty($_FILES) && empty($_POST) && isset($_SERVER['CONTENT_LENGTH']) && $_SERVER['CONTENT_LENGTH'] > 0) {
    ob_end_clean();
    echo json_encode(['status' => 'error', 'message' => 'El archivo supera el límite de peso de PHP en Termux.']);
    exit;
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$host_ip = $_POST['host_ip'] ?? $_GET['host_ip'] ?? '';
$cusa = strtoupper(trim($_POST['cusa_id'] ?? $_GET['cusa_id'] ?? ''));
$port = 2121;

$cache_afr = __DIR__ . '/../user/cache/backups_afr';
if (!file_exists($cache_afr)) { @mkdir($cache_afr, 0777, true); }
if (!file_exists($cache_afr . '/.nomedia')) { @file_put_contents($cache_afr . '/.nomedia', ''); }

// Recolector de Basura (Limpia temps huérfanos de más de 24 horas por si cancelaste definitivamente)
$archivos_tmp = glob("$cache_afr/*.tmp");
if ($archivos_tmp) {
    $tiempo_actual = time();
    foreach ($archivos_tmp as $tmp) {
        if ($tiempo_actual - filemtime($tmp) > 86400) { @unlink($tmp); }
    }
}

$db_file = "$cache_afr/{$cusa}_mods_v3.json"; 

function obtener_db($archivo) {
    if (file_exists($archivo)) { return json_decode(file_get_contents($archivo), true); }
    return [
        'calibracion_slot' => -1, 
        'categorias' => [['id' => 'cat_skin', 'nombre' => 'SKIN']],
        'grupos' => [],
        'mods' => []
    ];
}
function guardar_db($archivo, $data) { file_put_contents($archivo, json_encode($data, JSON_PRETTY_PRINT)); }

// ==========================================
// FUNCIONES FTP
// ==========================================
function ftp_list_files($ip, $port, $path) {
    $ch = curl_init("ftp://$ip:$port$path/");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "LIST");
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    $res = curl_exec($ch);
    curl_close($ch);
    $files = [];
    if ($res) {
        $lines = explode("\n", trim($res));
        foreach ($lines as $line) {
            if (empty(trim($line))) continue;
            $parts = preg_split('/\s+/', trim($line), 9);
            if (count($parts) >= 9) {
                $perms = $parts[0]; $name = trim($parts[8]); 
                if ($perms[0] !== 'd' && $name !== '.' && $name !== '..') { $files[] = $name; }
            }
        }
    }
    return $files;
}
function ftp_upload($ip, $port, $remote_path, $local_path) {
    $fp = fopen($local_path, 'r');
    $ch = curl_init("ftp://$ip:$port$remote_path");
    curl_setopt($ch, CURLOPT_UPLOAD, 1);
    curl_setopt($ch, CURLOPT_INFILE, $fp);
    curl_setopt($ch, CURLOPT_INFILESIZE, filesize($local_path));
    curl_setopt($ch, CURLOPT_TIMEOUT, 600); 
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5); 
    $res = curl_exec($ch);
    curl_close($ch); fclose($fp); return $res;
}
function ftp_mkdir($ip, $port, $path) {
    $ch = curl_init("ftp://$ip:$port/");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTQUOTE, ["MKD $path"]);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_exec($ch); curl_close($ch);
}
function ftp_rename($ip, $port, $old_path, $new_path) {
    $ch = curl_init("ftp://$ip:$port/");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTQUOTE, ["RNFR $old_path", "RNTO $new_path"]);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    $res = curl_exec($ch); curl_close($ch); return $res;
}
function ftp_delete($ip, $port, $path) {
    $ch = curl_init("ftp://$ip:$port/");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTQUOTE, ["DELE $path"]);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_exec($ch); curl_close($ch);
}
function ftp_download($ip, $port, $remote_file, $local_file) {
    $ch = curl_init("ftp://$ip:$port$remote_file");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    $data = curl_exec($ch);
    curl_close($ch);
    if ($data !== false && strlen($data) > 0) {
        @file_put_contents($local_file, $data);
        return true;
    }
    return false;
}

// ==========================================
// MÓDULOS DE SUBIDA INTELIGENTE (REANUDABLE)
// ==========================================
if ($action === 'list_mods') {
    ob_end_clean(); echo json_encode(['status' => 'success', 'data' => obtener_db($db_file)]); exit;
}

if ($action === 'check_resume') {
    $file_hash = $_POST['file_hash'];
    $temp_path = "$cache_afr/temp_{$file_hash}.tmp";
    $size = file_exists($temp_path) ? filesize($temp_path) : 0;
    ob_end_clean(); echo json_encode(['status' => 'success', 'size' => $size]); exit;
}

if ($action === 'upload_chunk') {
    $file_hash = $_POST['file_hash'];
    $offset = intval($_POST['offset']);
    $chunk_file = $_FILES['chunk']['tmp_name'];
    $temp_path = "$cache_afr/temp_{$file_hash}.tmp";

    $out = fopen($temp_path, "c"); 
    fseek($out, $offset); 
    $in = fopen($chunk_file, "r");
    while ($data = fread($in, 4096)) { fwrite($out, $data); }
    fclose($in); fclose($out);

    ob_end_clean(); echo json_encode(['status' => 'success']); exit;
}

if ($action === 'finalize_mod') {
    $mod_id = $_POST['mod_id'];
    $hashes = json_decode($_POST['file_hashes'], true);

    ftp_mkdir($host_ip, $port, "/data/GoldHEN/AFR");
    ftp_mkdir($host_ip, $port, "/data/GoldHEN/AFR/$cusa");

    $archivos_off = [];
    $fallo_ftp = false;
    foreach ($hashes as $i => $hash) {
        $temp_path = "$cache_afr/temp_{$hash}.tmp";
        if (file_exists($temp_path)) {
            $filename_off = "{$mod_id}_{$i}.off";
            $remote_path = "/data/GoldHEN/AFR/$cusa/$filename_off";
            
            if (ftp_upload($host_ip, $port, $remote_path, $temp_path)) { 
                $archivos_off[] = $filename_off; 
                @unlink($temp_path); // 🔥 SOLO BORRA SI SUBIÓ BIEN A LA PS4
            } else {
                $fallo_ftp = true;
                // No borra el archivo, lo deja para que funcione el REINTENTAR
            }
        }
    }
    
    if($fallo_ftp) {
        ob_end_clean(); echo json_encode(['status' => 'error', 'message' => 'Fallo la conexión FTP con la consola.']); exit;
    }
    
    ob_end_clean(); echo json_encode(['status' => 'success', 'archivos_off' => $archivos_off]); exit;
}

if ($action === 'calibrar_motor') {
    $db = obtener_db($db_file);
    $sandbox_path = "/mnt/sandbox/pfsmnt/$cusa-patch0";
    $archivos_base = ftp_list_files($host_ip, $port, $sandbox_path);
    if (empty($archivos_base)) { ob_end_clean(); echo json_encode(['status' => 'error', 'message' => 'JUEGO_CERRADO']); exit; }

    $max_slot = 0;
    foreach ($archivos_base as $file) {
        if (preg_match('/re_chunk_000\.pak\.patch_(\d+)\.pak/i', $file, $matches)) {
            $num = intval($matches[1]); if ($num > $max_slot) { $max_slot = $num; }
        }
    }
    $db['calibracion_slot'] = $max_slot; guardar_db($db_file, $db);
    ob_end_clean(); echo json_encode(['status' => 'success', 'calibracion_slot' => $max_slot]); exit;
}

if ($action === 'toggle_mod') {
    $mod_id = $_POST['mod_id'];
    $db = obtener_db($db_file);
    if ($db['calibracion_slot'] === -1) { ob_end_clean(); echo json_encode(['status' => 'error', 'message' => 'REQUIERE_CALIBRACION']); exit; }

    $mod_index = null; 
    foreach ($db['mods'] as $index => $mod) {
        if ($mod['id'] === $mod_id) { $mod_index = $index; break; }
    }
    if ($mod_index === null) { ob_end_clean(); echo json_encode(['status' => 'error']); exit; }

    $target_mod = &$db['mods'][$mod_index];
    $is_turning_on = !$target_mod['activo'];
    
    if (!$is_turning_on) {
        foreach ($target_mod['archivos_pak'] as $index => $pak_name) {
            $off_name = $target_mod['archivos_off'][$index];
            ftp_rename($host_ip, $port, "/data/GoldHEN/AFR/$cusa/$pak_name", "/data/GoldHEN/AFR/$cusa/$off_name");
        }
        $target_mod['activo'] = false; $target_mod['archivos_pak'] = [];
    } 
    else {
        $slot_heredado = null;
        if ($target_mod['tipo'] === 'variante' && !empty($target_mod['id_grupo'])) {
            foreach ($db['mods'] as &$other_mod) {
                if ($other_mod['id'] !== $mod_id && $other_mod['id_grupo'] === $target_mod['id_grupo'] && $other_mod['tipo'] === 'variante' && $other_mod['activo']) {
                    foreach ($other_mod['archivos_pak'] as $i => $pak_name) {
                        $off_name = $other_mod['archivos_off'][$i];
                        ftp_rename($host_ip, $port, "/data/GoldHEN/AFR/$cusa/$pak_name", "/data/GoldHEN/AFR/$cusa/$off_name");
                        if (preg_match('/patch_(\d+)\.pak/i', $pak_name, $matches)) { $slot_heredado = intval($matches[1]); }
                    }
                    $other_mod['activo'] = false; $other_mod['archivos_pak'] = [];
                }
            }
        }
        if ($slot_heredado === null) {
            $max_slot = $db['calibracion_slot'];
            $archivos_afr = ftp_list_files($host_ip, $port, "/data/GoldHEN/AFR/$cusa");
            if (is_array($archivos_afr)) {
                foreach ($archivos_afr as $file) {
                    if (preg_match('/patch_(\d+)\.pak/i', $file, $matches)) {
                        $num = intval($matches[1]); if ($num > $max_slot) { $max_slot = $num; }
                    }
                }
            }
            $slot_heredado = $max_slot + 1; 
        }

        $nuevos_paks = [];
        foreach ($target_mod['archivos_off'] as $index => $off_name) {
            $slot_final = $slot_heredado + $index; 
            $slot_str = str_pad($slot_final, 3, "0", STR_PAD_LEFT);
            $pak_name = "re_chunk_000.pak.patch_$slot_str.pak"; 
            ftp_rename($host_ip, $port, "/data/GoldHEN/AFR/$cusa/$off_name", "/data/GoldHEN/AFR/$cusa/$pak_name");
            $nuevos_paks[] = $pak_name;
        }
        $target_mod['activo'] = true; $target_mod['archivos_pak'] = $nuevos_paks;
    }

    guardar_db($db_file, $db);
    ob_end_clean(); echo json_encode(['status' => 'success', 'data' => $db]); exit;
}

if ($action === 'sync_db') {
    $db_data = json_decode($_POST['db_data'], true);
    if ($db_data) { guardar_db($db_file, $db_data); }
    ob_end_clean(); echo json_encode(['status' => 'success']); exit;
}

if ($action === 'delete_mod_files') {
    $archivos = json_decode($_POST['archivos'], true);
    if (is_array($archivos)) {
        foreach ($archivos as $archivo) { ftp_delete($host_ip, $port, "/data/GoldHEN/AFR/$cusa/$archivo"); }
    }
    ob_end_clean(); echo json_encode(['status' => 'success']); exit;
}

// ==========================================
// 6 Y 7: RESTAURACIÓN SISTEMA DE PLUGINS
// ==========================================
if ($action === 'backup_plugins') {
    $timestamp = date('Ymd_His');
    $zip_name = "GoldHEN_Plugins_Originales_$timestamp.zip";
    $zip_path = "$cache_afr/$zip_name";
    
    $zip = new ZipArchive();
    $zip->open($zip_path, ZipArchive::CREATE | ZipArchive::OVERWRITE);
    
    $ini_local = "$cache_afr/plugins.ini";
    if (ftp_download($host_ip, $port, '/data/GoldHEN/plugins.ini', $ini_local)) { $zip->addFile($ini_local, 'plugins.ini'); }

    $plugins = ftp_list_files($host_ip, $port, '/data/GoldHEN/plugins');
    $descargados = 0;
    foreach ($plugins as $plugin) {
        $local_file = "$cache_afr/$plugin";
        if (ftp_download($host_ip, $port, "/data/GoldHEN/plugins/$plugin", $local_file)) {
            $zip->addFile($local_file, "plugins/$plugin");
            $descargados++;
        }
    }
    $zip->close();
    @unlink($ini_local);
    foreach ($plugins as $plugin) { @unlink("$cache_afr/$plugin"); }

    if ($descargados > 0 || file_exists($zip_path)) {
        ob_end_clean(); echo json_encode(['status' => 'success', 'file_url' => "user/cache/backups_afr/$zip_name"]);
    } else {
        @unlink($zip_path); ob_end_clean(); echo json_encode(['status' => 'error', 'message' => 'No se encontraron archivos en plugins.']);
    }
    exit;
}

if ($action === 'install_plugins') {
    $zip_file = $_FILES['plugin_zip']['tmp_name'];
    $zip = new ZipArchive();
    if ($zip->open($zip_file) === TRUE) {
        $extract_path = $cache_afr . '/temp_extract_' . time();
        @mkdir($extract_path, 0777, true);
        $zip->extractTo($extract_path);
        $zip->close();

        if (file_exists("$extract_path/plugins.ini")) { ftp_upload($host_ip, $port, "/data/GoldHEN/plugins.ini", "$extract_path/plugins.ini"); }
        ftp_mkdir($host_ip, $port, "/data/GoldHEN/plugins");
        if (is_dir("$extract_path/plugins")) {
            foreach (glob("$extract_path/plugins/*") as $file) {
                if (is_file($file)) { ftp_upload($host_ip, $port, "/data/GoldHEN/plugins/" . basename($file), $file); }
            }
        }

        if (is_dir("$extract_path/plugins")) { array_map('unlink', glob("$extract_path/plugins/*")); @rmdir("$extract_path/plugins"); }
        @unlink("$extract_path/plugins.ini"); @rmdir($extract_path);

        ob_end_clean(); echo json_encode(['status' => 'success']);
    } else { ob_end_clean(); echo json_encode(['status' => 'error']); }
    exit;
}
?>
