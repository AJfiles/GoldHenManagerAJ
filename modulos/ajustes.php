<?php
/**
 * ====================================================================
 * GOLDHEN MANAGER AJ 🚀 - MÓDULO DE CONFIGURACIÓN Y TEMAS GAMING
 * Mantenido por AJ · Basado en proyecto original de SeBaS
 * RUTA: modulos/ajustes.php
 * ====================================================================
 */
?>
<div id="layer-ajustes" class="app-layer flex flex-col p-4 h-[100dvh] w-full overflow-hidden bg-[#02040a] relative hidden">
    
    <div class="w-full flex items-center justify-between z-30 shrink-0 pt-1 mb-4">
        <div class="flex items-center gap-3">
            <button onclick="volverAlLauncher()" class="w-10 h-10 rounded-xl bg-white/[0.02] border border-white/5 flex items-center justify-center active:scale-90 transition-all hover:bg-white/5">
                <i class="fas fa-arrow-left text-gray-300"></i>
            </button>
            <div class="flex flex-col">
                <h2 class="text-[17px] font-black tracking-tighter uppercase text-white leading-none">Configuración</h2>
                <span class="text-[9px] font-mono text-cyan-400 tracking-widest mt-0.5">Entorno, Audio y SFX</span>
            </div>
        </div>
        <div class="w-10 h-10 rounded-xl bg-cyan-500/10 border border-cyan-500/20 flex items-center justify-center text-cyan-400 shadow-[0_0_15px_rgba(34,211,238,0.2)]">
            <i class="fas fa-sliders text-lg"></i>
        </div>
    </div>

    <div class="flex-1 overflow-y-auto hide-scrollbar flex flex-col gap-4 pb-10 z-20">

        <!-- Notificaciones -->
        <div class="w-full bg-[#0a0f1a] border border-white/5 rounded-[1.5rem] p-4 shadow-lg flex flex-col gap-3 relative z-[100]">
            <div class="flex items-center gap-2 border-b border-white/5 pb-2">
                <i class="fas fa-bell text-cyan-400 text-sm"></i>
                <span class="text-[10px] font-black uppercase text-gray-400 tracking-wider">Alertas del Sistema</span>
            </div>
            <div class="flex items-center justify-between p-1">
                <div class="flex flex-col flex-1 pr-4">
                    <span class="text-[11px] font-bold text-gray-200 uppercase">Notificaciones Flotantes</span>
                    <span class="text-[8.5px] font-mono text-gray-500 mt-0.5">Activa las burbujas flotantes estilo consola.</span>
                </div>
                <label class="relative inline-flex items-center cursor-pointer shrink-0">
                    <input type="checkbox" id="cfg-switch-notificaciones" class="sr-only peer" checked onchange="guardarAjustesNotificaciones(this)">
                    <div class="w-11 h-6 bg-gray-800 border border-white/5 rounded-full peer peer-checked:after:translate-x-full peer-checked:peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-gray-400 after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-cyan-600 peer-checked:after:bg-white"></div>
                </label>
            </div>
        </div>

        <!-- Sonido -->
        <div class="w-full bg-[#0a0f1a] border border-white/5 rounded-[1.5rem] p-4 shadow-lg flex flex-col gap-3">
            <div class="flex items-center gap-2 border-b border-white/5 pb-2">
                <i class="fas fa-volume-up text-amber-400 text-sm"></i>
                <span class="text-[10px] font-black uppercase text-gray-400 tracking-wider">Motor de Audio (SFX)</span>
            </div>
            <div class="flex flex-col px-1 mt-1">
                <div class="flex justify-between items-center mb-2">
                    <span class="text-[9px] font-mono text-gray-400 uppercase tracking-widest">Volumen SFX</span>
                    <span id="lbl-volumen-sfx" class="text-[9px] font-bold text-amber-400">50%</span>
                </div>
                <input type="range" id="cfg-slider-volumen" min="0" max="100" value="50" oninput="cambiarVolumenSFX(this.value)" class="w-full h-1 bg-gray-800 rounded-lg appearance-none cursor-pointer accent-amber-500">
            </div>
        </div>

        <!-- Fondos Animados (Wallpapers) -->
        <div class="w-full bg-[#0a0f1a] border border-white/5 rounded-[1.5rem] p-4 shadow-lg flex flex-col gap-3">
            <div class="flex items-center gap-2 border-b border-white/5 pb-2">
                <i class="fas fa-desktop text-purple-400 text-sm"></i>
                <span class="text-[10px] font-black uppercase text-gray-400 tracking-wider">Fondos Animados (Wallpapers)</span>
            </div>
            
            <div class="w-full mt-1">
                <button type="button" onclick="abrirSelectorFondo()" class="w-full bg-[#111827] border border-white/10 rounded-xl px-4 py-3.5 text-[11px] font-bold text-white flex justify-between items-center cursor-pointer hover:bg-white/5 transition-colors shadow-inner">
                    <span id="custom-select-label" class="uppercase tracking-wider">Olas Líquidas (PS4)</span>
                    <i class="fas fa-chevron-right text-cyan-400"></i>
                </button>
            </div>
        </div>

        <!-- Animaciones de Inicio (Boot) -->
        <div class="w-full bg-[#0a0f1a] border border-white/5 rounded-[1.5rem] p-4 shadow-lg flex flex-col gap-3 relative z-[90]">
            <div class="flex items-center gap-2 border-b border-white/5 pb-2">
                <i class="fas fa-play-circle text-green-400 text-sm"></i>
                <span class="text-[10px] font-black uppercase text-gray-400 tracking-wider">Animaciones de Inicio (Boot)</span>
            </div>
            
            <div class="w-full mt-1">
                <button type="button" onclick="abrirSelectorIntro()" class="w-full bg-[#111827] border border-white/10 rounded-xl px-4 py-3.5 text-[11px] font-bold text-white flex justify-between items-center cursor-pointer hover:bg-white/5 transition-colors shadow-inner">
                    <span id="custom-select-intro-label" class="uppercase tracking-wider">Sin Intro (Rápido)</span>
                    <i class="fas fa-chevron-right text-green-400"></i>
                </button>
            </div>
        </div>

        <div class="w-full bg-[#0a0f1a] border border-white/5 rounded-[1.5rem] p-4 shadow-lg flex flex-col gap-3">
            <div class="flex items-center gap-2 border-b border-white/5 pb-2">
                <i class="fas fa-text-height text-violet-400 text-sm"></i>
                <span class="text-[10px] font-black uppercase text-gray-400 tracking-wider">Tamaño de texto</span>
            </div>
            <label class="flex flex-col gap-2 text-[9px] font-mono text-gray-400 uppercase tracking-wider">
                Tamaño del texto <span id="lbl-tamano-texto" class="text-cyan-400">100%</span>
                <input id="cfg-tamano-texto" type="range" min="85" max="130" step="5" value="100" oninput="guardarTamanoTexto(this.value)" class="w-full accent-cyan-500">
            </label>
        </div>

        <!-- Créditos (nuevo) -->
        <div class="w-full bg-[#0a0f1a] border border-white/5 rounded-[1.5rem] p-4 shadow-lg flex flex-col gap-2">
            <div class="flex items-center gap-2 border-b border-white/5 pb-2">
                <i class="fas fa-code text-cyan-400 text-sm"></i>
                <span class="text-[10px] font-black uppercase text-gray-400 tracking-wider">Acerca de</span>
            </div>
            <div class="flex flex-col items-center py-2">
                <span class="text-[9px] font-mono text-gray-500 tracking-widest">GoldHen Manager AJ</span>
                <span class="text-[8px] font-mono text-gray-600 tracking-wider">Versión 3.3 • Mantenido por AJ</span>
                <span class="text-[7px] font-mono text-gray-700 mt-1">Basado en el trabajo original de SeBaS</span>
                <div class="flex gap-3 mt-2">
                    <span class="text-[8px] font-mono text-cyan-500/50">❤️ Comunidad</span>
                    <span class="text-[8px] font-mono text-cyan-500/50">🔓 Código Abierto</span>
                </div>
            </div>
        </div>

        <div class="w-full bg-[#0a0f1a] border border-white/5 rounded-[1.5rem] p-4 shadow-lg flex flex-col gap-3">
            <div class="flex items-center gap-2 border-b border-white/5 pb-2"><i class="fas fa-cloud-arrow-down text-emerald-400 text-sm"></i><span class="text-[10px] font-black uppercase text-gray-400 tracking-wider">Actualizaciones</span></div>
            <p id="update-status" class="text-[9px] text-gray-500 font-mono">Comprueba cambios publicados en GitHub.</p>
            <div class="grid grid-cols-2 gap-2"><button onclick="comprobarActualizacion()" class="py-3 rounded-xl bg-white/5 border border-white/10 text-[9px] font-black uppercase tracking-wider text-gray-300"><i class="fas fa-rotate-right"></i> Verificar</button><button id="btn-aplicar-update" onclick="aplicarActualizacion()" disabled class="py-3 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-[9px] font-black uppercase tracking-wider text-emerald-400 opacity-40">Actualizar</button></div>
            <button onclick="mostrarInstalacionPWA()" class="py-3 rounded-xl bg-cyan-500/10 border border-cyan-500/20 text-[9px] font-black uppercase tracking-wider text-cyan-400"><i class="fas fa-mobile-screen-button"></i> Instalar app en el teléfono</button>
        </div>

    </div>
</div>

<!-- Selectores en modal: no quedan recortados por el scroll del módulo. -->
<div id="modal-selector-fondo" class="fixed inset-0 z-[10050] hidden items-end sm:items-center justify-center bg-black/70 p-4" onclick="if(event.target===this)cerrarSelectorFondo()"><div class="w-full max-w-md max-h-[82dvh] overflow-y-auto rounded-3xl border border-white/10 bg-[#0a0f1a] p-4 shadow-2xl"><div class="mb-3 flex items-center justify-between"><div><b class="text-sm text-white">Fondos animados</b><p class="text-[9px] text-gray-500">Elige el estilo visual del host.</p></div><button onclick="cerrarSelectorFondo()" class="h-9 w-9 rounded-xl bg-white/5 text-gray-300"><i class="fas fa-xmark"></i></button></div><div class="grid grid-cols-1 gap-2"><button onclick="seleccionarFondoCustom('none','Apagar fondos')" class="selector-ajuste text-left text-gray-300">Apagar fondos</button><button onclick="seleccionarFondoCustom('bg-ps4','Olas líquidas (PS4)')" class="selector-ajuste text-left text-blue-300">Olas líquidas (PS4)</button><button onclick="seleccionarFondoCustom('bg-ps2','Cubos 3D (PS2)')" class="selector-ajuste text-left text-indigo-300">Cubos 3D (PS2)</button><button onclick="seleccionarFondoCustom('bg-matrix','Lluvia de código (Matrix)')" class="selector-ajuste text-left text-emerald-300">Lluvia de código (Matrix)</button><button onclick="seleccionarFondoCustom('bg-warp','Velocidad Warp')" class="selector-ajuste text-left text-white">Velocidad Warp</button><button onclick="seleccionarFondoCustom('bg-plasma','Fluido plasma')" class="selector-ajuste text-left text-pink-300">Fluido plasma</button><button onclick="seleccionarFondoCustom('bg-network','Red neuronal')" class="selector-ajuste text-left text-sky-300">Red neuronal</button></div></div></div>
<div id="modal-selector-intro" class="fixed inset-0 z-[10050] hidden items-end sm:items-center justify-center bg-black/70 p-4" onclick="if(event.target===this)cerrarSelectorIntro()"><div class="w-full max-w-md max-h-[82dvh] overflow-y-auto rounded-3xl border border-white/10 bg-[#0a0f1a] p-4 shadow-2xl"><div class="mb-3 flex items-center justify-between"><div><b class="text-sm text-white">Animación de inicio</b><p class="text-[9px] text-gray-500">Se reproduce al abrir la aplicación.</p></div><button onclick="cerrarSelectorIntro()" class="h-9 w-9 rounded-xl bg-white/5 text-gray-300"><i class="fas fa-xmark"></i></button></div><div class="grid grid-cols-1 gap-2"><button onclick="seleccionarIntroCustom('none','Sin intro (rápido)')" class="selector-ajuste text-left text-gray-300">Sin intro (rápido)</button><button onclick="seleccionarIntroCustom('intro-ps4','PlayStation 4')" class="selector-ajuste text-left text-blue-300">PlayStation 4</button><button onclick="seleccionarIntroCustom('intro-glitch','Glitch Hacker')" class="selector-ajuste text-left text-fuchsia-300">Glitch Hacker</button><button onclick="seleccionarIntroCustom('intro-ps2','PlayStation 2 clásica')" class="selector-ajuste text-left text-indigo-300">PlayStation 2 clásica</button><button onclick="seleccionarIntroCustom('intro-hud','Sci-Fi HUD')" class="selector-ajuste text-left text-sky-300">Sci-Fi HUD</button><button onclick="seleccionarIntroCustom('intro-neon','Cyberpunk Neon')" class="selector-ajuste text-left text-pink-300">Cyberpunk Neon</button><button onclick="seleccionarIntroCustom('intro-decrypt','Decrypt System')" class="selector-ajuste text-left text-green-300">Decrypt System</button><button onclick="seleccionarIntroCustom('intro-arcade','Retro Arcade')" class="selector-ajuste text-left text-yellow-300">Retro Arcade</button><button onclick="seleccionarIntroCustom('intro-matrix-rain','Matrix Rain')" class="selector-ajuste text-left text-emerald-300">Matrix Rain</button><button onclick="seleccionarIntroCustom('intro-crt','Terminal CRT (Boot)')" class="selector-ajuste text-left text-orange-300">Terminal CRT (Boot)</button><button onclick="seleccionarIntroCustom('intro-gb','Game Boy clásica')" class="selector-ajuste text-left text-lime-300">Game Boy clásica</button><button onclick="seleccionarIntroCustom('intro-breach','System Breach')" class="selector-ajuste text-left text-red-300">System Breach</button></div></div></div>
<style>.selector-ajuste{padding:.85rem 1rem;border:1px solid rgba(255,255,255,.08);border-radius:.8rem;background:rgba(255,255,255,.035);font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em}.selector-ajuste:hover{background:rgba(255,255,255,.09)}</style>
