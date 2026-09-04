<?php
/**
 * ====================================================================
 * GOLDHEN MANAGER - MÓDULO STORE V9 (ONBOARDING + UI OPTIMIZADA)
 * ====================================================================
 */
error_reporting(0);
$archivoCatalogo = 'juegos.json';

// ====================================================================
// 1. API DEL RADAR
// ====================================================================
if (isset($_GET['check_ip'])) {
    header('Content-Type: application/json');
    $ip = trim($_GET['check_ip']);
    $port = intval($_GET['port'] ?? 12800);
    $fp = @fsockopen($ip, $port, $errno, $errstr, 1.5);
    if ($fp) { fclose($fp); echo json_encode(['status' => 'online']); } 
    else { echo json_encode(['status' => 'offline']); }
    exit;
}

// ====================================================================
// 2. MOTOR EXTRACTOR DIRECTO (MEDIAFIRE)
// ====================================================================
function extraerEnlaceDirecto($url) {
    if (preg_match('/\.pkg(\?.*)?$/i', $url) && strpos($url, '/d/') === false && strpos($url, '/file/') === false) return $url;

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $html = curl_exec($ch);
    $urlEfectiva = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
    curl_close($ch);

    if (preg_match('/\.pkg(\?.*)?$/i', $urlEfectiva) && strpos($urlEfectiva, '/d/') === false) return $urlEfectiva;
    
    if (strpos($url, 'mediafire.com') !== false) {
        if (preg_match('/id="downloadButton"[^>]*href=["\']([^"\']+)["\']/i', $html, $m) || preg_match('/class="[^"]*popsok[^"]*"[^>]*href=["\']([^"\']+)["\']/i', $html, $m)) return $m[1];
    }
    
    if (preg_match('/href=["\']([^"\']+\.pkg)["\']/i', $html, $m)) return $m[1];
    return false;
}

// ====================================================================
// 3. API DE INSTALACIÓN AJAX (POST)
// ====================================================================
if (isset($_POST['action']) && $_POST['action'] === 'install') {
    header('Content-Type: application/json');
    $ipPs4 = trim($_POST['ip'] ?? '');
    $puertoPs4 = trim($_POST['puerto'] ?? '12800');
    $enlaceWeb = trim($_POST['url_web'] ?? '');

    if (empty($ipPs4) || empty($enlaceWeb)) { echo json_encode(['success' => false, 'title' => 'Faltan datos', 'message' => 'Revisa la IP y el enlace.']); exit; }

    $urlDirecta = extraerEnlaceDirecto($enlaceWeb);
    
    if ($urlDirecta) {
        $urlParaPs4 = str_replace('https://', 'http://', $urlDirecta);
        if (!preg_match('/\.pkg$/i', $urlParaPs4)) $urlParaPs4 .= "#/juego.pkg";
        
        $rpiUrl = "http://{$ipPs4}:{$puertoPs4}/api/install";
        $payload = json_encode(["type" => "direct", "packages" => [$urlParaPs4]], JSON_UNESCAPED_SLASHES);

        $chRpi = curl_init($rpiUrl);
        curl_setopt($chRpi, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($chRpi, CURLOPT_POST, true);
        curl_setopt($chRpi, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($chRpi, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Content-Length: ' . strlen($payload)]);
        curl_setopt($chRpi, CURLOPT_TIMEOUT, 8); 
        $rpiRespuesta = curl_exec($chRpi);
        $httpCode = curl_getinfo($chRpi, CURLINFO_HTTP_CODE);
        $curlError = curl_error($chRpi);
        curl_close($chRpi);

        if ($httpCode >= 200 && $httpCode < 300 && strpos($rpiRespuesta, 'success') !== false) {
            echo json_encode(['success' => true, 'title' => '¡Enviado a PS4!', 'message' => 'Revisa las notificaciones de tu consola.']);
        } else {
            $errorDetalle = "Cód HTTP: $httpCode\n" . (!empty($curlError) ? "Red: $curlError\n" : "") . (!empty($rpiRespuesta) ? "Consola: ".strip_tags($rpiRespuesta) : "");
            echo json_encode(['success' => false, 'title' => 'Error Detallado', 'message' => $errorDetalle]);
        }
    } else {
        echo json_encode(['success' => false, 'title' => 'Extracción fallida', 'message' => 'No se encontró un archivo instalable en ese enlace.']);
    }
    exit;
}

// ====================================================================
// 4. CARGA DEL CATÁLOGO
// ====================================================================
$juegosJson = @file_get_contents($archivoCatalogo);
$catalogo = !empty($juegosJson) ? json_decode($juegosJson, true) : [];

if (empty($catalogo)) {
    $catalogo = [
        [
            "titulo" => "DBZ Tenkaichi 3", "subtitulo" => "MOD Womanverse", "categoria" => "PS2",
            "portada" => "https://i.postimg.cc/br79pmZK/img-1-1788471236413.jpg",
            "servidor" => "Mediafire", "creditos" => "marceloviveros2002",
            "resena" => "Bugs reparados y rendimiento mejorado. Voces en español latino. PS2 a PS4.",
            "enlace" => "https://www.mediafire.com/file/epw61obsxjhvhxc/ISO_WOMENVERSE_SETH_VIVEROS.pkg/file"
        ],
        [
            "titulo" => "Sunset Riders", "subtitulo" => "SNES Station", "categoria" => "RETRO",
            "portada" => "https://i.postimg.cc/1Xkrsv8T/img-1-1788471340476.jpg",
            "servidor" => "Mediafire", "creditos" => "Anónimo",
            "resena" => "Totalmente compatible, idioma original en inglés. Disfruta de la acción clásica.",
            "enlace" => "https://www.mediafire.com/file/sggvudfjbys3mjq/Sunset+Riders+SETH+VIVEROS.pkg/file"
        ],
        [
            "titulo" => "Bomberman", "subtitulo" => "Collection PSONE", "categoria" => "PS1",
            "portada" => "https://i.postimg.cc/FzMVNTYw/Smart-Select-20260903-183631-Chrome.jpg",
            "servidor" => "Mediafire", "creditos" => "Anónimo",
            "resena" => "Colección definitiva de Bomberman PSONE. Compatible con FW 9.00 a 13.00.",
            "enlace" => "https://www.mediafire.com/file/gr5myvk3vuxi3b2/Bomberman+Collection+SETH+VIVEROS.pkg/file"
        ],
        [
            "titulo" => "DBZ Budokai 3", "subtitulo" => "Super VS AF", "categoria" => "PS2",
            "portada" => "https://i.postimg.cc/J0f5Mps2/Smart-Select-20260903-183646-Chrome.jpg",
            "servidor" => "Mediafire", "creditos" => "marceloviveros2002",
            "resena" => "Textos y menú en español. Disfruta el Mod de Super VS AF con mejoras gráficas.",
            "enlace" => "https://www.mediafire.com/file/rnrfucrin2jd1u7/Dragon_Ball_Super_vs_AF_SETH_VIVEROS.pkg/file"
        ]
    ];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="referrer" content="no-referrer">
    <title>GoldHen Manager - Store</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', sans-serif; background-color: #050711; color: white; min-height: 100vh; overflow-x: hidden; }
        .glass-panel { background: rgba(10, 14, 28, 0.75); backdrop-filter: blur(24px); border: 1px solid rgba(255, 255, 255, 0.05); }
        .modal-overlay { background: rgba(0,0,0,0.85); backdrop-filter: blur(8px); }
        .ps4-box { box-shadow: inset 0 0 20px rgba(0,0,0,0.8), 0 10px 20px rgba(0,0,0,0.5); }
    </style>
</head>
<body class="relative pb-10">

    <!-- TOAST NOTIFICACIONES -->
    <div id="toast-notification" class="fixed top-4 left-1/2 -translate-x-1/2 z-[100] w-[95%] max-w-sm transition-all duration-500 transform -translate-y-32 opacity-0 pointer-events-none">
        <div id="toast-bg" class="glass-panel p-4 rounded-2xl flex items-start gap-4 shadow-2xl border border-white/10">
            <div id="toast-icon-bg" class="w-10 h-10 rounded-full flex items-center justify-center shrink-0 mt-1">
                <i id="toast-icon" class="text-xl"></i>
            </div>
            <div class="flex-1 overflow-hidden">
                <h4 id="toast-title" class="text-sm font-black uppercase tracking-wider text-white">Titulo</h4>
                <p id="toast-msg" class="text-[12px] text-gray-300 mt-1 leading-relaxed whitespace-pre-wrap break-words font-mono">Mensaje</p>
            </div>
        </div>
    </div>

    <!-- FONDO -->
    <div class="fixed inset-0 z-0 overflow-hidden pointer-events-none">
        <img src="https://images.unsplash.com/photo-1552820728-8b83bb6b773f?q=80&w=1080&auto=format&fit=crop" alt="Fondo" class="w-full h-full object-cover opacity-20">
        <div class="absolute inset-0 bg-gradient-to-b from-[#050711]/80 via-[#050711]/95 to-[#050711] z-10"></div>
    </div>

    <!-- HEADER FLOTANTE -->
    <div class="fixed top-0 left-0 right-0 z-40 glass-panel border-b border-white/10 px-4 py-3 flex items-center justify-between">
        <div class="flex items-center gap-2">
            <div class="bg-cyan-500 w-8 h-8 rounded-lg flex items-center justify-center shadow-[0_0_15px_rgba(6,182,212,0.5)]">
                <i class="fa-solid fa-store text-white text-sm"></i>
            </div>
            <div>
                <h1 class="text-sm font-black tracking-widest uppercase leading-none">GoldHen Store</h1>
                <span class="text-[10px] text-cyan-400 font-bold uppercase tracking-widest">Catálogo Premium</span>
            </div>
        </div>
        <button onclick="toggleModal('modal-config')" class="w-10 h-10 rounded-full bg-white/5 border border-white/10 flex items-center justify-center hover:bg-white/10 active:scale-90 transition-all relative shadow-lg">
            <i class="fa-solid fa-gear text-gray-300"></i>
            <div id="luz-radar-mini" class="absolute top-2 right-2 w-2.5 h-2.5 rounded-full bg-red-500 border-[2.5px] border-[#050711]"></div>
        </button>
    </div>

    <!-- CONTENEDOR PRINCIPAL -->
    <div class="w-full max-w-xl mx-auto px-3 pt-20 relative z-20">
        
        <!-- INSTALACIÓN MANUAL DIRECTA -->
        <div class="mb-4 glass-panel rounded-2xl p-3 border border-white/5 shadow-lg">
            <div class="flex items-center justify-between mb-2 pl-1">
                <h3 class="text-[10px] font-black uppercase tracking-widest text-gray-300 flex items-center gap-1.5">
                    <i class="fa-solid fa-bolt text-fuchsia-400"></i> Instalación Rápida
                </h3>
                <span class="text-[8px] text-gray-400 font-medium tracking-wide">
                    * Solo URLs Mediafire (PKG directo)
                </span>
            </div>
            <div class="flex gap-2">
                <input type="url" id="manual-url" placeholder="Pega el enlace web aquí..." class="flex-1 bg-black/50 border border-white/10 rounded-xl px-3 py-2.5 text-[11px] text-white outline-none focus:border-fuchsia-400 transition-colors">
                <button id="btn-manual-install" class="bg-gradient-to-r from-fuchsia-600 to-purple-600 hover:from-fuchsia-500 hover:to-purple-500 active:scale-95 text-white w-12 rounded-xl flex items-center justify-center shadow-[0_0_15px_rgba(192,38,211,0.3)] transition-all">
                    <i class="fa-solid fa-paper-plane text-xs"></i>
                </button>
            </div>
        </div>

        <!-- BARRA DE BÚSQUEDA Y FILTROS -->
        <div class="flex gap-2 mb-6 sticky top-16 z-30 pt-1 pb-2 bg-[#050711]/90 backdrop-blur-md">
            <div class="flex-1 relative bg-black/50 border border-white/10 rounded-xl overflow-hidden flex items-center px-3 focus-within:border-cyan-400 transition-colors shadow-inner">
                <i class="fa-solid fa-magnifying-glass text-gray-400 text-sm"></i>
                <input type="text" id="buscador" placeholder="Buscar juego..." class="w-full bg-transparent text-white text-xs py-3.5 pl-3 outline-none">
            </div>
            <div class="w-32 relative bg-black/50 border border-white/10 rounded-xl overflow-hidden flex items-center focus-within:border-cyan-400 transition-colors shadow-inner">
                <select id="filtro-categoria" class="w-full bg-transparent text-white text-[11px] font-bold py-3.5 pl-3 pr-6 outline-none appearance-none cursor-pointer">
                    <option value="ALL">Todas</option>
                    <option value="PS4">PS4</option>
                    <option value="PS2">PS2</option>
                    <option value="PS1">PS1</option>
                    <option value="RETRO">Retro</option>
                </select>
                <i class="fa-solid fa-filter absolute right-3 text-gray-400 text-[10px] pointer-events-none"></i>
            </div>
        </div>

        <!-- LISTA DE JUEGOS (GRILLA 2 COLUMNAS) -->
        <div class="grid grid-cols-2 gap-4 pb-8" id="lista-catalogo">
            <?php foreach ($catalogo as $juego): ?>
                <div class="game-card glass-panel rounded-2xl p-2.5 border border-white/10 shadow-lg flex flex-col" 
                     data-titulo="<?= strtolower(htmlspecialchars($juego['titulo'])) ?>" 
                     data-categoria="<?= htmlspecialchars($juego['categoria'] ?? 'ALL') ?>">
                    
                    <!-- CAJA ESTILO PS4 -->
                    <div class="relative w-full aspect-[3/4] rounded-lg ps4-box border border-gray-700/60 overflow-hidden group mb-3 transform transition-transform duration-300 hover:scale-[1.03]">
                        <div class="absolute top-0 left-0 right-0 h-[12%] min-h-[18px] bg-gradient-to-r from-blue-700 via-blue-500 to-blue-800 flex items-center px-2 border-b border-white/30 z-20 shadow-md">
                            <span class="text-[8px] text-white font-black tracking-widest italic drop-shadow-sm">PS4</span>
                        </div>
                        <div class="absolute inset-0 top-[12%] bg-gradient-to-br from-slate-800 to-slate-950 flex flex-col items-center justify-center text-center overflow-hidden">
                            <div class="absolute inset-0 bg-black/20 bg-cover bg-center group-hover:scale-110 transition-transform duration-700" style="background-image: url('<?= htmlspecialchars($juego['portada']) ?>'); background-size: cover;"></div>
                            <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent z-10 pointer-events-none"></div>
                            <span class="absolute top-2 right-2 text-[8px] text-cyan-400 font-black uppercase mt-1.5 z-20 drop-shadow-md bg-black/60 px-1.5 py-0.5 rounded border border-white/10">
                                <?= htmlspecialchars($juego['categoria'] ?? 'PS4') ?>
                            </span>
                        </div>
                    </div>
                    
                    <!-- INFORMACIÓN DEL JUEGO -->
                    <div class="flex flex-col flex-1 px-1">
                        <h3 class="text-[13px] font-black uppercase tracking-wider leading-tight text-white mb-1 line-clamp-2">
                            <?= htmlspecialchars($juego['titulo']) ?>
                        </h3>
                        <?php if(!empty($juego['subtitulo'])): ?>
                            <p class="text-[10px] text-cyan-400 font-bold uppercase tracking-widest mb-2 truncate">
                                <?= htmlspecialchars($juego['subtitulo']) ?>
                            </p>
                        <?php endif; ?>

                        <?php if(!empty($juego['resena'])): ?>
                            <p class="text-[11px] text-gray-300 font-light leading-snug line-clamp-3 mb-3">
                                <?= htmlspecialchars($juego['resena']) ?>
                            </p>
                        <?php endif; ?>

                        <div class="mt-auto pt-3 border-t border-white/10">
                            <div class="flex items-center gap-1.5 mb-3">
                                <i class="fa-solid fa-user-astronaut text-[10px] text-gray-400"></i>
                                <span class="text-[10px] text-gray-400 font-bold truncate">Por: <span class="text-white"><?= htmlspecialchars($juego['creditos'] ?? 'Anónimo') ?></span></span>
                            </div>

                            <button class="btn-install-game w-full bg-gradient-to-r from-blue-600 to-cyan-500 hover:from-blue-500 hover:to-cyan-400 text-white font-black text-[11px] py-2.5 rounded-xl tracking-widest uppercase transition-all flex items-center justify-center gap-2 shadow-[0_5px_15px_rgba(6,182,212,0.3)] active:scale-95" 
                                    data-url="<?= htmlspecialchars($juego['enlace']) ?>">
                                <i class="fa-solid fa-download"></i> Bajar
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
            
            <div id="no-results" class="hidden col-span-2 glass-panel p-10 text-center rounded-3xl border border-white/10 mt-4">
                <i class="fa-solid fa-ghost text-4xl text-gray-600 mb-3"></i>
                <p class="text-xs text-gray-400 font-bold uppercase tracking-widest">No se encontraron juegos</p>
            </div>
        </div>
    </div>

    <!-- MODAL DE CONFIGURACIÓN (ONBOARDING) -->
    <div id="modal-config" class="fixed inset-0 z-50 modal-overlay flex flex-col justify-end md:justify-center transition-opacity duration-300 opacity-0 pointer-events-none p-4">
        <div class="glass-panel w-full max-w-sm mx-auto rounded-[2rem] border border-white/10 p-6 transform translate-y-8 md:translate-y-0 transition-transform duration-300 shadow-[0_10px_40px_rgba(0,0,0,0.8)]" id="modal-content">
            
            <div class="flex justify-between items-center mb-5">
                <h2 class="text-sm font-black uppercase tracking-widest flex items-center gap-2 text-white">
                    <i class="fa-solid fa-gear text-cyan-400"></i> Configuración Inicial
                </h2>
                <button onclick="toggleModal('modal-config')" class="w-8 h-8 bg-white/5 rounded-full flex items-center justify-center hover:bg-white/10 active:scale-90 transition-all border border-white/10 hidden" id="btn-cerrar-modal">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <!-- ADVERTENCIA -->
            <div class="bg-amber-500/10 border border-amber-500/30 rounded-xl p-3 mb-4 flex items-start gap-3">
                <i class="fa-solid fa-triangle-exclamation text-amber-400 text-base mt-0.5"></i>
                <p class="text-[11px] text-amber-200 font-medium leading-relaxed">
                    ¡Atención! Para que la tienda funcione, la aplicación <strong>Remote Package Installer</strong> debe estar abierta en tu consola.
                </p>
            </div>

            <!-- MINI GUIA -->
            <div class="bg-blue-500/10 border border-blue-500/20 rounded-xl p-3 mb-4">
                <h3 class="text-[10px] font-black text-blue-400 uppercase tracking-widest mb-2">Guía Rápida</h3>
                <ol class="text-[11px] text-blue-100/80 space-y-1.5 ml-4 list-decimal font-medium">
                    <li>Enciende la PS4 y activa el <b>GoldHen</b>.</li>
                    <li>Abre el <b>Remote Package Installer</b>.</li>
                    <li>Ingresa la <b>IP de tu consola</b> aquí abajo.</li>
                </ol>
            </div>

            <!-- INPUTS DE RED -->
            <div class="flex justify-between items-center mb-2 px-1">
                <span class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">Ajustes de Red</span>
                <span id="estado-texto-modal" class="text-gray-500 text-[9px] uppercase tracking-widest font-black">Buscando...</span>
            </div>
            
            <div class="grid grid-cols-5 gap-2 mb-4">
                <div class="col-span-3 bg-black/60 border border-white/10 rounded-xl overflow-hidden flex items-center px-3 focus-within:border-cyan-400 transition-colors shadow-inner">
                    <i class="fa-solid fa-gamepad text-gray-500 text-sm"></i>
                    <input type="text" id="ip-input" placeholder="192.168..." class="w-full bg-transparent text-white text-xs py-3.5 pl-2 outline-none font-mono">
                </div>
                <div class="col-span-2 bg-black/60 border border-white/10 rounded-xl overflow-hidden relative flex items-center focus-within:border-cyan-400 transition-colors shadow-inner">
                    <select id="puerto-input" class="w-full bg-transparent text-white text-[10px] font-bold py-3.5 pl-2 pr-6 outline-none appearance-none cursor-pointer">
                        <option value="12800">RPI Orig</option>
                        <option value="12801" selected>RPI Nova</option>
                    </select>
                    <i class="fa-solid fa-chevron-down absolute right-3 text-gray-400 text-[10px] pointer-events-none"></i>
                </div>
            </div>

            <!-- DESCARGAS RPI -->
            <div class="flex gap-2 mb-6">
                <!-- Cambia el "#" por los links reales de tus PKGs -->
                <a href="#" onclick="alert('Reemplaza este link por el PKG del RPI Original')" class="flex-1 bg-white/5 hover:bg-white/10 border border-white/10 text-center py-2.5 rounded-xl text-[9px] font-black uppercase tracking-widest text-gray-300 transition-colors">
                    <i class="fa-solid fa-download text-cyan-400 mr-1"></i> RPI Original
                </a>
                <a href="#" onclick="alert('Reemplaza este link por el PKG del RPI Nova')" class="flex-1 bg-white/5 hover:bg-white/10 border border-white/10 text-center py-2.5 rounded-xl text-[9px] font-black uppercase tracking-widest text-gray-300 transition-colors">
                    <i class="fa-solid fa-download text-fuchsia-400 mr-1"></i> RPI Nova
                </a>
            </div>

            <button onclick="guardarYEntrar()" class="w-full bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500 active:scale-95 text-white font-black text-xs py-4 rounded-2xl tracking-widest uppercase transition-all shadow-lg flex items-center justify-center gap-2">
                <i class="fa-solid fa-check-circle"></i> Aceptar y Entrar
            </button>
        </div>
    </div>

    <script>
        // --- 1. GESTIÓN DEL MODAL ONBOARDING ---
        document.addEventListener('DOMContentLoaded', () => {
            const ipGuardada = localStorage.getItem('ps4_store_ip');
            const puertoGuardado = localStorage.getItem('ps4_store_puerto');
            
            if (ipGuardada) {
                document.getElementById('ip-input').value = ipGuardada;
                document.getElementById('puerto-input').value = puertoGuardado || '12801';
                document.getElementById('btn-cerrar-modal').classList.remove('hidden');
            } else {
                // Si es la primera vez, mostramos el modal automáticamente
                toggleModal('modal-config');
            }
            verificarConexion();
        });

        function guardarYEntrar() {
            const ip = document.getElementById('ip-input').value.trim();
            const puerto = document.getElementById('puerto-input').value;
            
            if(ip !== '') {
                localStorage.setItem('ps4_store_ip', ip);
                localStorage.setItem('ps4_store_puerto', puerto);
                document.getElementById('btn-cerrar-modal').classList.remove('hidden');
                toggleModal('modal-config');
            } else {
                alert("Por favor, ingresa la IP de tu consola.");
            }
        }

        function toggleModal(modalID) {
            const modal = document.getElementById(modalID);
            const content = document.getElementById('modal-content');
            if (modal.classList.contains('opacity-0')) {
                modal.classList.remove('opacity-0', 'pointer-events-none');
                setTimeout(() => content.classList.remove('translate-y-8', 'md:translate-y-0'), 10);
            } else {
                content.classList.add('translate-y-8', 'md:translate-y-0');
                setTimeout(() => modal.classList.add('opacity-0', 'pointer-events-none'), 300);
            }
        }

        // --- 2. BUSCADOR Y FILTROS ---
        const buscador = document.getElementById('buscador');
        const filtroCat = document.getElementById('filtro-categoria');
        const cards = document.querySelectorAll('.game-card');
        const noResults = document.getElementById('no-results');

        function filtrarJuegos() {
            const texto = buscador.value.toLowerCase();
            const cat = filtroCat.value;
            let visibles = 0;

            cards.forEach(card => {
                const titulo = card.getAttribute('data-titulo');
                const categoria = card.getAttribute('data-categoria');
                const coincideTexto = titulo.includes(texto);
                const coincideCat = (cat === 'ALL' || categoria === cat);

                if (coincideTexto && coincideCat) {
                    card.style.display = 'flex';
                    visibles++;
                } else {
                    card.style.display = 'none';
                }
            });
            noResults.style.display = visibles === 0 ? 'block' : 'none';
        }

        buscador.addEventListener('input', filtrarJuegos);
        filtroCat.addEventListener('change', filtrarJuegos);

        // --- 3. RADAR DE CONEXIÓN ---
        const inputIp = document.getElementById('ip-input');
        const selectPuerto = document.getElementById('puerto-input');
        const luzMini = document.getElementById('luz-radar-mini');
        const estadoModal = document.getElementById('estado-texto-modal');
        let timer;

        function verificarConexion() {
            const ip = inputIp.value.trim();
            const puerto = selectPuerto.value;

            if (!ip) {
                luzMini.className = 'absolute top-2 right-2 w-2.5 h-2.5 rounded-full bg-gray-600 border-[2.5px] border-[#050711]';
                estadoModal.innerText = "ESPERANDO IP...";
                estadoModal.className = "text-gray-500 text-[10px] uppercase tracking-widest font-black";
                return;
            }

            luzMini.className = 'absolute top-2 right-2 w-2.5 h-2.5 rounded-full bg-yellow-400 border-[2.5px] border-[#050711] animate-pulse';
            estadoModal.innerText = "BUSCANDO...";
            estadoModal.className = "text-yellow-500 text-[10px] uppercase tracking-widest font-black";

            fetch(`?check_ip=${encodeURIComponent(ip)}&port=${encodeURIComponent(puerto)}`)
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'online') {
                        luzMini.className = 'absolute top-2 right-2 w-3 h-3 rounded-full bg-emerald-400 shadow-[0_0_10px_rgba(52,211,153,0.8)] border-[2.5px] border-[#050711]';
                        estadoModal.innerText = "¡CONECTADO!";
                        estadoModal.className = "text-emerald-400 text-[10px] uppercase tracking-widest font-black";
                    } else {
                        luzMini.className = 'absolute top-2 right-2 w-3 h-3 rounded-full bg-red-500 shadow-[0_0_10px_rgba(239,68,68,0.8)] border-[2.5px] border-[#050711]';
                        estadoModal.innerText = "OFFLINE";
                        estadoModal.className = "text-red-400 text-[10px] uppercase tracking-widest font-black";
                    }
                }).catch(() => {
                    luzMini.className = 'absolute top-2 right-2 w-3 h-3 rounded-full bg-red-500 shadow-[0_0_10px_rgba(239,68,68,0.8)] border-[2.5px] border-[#050711]';
                    estadoModal.innerText = "ERROR DE RED";
                    estadoModal.className = "text-red-400 text-[10px] uppercase tracking-widest font-black";
                });
        }

        inputIp.addEventListener('input', () => { clearTimeout(timer); timer = setTimeout(verificarConexion, 800); });
        selectPuerto.addEventListener('change', verificarConexion); 

        // --- 4. SISTEMA DE DESCARGA (AJAX) ---
        const toast = document.getElementById('toast-notification');
        const toastBg = document.getElementById('toast-bg');
        const toastIconBg = document.getElementById('toast-icon-bg');
        const toastIcon = document.getElementById('toast-icon');
        const toastTitle = document.getElementById('toast-title');
        const toastMsg = document.getElementById('toast-msg');

        function showToast(isSuccess, title, msg) {
            toastBg.className = isSuccess 
                ? "glass-panel p-4 rounded-2xl flex items-start gap-4 shadow-2xl border border-emerald-500/30"
                : "glass-panel p-4 rounded-2xl flex items-start gap-4 shadow-2xl border border-red-500/30";
            toastIconBg.className = isSuccess
                ? "w-10 h-10 rounded-full flex items-center justify-center shrink-0 bg-emerald-500/20 text-emerald-400 mt-1"
                : "w-10 h-10 rounded-full flex items-center justify-center shrink-0 bg-red-500/20 text-red-400 mt-1";
            toastIcon.className = isSuccess ? "fa-solid fa-check text-xl" : "fa-solid fa-bug text-xl";
            toastTitle.innerText = title;
            toastMsg.innerText = msg;

            toast.classList.remove('-translate-y-32', 'opacity-0', 'pointer-events-none');
            toast.classList.add('translate-y-0', 'opacity-100');

            setTimeout(() => {
                toast.classList.remove('translate-y-0', 'opacity-100');
                toast.classList.add('-translate-y-32', 'opacity-0', 'pointer-events-none');
            }, 8000); 
        }

        function enviarA_PS4(urlJuego, btnElement) {
            // Siempre leer la IP de los inputs ocultos en el modal o del localStorage
            const ip = document.getElementById('ip-input').value.trim() || localStorage.getItem('ps4_store_ip');
            const puerto = document.getElementById('puerto-input').value || localStorage.getItem('ps4_store_puerto');

            if (!ip) { 
                showToast(false, "Falta la IP", "Toca la tuerca arriba a la derecha y configura la IP de tu consola."); 
                toggleModal('modal-config');
                return; 
            }
            if (!urlJuego) { showToast(false, "Falta el Enlace", "No hay ningún enlace."); return; }

            const textoOriginal = btnElement.innerHTML;
            btnElement.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> ...';
            btnElement.disabled = true;
            btnElement.classList.add('opacity-50');

            const formData = new FormData();
            formData.append('action', 'install');
            formData.append('ip', ip);
            formData.append('puerto', puerto);
            formData.append('url_web', urlJuego);

            fetch('', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => { showToast(data.success, data.title, data.message); })
            .catch(err => { showToast(false, 'Error', 'Fallo de conexión interna con el teléfono.'); })
            .finally(() => {
                btnElement.innerHTML = textoOriginal;
                btnElement.disabled = false;
                btnElement.classList.remove('opacity-50');
            });
        }

        document.querySelectorAll('.btn-install-game').forEach(btn => {
            btn.addEventListener('click', function() { enviarA_PS4(this.getAttribute('data-url'), this); });
        });

        document.getElementById('btn-manual-install').addEventListener('click', function() {
            enviarA_PS4(document.getElementById('manual-url').value.trim(), this);
            document.getElementById('manual-url').value = ''; // Limpia el input despues de enviar
        });
    </script>
</body>
</html>
