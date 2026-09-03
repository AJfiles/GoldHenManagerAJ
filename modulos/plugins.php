<div id="layer-plugins" class="app-layer hidden flex-col p-4 h-[100dvh] w-full overflow-hidden bg-[#060913]">
    <div class="flex items-center justify-between shrink-0 mb-4">
        <div class="flex items-center gap-3"><button onclick="volverAlLauncher()" class="w-10 h-10 rounded-xl bg-white/5 text-gray-300"><i class="fa-solid fa-arrow-left"></i></button><div><h2 class="text-[17px] font-black uppercase text-white">Plugins</h2><p class="text-[8px] text-violet-400 font-mono tracking-widest">GOLDHEN PLUGIN LOADER</p></div></div>
        <button onclick="pluginsRecargar()" class="w-10 h-10 rounded-xl bg-violet-500/10 border border-violet-500/20 text-violet-400"><i class="fa-solid fa-rotate-right"></i></button>
    </div>
    <div class="flex-1 overflow-y-auto hide-scrollbar flex flex-col gap-4 pb-6">
        <div class="bg-[#0a0f1a] border border-white/5 rounded-2xl p-4"><p class="text-[9px] text-gray-400 leading-relaxed">Los cambios se guardan en <b class="text-violet-400">plugins.ini</b>. Se crea una copia local antes de editarlo. Reinicia GoldHEN o la consola para aplicar los plugins.</p></div>
        <div class="bg-[#0a0f1a] border border-white/5 rounded-2xl p-4 flex flex-col gap-3">
            <span class="text-[9px] uppercase font-black tracking-widest text-gray-400">Destino de asignación</span>
            <select id="plugins-section" onchange="pluginsRenderizar()" class="bg-[#111827] border border-white/10 rounded-xl px-3 py-3 text-[11px] text-white outline-none"><option value="default">[default] — Entorno general</option></select>
        </div>
        <div class="bg-[#0a0f1a] border border-white/5 rounded-2xl p-4"><div class="flex justify-between mb-3"><span class="text-[10px] uppercase font-black tracking-widest text-white">Plugins locales</span><label class="text-[9px] text-violet-400 cursor-pointer"><i class="fa-solid fa-plus"></i> Añadir PRX<input id="plugins-upload" type="file" accept=".prx" class="hidden" onchange="pluginsSubir(event)"></label></div><div id="plugins-local-list" class="flex flex-col gap-2"></div></div>
        <div class="bg-[#0a0f1a] border border-white/5 rounded-2xl p-4"><div class="flex justify-between mb-3"><span class="text-[10px] uppercase font-black tracking-widest text-white">Instalados en PS4</span><span id="plugins-remote-count" class="text-[9px] text-gray-500">0</span></div><div id="plugins-remote-list" class="flex flex-col gap-2"></div></div>
    </div>
</div>
