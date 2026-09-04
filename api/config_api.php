<?php
declare(strict_types=1);
error_reporting(0); set_time_limit(120);
$root = dirname(__DIR__); $user = $root . '/user'; $configDir = $user . '/config';
foreach ([$configDir, $user . '/plugins', $user . '/payloads'] as $dir) if (!is_dir($dir)) mkdir($dir, 0775, true);
$action = $_POST['action'] ?? $_GET['action'] ?? '';
function config_allowed(string $name): bool { return $name === 'store/data/catalogo.json' || preg_match('#^user/(config/browser-settings\.json|plugins/[A-Za-z0-9._-]+\.prx|payloads/[A-Za-z0-9._-]+\.(bin|payload|elf))$#', $name); }
function config_files(string $root): array { $out=[]; foreach (['user/config/browser-settings.json','store/data/catalogo.json'] as $file) if (is_file($root.'/'.$file)) $out[]=$file; foreach (['user/plugins','user/payloads'] as $dir) if (is_dir($root.'/'.$dir)) foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root.'/'.$dir, FilesystemIterator::SKIP_DOTS)) as $file) { $rel=str_replace('\\','/',substr($file->getPathname(),strlen($root)+1)); if ($file->isFile() && config_allowed($rel)) $out[]=$rel; } return $out; }
if ($action === 'export') {
    $tmp=tempnam(sys_get_temp_dir(),'ghm_config_').'.zip'; $manifest=['format'=>1,'created_at'=>gmdate('c'),'files'=>config_files($root)];
    if (class_exists('ZipArchive')) { $zip=new ZipArchive(); if($zip->open($tmp,ZipArchive::CREATE)!==true){http_response_code(500);exit;} foreach($manifest['files'] as $file)$zip->addFile($root.'/'.$file,$file); $zip->addFromString('goldhen-config-manifest.json',json_encode($manifest,JSON_PRETTY_PRINT)); $zip->close(); }
    else { $stage=sys_get_temp_dir().'/ghm_cfg_'.bin2hex(random_bytes(5)); mkdir($stage,0700,true); foreach($manifest['files'] as $file){$to=$stage.'/'.$file;mkdir(dirname($to),0700,true);copy($root.'/'.$file,$to);} file_put_contents($stage.'/goldhen-config-manifest.json',json_encode($manifest,JSON_PRETTY_PRINT)); exec('cd '.escapeshellarg($stage).' && zip -qr '.escapeshellarg($tmp).' .',$o,$code); foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($stage,FilesystemIterator::SKIP_DOTS),RecursiveIteratorIterator::CHILD_FIRST) as $f)$f->isDir()?rmdir($f->getPathname()):unlink($f->getPathname());rmdir($stage);if($code!==0){http_response_code(501);exit('Instala zip en Termux y vuelve a intentarlo.');} }
    header('Content-Type: application/zip'); header('Content-Disposition: attachment; filename="goldhen-manager-config-'.date('Ymd-His').'.zip"'); header('Content-Length: '.filesize($tmp)); readfile($tmp); unlink($tmp); exit;
}
header('Content-Type: application/json; charset=utf-8');
try {
    if ($action === 'save_browser_settings') { $settings=json_decode((string)($_POST['settings']??''),true); if(!is_array($settings))throw new RuntimeException('Ajustes inválidos.'); file_put_contents($configDir.'/browser-settings.json',json_encode($settings,JSON_PRETTY_PRINT),LOCK_EX); echo json_encode(['status'=>'success']); exit; }
    if ($action === 'import') {
        if (empty($_FILES['archive']) || $_FILES['archive']['error']!==UPLOAD_ERR_OK || $_FILES['archive']['size']>52428800) throw new RuntimeException('Selecciona un ZIP válido de hasta 50 MB.');
        $archive=$_FILES['archive']['tmp_name']; $restored=[];
        if(class_exists('ZipArchive')) { $zip=new ZipArchive(); if($zip->open($archive)!==true)throw new RuntimeException('No se pudo abrir el ZIP.'); for($i=0;$i<$zip->numFiles;$i++){ $name=str_replace('\\','/',$zip->getNameIndex($i)); if(!config_allowed($name))continue; $data=$zip->getFromIndex($i); if($data===false)continue; $target=$root.'/'.$name; if(!is_dir(dirname($target)))mkdir(dirname($target),0775,true); file_put_contents($target,$data,LOCK_EX); $restored[]=$name; } $zip->close(); }
        else { exec('unzip -Z1 '.escapeshellarg($archive),$names,$code); if($code!==0)throw new RuntimeException('No se pudo abrir el ZIP.'); foreach($names as $name){$name=str_replace('\\','/',$name);if(!config_allowed($name))continue;$data=shell_exec('unzip -p '.escapeshellarg($archive).' '.escapeshellarg($name));if($data===null)continue;$target=$root.'/'.$name;if(!is_dir(dirname($target)))mkdir(dirname($target),0775,true);file_put_contents($target,$data,LOCK_EX);$restored[]=$name;} }
        $settings=is_file($configDir.'/browser-settings.json')?json_decode((string)file_get_contents($configDir.'/browser-settings.json'),true):null; echo json_encode(['status'=>'success','restored'=>$restored,'settings'=>$settings]); exit;
    }
    throw new RuntimeException('Acción no válida.');
} catch(Throwable $e) { http_response_code(400); echo json_encode(['status'=>'error','message'=>$e->getMessage()]); }
?>
