<?php
error_reporting(0); ini_set('display_errors', 0); set_time_limit(120);
header('Content-Type: application/json; charset=utf-8');
$action = $_POST['action'] ?? $_GET['action'] ?? ''; $host = $_POST['host_ip'] ?? $_GET['host_ip'] ?? '';
$local_dir = __DIR__ . '/../user/payloads'; $remote_dir = '/data/payloads';
@mkdir($local_dir, 0777, true);
function payload_out($data) { echo json_encode($data); exit; }
function payload_name($name) { $name = basename($name); return preg_match('/^[A-Za-z0-9._-]+\.(bin|payload)$/i', $name) ? $name : false; }
function payload_url($host, $path) { return 'ftp://' . $host . ':2121' . implode('/', array_map('rawurlencode', explode('/', $path))); }
function payload_list_ftp($host, $dir) { $ch=curl_init(payload_url($host,$dir.'/')); curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_CUSTOMREQUEST=>'LIST',CURLOPT_TIMEOUT=>10]); $raw=curl_exec($ch); curl_close($ch); $out=[]; foreach(explode("\n",trim((string)$raw)) as $line){$p=preg_split('/\s+/',trim($line),9);if(count($p)>=9&&$p[0][0]!=='d'&&payload_name($p[8]))$out[]=['name'=>$p[8],'size'=>(int)$p[4]];} return $out; }
function payload_get_ftp($host,$path){$ch=curl_init(payload_url($host,$path));curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_TIMEOUT=>60]);$d=curl_exec($ch);curl_close($ch);return $d;}
if ($action === 'list') { $local=[]; foreach(glob($local_dir.'/*')?:[] as $f) if(payload_name(basename($f)))$local[]=['name'=>basename($f),'size'=>filesize($f)]; $remote=filter_var($host,FILTER_VALIDATE_IP,FILTER_FLAG_IPV4)?payload_list_ftp($host,$remote_dir):[]; payload_out(['status'=>'success','local'=>$local,'remote'=>$remote]); }
if (!filter_var($host,FILTER_VALIDATE_IP,FILTER_FLAG_IPV4)) payload_out(['status'=>'error','message'=>'IP de PS4 inválida.']);
if ($action === 'upload') { if(!isset($_FILES['payload'])||$_FILES['payload']['error']!==UPLOAD_ERR_OK||!($name=payload_name($_FILES['payload']['name'])))payload_out(['status'=>'error','message'=>'Payload no válido.']); if(!move_uploaded_file($_FILES['payload']['tmp_name'],$local_dir.'/'.$name))payload_out(['status'=>'error','message'=>'No se pudo guardar el payload.']); payload_out(['status'=>'success']); }
if ($action === 'send') { $source=$_POST['source']??'local'; $name=payload_name($_POST['name']??''); if(!$name)payload_out(['status'=>'error','message'=>'Payload no válido.']); $data=$source==='remote'?payload_get_ftp($host,"$remote_dir/$name"):@file_get_contents($local_dir.'/'.$name); if($data===false||$data===''||strlen($data)>16*1024*1024)payload_out(['status'=>'error','message'=>'Payload ausente o supera 16 MB.']); $sock=@fsockopen($host,9090,$errno,$err,5); if(!$sock)payload_out(['status'=>'error','message'=>'BinLoader no responde en el puerto 9090.']); stream_set_timeout($sock,30); $written=0;$length=strlen($data);while($written<$length){$n=fwrite($sock,substr($data,$written));if($n===false)break;$written+=$n;}fclose($sock);payload_out($written===$length?['status'=>'success','bytes'=>$written]:['status'=>'error','message'=>'No se pudo enviar todo el payload.']); }
payload_out(['status'=>'error','message'=>'Acción no válida.']);
?>
