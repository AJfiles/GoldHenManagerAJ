<?php
/**
 * ====================================================================
 * GOLDHEN MANAGER V3.0 🚀 - API: VERIFICADOR DE CONEXIÓN EN VIVO
 * DEVELOPED By SeBaS - RUTA: api/scanner.php
 * ====================================================================
 */
error_reporting(0);
header('Content-Type: application/json; charset=utf-8');

$ip = $_GET['ip'] ?? '';
$port = $_GET['port'] ?? 2121; 

if (empty($ip) || strpos($ip, '127.') === 0 || $ip === '0.0.0.0') { 
    echo json_encode(['status' => 'error']); 
    exit; 
}

// Timeout de 1 segundo (Balance perfecto para red local)
$timeout = 1.0; 
$fp = @fsockopen($ip, $port, $errno, $errstr, $timeout);

if ($fp) {
    // 🔥 LA CLAVE: No le pedimos a GoldHEN que nos mande el texto de bienvenida (fgets).
    // Si fsockopen logró abrir el puerto, significa que la PS4 está encendida y conectada.
    fclose($fp);
    echo json_encode(['status' => 'success', 'ip' => $ip]); 
} else { 
    echo json_encode(['status' => 'error']); 
}
?>
