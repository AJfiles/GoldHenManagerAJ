<?php
error_reporting(0); ini_set('display_errors', 0); set_time_limit(60);
header('Content-Type: application/json; charset=utf-8');
function fw_reply($data) { echo json_encode($data); exit; }
$host = $_POST['host_ip'] ?? '';
$fw = $_POST['firmware'] ?? '';
$action = $_POST['action'] ?? '';
if (!filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) fw_reply(['status'=>'error','message'=>'Conecta una PS4 con IP válida.']);
$base = realpath(__DIR__ . '/../payloads fw');
$map = [
  'updates' => ['5.05'=>['enableupdates.bin','disableupdates.bin'],'6.72'=>['enableupdates.bin','disableupdates.bin'],'7.02'=>['enableupdates.bin','disableupdates.bin'],'7.55'=>['enableupdates.bin','disableupdates.bin'],'9.00'=>['enableupdate.bin','disableupdates.bin'],'11.00'=>['enable-updates.bin','disable-updates.bin']],
  'coolers' => ['5.05'=>['fan50.bin','fan55.bin','fan60.bin','fan65.bin','fan70.bin','fan75.bin','fan80.bin'],'9.00'=>['fan50.bin','fan55.bin','fan60.bin','fan65.bin','fan70.bin','fan75.bin','fan80.bin','fanDefault.bin']]
];
if (!isset($map[$action][$fw])) fw_reply(['status'=>'error','message'=>'Firmware no compatible con esta sección.']);
$name = $_POST['name'] ?? '';
if (!in_array($name, $map[$action][$fw], true)) fw_reply(['status'=>'error','message'=>'Payload no disponible para esta versión.']);
$sub = $action === 'coolers' ? '/Fan/' : '/';
$file = realpath($base . '/' . $fw . $sub . $name);
if (!$base || !$file || strpos($file, $base . DIRECTORY_SEPARATOR) !== 0 || !is_file($file)) fw_reply(['status'=>'error','message'=>'No se encontró el payload.']);
$size = filesize($file);
if ($size < 1 || $size > 16*1024*1024) fw_reply(['status'=>'error','message'=>'Tamaño de payload inválido.']);
$sock = @fsockopen($host, 9090, $errno, $err, 5);
if (!$sock) fw_reply(['status'=>'error','message'=>'BinLoader no responde en el puerto 9090.']);
$fp = fopen($file, 'rb'); $sent = 0;
while (!feof($fp)) { $chunk = fread($fp, 65536); if ($chunk === false || $chunk === '') break; $offset=0; while ($offset < strlen($chunk)) { $n=fwrite($sock, substr($chunk,$offset)); if ($n === false || $n === 0) break 2; $offset += $n; } $sent += strlen($chunk); }
fclose($fp); fclose($sock);
fw_reply($sent === $size ? ['status'=>'success','bytes'=>$sent] : ['status'=>'error','message'=>'El envío quedó incompleto.']);
?>
