<?php
/**
 * ====================================================================
 * GOLDHEN MANAGER V3.0 🚀 - MÓDULO GESTOR DE MODS AFR (PS4)
 * DEVELOPED By SeBaS - RUTA: modulos/afr_mods.php
 * ====================================================================
 */
?>
<div id="layer-afr_mods" class="app-layer hidden flex-col h-screen w-full overflow-hidden bg-[#060913] z-20">
    <!-- Header Principal -->
    <div class="glass-premium w-full flex items-center justify-between p-4 shrink-0 rounded-b-3xl border-b border-indigo-500/20 shadow-[0_10px_30px_rgba(99,102,241,0.1)] relative z-20">
        <button onclick="volverAlLauncher()" class="w-10 h-10 rounded-xl bg-indigo-900/30 text-indigo-400 flex items-center justify-center active:scale-90 transition-all border border-indigo-500/20">
            <i class="fa-solid fa-arrow-left"></i>
        </button>
        <div class="flex flex-col items-center">
            <h2 class="text-sm font-black tracking-widest text-white uppercase drop-shadow-md">GAME MODS</h2>
            <span class="text-[9px] font-mono text-indigo-400 tracking-widest uppercase">AFR Engine Auto-Routing</span>
        </div>
        <div class="w-10 h-10 rounded-xl bg-indigo-900/20 border border-indigo-500/10 flex items-center justify-center text-indigo-300">
            <i class="fa-solid fa-cubes text-sm"></i>
        </div>
    </div>

    <!-- Contenido Scrolleable -->
    <div class="flex-1 overflow-y-auto hide-scrollbar p-4 flex flex-col gap-5 relative z-10 pb-24">
        
        <!-- Selector Inteligente de Juego (ARRIBA) -->
        <div class="glass-premium p-4 rounded-3xl border border-white/5 flex flex-col relative overflow-hidden group cursor-pointer" onclick="console.log('Abrir selector juegos afr')">
            <div id="afr-bg-blur" class="absolute inset-0 bg-cover bg-center opacity-20 blur-xl transition-all duration-500" style="background-image: url('https://images.unsplash.com/photo-1612287230202-1bf1d85d1bdf?w=400&q=80');"></div>
            <div class="absolute inset-0 bg-gradient-to-t from-[#060913] via-[#060913]/80 to-transparent"></div>
            
            <div class="relative z-10 flex items-center gap-4">
                <div id="afr-avatar" class="w-16 h-16 rounded-2xl bg-gray-800 border-2 border-indigo-500/30 shadow-lg bg-cover bg-center flex items-center justify-center shrink-0" style="background-image: url('https://images.unsplash.com/photo-1612287230202-1bf1d85d1bdf?w=400&q=80');">
                </div>
                <div class="flex flex-col flex-1 min-w-0">
                    <span class="text-[9px] text-indigo-400 font-black tracking-widest uppercase mb-1">Target Game</span>
                    <h3 id="afr-title" class="text-xs font-black tracking-widest text-white uppercase truncate">Resident Evil 3</h3>
                    <span id="afr-cusa" class="text-[10px] font-mono text-gray-500 mt-0.5">CUSA14129</span>
                </div>
                <i class="fa-solid fa-chevron-right text-indigo-500/50 mr-2 shrink-0"></i>
            </div>
        </div>

        <!-- Estado de Plugins y Seguridad (ABAJO DEL SELECTOR) -->
        <div class="glass-premium p-4 rounded-3xl border border-white/5 flex flex-col gap-3">
            <div class="flex items-center justify-between border-b border-white/5 pb-2">
                <span class="text-[10px] font-black text-gray-400 tracking-widest uppercase"><i class="fa-solid fa-microchip text-indigo-400 mr-1.5"></i> Sistema Plugins</span>
                <span class="text-[8px] font-mono text-emerald-400"><i class="fa-solid fa-check mr-1"></i> Sin reinicio</span>
            </div>
            
            <div class="grid grid-cols-2 gap-2">
                <button onclick="realizarBackupPlugins()" class="bg-[#111827] border border-white/10 p-3 rounded-xl flex flex-col items-center justify-center gap-2 active:scale-95 transition-all">
                    <i class="fa-solid fa-download text-gray-400 text-lg"></i>
                    <span class="text-[9px] font-bold text-gray-300 uppercase tracking-wider text-center">Backup<br>Originales</span>
                </button>
                <!-- Botón de instalación inicialmente BLOQUEADO por seguridad -->
                <button id="btn-instalar-afr" onclick="instalarPluginsAFR()" class="bg-indigo-600/20 border border-indigo-500/40 p-3 rounded-xl flex flex-col items-center justify-center gap-2 transition-all shadow-[0_0_15px_rgba(79,70,229,0.15)] opacity-40 pointer-events-none">
                    <i class="fa-solid fa-bolt text-indigo-400 text-lg"></i>
                    <span class="text-[9px] font-bold text-indigo-300 uppercase tracking-wider text-center">Instalar<br>Plugins AFR</span>
                </button>
            </div>
        </div>

        <!-- Botón Subir Archivo Mod -->
        <div class="w-full">
            <input type="file" id="input-afr-file" accept=".pak" class="hidden">
            <button onclick="document.getElementById('input-afr-file').click()" class="w-full bg-indigo-600/20 border border-indigo-500/40 p-4 rounded-2xl flex items-center justify-center gap-3 active:scale-[0.98] transition-all shadow-[0_0_20px_rgba(79,70,229,0.1)] text-indigo-300 hover:bg-indigo-600/30">
                <i class="fa-solid fa-file-arrow-up text-xl"></i>
                <div class="flex flex-col items-start text-left">
                    <span class="text-[10px] font-black uppercase tracking-widest">Subir Archivo .PAK</span>
                    <span class="text-[8px] font-mono opacity-70 tracking-wider">El sistema calculará el número de parche</span>
                </div>
            </button>
        </div>

        <!-- GESTOR DE PAQUETES Y SLOTS -->
        <div class="glass-premium rounded-3xl border border-white/5 flex flex-col overflow-hidden">
            <div class="p-4 border-b border-white/5 bg-black/20 flex justify-between items-center">
                <span class="text-[10px] font-black text-white tracking-widest uppercase"><i class="fa-solid fa-layer-group text-indigo-400 mr-1.5"></i> Bóveda de Mods</span>
                <!-- Cartel de reinicio aplicado a la zona de activación de mods -->
                <span class="text-[9px] font-mono bg-amber-500/20 text-amber-400 px-2 py-0.5 rounded-md border border-amber-500/30"><i class="fa-solid fa-triangle-exclamation mr-1"></i> Requiere Reinicio</span>
            </div>

            <!-- 1. CATEGORÍA: EXCLUYENTES (Slot Único Reservado para Skins) -->
            <div class="p-3 border-b border-white/5">
                <div class="flex items-center justify-between mb-3 pl-1 pr-2">
                    <div class="flex items-center gap-2">
                        <div class="w-1.5 h-1.5 rounded-full bg-rose-500 shadow-[0_0_5px_#f43f5e] animate-pulse"></div>
                        <span class="text-[9px] font-black tracking-widest text-gray-400 uppercase">Skins y Modelos <span class="text-rose-400">(Slot Exclusivo)</span></span>
                    </div>
                    <span class="text-[8px] font-mono text-gray-600">Último original: 007</span>
                </div>
                
                <div id="lista-afr-skins" class="flex flex-col gap-2">
                    
                    <!-- Skin Activo -->
                    <div class="flex items-center justify-between bg-indigo-900/10 border border-indigo-500/30 p-2.5 rounded-xl">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-indigo-900/40 border border-indigo-500/40 flex items-center justify-center text-indigo-400 shrink-0">
                                <i class="fa-solid fa-shirt text-xs"></i>
                            </div>
                            <div class="flex flex-col overflow-hidden pr-2">
                                <span class="text-[10px] font-bold text-white truncate">Jill S.T.A.R.S Classic</span>
                                <span class="text-[8px] font-mono text-indigo-400 mt-0.5">Activo: patch_008.pak</span>
                            </div>
                        </div>
                        <div class="w-10 h-5 bg-indigo-600 rounded-full flex items-center p-0.5 cursor-pointer shadow-[0_0_10px_rgba(79,70,229,0.4)] shrink-0">
                            <div class="w-4 h-4 bg-white rounded-full translate-x-5 transition-transform"></div>
                        </div>
                    </div>

                    <!-- Skin Inactivo -->
                    <div class="flex items-center justify-between bg-black/40 border border-white/5 p-2.5 rounded-xl opacity-60 hover:opacity-100 transition-opacity">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-gray-900 border border-white/5 flex items-center justify-center text-gray-500 shrink-0">
                                <i class="fa-solid fa-shirt text-xs"></i>
                            </div>
                            <div class="flex flex-col overflow-hidden pr-2">
                                <span class="text-[10px] font-bold text-gray-300 truncate">Jill Vestido Negro</span>
                                <span class="text-[8px] font-mono text-gray-600 mt-0.5 line-through">Jill_Vest_Negro.off</span>
                            </div>
                        </div>
                        <div class="w-10 h-5 bg-gray-800 rounded-full flex items-center p-0.5 cursor-pointer border border-white/10 shrink-0">
                            <div class="w-4 h-4 bg-gray-500 rounded-full transition-transform"></div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- 2. CATEGORÍA: OTROS MODS -->
            <div class="p-3">
                <div class="flex items-center gap-2 mb-3 pl-1">
                    <div class="w-1.5 h-1.5 rounded-full bg-emerald-500 shadow-[0_0_5px_#10b981]"></div>
                    <span class="text-[9px] font-black tracking-widest text-gray-400 uppercase">Otros Mods</span>
                </div>
                
                <div id="lista-afr-otros" class="flex flex-col gap-2">
                    
                    <!-- Mod Extra Activo -->
                    <div class="flex items-center justify-between bg-emerald-900/10 border border-emerald-500/30 p-2.5 rounded-xl">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-emerald-900/30 border border-emerald-500/30 flex items-center justify-center text-emerald-400 shrink-0">
                                <i class="fa-solid fa-gun text-xs"></i>
                            </div>
                            <div class="flex flex-col overflow-hidden pr-2">
                                <span class="text-[10px] font-bold text-white truncate">Munición Infinita All Weps</span>
                                <span class="text-[8px] font-mono text-emerald-400 mt-0.5">Activo: patch_009.pak</span>
                            </div>
                        </div>
                        <div class="w-10 h-5 bg-emerald-600 rounded-full flex items-center p-0.5 cursor-pointer shadow-[0_0_10px_rgba(16,185,129,0.3)] shrink-0">
                            <div class="w-4 h-4 bg-white rounded-full translate-x-5 transition-transform"></div>
                        </div>
                    </div>

                </div>
            </div>
            <!-- Fin de Categorías -->
        </div>
    </div>
</div>
