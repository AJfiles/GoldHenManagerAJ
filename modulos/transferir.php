<?php
/**
 * ====================================================================
 * GOLD HEN SUITE PRO 🚀 - MÓDULO TRANSFERENCIAS Y RPI
 * DEVELOPED By SeBaS - RUTA: modulos/transferir.php
 * ====================================================================
 */
?>
<div id="layer-transferir" class="app-layer flex flex-col p-4 h-[100dvh] w-full overflow-hidden bg-[#02040a] relative">
    
    <div class="w-full flex items-center justify-between z-30 shrink-0 pt-1 mb-4">
        <div class="flex items-center gap-3">
            <button onclick="volverAlLauncher()" class="w-10 h-10 rounded-xl bg-white/[0.02] border border-white/5 flex items-center justify-center active:scale-90 transition-all hover:bg-white/5">
                <i class="fa-solid fa-arrow-left text-gray-300"></i>
            </button>
            <div class="flex flex-col">
                <h2 class="text-[18px] font-black tracking-tighter uppercase text-white leading-none">Inyectores</h2>
                <span class="text-[9px] font-mono text-amber-400 tracking-widest mt-0.5">Gestor de Carga Pesada</span>
            </div>
        </div>
        <div class="w-10 h-10 rounded-xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-center text-amber-400 shadow-[0_0_15px_rgba(245,158,11,0.2)]">
            <i class="fa-solid fa-cloud-upload-alt text-lg"></i>
        </div>
    </div>

    <div class="w-full flex gap-2 p-1 rounded-2xl bg-[#0a0f1a] border border-white/5 shadow-inner mb-4 shrink-0">
        <button onclick="cambiarModoTransferencia('rpi')" id="tab-trans-rpi" class="flex-1 py-2.5 rounded-xl bg-amber-500/20 text-amber-400 border border-amber-500/30 text-[10px] font-black tracking-widest uppercase transition-all shadow-md">
            <i class="fa-solid fa-gamepad mr-1"></i> RPI (Instalar)
        </button>
        <button onclick="cambiarModoTransferencia('ftp')" id="tab-trans-ftp" class="flex-1 py-2.5 rounded-xl bg-transparent text-gray-500 border border-transparent text-[10px] font-black tracking-widest uppercase transition-all hover:text-white">
            <i class="fa-solid fa-sitemap mr-1"></i> FTP (Manual)
        </button>
    </div>

    <div id="vista-trans-rpi" class="flex-1 flex flex-col gap-4 overflow-y-auto hide-scrollbar pb-10">
        
        <div class="w-full bg-blue-900/20 border border-blue-500/30 rounded-xl p-3 flex gap-3 shrink-0">
            <i class="fa-solid fa-info-circle text-blue-400 mt-0.5"></i>
            <p class="text-[9px] text-blue-200 font-mono leading-relaxed">
                Mueve tus PKG a la carpeta <b class="text-white">"user/pkgs_rpi"</b> dentro de los archivos de SeBaS OS. Abre el <b class="text-white">Package Installer</b> en la PS4 y toca el botón de descargar aquí.
            </p>
        </div>

        <div class="w-full bg-[#0a0f1a] rounded-[1.5rem] p-4 border border-white/5 shadow-md flex flex-col gap-2 shrink-0">
            <label class="text-[9px] font-black uppercase tracking-widest text-gray-400 flex items-center justify-between">
                IP DE ESTE CELULAR (TERMUX)
                <button onclick="detectarIPCelular()" class="text-cyan-400 hover:text-white active:scale-95"><i class="fa-solid fa-sync"></i> Autodetectar</button>
            </label>
            <div class="flex items-center bg-black/40 border border-white/10 rounded-xl px-3 py-2.5 focus-within:border-cyan-500/50">
                <span class="text-cyan-500 mr-2 text-[12px]"><i class="fa-solid fa-wifi"></i></span>
                <input type="text" id="rpi-phone-ip" placeholder="Detectando IP..." class="w-full bg-transparent text-[12px] font-mono text-white outline-none">
            </div>
        </div>

        <div class="flex items-center justify-between mt-2 px-1 shrink-0">
            <span class="text-[10px] font-black tracking-widest uppercase text-gray-400">Juegos en Carpeta Local:</span>
            <button onclick="escanearCarpetaRPI()" class="text-[10px] text-cyan-400 font-bold uppercase active:scale-95"><i class="fa-solid fa-sync-alt mr-1"></i> Recargar</button>
        </div>

        <div id="rpi-list-container" class="flex flex-col gap-2 flex-1 overflow-y-auto custom-scrollbar">
            <div class="w-full p-6 text-center border-2 border-dashed border-white/5 rounded-xl opacity-50">
                <span class="text-[9px] uppercase font-bold tracking-widest text-gray-500">Buscando PKGs...</span>
            </div>
        </div>
    </div>

    <div id="vista-trans-ftp" class="hidden flex-1 flex flex-col gap-4 overflow-y-auto hide-scrollbar pb-10">
        
        <div class="w-full bg-purple-900/20 border border-purple-500/30 rounded-xl p-3 flex gap-3 shrink-0 mb-1">
            <i class="fa-solid fa-image text-purple-400 mt-0.5"></i>
            <p class="text-[9px] text-purple-200 font-mono leading-relaxed">
                ¿Buscas editar juegos? Guarda tus imágenes en la carpeta <b class="text-white">"user/portadas_custom"</b> para usarlas directamente desde el panel de Modding.
            </p>
        </div>

        <div id="transfer-dropzone" onclick="document.getElementById('input-archivo-pesado').click()" class="w-full h-28 rounded-[2rem] border-2 border-dashed border-white/10 bg-[#0a0f1a] flex flex-col items-center justify-center cursor-pointer hover:border-amber-500/40 hover:bg-amber-500/5 transition-all active:scale-[0.98] shrink-0">
            <div class="w-10 h-10 rounded-full bg-amber-500/10 text-amber-500 flex items-center justify-center text-xl mb-1 shadow-inner"><i class="fa-solid fa-file-upload"></i></div>
            <span class="text-[11px] font-black uppercase text-white tracking-widest text-center px-4 truncate w-full" id="transfer-filename">Elegir Archivos...</span>
            <span class="text-[8px] font-mono text-gray-500 mt-1 uppercase" id="transfer-queue-status">Cola de Envío Vacía</span>
        </div>
        <input type="file" id="input-archivo-pesado" multiple class="hidden" onchange="prepararArchivoTransferencia(event)">

        <div class="w-full bg-[#0a0f1a] rounded-[1.5rem] p-4 border border-white/5 shadow-md flex flex-col gap-2 shrink-0">
            <label class="text-[9px] font-black uppercase tracking-widest text-gray-400">Ruta Destino FTP:</label>
            <div class="flex items-center gap-2">
                <div class="flex-1 flex items-center bg-black/40 border border-white/10 rounded-xl px-3 py-2.5">
                    <span class="text-amber-500 mr-2"><i class="fa-solid fa-folder-open"></i></span>
                    <input type="text" id="transfer-target-path" value="/data/" placeholder="/data/" class="w-full bg-transparent text-[11px] font-mono text-white outline-none">
                </div>
                <button onclick="abrirExploradorFTP()" class="w-11 h-11 rounded-xl bg-amber-500/10 text-amber-500 border border-amber-500/30 flex items-center justify-center active:scale-90 transition-all hover:bg-amber-500/20 shadow-inner">
                    <i class="fa-solid fa-sitemap"></i>
                </button>
            </div>
        </div>

        <div class="w-full bg-[#0a0f1a] rounded-[2rem] p-5 border border-white/5 shadow-xl relative shrink-0">
            <div class="flex justify-between items-center mb-3">
                <span class="text-[10px] font-black uppercase tracking-widest text-gray-400">Progreso</span>
                <span class="text-[12px] font-black text-amber-400 font-mono" id="transfer-percent">0%</span>
            </div>
            <div class="w-full h-2.5 bg-black rounded-full overflow-hidden mb-4 border border-white/5">
                <div id="transfer-bar" class="h-full bg-gradient-to-r from-amber-600 to-yellow-400 w-0 transition-all"></div>
            </div>
            <div class="grid grid-cols-2 gap-3 text-center">
                <div class="flex flex-col bg-black/40 p-2 rounded-xl border border-white/5">
                    <span class="text-[8px] font-bold text-gray-500 uppercase">Velocidad</span>
                    <span class="text-[11px] font-mono text-white" id="transfer-speed">0.00 MB/s</span>
                </div>
                <div class="flex flex-col bg-black/40 p-2 rounded-xl border border-white/5">
                    <span class="text-[8px] font-bold text-gray-500 uppercase">Restante</span>
                    <span class="text-[11px] font-mono text-white" id="transfer-eta">--:--</span>
                </div>
            </div>
            <div class="flex justify-between items-center mt-3 px-1">
                <span class="text-[8px] font-black tracking-widest text-gray-500 uppercase">Enviado: <span id="transfer-sent" class="text-white ml-1 font-mono">0 B</span></span>
                <span class="text-[8px] font-black tracking-widest text-gray-500 uppercase">Total: <span id="transfer-total" class="text-white ml-1 font-mono">0 B</span></span>
            </div>
        </div>

        <div class="flex gap-3 mt-auto shrink-0 mb-2">
            <button id="btn-iniciar-transferencia" onclick="iniciarGestorDeCola()" disabled class="flex-1 py-4 rounded-[1.5rem] bg-gray-800 text-gray-500 text-[12px] font-black uppercase tracking-widest transition-all flex items-center justify-center gap-3 pointer-events-none">
                <i class="fa-solid fa-rocket"></i> Enviar por FTP
            </button>
            <button id="btn-abortar-transferencia" onclick="abortarTransferenciaActiva()" class="hidden w-16 h-[56px] rounded-[1.5rem] border border-red-500/30 bg-red-500/10 text-red-500 flex items-center justify-center text-xl active:scale-95 transition-all hover:bg-red-500/20">
                <i class="fa-solid fa-times"></i>
            </button>
        </div>
    </div>
</div>

<div id="modal-explorador-ftp" class="fixed inset-0 z-[990] bg-black/90 backdrop-blur-md hidden flex items-center justify-center p-4 opacity-0 transition-opacity duration-300">
    <div id="modal-content-explorador" class="w-full max-w-sm rounded-[2rem] border border-white/10 bg-[#0a0f1a] p-5 shadow-2xl flex flex-col h-[75vh] transform translate-y-full transition-transform duration-300">
        
        <div class="flex items-center justify-between mb-4 shrink-0">
            <h3 class="text-[13px] font-black tracking-widest text-amber-400 uppercase"><i class="fa-solid fa-network-wired mr-2"></i> Elegir Destino</h3>
            <button onclick="cerrarExploradorFTP()" class="w-8 h-8 rounded-full bg-white/5 text-gray-400 flex items-center justify-center hover:text-white active:scale-90 transition-all"><i class="fa-solid fa-times"></i></button>
        </div>
        
        <div class="flex items-center bg-[#111827] border border-white/5 rounded-xl px-3 py-2.5 mb-3 shrink-0">
            <span class="text-amber-500 mr-2 text-[12px]"><i class="fa-solid fa-map-marker-alt"></i></span>
            <span id="explorador-ruta-actual" class="text-[11px] font-mono text-gray-200 w-full overflow-hidden" style="direction: rtl; text-align: left;">/data/</span>
        </div>

        <div id="explorador-lista" class="flex-1 overflow-y-auto custom-scrollbar flex flex-col gap-1.5 mb-4 bg-[#060913] rounded-xl p-2 border border-white/5 shadow-inner">
            </div>

        <button onclick="confirmarRutaFTP()" class="w-full py-4 rounded-xl bg-amber-600 text-[11px] font-black tracking-widest uppercase text-black active:scale-95 transition-all shadow-[0_0_15px_rgba(245,158,11,0.4)] shrink-0">
            <i class="fa-solid fa-check mr-1"></i> Fijar Esta Carpeta
        </button>
    </div>
</div>

<div id="modal-colision-ftp" class="fixed inset-0 z-[990] bg-black/90 backdrop-blur-md hidden flex items-center justify-center p-4 opacity-0 transition-opacity duration-300">
    <div class="w-full max-w-sm rounded-[2rem] border-2 border-amber-500/50 bg-[#0a0f1a] p-6 shadow-2xl flex flex-col items-center">
        <div class="w-16 h-16 rounded-full bg-amber-500/20 text-amber-500 flex items-center justify-center text-3xl mb-4 shadow-[0_0_15px_rgba(245,158,11,0.3)]">
            <i class="fa-solid fa-clone"></i>
        </div>
        <h3 class="text-[15px] font-black tracking-widest text-white uppercase text-center mb-2">Archivo Existente</h3>
        <p class="text-[10px] text-gray-400 text-center font-mono mb-4 px-2 leading-relaxed">
            El archivo <b class="text-amber-400" id="colision-filename">nombre.pkg</b> ya existe en la consola. ¿Qué deseas hacer con él?
        </p>
        <input type="text" id="colision-input-rename" class="w-full bg-[#111827] border border-white/10 rounded-xl px-4 py-3 mb-5 text-[11px] font-bold text-white outline-none focus:border-amber-500/50 transition-colors" placeholder="Nuevo nombre...">
        <div class="flex flex-col gap-2 w-full">
            <button onclick="accionColision('renombrar')" class="w-full py-3.5 rounded-xl bg-amber-600 text-[10px] font-black tracking-widest uppercase text-black active:scale-95 transition-all shadow-[0_0_15px_rgba(245,158,11,0.4)]">
                <i class="fa-solid fa-edit mr-1"></i> Renombrar y Subir
            </button>
            <button onclick="accionColision('reemplazar')" class="w-full py-3.5 rounded-xl border border-red-500/30 bg-red-500/10 text-[10px] font-black tracking-widest uppercase text-red-400 active:scale-95 transition-all hover:bg-red-500/20">
                <i class="fa-solid fa-exclamation-triangle mr-1"></i> Sobrescribir Original
            </button>
            <button onclick="accionColision('omitir')" class="w-full py-3.5 rounded-xl border border-white/10 bg-white/5 text-[10px] font-black tracking-widest uppercase text-gray-300 active:scale-95 transition-all">
                <i class="fa-solid fa-step-forward mr-1"></i> Omitir Archivo
            </button>
        </div>
    </div>
</div>
