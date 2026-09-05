<?php
/* Catálogo privado: únicamente paquetes cuya distribución esté autorizada. */
error_reporting(0); header('Content-Type: application/json; charset=utf-8');
const STORE_CATALOG = __DIR__ . '/../store/data/catalogo.json';
function store_out($data) { echo json_encode($data, JSON_UNESCAPED_UNICODE); exit; }
function store_catalog() { $raw = @file_get_contents(STORE_CATALOG); $data = json_decode((string)$raw, true); return is_array($data) ? $data : []; }
function store_ip($ip) { return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4); }
function store_url($url) { if (!is_string($url) || !preg_match('#^https?://#i', $url)) return false; $p=parse_url($url); if (!$p || empty($p['host']) || preg_match('/(^|\.)(localhost|local)$/i',$p['host'])) return false; if (filter_var($p['host'], FILTER_VALIDATE_IP) && !filter_var($p['host'], FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) return false; return preg_match('/\.pkg(?:$|[?#])/i', $url) ? $url : false; }
function store_find($id) { foreach (store_catalog() as $item) if (($item['id'] ?? '') === $id && !empty($item['licencia_confirmada'])) return $item; return null; }
$action = $_POST['action'] ?? $_GET['action'] ?? 'catalog';
if ($action === 'catalog') { $visible=[]; foreach (store_catalog() as $item) if (!empty($item['licencia_confirmada'])) $visible[]=$item; store_out(['status'=>'success','data'=>$visible]); }
if ($action === 'probe') {
    $ip = $_POST['ip'] ?? $_GET['ip'] ?? ''; $port = (int)($_POST['port'] ?? $_GET['port'] ?? 12801);
    if (!store_ip($ip) || $port < 1 || $port > 65535) store_out(['status'=>'error','message'=>'Destino RPI inválido.']);
    $errno = 0; $error = ''; $socket = @fsockopen($ip, $port, $errno, $error, 1.5);
    if (is_resource($socket)) { fclose($socket); store_out(['status'=>'success','online'=>true,'message'=>"RPI responde en {$ip}:{$port}."]); }
    store_out(['status'=>'error','online'=>false,'message'=>"No responde RPI en el puerto {$port}."]);
}
if ($action === 'install') {
    $ip=$_POST['ip']??''; $port=(int)($_POST['port']??12800); $id=$_POST['id']??''; $kind=$_POST['kind']??'pkg'; $index=(int)($_POST['index']??0);
    if (!store_ip($ip) || $port<1 || $port>65535) store_out(['status'=>'error','message'=>'Destino RPI inválido.']);
    $item=store_find($id); if (!$item) store_out(['status'=>'error','message'=>'Elemento de catálogo no disponible.']);
    $links=$item['enlaces']??[]; $url=$kind==='dlc' ? (($links['dlc'][$index]??null)) : ($links[$kind]??null); $url=store_url($url);
    if (!$url) store_out(['status'=>'error','message'=>'El enlace no es un PKG directo permitido.']);
    $payload=json_encode(['type'=>'direct','packages'=>[$url]], JSON_UNESCAPED_SLASHES);
    $ch=curl_init("http://{$ip}:{$port}/api/install"); curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_POST=>true,CURLOPT_POSTFIELDS=>$payload,CURLOPT_HTTPHEADER=>['Content-Type: application/json'],CURLOPT_TIMEOUT=>10]);
    $body=curl_exec($ch); $code=(int)curl_getinfo($ch,CURLINFO_HTTP_CODE); $error=curl_error($ch); curl_close($ch);
    if ($code>=200 && $code<300) store_out(['status'=>'success','message'=>'Solicitud enviada al instalador de PS4.']);
    store_out(['status'=>'error','message'=>$error ?: "El instalador respondió HTTP $code."]);
}
if ($action === 'check') {
    $item=store_find($_POST['id']??''); if (!$item) store_out(['status'=>'error','message'=>'Elemento no disponible.']); $url=store_url(($item['enlaces']['pkg']??'')); if (!$url) store_out(['status'=>'error','message'=>'Enlace PKG inválido.']);
    $ch=curl_init($url); curl_setopt_array($ch,[CURLOPT_NOBODY=>true,CURLOPT_FOLLOWLOCATION=>true,CURLOPT_MAXREDIRS=>3,CURLOPT_TIMEOUT=>15,CURLOPT_RETURNTRANSFER=>true]); curl_exec($ch); $code=(int)curl_getinfo($ch,CURLINFO_HTTP_CODE); $size=(int)curl_getinfo($ch,CURLINFO_CONTENT_LENGTH_DOWNLOAD); $error=curl_error($ch); curl_close($ch);
    store_out(['status'=>$code>=200&&$code<400?'success':'error','message'=>$code>=200&&$code<400?'Enlace disponible.':($error?:"HTTP $code"),'size'=>$size]);
}
store_out(['status'=>'error','message'=>'Acción no válida.']);
