<?php
/**
 * ====================================================================
 * GOLDHEN MANAGER AJ 🚀 - MÓDULO DE CONFIGURACIÓN Y TEMAS GAMING
 * DEVELOPED By SeBaS - Mod AJ
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
            
            <div class="relative w-full mt-1" id="custom-select-container">
                <div onclick="toggleCustomSelect()" class="w-full bg-[#111827] border border-white/10 rounded-xl px-4 py-3.5 text-[11px] font-bold text-white flex justify-between items-center cursor-pointer hover:bg-white/5 transition-colors shadow-inner">
                    <span id="custom-select-label" class="uppercase tracking-wider">Olas Líquidas (PS4)</span>
                    <i id="custom-select-icon" class="fas fa-chevron-down text-cyan-400 transition-transform duration-300"></i>
                </div>
                
                <div id="custom-select-options" class="absolute w-full mt-2 bg-[#0a0f1a] border border-white/10 rounded-xl shadow-2xl overflow-hidden z-[200] origin-top transform scale-y-0 opacity-0 transition-all duration-300 max-h-[220px] overflow-y-auto custom-scrollbar flex flex-col pointer-events-none">
                    <div onclick="seleccionarFondoCustom('none', 'Apagar Fondos')" class="p-3.5 border-b border-white/5 hover:bg-white/5 text-[10.5px] font-bold text-gray-400 uppercase tracking-wider cursor-pointer transition-colors">Apagar Fondos</div>
                    <div onclick="seleccionarFondoCustom('bg-ps4', 'Olas Líquidas (PS4)')" class="p-3.5 border-b border-white/5 hover:bg-white/5 text-[10.5px] font-bold text-blue-400 uppercase tracking-wider cursor-pointer transition-colors">Olas Líquidas (PS4)</div>
                    <div onclick="seleccionarFondoCustom('bg-ps2', 'Cubos 3D (PS2)')" class="p-3.5 border-b border-white/5 hover:bg-white/5 text-[10.5px] font-bold text-indigo-400 uppercase tracking-wider cursor-pointer transition-colors">Cubos 3D (PS2)</div>
                    <div onclick="seleccionarFondoCustom('bg-matrix', 'Lluvia de Código (Matrix)')" class="p-3.5 border-b border-white/5 hover:bg-white/5 text-[10.5px] font-bold text-emerald-400 uppercase tracking-wider cursor-pointer transition-colors">Lluvia de Código (Matrix)</div>
                    <div onclick="seleccionarFondoCustom('bg-warp', 'Velocidad Warp (Espacio)')" class="p-3.5 border-b border-white/5 hover:bg-white/5 text-[10.5px] font-bold text-white uppercase tracking-wider cursor-pointer transition-colors">Velocidad Warp (Espacio)</div>
                    <div onclick="seleccionarFondoCustom('bg-plasma', 'Fluido Plasma')" class="p-3.5 border-b border-white/5 hover:bg-white/5 text-[10.5px] font-bold text-pink-400 uppercase tracking-wider cursor-pointer transition-colors">Fluido Plasma</div>
                    <div onclick="seleccionarFondoCustom('bg-network', 'Red Neuronal (Network)')" class="p-3.5 hover:bg-white/5 text-[10.5px] font-bold text-sky-400 uppercase tracking-wider cursor-pointer transition-colors">Red Neuronal (Network)</div>
                </div>
            </div>
        </div>

        <!-- Animaciones de Inicio (Boot) -->
        <div class="w-full bg-[#0a0f1a] border border-white/5 rounded-[1.5rem] p-4 shadow-lg flex flex-col gap-3 relative z-[90]">
            <div class="flex items-center gap-2 border-b border-white/5 pb-2">
                <i class="fas fa-play-circle text-green-400 text-sm"></i>
                <span class="text-[10px] font-black uppercase text-gray-400 tracking-wider">Animaciones de Inicio (Boot)</span>
            </div>
            
            <div class="relative w-full mt-1">
                <div onclick="toggleCustomSelectIntro()" class="w-full bg-[#111827] border border-white/10 rounded-xl px-4 py-3.5 text-[11px] font-bold text-white flex justify-between items-center cursor-pointer hover:bg-white/5 transition-colors shadow-inner">
                    <span id="custom-select-intro-label" class="uppercase tracking-wider">Sin Intro (Rápido)</span>
                    <i id="custom-select-intro-icon" class="fas fa-chevron-down text-green-400 transition-transform duration-300"></i>
                </div>
                
                <div id="custom-select-intro-options" class="absolute w-full mt-2 bg-[#0a0f1a] border border-white/10 rounded-xl shadow-2xl overflow-hidden z-[200] origin-top transform scale-y-0 opacity-0 transition-all duration-300 max-h-[220px] overflow-y-auto custom-scrollbar flex flex-col pointer-events-none">
                    <div onclick="seleccionarIntroCustom('none', 'Sin Intro (Rápido)')" class="p-3.5 border-b border-white/5 hover:bg-white/5 text-[10.5px] font-bold text-gray-400 uppercase tracking-wider cursor-pointer transition-colors">Sin Intro (Rápido)</div>
                    <div onclick="seleccionarIntroCustom('intro-ps4', 'PlayStation 4')" class="p-3.5 border-b border-white/5 hover:bg-white/5 text-[10.5px] font-bold text-blue-400 uppercase tracking-wider cursor-pointer transition-colors">PlayStation 4</div>
                    <div onclick="seleccionarIntroCustom('intro-glitch', 'Glitch Hacker')" class="p-3.5 border-b border-white/5 hover:bg-white/5 text-[10.5px] font-bold text-fuchsia-400 uppercase tracking-wider cursor-pointer transition-colors">Glitch Hacker</div>
                    <div onclick="seleccionarIntroCustom('intro-ps2', 'PlayStation 2 Clásica')" class="p-3.5 border-b border-white/5 hover:bg-white/5 text-[10.5px] font-bold text-indigo-400 uppercase tracking-wider cursor-pointer transition-colors">PlayStation 2 Clásica</div>
                    <div onclick="seleccionarIntroCustom('intro-hud', 'Sci-Fi HUD')" class="p-3.5 border-b border-white/5 hover:bg-white/5 text-[10.5px] font-bold text-sky-400 uppercase tracking-wider cursor-pointer transition-colors">Sci-Fi HUD</div>
                    <div onclick="seleccionarIntroCustom('intro-neon', 'Cyberpunk Neon')" class="p-3.5 border-b border-white/5 hover:bg-white/5 text-[10.5px] font-bold text-pink-500 uppercase tracking-wider cursor-pointer transition-colors">Cyberpunk Neon</div>
                    <div onclick="seleccionarIntroCustom('intro-decrypt', 'Decrypt System')" class="p-3.5 border-b border-white/5 hover:bg-white/5 text-[10.5px] font-bold text-green-500 uppercase tracking-wider cursor-pointer transition-colors">Decrypt System</div>
                    <div onclick="seleccionarIntroCustom('intro-arcade', 'Retro Arcade')" class="p-3.5 border-b border-white/5 hover:bg-white/5 text-[10.5px] font-bold text-yellow-400 uppercase tracking-wider cursor-pointer transition-colors">Retro Arcade</div>
                    <div onclick="seleccionarIntroCustom('intro-matrix-rain', 'Matrix Rain')" class="p-3.5 border-b border-white/5 hover:bg-white/5 text-[10.5px] font-bold text-emerald-400 uppercase tracking-wider cursor-pointer transition-colors">Matrix Rain</div>
                    <div onclick="seleccionarIntroCustom('intro-crt', 'Terminal CRT (Boot)')" class="p-3.5 border-b border-white/5 hover:bg-white/5 text-[10.5px] font-bold text-orange-400 uppercase tracking-wider cursor-pointer transition-colors">Terminal CRT (Boot)</div>
                    <div onclick="seleccionarIntroCustom('intro-gb', 'Game Boy Clásica')" class="p-3.5 border-b border-white/5 hover:bg-white/5 text-[10.5px] font-bold text-lime-500 uppercase tracking-wider cursor-pointer transition-colors">Game Boy Clásica</div>
                    <div onclick="seleccionarIntroCustom('intro-breach', 'System Breach')" class="p-3.5 hover:bg-white/5 text-[10.5px] font-bold text-red-500 uppercase tracking-wider cursor-pointer transition-colors">System Breach</div>
                </div>
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
                <span class="text-[8px] font-mono text-gray-600 tracking-wider">Versión 3.0 • Modificado por AJ</span>
                <span class="text-[7px] font-mono text-gray-700 mt-1">Basado en el trabajo original de SeBaS</span>
                <div class="flex gap-3 mt-2">
                    <span class="text-[8px] font-mono text-cyan-500/50">❤️ Comunidad</span>
                    <span class="text-[8px] font-mono text-cyan-500/50">🔓 Código Abierto</span>
                </div>
            </div>
        </div>

    </div>
</div>
