<?php
/**
 * ====================================================================
 * GOLDHEN MANAGER AJ 🚀 (PS4) - EDICIÓN MODULAR TERMUX
 * Mantenido por AJ · Basado en proyecto original de SeBaS
 * RUTA: index.php
 * ====================================================================
 */
error_reporting(0);
@ini_set('display_errors', 0);
@ini_set('memory_limit', '512M'); 

header('Content-Type: text/html; charset=utf-8');
// Firma original de SeBaS + mención de modificación
$firma = chr(83).chr(101).chr(66).chr(97).chr(83) . " (Mod AJ)"; 
header('X-Author: ' . $firma);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>GoldHen Manager AJ | PS4 Tool</title>

    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#060913">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="GoldHen AJ">
    <link rel="apple-touch-icon" href="/js/icon-192.png">

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700;900&display=swap" rel="stylesheet">
    
    <style>
        html { background-color: #060913; }
        body { font-family: var(--app-font, 'Outfit'), sans-serif; background-color: transparent !important; overflow: hidden; height: 100dvh; }
        body :not(i):not(svg):not([class*="fa-"]) { font-family: var(--app-font, 'Outfit'), sans-serif !important; }
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
        .launcher-card > span { width:100%; text-align:center; line-height:1.35; }
        #launcher-grid.launcher-vertical { grid-template-columns:minmax(0,1fr); max-width:32rem; }
        #launcher-grid.launcher-vertical .launcher-card { aspect-ratio:auto; min-height:74px; display:grid; grid-template-columns:3rem minmax(0,1fr); grid-template-rows:auto auto; justify-items:start; align-content:center; padding:.8rem 1rem; }
        #launcher-grid.launcher-vertical .launcher-card > div:first-child { grid-row:1 / span 2; width:2.65rem;height:2.65rem;margin:0 .7rem 0 0; }
        #launcher-grid.launcher-vertical .launcher-card > span { width:auto;text-align:left;justify-self:stretch; }
        body.layout-desktop #layer-launcher { padding:2rem clamp(2rem,5vw,6rem); }
        body.layout-desktop #layer-launcher > .w-full.max-w-4xl { max-width:1100px; }
        body.layout-desktop #launcher-grid { grid-template-columns:repeat(4,minmax(0,1fr)); max-width:1100px; gap:1.2rem; }
        body.layout-desktop #launcher-grid .launcher-card { aspect-ratio:1.45/1; }
        body.layout-desktop #launcher-grid.launcher-vertical { grid-template-columns:minmax(0,1fr); max-width:760px; }
        body.layout-desktop #launcher-grid.launcher-vertical .launcher-card { aspect-ratio:auto; }
        body.layout-desktop .app-layer:not(#layer-launcher) { padding-left:max(24px,calc((100vw - 1120px)/2)) !important; padding-right:max(24px,calc((100vw - 1120px)/2)) !important; }
        @media (max-width:1050px) { body.layout-desktop #launcher-grid { grid-template-columns:repeat(3,minmax(0,1fr)); } }
        @media (max-width:760px) { body.layout-desktop #launcher-grid { grid-template-columns:repeat(2,minmax(0,1fr)); } }

        .app-layer .bg-\[\#0a0f1a\], 
        .app-layer .bg-\[\#02040a\], 
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

        .bg-black\/80, .bg-black\/90 {
            background-color: rgba(0, 0, 0, 0.85) !important;
        }

        .scanner-line { width: 100%; height: 2px; background: #10b981; position: absolute; left: 0; top: 0; box-shadow: 0 0 15px #10b981, 0 0 5px #10b981; opacity: 0.8; animation: scan 1.5s linear infinite; z-index: 20; }
        @keyframes scan { 0% { top: 0%; opacity: 0; } 10% { opacity: 1; } 90% { opacity: 1; } 100% { top: 100%; opacity: 0; } }
        .radar-galaxy { position:relative;height:100px;overflow:hidden;border-radius:16px;background:radial-gradient(ellipse at 50% 65%,rgba(16,185,129,.2),rgba(5,10,20,.94) 70%); }
        .radar-orbit { position:absolute;left:50%;top:50%;width:82px;height:82px;border:1px solid rgba(52,211,153,.38);border-radius:50%;transform:translate(-50%,-50%) rotateX(66deg);animation:radar-spin 5s linear infinite; }
        .radar-orbit:nth-child(2){width:126px;height:126px;animation-duration:8s;border-color:rgba(34,211,238,.23)}
        .radar-sun { position:absolute;left:50%;top:50%;width:16px;height:16px;border-radius:50%;transform:translate(-50%,-50%);background:#a7f3d0;box-shadow:0 0 22px #34d399; }
        .radar-planet { position:absolute;left:calc(50% + 40px);top:50%;width:11px;height:11px;border-radius:50%;background:#22d3ee;box-shadow:0 0 15px #22d3ee;animation:radar-planet 5s linear infinite; }
        .radar-galaxy.found .radar-planet { background:#fbbf24;box-shadow:0 0 22px #fbbf24; }
        @keyframes radar-spin { to { transform:translate(-50%,-50%) rotateX(66deg) rotateZ(360deg); } }
        @keyframes radar-planet { to { transform:rotate(360deg) translateX(40px) rotate(-360deg); } }

        .hide-scrollbar::-webkit-scrollbar { display: none !important; }
        .hide-scrollbar { -ms-overflow-style: none !important; scrollbar-width: none !important; }
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.2); border-radius: 4px; }

        html[data-theme="light"] { background: #e8edf5; color: #101827; }
        html[data-theme="light"] body { color: #101827; }
        html[data-theme="light"] .app-layer,
        html[data-theme="light"] .glass-premium,
        html[data-theme="light"] .app-layer .bg-\[\#0a0f1a\],
        html[data-theme="light"] .app-layer .bg-\[\#02040a\] { background-color: rgba(237, 242, 247, .94) !important; }
        html[data-theme="light"] .app-layer .text-white { color: #101827 !important; }
        html[data-theme="light"] .app-layer .text-gray-200,
        html[data-theme="light"] .app-layer .text-gray-300,
        html[data-theme="light"] .app-layer .text-gray-400 { color: #4b5563 !important; }
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
                <span class="text-[8px] font-black tracking-[0.25em] text-gray-400 uppercase">GoldHen Manager AJ</span>
                <i class="fa-solid fa-gamepad text-cyan-400 text-[10px] opacity-70"></i>
            </div>
            <h1 class="text-4xl font-black tracking-tighter text-transparent bg-clip-text bg-gradient-to-b from-[#ffffff] to-[#9aa0ab] uppercase leading-none drop-shadow-md">
                GoldHen Manager
            </h1>
            <div class="flex items-center justify-center gap-3 mt-2.5 opacity-80">
                <span class="h-[1px] w-6 bg-gradient-to-r from-transparent to-cyan-500/80"></span>
                <span class="text-[9px] font-mono tracking-[0.4em] font-bold text-cyan-400">VERSION 4.0 • AJ</span>
                <span class="h-[1px] w-6 bg-gradient-to-l from-transparent to-cyan-500/80"></span>
            </div>
        </div>

        <div class="w-full max-w-4xl mx-auto z-20 mt-4 shrink-0">
            <button onclick="abrirBusquedaGlobal()" class="mb-3 flex w-full items-center gap-3 rounded-2xl border border-violet-500/20 bg-violet-500/5 px-4 py-3 text-left active:scale-[.99] transition-all hover:bg-violet-500/10">
                <i class="fa-solid fa-magnifying-glass text-violet-300"></i><span class="flex-1 text-[10px] font-bold tracking-wide text-violet-100">Buscar en GoldHen Manager…</span><span class="text-[8px] uppercase tracking-widest text-violet-300/70">Global</span>
            </button>
            <div class="glass-premium p-4 rounded-[2rem] flex flex-col gap-3 shadow-2xl">
                
                <div class="flex items-center justify-between px-1 w-full">
                    <div class="flex items-center gap-2">
                        <div id="connection-ping-indicator" class="w-2 h-2 bg-red-500 rounded-full shadow-[0_0_8px_#ef4444]"></div>
                        <span class="text-[9px] font-black tracking-widest text-gray-400 uppercase">Centro de conexión PS4</span>
                    </div>
                    <span class="text-[9px] font-mono text-red-400 font-bold uppercase" id="console-status-label">Desconectado</span>
                </div>

                <div class="flex flex-col gap-2.5 w-full mt-1">
                    <div class="flex gap-2 items-center w-full">
                    <div class="relative flex-grow h-11 bg-black/60 border border-white/10 rounded-xl focus-within:border-cyan-400 overflow-hidden shadow-inner">
                        <i class="fa-solid fa-network-wired absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-600 text-[10px]"></i>
                        <input type="text" id="ps-ip-full-input" value="192.168." placeholder="192.168.xxx.xxx" class="w-full h-full bg-transparent pl-8 pr-1 text-[13px] font-mono font-bold tracking-wider text-white outline-none">
                    </div>

                    <div class="flex items-center w-24 bg-black/60 border border-white/10 rounded-xl h-11 overflow-hidden focus-within:border-cyan-400 shrink-0">
                        <input type="number" id="ps-port-input" value="2121" class="w-full h-full bg-transparent text-[11px] font-mono font-bold text-gray-300 outline-none text-center [appearance:textfield]">
                    </div>
                    </div>
                    <div class="grid grid-cols-2 gap-2 w-full">
                        <button onclick="conectarIPManualValidando()" class="h-10 rounded-xl bg-cyan-600/10 border border-cyan-500/20 flex items-center justify-center gap-2 text-cyan-300 active:scale-95 transition-all hover:bg-cyan-600/20 text-[10px] font-black uppercase tracking-widest"><i class="fa-solid fa-link text-[11px]"></i> Conectar</button>
                        <button onclick="lanzarRadarVentanaEmergente()" class="h-10 rounded-xl bg-cyan-600/20 border border-cyan-500/30 flex items-center justify-center gap-2 text-cyan-300 font-bold text-[10px] tracking-wider active:scale-95 transition-all hover:bg-cyan-600/30"><i class="fa-solid fa-satellite-dish" id="radar-icon-loop"></i> Radar de red</button>
                    </div>
                </div>
            </div>
        </div>

        <div id="launcher-grid" class="grid grid-cols-2 gap-3.5 max-w-sm mx-auto w-full my-auto z-10 shrink-0 px-1">
            <div onclick="abrirModulo('biblioteca')" class="launcher-card p-5 rounded-[2rem] flex flex-col items-center justify-center cursor-pointer aspect-square"><div class="w-12 h-12 rounded-[1rem] bg-cyan-900/30 text-cyan-400 flex items-center justify-center mb-3 border border-cyan-500/10"><i class="fa-solid fa-gamepad text-xl"></i></div><span class="text-xs font-black tracking-wider uppercase text-white">Biblioteca</span><span class="text-[8px] font-bold tracking-widest text-gray-500 uppercase mt-1">Juegos e Iconos</span></div>
            <div onclick="abrirModulo('explorador')" class="launcher-card p-5 rounded-[2rem] flex flex-col items-center justify-center cursor-pointer aspect-square"><div class="w-12 h-12 rounded-[1rem] bg-emerald-900/30 text-emerald-400 flex items-center justify-center mb-3 border border-emerald-500/10"><i class="fa-solid fa-folder-open text-xl"></i></div><span class="text-xs font-black tracking-wider uppercase text-white">Explorador FTP</span><span class="text-[8px] font-bold tracking-widest text-gray-500 uppercase mt-1">Raíz Consola</span></div>
            <div onclick="abrirModulo('modding')" class="launcher-card p-5 rounded-[2rem] flex flex-col items-center justify-center cursor-pointer aspect-square"><div class="w-12 h-12 rounded-[1rem] bg-purple-900/30 text-purple-400 flex items-center justify-center mb-3 border border-purple-500/10"><i class="fa-solid fa-wand-magic-sparkles text-xl"></i></div><span class="text-xs font-black tracking-wider uppercase text-white">Modding</span><span class="text-[8px] font-bold tracking-widest text-gray-500 uppercase mt-1">Inyectar Portadas</span></div>
            <div onclick="abrirModulo('transferir')" class="launcher-card p-5 rounded-[2rem] flex flex-col items-center justify-center cursor-pointer aspect-square"><div class="w-12 h-12 rounded-[1rem] bg-amber-900/30 text-amber-400 flex items-center justify-center mb-3 border border-amber-500/10"><i class="fa-solid fa-cloud-arrow-up text-xl"></i></div><span class="text-xs font-black tracking-wider uppercase text-white">Transferencias</span><span class="text-[8px] font-bold tracking-widest text-gray-500 uppercase mt-1">Chunks de PKG</span></div>
            <div onclick="abrirModuloNativo('mods')" class="launcher-card p-5 rounded-[2rem] flex flex-col items-center justify-center cursor-pointer aspect-square"><div class="w-12 h-12 rounded-[1rem] bg-indigo-900/30 text-indigo-400 flex items-center justify-center mb-3 border border-indigo-500/10"><i class="fa-solid fa-cubes text-xl"></i></div><span class="text-xs font-black tracking-wider uppercase text-white">Game Mods</span><span class="text-[8px] font-bold tracking-widest text-gray-500 uppercase mt-1">Trucos y Parches</span></div>
            <div onclick="abrirModulo('plugins')" class="launcher-card p-5 rounded-[2rem] flex flex-col items-center justify-center cursor-pointer aspect-square"><div class="w-12 h-12 rounded-[1rem] bg-violet-900/30 text-violet-400 flex items-center justify-center mb-3 border border-violet-500/10"><i class="fa-solid fa-plug text-xl"></i></div><span class="text-xs font-black tracking-wider uppercase text-white">Plugins</span><span class="text-[8px] font-bold tracking-widest text-gray-500 uppercase mt-1">GoldHEN Loader</span></div>
            <div onclick="abrirModulo('payloads')" class="launcher-card p-5 rounded-[2rem] flex flex-col items-center justify-center cursor-pointer aspect-square"><div class="w-12 h-12 rounded-[1rem] bg-amber-900/30 text-amber-400 flex items-center justify-center mb-3 border border-amber-500/10"><i class="fa-solid fa-rocket text-xl"></i></div><span class="text-xs font-black tracking-wider uppercase text-white">Payloads</span><span class="text-[8px] font-bold tracking-widest text-gray-500 uppercase mt-1">BinLoader 9090</span></div>
            <div onclick="abrirModulo('coolers')" class="launcher-card p-5 rounded-[2rem] flex flex-col items-center justify-center cursor-pointer aspect-square"><div class="w-12 h-12 rounded-[1rem] bg-cyan-900/30 text-cyan-300 flex items-center justify-center mb-3 border border-cyan-500/10"><i class="fa-solid fa-fan text-xl"></i></div><span class="text-xs font-black tracking-wider uppercase text-white">Ventilador</span><span class="text-[8px] font-bold tracking-widest text-gray-500 uppercase mt-1">Control de temperatura</span></div>
            <div onclick="abrirModulo('actualizaciones-fw')" class="launcher-card p-5 rounded-[2rem] flex flex-col items-center justify-center cursor-pointer aspect-square"><div class="w-12 h-12 rounded-[1rem] bg-rose-900/30 text-rose-300 flex items-center justify-center mb-3 border border-rose-500/10"><i class="fa-solid fa-shield-halved text-xl"></i></div><span class="text-xs font-black tracking-wider uppercase text-white">Actualizaciones FW</span><span class="text-[8px] font-bold tracking-widest text-gray-500 uppercase mt-1">Permitir o bloquear</span></div>
            <div onclick="abrirModulo('backup')" class="launcher-card p-5 rounded-[2rem] flex flex-col items-center justify-center cursor-pointer aspect-square"><div class="w-12 h-12 rounded-[1rem] bg-emerald-900/30 text-emerald-300 flex items-center justify-center mb-3 border border-emerald-500/10"><i class="fa-solid fa-box-archive text-xl"></i></div><span class="text-xs font-black tracking-wider uppercase text-white">Respaldo</span><span class="text-[8px] font-bold tracking-widest text-gray-500 uppercase mt-1">Migrar Configuración</span></div>
            <div onclick="window.location.href='store/store.php'" class="launcher-card p-5 rounded-[2rem] flex flex-col items-center justify-center cursor-pointer aspect-square"><div class="w-12 h-12 rounded-[1rem] bg-cyan-900/30 text-cyan-300 flex items-center justify-center mb-3 border border-cyan-500/10"><i class="fa-solid fa-store text-xl"></i></div><span class="text-xs font-black tracking-wider uppercase text-white">Store</span><span class="text-[8px] font-bold tracking-widest text-gray-500 uppercase mt-1">Catálogo autorizado</span></div>
            <div onclick="abrirModulo('ajustes')" class="launcher-card p-5 rounded-[2rem] flex flex-col items-center justify-center cursor-pointer aspect-square"><div class="w-12 h-12 rounded-[1rem] bg-gray-700/30 text-gray-300 flex items-center justify-center mb-3 border border-gray-500/10"><i class="fa-solid fa-sliders text-xl"></i></div><span class="text-xs font-black tracking-wider uppercase text-white">Ajustes</span><span class="text-[8px] font-bold tracking-widest text-gray-500 uppercase mt-1">Live BGs e Intros</span></div>
        </div>

        <div class="w-full text-center pt-2 pb-1 shrink-0 text-[9px] tracking-widest font-mono uppercase text-cyan-300/70">GoldHen Manager AJ <span class="text-gray-500">· Base de SeBaS</span></div>
    </div>

    <div id="modal-busqueda-global" class="fixed inset-0 z-[10060] hidden items-start justify-center bg-black/75 backdrop-blur-sm p-4 pt-[12dvh]" onclick="if(event.target===this)cerrarBusquedaGlobal()">
        <div class="w-full max-w-xl rounded-3xl border border-violet-500/25 bg-[#0a0f1a] p-4 shadow-2xl">
            <div class="flex items-center gap-2"><i class="fa-solid fa-magnifying-glass text-violet-300"></i><input id="global-search-input" oninput="ejecutarBusquedaGlobal()" placeholder="Buscar módulos, juegos sincronizados, archivos actuales, plugins o payloads…" class="flex-1 bg-transparent py-3 text-[12px] text-white outline-none"><button onclick="cerrarBusquedaGlobal()" class="w-9 h-9 rounded-xl bg-white/5 text-gray-300"><i class="fa-solid fa-xmark"></i></button></div>
            <p class="border-t border-white/5 pt-2 text-[9px] text-gray-500">Los juegos requieren una Biblioteca sincronizada y los archivos corresponden a la carpeta FTP abierta.</p>
            <div id="global-search-results" class="mt-3 max-h-[60dvh] overflow-y-auto space-y-3"></div>
        </div>
    </div>

    <div id="modal-radar-emergente" class="fixed inset-0 z-[150] bg-black/90 backdrop-blur-sm hidden flex items-center justify-center p-6 opacity-0 transition-opacity duration-300">
        <div id="radar-caja" class="glass-premium w-full max-w-sm rounded-[2rem] p-6 shadow-[0_0_60px_rgba(16,185,129,0.15)] flex flex-col modal-pop scale-90 border-emerald-500/30">
            
            <div class="flex justify-between items-center mb-4 border-b border-emerald-900/50 pb-3">
                <div class="flex items-center gap-3">
                    <i class="fa-solid fa-satellite-dish text-emerald-400 animate-pulse text-lg"></i>
                    <div>
                        <h3 class="text-xs font-black tracking-widest uppercase text-white">RADAR DE RED</h3>
                        <p class="text-[9px] text-emerald-700 font-mono" id="radar-subnet-txt">ANALYZING.SUBNETS</p>
                    </div>
                </div>
                <button onclick="abortarYEstabilizarRadar()" class="w-8 h-8 rounded-full bg-emerald-950/30 flex items-center justify-center text-emerald-600 active:bg-red-900/50 active:text-red-500 transition-colors">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>

            <div id="radar-galaxy" class="radar-galaxy mb-3"><div class="radar-orbit"></div><div class="radar-orbit"></div><div class="radar-sun"></div><div class="radar-planet"></div><span class="absolute bottom-2 left-0 right-0 text-center text-[7px] tracking-[.35em] text-emerald-200/70">BUSCANDO PS4 EN LA RED</span></div>
            <div class="relative bg-black rounded-xl p-3 h-40 overflow-hidden font-mono text-[9px] border border-emerald-900/50 shadow-inner">
                <div class="scanner-line"></div>
                <div id="radar-log-terminal" class="absolute inset-0 p-3 overflow-y-auto custom-scrollbar flex flex-col gap-1 text-emerald-500 z-10"></div>
            </div>
            
            <div class="mt-4 text-center text-[8px] font-mono tracking-widest text-emerald-800 uppercase flex items-center justify-center gap-2">
                <div class="w-1.5 h-1.5 bg-emerald-600 rounded-full animate-ping"></div>
                ESCÁNER LAN · SERVICIOS GOLDHEN
            </div>
        </div>
    </div>

    <div id="modal-fw-risk" class="fixed inset-0 z-[20000] hidden items-center justify-center bg-black/85 p-5 backdrop-blur-md" role="dialog" aria-modal="true" aria-labelledby="fw-risk-title">
        <div class="w-full max-w-lg rounded-3xl border border-amber-400/30 bg-[#0a0f1a] p-6 shadow-2xl">
            <div class="mb-4 flex items-center gap-3"><i class="fa-solid fa-triangle-exclamation text-amber-400 text-xl"></i><h2 id="fw-risk-title" class="text-sm font-black uppercase tracking-widest text-amber-200">Advertencia de firmware</h2></div>
            <p class="text-sm leading-relaxed text-gray-200">Este módulo puede dañar tu consola si eliges el archivo que NO CORRESPONDA a tu versión de PS4. ¿Entiendes los riesgos y aceptas usarlo bajo tu responsabilidad?</p>
            <p class="mt-3 text-[10px] leading-relaxed text-gray-400">Verifica el firmware exacto antes de enviar cualquier payload. La aceptación se guardará en este navegador para no repetir el aviso en cada visita.</p>
            <div class="mt-6 grid grid-cols-2 gap-3"><button onclick="cancelarAccesoFirmware()" class="rounded-xl border border-white/10 bg-white/5 py-3 text-[10px] font-black uppercase text-gray-300">Cancelar</button><button onclick="aceptarAccesoFirmware()" class="rounded-xl bg-amber-400 py-3 text-[10px] font-black uppercase text-black">Entiendo y acepto</button></div>
        </div>
    </div>

    <?php include 'modulos/biblioteca.php'; ?>
    <?php include 'modulos/modding.php'; ?>
    <?php include 'modulos/explorador.php'; ?>
    <?php include 'modulos/transferir.php'; ?>
    <?php include 'modulos/mods.php'; ?>
    <?php include 'modulos/ajustes.php'; ?>
    <?php include 'modulos/plugins.php'; ?>
    <?php include 'modulos/payloads.php'; ?>
    <?php include 'modulos/coolers.php'; ?>
    <?php include 'modulos/actualizaciones_fw.php'; ?>
    <?php include 'modulos/backup.php'; ?>

    <script src="js/app.js"></script>
    <script src="js/biblioteca.js"></script>
    <script src="js/modding.js"></script>
    <script src="js/explorador.js"></script>
    <script src="js/transferir.js"></script>
    <script src="js/mods.js"></script>
    <script src="js/afr_mods.js"></script>
    <script src="js/plugins.js"></script>
    <script src="js/backup.js"></script>
    <script src="js/payloads.js"></script>
    <script src="js/coolers.js"></script>
    <script src="js/actualizaciones_fw.js"></script>

    <script>
        history.replaceState({ page: 'launcher' }, "Launcher", "");

        let moduloFirmwarePendiente = null;
        const aceptarRiesgosFirmware = { coolers: 'ghm_fw_risk_fan_accepted', 'actualizaciones-fw': 'ghm_fw_risk_updates_accepted' };

        window.abrirModuloNativo = function(idModulo) {
            const storageKey = aceptarRiesgosFirmware[idModulo];
            if (storageKey && localStorage.getItem(storageKey) !== 'true') {
                moduloFirmwarePendiente = idModulo;
                const warning = document.getElementById('modal-fw-risk');
                warning.classList.remove('hidden'); warning.classList.add('flex');
                return;
            }
            history.pushState({ page: idModulo, ruta: '/' }, "Modulo", "");
            activarCapaVisual(idModulo);

            if (idModulo === 'explorador' && typeof cargarRutaFtp === 'function') {
                cargarRutaFtp('/', true); 
                if (typeof renderizarAccesosRapidos === 'function') renderizarAccesosRapidos();
            }
        };

        window.aceptarAccesoFirmware = function() {
            if (!moduloFirmwarePendiente) return;
            const moduleId = moduloFirmwarePendiente;
            localStorage.setItem(aceptarRiesgosFirmware[moduleId], 'true');
            moduloFirmwarePendiente = null;
            const warning = document.getElementById('modal-fw-risk');
            warning.classList.add('hidden'); warning.classList.remove('flex');
            abrirModuloNativo(moduleId);
        };
        window.cancelarAccesoFirmware = function() {
            moduloFirmwarePendiente = null;
            const warning = document.getElementById('modal-fw-risk');
            warning.classList.add('hidden'); warning.classList.remove('flex');
        };

        function activarCapaVisual(idModulo) {
            document.querySelectorAll('.app-layer').forEach(layer => {
                layer.classList.remove('active', 'flex');
                layer.classList.add('hidden');
            });
            
            const target = document.getElementById('layer-' + idModulo);
            if (target) {
                target.classList.remove('hidden');
                setTimeout(() => { target.classList.add('active', 'flex');
                    if (idModulo === 'plugins' && typeof pluginsRecargar === 'function') pluginsRecargar();
                    if (idModulo === 'payloads' && typeof payloadsRecargar === 'function') payloadsRecargar();
                }, 10);
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
                    // El botón de volver del Explorador siempre regresa al launcher.
                    // La navegación entre directorios se controla con la flecha "arriba".
                    if (event.state.page === 'ftp_folder') {
                        history.replaceState({ page: 'launcher' }, 'Launcher', '');
                        activarCapaVisual('launcher');
                        return;
                    }
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
            history.replaceState({ page: 'launcher' }, 'Launcher', '');
            activarCapaVisual('launcher');
        };
    </script>
</body>
</html>
