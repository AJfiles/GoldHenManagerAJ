<?php
/**
 * ====================================================================
 * GOLDHEN MANAGER V3.0 🚀 (PS4) - EDICIÓN MODULAR TERMUX
 * DEVELOPED By SeBaS - RUTA: index.php
 * ====================================================================
 */
error_reporting(0);
@ini_set('display_errors', 0);
@ini_set('memory_limit', '512M'); 

header('Content-Type: text/html; charset=utf-8');
$firma = chr(83).chr(101).chr(66).chr(97).chr(83); 
header('X-Author: ' . $firma);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>GoldHen Manager V3.0</title>

    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#060913">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <link rel="apple-touch-icon" href="/js/icon-192.png">

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700;900&display=swap" rel="stylesheet">
    
    <style>
        html { background-color: #060913; }
        body { font-family: 'Outfit', sans-serif; background-color: transparent !important; overflow: hidden; height: 100dvh; }
        .bg-radial-glow { background: transparent !important; }
        
        .app-layer { 
            position: fixed; inset: 0; transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); opacity: 0; pointer-events: none; transform: scale(0.97); z-index: 10; 
            background-color: rgba(4, 7, 16, 0.65) !important; 
        }
        .app-layer.active { opacity: 1; pointer-events: auto; transform: scale(1); }
        
        .launcher-card { 
            background: rgba(10, 13, 24, 0.55) !important; border: 1px solid rgba(255, 255, 255, 0.05) !important; transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1); box-shadow: 0 10px 30px rgba(0,0,0,0.3); backdrop-filter: blur(8px); 
        }
        .launcher-card:active { transform: scale(0.95); border-color: rgba(34, 211, 238, 0.4) !important; background: rgba(10, 13, 24, 0.7) !important; }

        .app-layer .bg-\\[\\#0a0f1a\\], 
        .app-layer .bg-\\[\\#02040a\\], 
        .glass-premium {
            background-color: rgba(10, 15, 26, 0.60) !important;
            backdrop-filter: blur(8px) !important;
            border: 1px solid rgba(255,255,255,0.05) !important;
        }

        .modal-pop,
        #modal-selector-juegos-content,
        #radar-caja,
        .dropdown-options,
        div.fixed.bottom-0,
        div.fixed.inset-x-0.bottom-0 {
            background-color: #060913 !important; 
            background-image: none !important;
            backdrop-filter: blur(40px) !important;
            -webkit-backdrop-filter: blur(40px) !important;
            box-shadow: 0 -20px 60px rgba(0,0,0,0.95) !important;
            border-top: 1px solid rgba(34, 211, 238, 0.15) !important;
            opacity: 1 !important;
            z-index: 9999 !important;
        }

        .bg-black\\/80, .bg-black\\/90 {
            background-color: rgba(0, 0, 0, 0.85) !important;
        }

        .scanner-line { width: 100%; height: 2px; background: #10b981; position: absolute; left: 0; top: 0; box-shadow: 0 0 15px #10b981, 0 0 5px #10b981; opacity: 0.8; animation: scan 1.5s linear infinite; z-index: 20; }
        @keyframes scan { 0% { top: 0%; opacity: 0; } 10% { opacity: 1; } 90% { opacity: 1; } 100% { top: 100%; opacity: 0; } }

        .hide-scrollbar::-webkit-scrollbar { display: none !important; }
        .hide-scrollbar { -ms-overflow-style: none !important; scrollbar-width: none !important; }
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.2); border-radius: 4px; }
    </style>
</head>
<body class="text-white select-none hide-scrollbar bg-radial-glow">

    <?php include 'modulos/wallpapers.php'; ?>
    <div id="intro-wrapper" class="fixed inset-0 z-[9999] bg-[#05050a] flex items-center justify-center">
        <?php include 'modulos/intros.php'; ?>
    </div>

    <div id="layer-launcher" class="app-layer active flex flex-col justify-between p-5 h-screen w-full overflow-y-auto hide-scrollbar z-10">
        
        <div class="text-center w-full pt-1 shrink-0 z-10 flex flex-col items-center">
            <div class="flex items-center justify-center gap-2 mb-1.5 hover:opacity-100 transition-opacity">
                <span class="text-[8px] font-black tracking-[0.25em] text-gray-400 uppercase">Developed By SeBaS</span>
                <div class="flex flex-col w-3.5 h-2.5 justify-between opacity-60">
                    <div class="h-[33%] bg-[#74ACDF]"></div>
                    <div class="h-[34%] bg-white flex items-center justify-center"><div class="w-[3px] h-[3px] bg-[#F1B517] rounded-full"></div></div>
                    <div class="h-[33%] bg-[#74ACDF]"></div>
                </div>
            </div>
            <h1 class="text-4xl font-black tracking-tighter text-transparent bg-clip-text bg-gradient-to-b from-[#ffffff] to-[#9aa0ab] uppercase leading-none drop-shadow-md">
                GoldHen Manager
            </h1>
            <div class="flex items-center justify-center gap-3 mt-2.5 opacity-80">
                <span class="h-[1px] w-6 bg-gradient-to-r from-transparent to-cyan-500/80"></span>
                <span class="text-[9px] font-mono tracking-[0.4em] font-bold text-cyan-400">VERSION 3.0</span>
                <span class="h-[1px] w-6 bg-gradient-to-l from-transparent to-cyan-500/80"></span>
            </div>
        </div>

        <div class="w-full max-w-4xl mx-auto z-20 mt-4 shrink-0">
            <div class="glass-premium p-4 rounded-[2rem] flex flex-col gap-3 shadow-2xl">
                
                <div class="flex items-center justify-between px-1 w-full">
                    <div class="flex items-center gap-2">
                        <div id="connection-ping-indicator" class="w-2 h-2 bg-red-500 rounded-full shadow-[0_0_8px_#ef4444]"></div>
                        <span class="text-[9px] font-black tracking-widest text-gray-400 uppercase">Termux Network Interface</span>
                    </div>
                    <span class="text-[9px] font-mono text-red-400 font-bold uppercase" id="console-status-label">Desconectado</span>
                </div>

                <div class="flex gap-1.5 items-center w-full mt-1">
                    <div class="relative flex-grow h-10 bg-black/60 border border-white/10 rounded-xl focus-within:border-cyan-400 overflow-hidden shadow-inner">
                        <i class="fa-solid fa-network-wired absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-600 text-[10px]"></i>
                        <input type="text" id="ps-ip-full-input" value="192.168." placeholder="192.168.xxx.xxx" class="w-full h-full bg-transparent pl-8 pr-1 text-[13px] font-mono font-bold tracking-wider text-white outline-none">
                    </div>

                    <div class="flex items-center w-[3.5rem] bg-black/60 border border-white/10 rounded-xl h-10 overflow-hidden focus-within:border-cyan-400 shrink-0">
                        <input type="number" id="ps-port-input" value="2121" class="w-full h-full bg-transparent text-[11px] font-mono font-bold text-gray-300 outline-none text-center [appearance:textfield]">
                    </div>

                    <button onclick="conectarIPManualValidando()" class="w-10 h-10 rounded-xl bg-cyan-600/10 border border-cyan-500/20 flex items-center justify-center text-cyan-400 active:scale-95 transition-all shrink-0 hover:bg-cyan-600/20">
                        <i class="fa-solid fa-link text-[11px]"></i>
                    </button>

                    <button onclick="lanzarRadarVentanaEmergente()" class="h-10 px-3 rounded-xl bg-cyan-600/20 border border-cyan-500/30 flex items-center gap-1.5 text-cyan-400 font-bold text-[10px] tracking-wider active:scale-95 transition-all shrink-0 hover:bg-cyan-600/30">
                        <i class="fa-solid fa-satellite-dish" id="radar-icon-loop"></i> Radar
                    </button>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-3.5 max-w-sm mx-auto w-full my-auto z-10 shrink-0 px-1">
            <div onclick="abrirModulo('biblioteca')" class="launcher-card p-5 rounded-[2rem] flex flex-col items-center justify-center cursor-pointer aspect-square"><div class="w-12 h-12 rounded-[1rem] bg-cyan-900/30 text-cyan-400 flex items-center justify-center mb-3 border border-cyan-500/10"><i class="fa-solid fa-gamepad text-xl"></i></div><span class="text-xs font-black tracking-wider uppercase text-white">Biblioteca</span><span class="text-[8px] font-bold tracking-widest text-gray-500 uppercase mt-1">Juegos e Iconos</span></div>
            <div onclick="abrirModulo('explorador')" class="launcher-card p-5 rounded-[2rem] flex flex-col items-center justify-center cursor-pointer aspect-square"><div class="w-12 h-12 rounded-[1rem] bg-emerald-900/30 text-emerald-400 flex items-center justify-center mb-3 border border-emerald-500/10"><i class="fa-solid fa-folder-open text-xl"></i></div><span class="text-xs font-black tracking-wider uppercase text-white">Explorador FTP</span><span class="text-[8px] font-bold tracking-widest text-gray-500 uppercase mt-1">Raíz Consola</span></div>
            <div onclick="abrirModulo('modding')" class="launcher-card p-5 rounded-[2rem] flex flex-col items-center justify-center cursor-pointer aspect-square"><div class="w-12 h-12 rounded-[1rem] bg-purple-900/30 text-purple-400 flex items-center justify-center mb-3 border border-purple-500/10"><i class="fa-solid fa-wand-magic-sparkles text-xl"></i></div><span class="text-xs font-black tracking-wider uppercase text-white">Modding</span><span class="text-[8px] font-bold tracking-widest text-gray-500 uppercase mt-1">Inyectar Portadas</span></div>
            <div onclick="abrirModulo('transferir')" class="launcher-card p-5 rounded-[2rem] flex flex-col items-center justify-center cursor-pointer aspect-square"><div class="w-12 h-12 rounded-[1rem] bg-amber-900/30 text-amber-400 flex items-center justify-center mb-3 border border-amber-500/10"><i class="fa-solid fa-cloud-arrow-up text-xl"></i></div><span class="text-xs font-black tracking-wider uppercase text-white">Transferencias</span><span class="text-[8px] font-bold tracking-widest text-gray-500 uppercase mt-1">Chunks de PKG</span></div>
            <div onclick="abrirModuloNativo('mods')" class="launcher-card p-5 rounded-[2rem] flex flex-col items-center justify-center cursor-pointer aspect-square"><div class="w-12 h-12 rounded-[1rem] bg-indigo-900/30 text-indigo-400 flex items-center justify-center mb-3 border border-indigo-500/10"><i class="fa-solid fa-cubes text-xl"></i></div><span class="text-xs font-black tracking-wider uppercase text-white">Game Mods</span><span class="text-[8px] font-bold tracking-widest text-gray-500 uppercase mt-1">Trucos y Parches</span></div>
            <div onclick="abrirModulo('ajustes')" class="launcher-card p-5 rounded-[2rem] flex flex-col items-center justify-center cursor-pointer aspect-square"><div class="w-12 h-12 rounded-[1rem] bg-gray-700/30 text-gray-300 flex items-center justify-center mb-3 border border-gray-500/10"><i class="fa-solid fa-sliders text-xl"></i></div><span class="text-xs font-black tracking-wider uppercase text-white">Ajustes</span><span class="text-[8px] font-bold tracking-widest text-gray-500 uppercase mt-1">Live BGs e Intros</span></div>
        </div>

        <div class="w-full text-center pt-2 pb-1 shrink-0 opacity-40 text-[9px] tracking-widest font-mono uppercase">SANTIAGO DEL ESTERO • ARGENTINA</div>
    </div>

    <div id="modal-radar-emergente" class="fixed inset-0 z-[150] bg-black/90 backdrop-blur-sm hidden flex items-center justify-center p-6 opacity-0 transition-opacity duration-300">
        <div id="radar-caja" class="glass-premium w-full max-w-sm rounded-[2rem] p-6 shadow-[0_0_60px_rgba(16,185,129,0.15)] flex flex-col modal-pop scale-90 border-emerald-500/30">
            
            <div class="flex justify-between items-center mb-4 border-b border-emerald-900/50 pb-3">
                <div class="flex items-center gap-3">
                    <i class="fa-solid fa-satellite-dish text-emerald-400 animate-pulse text-lg"></i>
                    <div>
                        <h3 class="text-xs font-black tracking-widest uppercase text-white">NET RADAR 💀</h3>
                        <p class="text-[9px] text-emerald-700 font-mono" id="radar-subnet-txt">ANALYZING.SUBNETS</p>
                    </div>
                </div>
                <button onclick="abortarYEstabilizarRadar()" class="w-8 h-8 rounded-full bg-emerald-950/30 flex items-center justify-center text-emerald-600 active:bg-red-900/50 active:text-red-500 transition-colors">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>

            <div class="relative bg-black rounded-xl p-3 h-48 overflow-hidden font-mono text-[9px] border border-emerald-900/50 shadow-inner">
                <div class="scanner-line"></div>
                <div id="radar-log-terminal" class="absolute inset-0 p-3 overflow-y-auto custom-scrollbar flex flex-col gap-1 text-emerald-500 z-10"></div>
            </div>
            
            <div class="mt-4 text-center text-[8px] font-mono tracking-widest text-emerald-800 uppercase flex items-center justify-center gap-2">
                <div class="w-1.5 h-1.5 bg-emerald-600 rounded-full animate-ping"></div>
                Termux Core Brute-Force Radar
            </div>
        </div>
    </div>

    <?php include 'modulos/biblioteca.php'; ?>
    <?php include 'modulos/modding.php'; ?>
    <?php include 'modulos/explorador.php'; ?>
    <?php include 'modulos/transferir.php'; ?>
    <?php include 'modulos/mods.php'; ?>
    <?php include 'modulos/ajustes.php'; ?>

    <script src="js/app.js"></script>
    <script src="js/biblioteca.js"></script>
    <script src="js/modding.js"></script>
    <script src="js/explorador.js"></script>
    <script src="js/transferir.js"></script>
    <script src="js/mods.js"></script>
    <script src="js/afr_mods.js"></script>

    <script>
        history.replaceState({ page: 'launcher' }, "Launcher", "");

        window.abrirModuloNativo = function(idModulo) {
            history.pushState({ page: idModulo, ruta: '/' }, "Modulo", "");
            activarCapaVisual(idModulo);

            if (idModulo === 'explorador' && typeof cargarRutaFtp === 'function') {
                cargarRutaFtp('/', true); 
                if (typeof renderizarAccesosRapidos === 'function') renderizarAccesosRapidos();
            }
        };

        function activarCapaVisual(idModulo) {
            document.querySelectorAll('.app-layer').forEach(layer => {
                layer.classList.remove('active', 'flex');
                layer.classList.add('hidden');
            });
            
            const target = document.getElementById('layer-' + idModulo);
            if (target) {
                target.classList.remove('hidden');
                setTimeout(() => { target.classList.add('active', 'flex'); }, 10);
            }
        }

        window.addEventListener('popstate', function(event) {
            let modals = Array.from(document.querySelectorAll('.fixed.inset-0:not(.hidden)'));
            modals = modals.filter(m => m.style.display !== 'none' && m.id !== 'layer-launcher' && !m.classList.contains('app-layer') && m.id !== 'intro-wrapper');
            
            if (modals.length > 0) {
                history.pushState(event.state, "", ""); 
                let topModal = modals[modals.length - 1];
                let closeBtn = topModal.querySelector('[onclick*="cerrar"], [onclick*="cancelar"]');
                
                if (closeBtn) { closeBtn.click(); } else {
                    topModal.classList.remove('opacity-100');
                    setTimeout(() => topModal.classList.add('hidden'), 300);
                }
                return; 
            }

            if (event.state && event.state.page) {
                if (event.state.page === 'launcher') {
                    activarCapaVisual('launcher');
                } else if (event.state.page === 'explorador' || event.state.page === 'ftp_folder') {
                    activarCapaVisual('explorador');
                    let rutaDestino = event.state.ruta || '/';
                    if (typeof cargarRutaFtp === 'function') {
                        cargarRutaFtp(rutaDestino, true); 
                    }
                } else {
                    activarCapaVisual(event.state.page);
                }
            } else {
                activarCapaVisual('launcher');
            }
        });

        window.volverAlLauncher = function() {
            history.back(); 
        };
    </script>
</body>
</html>
