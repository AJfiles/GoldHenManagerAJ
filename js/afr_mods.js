/**
 * ====================================================================
 * GOLDHEN MANAGER V3.0 🚀 - MÓDULO GESTOR AFR V3 + PLUGINS (TERMUX)
 * RUTA: js/afr_mods.js
 * ====================================================================
 */

let afrDatosBoveda = { calibracion_slot: -1, categorias: [], grupos: [], mods: [] };
let afrArchivosPendientes = [];
let afrModoEdicion = false;
let afrGrupoActivoPopup = null;
let afrBackupRealizado = false;
let afrUploadState = null; // 🔥 Guarda el estado de la subida para reintentar

// ==========================================
// RESTAURACIÓN: SISTEMA DE PLUGINS
// ==========================================
window.togglePluginButtonsAFR = function() {
    if(typeof emitirEfectoSonidoNativo === 'function') emitirEfectoSonidoNativo('click');
    const isHidden = localStorage.getItem('afr_hide_plugins') === 'true';
    localStorage.setItem('afr_hide_plugins', !isHidden);
    aplicarEstadoBotonesPlugins();
};

window.aplicarEstadoBotonesPlugins = function() {
    const isHidden = localStorage.getItem('afr_hide_plugins') === 'true';
    const container = document.getElementById('afr-btn-container');
    const btnBackup = document.getElementById('btn-afr-backup');
    const btnInstall = document.getElementById('btn-afr-install');
    const btnUpload = document.getElementById('btn-afr-upload');
    const toggleIcon = document.getElementById('icon-toggle-plugins');
    const toggleTxt = document.getElementById('txt-toggle-plugins');

    if (!container || !btnBackup || !btnInstall) return;

    if (isHidden) {
        btnBackup.classList.add('hidden');
        btnInstall.classList.add('hidden');
        btnUpload.classList.remove('col-span-1');
        container.style.gridTemplateColumns = '1fr';
        toggleIcon.className = 'fa-solid fa-eye';
        toggleTxt.innerText = 'Mostrar Sist. Plugins';
    } else {
        btnBackup.classList.remove('hidden');
        btnInstall.classList.remove('hidden');
        btnUpload.classList.add('col-span-1');
        container.style.gridTemplateColumns = 'repeat(3, minmax(0, 1fr))';
        toggleIcon.className = 'fa-solid fa-eye-slash';
        toggleTxt.innerText = 'Ocultar Sist. Plugins';
    }
};

window.verificarEstadoBackupAFR = function() {
    if (localStorage.getItem('afr_backup_done') === 'true') {
        afrBackupRealizado = true;
        const btnInstalar = document.getElementById('btn-afr-install');
        if(btnInstalar) {
            btnInstalar.classList.remove('opacity-40', 'pointer-events-none');
            btnInstalar.classList.add('hover:border-indigo-500/40', 'cursor-pointer');
        }
    }
};

window.realizarBackupPlugins = async function() {
    if(typeof emitirEfectoSonidoNativo === 'function') emitirEfectoSonidoNativo('click');
    const ip = localStorage.getItem('sebas_ip_final_libre') || '192.168.1.28';
    if(typeof ps5Notification === 'function') ps5Notification("AFR ENGINE", "Extrayendo plugins originales...", "fa-solid fa-download");
    
    const btnBackup = document.getElementById('btn-afr-backup');
    if (btnBackup) { btnBackup.style.opacity = '0.5'; btnBackup.style.pointerEvents = 'none'; }

    try {
        let fd = new FormData();
        fd.append('action', 'backup_plugins');
        fd.append('host_ip', ip);

        let res = await fetch('api/afr_api.php', { method: 'POST', body: fd });
        let json = await res.json();

        if (json && json.status === 'success') {
            localStorage.setItem('afr_backup_done', 'true');
            verificarEstadoBackupAFR();
            
            if(typeof ps5Notification === 'function') ps5Notification("ÉXITO", "Descargando ZIP al almacenamiento...", "fa-solid fa-check-double");
            
            const link = document.createElement('a');
            link.href = json.file_url;
            link.download = json.file_url.split('/').pop();
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        } else {
            if(typeof ps5Notification === 'function') ps5Notification("ERROR", json.message || "Fallo al hacer backup.", "fa-solid fa-bug");
        }
    } catch (error) {
        if(typeof ps5Notification === 'function') ps5Notification("ERROR", "Se perdió la conexión con el servidor local.", "fa-solid fa-wifi");
    } finally {
        if (btnBackup) { btnBackup.style.opacity = '1'; btnBackup.style.pointerEvents = 'auto'; }
    }
};

window.procesarInstalacionPlugins = async function(event) {
    const file = event.target.files[0];
    if (!file) return;
    const ip = localStorage.getItem('sebas_ip_final_libre') || '192.168.1.28';
    
    if(typeof ps5Notification === 'function') ps5Notification("AFR ENGINE", `Instalando ${file.name}...`, "fa-solid fa-file-zipper");
    
    try {
        let fd = new FormData();
        fd.append('action', 'install_plugins');
        fd.append('host_ip', ip);
        fd.append('plugin_zip', file);

        let res = await fetch('api/afr_api.php', { method: 'POST', body: fd });
        let json = await res.json();

        if (json.status === 'success') {
            if(typeof ps5Notification === 'function') ps5Notification("ÉXITO", "Nuevos Plugins inyectados en la PS4.", "fa-solid fa-check");
            localStorage.setItem('afr_hide_plugins', 'true');
            aplicarEstadoBotonesPlugins();
        } else {
            if(typeof ps5Notification === 'function') ps5Notification("ERROR", json.message, "fa-solid fa-bug");
        }
    } catch (error) {
        if(typeof ps5Notification === 'function') ps5Notification("ERROR", "Fallo la red al instalar.", "fa-solid fa-wifi");
    }
    event.target.value = ''; 
};

// ==========================================
// MODALES NATIVOS DE TEXTO/PELIGRO (NUEVA LÓGICA DE CALLBACKS)
// ==========================================
let cbModalTexto = null;
let cbModalPeligroAceptar = null;
let cbModalPeligroCancelar = null;

function abrirModalTexto(titulo, placeholder, valorActual, callback) {
    if(typeof emitirEfectoSonidoNativo === 'function') emitirEfectoSonidoNativo('click');
    document.getElementById('modal-texto-titulo').innerText = titulo;
    const input = document.getElementById('modal-texto-input');
    input.placeholder = placeholder; input.value = valorActual || '';
    cbModalTexto = callback;
    const modal = document.getElementById('modal-afr-texto');
    modal.classList.remove('hidden'); setTimeout(() => { modal.classList.add('opacity-100'); modal.querySelector('div').classList.remove('scale-95'); }, 10);
}
function cerrarModalTexto() {
    const modal = document.getElementById('modal-afr-texto');
    modal.classList.remove('opacity-100'); modal.querySelector('div').classList.add('scale-95');
    setTimeout(() => modal.classList.add('hidden'), 300);
}
function confirmarModalTexto() {
    const val = document.getElementById('modal-texto-input').value.trim();
    if (val && cbModalTexto) { cbModalTexto(val); }
    cerrarModalTexto();
}

// 🔥 FIX: Permite pasar un callback para Cancelar y cambiar los dos botones
function abrirModalPeligro(titulo, mensaje, cbAceptar, txtAceptar = "SÍ, DESTRUIR", cbCancelar = null, txtCancelar = "CANCELAR") {
    if(typeof emitirEfectoSonidoNativo === 'function') emitirEfectoSonidoNativo('click');
    document.getElementById('modal-peligro-titulo').innerText = titulo;
    document.getElementById('modal-peligro-msg').innerHTML = mensaje; // InnerHTML por si enviamos br tags
    document.getElementById('btn-peligro-action').innerText = txtAceptar;
    document.getElementById('btn-peligro-cancel').innerText = txtCancelar;
    
    cbModalPeligroAceptar = cbAceptar;
    cbModalPeligroCancelar = cbCancelar;
    
    const modal = document.getElementById('modal-afr-peligro');
    modal.classList.remove('hidden'); setTimeout(() => { modal.classList.add('opacity-100'); modal.querySelector('div').classList.remove('scale-95'); }, 10);
}

function confirmarModalPeligro() {
    if(cbModalPeligroAceptar) cbModalPeligroAceptar();
    cerrarModalPeligroInterno();
}

function cancelarModalPeligro() {
    if(cbModalPeligroCancelar) cbModalPeligroCancelar();
    cerrarModalPeligroInterno();
}

function cerrarModalPeligroInterno() {
    const modal = document.getElementById('modal-afr-peligro');
    modal.classList.remove('opacity-100'); modal.querySelector('div').classList.add('scale-95');
    setTimeout(() => modal.classList.add('hidden'), 300);
}

// ==========================================
// INICIALIZACIÓN Y BBDD LÍQUIDA
// ==========================================
window.cargarBovedaAFR_V3 = async function() {
    if (!globalModJuegoActivo) return;
    const ip = localStorage.getItem('sebas_ip_final_libre') || '192.168.1.28';
    try {
        let fd = new FormData(); fd.append('action', 'list_mods'); fd.append('host_ip', ip); fd.append('cusa_id', globalModJuegoActivo.id);
        let res = await fetch('api/afr_api.php', { method: 'POST', body: fd });
        let json = await res.json();
        if (json.status === 'success') {
            afrDatosBoveda = json.data;
            if (afrDatosBoveda.calibracion_slot === -1) {
                const modalCal = document.getElementById('modal-afr-calibracion');
                modalCal.classList.remove('hidden'); setTimeout(() => modalCal.classList.add('opacity-100'), 10);
            }
            renderizarBovedaAFR_Liquida();
        }
    } catch (error) { console.error("Error al cargar bóveda V3", error); }
};

window.cerrarModalCalibracion = function(ignorar = false) {
    if(typeof emitirEfectoSonidoNativo === 'function') emitirEfectoSonidoNativo('click');
    const modal = document.getElementById('modal-afr-calibracion');
    modal.classList.remove('opacity-100'); 
    setTimeout(() => {
        modal.classList.add('hidden');
        if(ignorar) {
            localStorage.removeItem('mods_global_cusa');
            globalModJuegoActivo = null;
            if (typeof inicializarEnrutadorMods === 'function') {
                inicializarEnrutadorMods();
            }
        }
    }, 300);
};

window.ejecutarCalibracionAFR = async function() {
    if(typeof emitirEfectoSonidoNativo === 'function') emitirEfectoSonidoNativo('click');
    const ip = localStorage.getItem('sebas_ip_final_libre') || '192.168.1.28';
    if(typeof ps5Notification === 'function') ps5Notification("AFR ENGINE", "Calibrando slot base...", "fa-solid fa-radar");
    try {
        let fd = new FormData(); fd.append('action', 'calibrar_motor'); fd.append('host_ip', ip); fd.append('cusa_id', globalModJuegoActivo.id);
        let res = await fetch('api/afr_api.php', { method: 'POST', body: fd });
        let textResult = await res.text();
        
        try {
            let json = JSON.parse(textResult);
            if (json.status === 'success') {
                afrDatosBoveda.calibracion_slot = json.calibracion_slot;
                if(typeof ps5Notification === 'function') ps5Notification("ÉXITO", "Calibración exitosa.", "fa-solid fa-check-double");
                cerrarModalCalibracion();
            } else {
                abrirModalPeligro("ERROR DE CALIBRACIÓN", json.message, null, "ENTENDIDO");
            }
        } catch (e) {
            abrirModalPeligro("ERROR DEL SERVIDOR", "La consola no respondió bien. Revisa IP y juego.", null, "ENTENDIDO");
        }
    } catch (error) {
        abrirModalPeligro("ERROR DE RED", "No se pudo contactar al servidor local.", null, "ENTENDIDO");
    }
};

async function sincronizarDbLocal() {
    const ip = localStorage.getItem('sebas_ip_final_libre') || '192.168.1.28';
    let fd = new FormData(); fd.append('action', 'sync_db'); fd.append('host_ip', ip); fd.append('cusa_id', globalModJuegoActivo.id); fd.append('db_data', JSON.stringify(afrDatosBoveda));
    await fetch('api/afr_api.php', { method: 'POST', body: fd });
}

// ==========================================
// RENDERIZADO VISUAL LÍQUIDO
// ==========================================
window.toggleModoEdicionAFR = function() {
    if(typeof emitirEfectoSonidoNativo === 'function') emitirEfectoSonidoNativo('click');
    afrModoEdicion = !afrModoEdicion;
    const btn = document.getElementById('btn-edit-afr');
    if(afrModoEdicion) {
        btn.classList.add('bg-indigo-600', 'text-white', 'border-indigo-500'); btn.innerHTML = `<i class="fas fa-check mr-2"></i> Hecho`;
    } else {
        btn.classList.remove('bg-indigo-600', 'text-white', 'border-indigo-500'); btn.innerHTML = `<i class="fas fa-pen mr-2"></i> Editar`;
    }
    renderizarBovedaAFR_Liquida();
    if(afrGrupoActivoPopup) refrescarPopupGrupo(); 
};

window.renderizarBovedaAFR_Liquida = function() {
    const contenedor = document.getElementById('contenedor-afr-liquido');
    if (!contenedor) return;
    contenedor.innerHTML = '';

    if (!afrDatosBoveda.categorias || afrDatosBoveda.categorias.length === 0) {
        contenedor.innerHTML = `<div class="w-full py-8 text-center opacity-40 border border-dashed border-white/10 rounded-xl bg-black/20"><span class="text-[11px] uppercase font-bold tracking-widest text-gray-500">Sin Categorías</span></div>`;
        return;
    }

    afrDatosBoveda.categorias.forEach(cat => {
        const catDiv = document.createElement('div');
        catDiv.className = "flex flex-col gap-3 w-full mb-2";
        
        let btnEditCat = afrModoEdicion ? `<button onclick="editarNombreCategoria('${cat.id}')" class="w-10 h-10 rounded-xl bg-white/5 text-gray-400 hover:text-indigo-400 ml-3 flex items-center justify-center active:scale-90"><i class="fas fa-pen text-[12px]"></i></button> <button onclick="eliminarCategoria('${cat.id}')" class="w-10 h-10 rounded-xl bg-red-500/10 text-red-500 hover:text-red-400 ml-2 flex items-center justify-center active:scale-90"><i class="fas fa-trash text-[12px]"></i></button>` : '';

        catDiv.innerHTML = `
            <div class="flex items-center border-b-2 border-white/5 pb-2 mt-2">
                <div class="w-2 h-2 rounded-full bg-indigo-500 shadow-[0_0_8px_rgba(99,102,241,0.8)] mr-3"></div>
                <span class="text-[12px] font-black tracking-widest text-gray-200 uppercase flex-1">${cat.nombre}</span>
                ${btnEditCat}
            </div>
            <div class="flex flex-col gap-3 pl-2" id="box-cat-${cat.id}"></div>
        `;
        contenedor.appendChild(catDiv);

        const boxGrupos = document.getElementById(`box-cat-${cat.id}`);
        const gruposDeCat = afrDatosBoveda.grupos.filter(g => g.id_categoria === cat.id);
        const sueltosDeCat = afrDatosBoveda.mods.filter(m => m.id_grupo === null && m.id_categoria === cat.id); 

        if (gruposDeCat.length === 0 && sueltosDeCat.length === 0) {
            boxGrupos.innerHTML = `<span class="text-[10px] font-mono text-gray-600 uppercase tracking-widest pl-2">- Vacío -</span>`;
        }

        gruposDeCat.forEach(grupo => {
            const modsDelGrupo = afrDatosBoveda.mods.filter(m => m.id_grupo === grupo.id);
            const activos = modsDelGrupo.some(m => m.activo);
            const borderGlow = activos ? 'border-emerald-500/30 bg-emerald-900/10' : 'border-white/5 bg-[#111827] hover:border-indigo-500/30';
            const iconGlow = activos ? 'text-emerald-400' : 'text-indigo-400';

            const grpDiv = document.createElement('div');
            grpDiv.className = `flex items-center justify-between p-4 rounded-xl cursor-pointer transition-all border-2 shadow-lg active:scale-[0.98] ${borderGlow}`;
            grpDiv.onclick = (e) => { if(!afrModoEdicion) abrirPopupGrupoAFR(grupo.id, grupo.nombre, cat.nombre); };

            let btnEditGrp = afrModoEdicion ? `
                <div class="flex items-center gap-2 ml-2" onclick="event.stopPropagation()">
                    <button onclick="editarNombreGrupo('${grupo.id}')" class="w-11 h-11 rounded-xl bg-white/5 text-gray-400 hover:text-indigo-400 flex items-center justify-center shadow-inner active:scale-90"><i class="fas fa-pen text-[12px]"></i></button>
                    <button onclick="eliminarGrupo('${grupo.id}')" class="w-11 h-11 rounded-xl bg-red-500/10 border border-red-500/20 text-red-500 hover:text-red-400 flex items-center justify-center shadow-inner active:scale-90"><i class="fas fa-trash text-[12px]"></i></button>
                </div>
            ` : `<i class="fa-solid fa-chevron-right text-gray-500 text-sm ml-2"></i>`;

            grpDiv.innerHTML = `
                <div class="flex items-center gap-4 overflow-hidden flex-1">
                    <div class="w-10 h-10 rounded-xl bg-black/40 flex items-center justify-center shrink-0 border border-white/5">
                        <i class="fa-solid fa-folder-open ${iconGlow} text-lg"></i>
                    </div>
                    <div class="flex flex-col overflow-hidden">
                        <span class="text-[13px] font-black text-white uppercase truncate leading-tight">${grupo.nombre}</span>
                        <span class="text-[9px] font-mono text-gray-500 mt-1 truncate">${modsDelGrupo.length} Paquete(s) dentro</span>
                    </div>
                </div>
                ${btnEditGrp}
            `;
            boxGrupos.appendChild(grpDiv);
        });

        sueltosDeCat.forEach(mod => {
            const bg = mod.activo ? 'bg-indigo-900/20 border-indigo-500/40' : 'bg-[#1a2235] border-white/5';
            const tColor = mod.activo ? 'text-white' : 'text-gray-300';
            const swBg = mod.activo ? 'bg-indigo-600 shadow-[0_0_15px_rgba(79,70,229,0.4)]' : 'bg-gray-800 border-2 border-white/10';
            const swDot = mod.activo ? 'bg-white translate-x-6' : 'bg-gray-500';

            const modDiv = document.createElement('div');
            modDiv.className = `flex items-center justify-between p-4 rounded-xl border-2 transition-all shadow-md ${bg}`;
            
            let btnEditMod = afrModoEdicion ? `
                <div class="flex items-center gap-2 mr-3 shrink-0">
                    <button onclick="abrirModalEdicionAvanzada('${mod.id}', true)" class="w-11 h-11 rounded-xl bg-white/5 text-gray-400 hover:text-indigo-400 flex items-center justify-center active:scale-90"><i class="fas fa-pen text-[12px]"></i></button>
                    <button onclick="eliminarModCompleto('${mod.id}')" class="w-11 h-11 rounded-xl bg-red-500/10 text-red-500 hover:text-red-400 flex items-center justify-center active:scale-90"><i class="fas fa-trash text-[12px]"></i></button>
                </div>
            ` : '';

            modDiv.innerHTML = `
                <div class="flex items-center gap-4 overflow-hidden flex-1">
                    <div class="w-10 h-10 rounded-xl bg-black/40 flex items-center justify-center shrink-0 border border-white/5 text-indigo-400">
                        <i class="fa-solid fa-cube text-lg"></i>
                    </div>
                    <div class="flex flex-col overflow-hidden pr-2">
                        <span class="text-[13px] font-black uppercase truncate ${tColor}">${mod.nombre}</span>
                        <span class="text-[9px] font-mono text-gray-500 truncate mt-1">Mod Suelto (Base)</span>
                    </div>
                </div>
                ${btnEditMod}
                <div onclick="toggleModEstadoAFR('${mod.id}')" class="w-12 h-6 rounded-full flex items-center p-1 cursor-pointer shrink-0 transition-colors ${swBg}">
                    <div class="w-4 h-4 rounded-full transition-transform ${swDot}"></div>
                </div>
            `;
            boxGrupos.appendChild(modDiv);
        });
    });
};

// ==========================================
// POPUP FLOTANTE DE GRUPO
// ==========================================
window.abrirPopupGrupoAFR = function(id_grupo, nombre_grupo, nombre_cat) {
    if(typeof emitirEfectoSonidoNativo === 'function') emitirEfectoSonidoNativo('click');
    afrGrupoActivoPopup = id_grupo;
    document.getElementById('popup-grupo-categoria').innerText = nombre_cat;
    document.getElementById('popup-grupo-titulo').innerText = nombre_grupo;
    refrescarPopupGrupo();
    const modal = document.getElementById('modal-afr-popup-grupo');
    const content = document.getElementById('content-afr-popup-grupo');
    modal.classList.remove('hidden'); setTimeout(() => { modal.classList.add('opacity-100'); content.classList.remove('translate-y-full'); }, 10);
};

window.cerrarPopupGrupoAFR = function(e, force = false) {
    if (e && e.target !== document.getElementById('modal-afr-popup-grupo') && !force) return;
    if(typeof emitirEfectoSonidoNativo === 'function') emitirEfectoSonidoNativo('click');
    afrGrupoActivoPopup = null;
    const modal = document.getElementById('modal-afr-popup-grupo');
    const content = document.getElementById('content-afr-popup-grupo');
    content.classList.add('translate-y-full'); modal.classList.remove('opacity-100');
    setTimeout(() => modal.classList.add('hidden'), 300); renderizarBovedaAFR_Liquida();
};

window.refrescarPopupGrupo = function() {
    if(!afrGrupoActivoPopup) return;
    const listaBase = document.getElementById('popup-lista-base');
    const listaVar = document.getElementById('popup-lista-variantes');
    listaBase.innerHTML = ''; listaVar.innerHTML = '';

    const mods = afrDatosBoveda.mods.filter(m => m.id_grupo === afrGrupoActivoPopup);
    const bases = mods.filter(m => m.tipo === 'base');
    const variantes = mods.filter(m => m.tipo === 'variante');

    const dibujarItem = (mod) => {
        const bg = mod.activo ? 'bg-indigo-900/20 border-indigo-500/40' : 'bg-[#111827] border-2 border-white/5';
        const tColor = mod.activo ? 'text-white' : 'text-gray-300';
        const swBg = mod.activo ? (mod.tipo === 'base' ? 'bg-indigo-600 shadow-[0_0_15px_rgba(79,70,229,0.4)]' : 'bg-rose-600 shadow-[0_0_15px_rgba(225,29,72,0.4)]') : 'bg-gray-800 border border-white/10';
        const swDot = mod.activo ? 'bg-white translate-x-6' : 'bg-gray-500';

        let btnEdit = `
            <div class="flex items-center gap-2 mr-3 shrink-0">
                <button onclick="abrirModalEdicionAvanzada('${mod.id}', false)" class="w-10 h-10 rounded-xl bg-white/5 text-gray-400 hover:text-indigo-400 flex items-center justify-center active:scale-90 transition-all"><i class="fas fa-pen text-[11px]"></i></button>
                <button onclick="eliminarModCompleto('${mod.id}')" class="w-10 h-10 rounded-xl bg-red-500/10 text-red-500 hover:text-red-400 flex items-center justify-center active:scale-90 transition-all"><i class="fas fa-trash text-[11px]"></i></button>
            </div>
        `;

        return `
            <div class="flex items-center justify-between p-3.5 rounded-xl transition-all ${bg}">
                <div class="flex flex-col overflow-hidden pr-2 flex-1">
                    <span class="text-[13px] font-black uppercase truncate ${tColor}">${mod.nombre}</span>
                    <span class="text-[9px] font-mono text-gray-500 mt-1 truncate">${mod.activo ? 'ACTIVO (Módulo en Consola)' : 'APAGADO (Bóveda)'}</span>
                </div>
                ${btnEdit}
                <div onclick="toggleModEstadoAFR('${mod.id}')" class="w-12 h-6 rounded-full flex items-center p-1 cursor-pointer shrink-0 transition-colors ${swBg}">
                    <div class="w-4 h-4 rounded-full transition-transform ${swDot}"></div>
                </div>
            </div>
        `;
    };

    if (bases.length === 0) listaBase.innerHTML = `<span class="text-[10px] font-mono text-gray-600 pl-2">- Sin Mod Base -</span>`;
    bases.forEach(m => listaBase.innerHTML += dibujarItem(m));

    if (variantes.length === 0) listaVar.innerHTML = `<span class="text-[10px] font-mono text-gray-600 pl-2">- Sin Variantes -</span>`;
    variantes.forEach(m => listaVar.innerHTML += dibujarItem(m));
};

window.toggleModEstadoAFR = async function(modId) {
    if(typeof emitirEfectoSonidoNativo === 'function') emitirEfectoSonidoNativo('click');
    const ip = localStorage.getItem('sebas_ip_final_libre') || '192.168.1.28';
    if(typeof ps5Notification === 'function') ps5Notification("AFR ENGINE", "Renombrando slots...", "fa-solid fa-microchip");

    try {
        let fd = new FormData(); fd.append('action', 'toggle_mod'); fd.append('host_ip', ip); fd.append('cusa_id', globalModJuegoActivo.id); fd.append('mod_id', modId);
        let res = await fetch('api/afr_api.php', { method: 'POST', body: fd });
        let json = await res.json();

        if (json.status === 'success') {
            afrDatosBoveda = json.data; 
            if (afrGrupoActivoPopup) { refrescarPopupGrupo(); } else { renderizarBovedaAFR_Liquida(); }
        } else {
            if (json.message === 'REQUIERE_CALIBRACION') {
                const modalCal = document.getElementById('modal-afr-calibracion');
                modalCal.classList.remove('hidden'); setTimeout(() => modalCal.classList.add('opacity-100'), 10);
            }
        }
    } catch (error) { 
        abrirModalPeligro("ERROR DE RED", "No se pudo cambiar el estado del mod.", null, "ENTENDIDO");
    }
};

// ==========================================
// 🔥 NUEVO: FLUJO DE SUBIDA REANUDABLE E INTELIGENTE
// ==========================================
window.iniciarFlujoSubidaAFR = function() {
    if(typeof emitirEfectoSonidoNativo === 'function') emitirEfectoSonidoNativo('click');
    if (!afrDatosBoveda.categorias || afrDatosBoveda.categorias.length === 0) { 
        abrirModalPeligro("ERROR", "Debes crear al menos una categoría primero.", null, "ENTENDIDO"); 
        return; 
    }
    document.getElementById('input-afr-file').click();
};

window.procesarArchivosAFRUnificado = function(event) {
    afrArchivosPendientes = Array.from(event.target.files);
    if (afrArchivosPendientes.length === 0) return;

    document.getElementById('afr-upload-file-count').innerText = `${afrArchivosPendientes.length} archivo(s) seleccionado(s)`;
    document.getElementById('afr-mod-name').value = '';
    
    const selCat = document.getElementById('afr-mod-cat');
    selCat.innerHTML = '';
    afrDatosBoveda.categorias.forEach(cat => { 
        selCat.innerHTML += `<option value="${cat.id}">${cat.nombre}</option>`; 
    });
    
    actualizarDesplegableDestino();

    const modal = document.getElementById('modal-afr-upload-config');
    modal.classList.remove('hidden'); 
    setTimeout(() => { 
        modal.classList.add('opacity-100'); 
        modal.querySelector('div').classList.remove('scale-95'); 
    }, 10);
};

window.actualizarDesplegableDestino = function() {
    const elCat = document.getElementById('afr-mod-cat');
    if (!elCat || !elCat.value) return; 
    
    const idCat = elCat.value;
    const selDest = document.getElementById('afr-mod-destino');
    selDest.innerHTML = '';
    
    selDest.innerHTML += `<option value="loose" class="font-bold text-indigo-400">📦 MOD SUELTO (Independiente)</option>`;
    selDest.innerHTML += `<option value="new_group" class="font-bold text-emerald-400">➕ CREAR NUEVO GRUPO</option>`;
    
    const grupos = afrDatosBoveda.grupos.filter(g => g.id_categoria === idCat);
    if (grupos.length > 0) {
        const optG = document.createElement('optgroup'); optG.label = "GRUPOS EXISTENTES:";
        grupos.forEach(g => { optG.innerHTML += `<option value="${g.id}">📁 ${g.nombre}</option>`; });
        selDest.appendChild(optG);
    }
    verificarInputNuevoGrupo();
};

window.verificarInputNuevoGrupo = function() {
    const val = document.getElementById('afr-mod-destino').value;
    const boxNuevo = document.getElementById('box-nuevo-grupo');
    document.getElementById('afr-mod-nuevo-grupo').value = '';
    if (val === 'new_group') { boxNuevo.classList.remove('hidden'); } else { boxNuevo.classList.add('hidden'); }
};

window.cerrarModalAFRUpload = function() {
    const modal = document.getElementById('modal-afr-upload-config');
    modal.classList.remove('opacity-100'); modal.querySelector('div').classList.add('scale-95');
    setTimeout(() => modal.classList.add('hidden'), 300);
    document.getElementById('input-afr-file').value = '';
};

// 🔥 ESTA FUNCIÓN SOLO PREPARA EL ESTADO Y LLAMA AL BUCLE DE SUBIDA
window.confirmarSubidaUnificadaAFR = async function() {
    const nombre = document.getElementById('afr-mod-name').value.trim();
    if (!nombre) { abrirModalPeligro("DATOS INCOMPLETOS", "Ponle un nombre visual al mod.", null, "ENTENDIDO"); return; }

    const idCat = document.getElementById('afr-mod-cat').value;
    const destinoVal = document.getElementById('afr-mod-destino').value;
    let finalGrupoId = null;
    let finalTipo = 'base'; 

    if (destinoVal === 'new_group') {
        const nombreNuevo = document.getElementById('afr-mod-nuevo-grupo').value.trim();
        if (!nombreNuevo) { abrirModalPeligro("DATOS INCOMPLETOS", "Ponle nombre al nuevo grupo.", null, "ENTENDIDO"); return; }
        finalGrupoId = 'grp_' + Date.now();
        afrDatosBoveda.grupos.push({ id: finalGrupoId, id_categoria: idCat, nombre: nombreNuevo });
        finalTipo = 'base'; 
    } else if (destinoVal !== 'loose') {
        finalGrupoId = destinoVal;
        finalTipo = 'variante'; 
    }

    cerrarModalAFRUpload();

    // Guardamos todo el estado de forma global por si hay que reintentar
    afrUploadState = {
        nombre: nombre,
        idCat: idCat,
        finalGrupoId: finalGrupoId,
        finalTipo: finalTipo,
        mod_id: 'mod_' + Date.now() // Se crea solo una vez por paquete
    };

    const modalProgreso = document.getElementById('modal-afr-upload-progress');
    modalProgreso.classList.remove('hidden'); 
    document.getElementById('afr-upload-bar').style.width = '0%'; 
    document.getElementById('afr-upload-pct').innerText = '0%';
    setTimeout(() => modalProgreso.classList.add('opacity-100'), 10);

    // Iniciamos la subida real
    await ejecutarBucleSubidaAFR();
};

// 🔥 EL BUCLE DE SUBIDA REAL (PUEDE LLAMARSE MÚLTIPLES VECES SI HAY ERROR)
window.ejecutarBucleSubidaAFR = async function() {
    const txtProgreso = document.getElementById('afr-progress-text');
    const barra = document.getElementById('afr-upload-bar');
    const pctTxt = document.getElementById('afr-upload-pct');
    const modalProgreso = document.getElementById('modal-afr-upload-progress');
    const ip = localStorage.getItem('sebas_ip_final_libre') || '192.168.1.28';
    
    // Fragmentos de 512KB para máxima compatibilidad con Termux
    const CHUNK_SIZE = 512 * 1024; 

    try {
        let fileHashes = [];
        let totalBytesToUpload = 0;
        let bytesUploadedGlobal = 0;
        
        afrArchivosPendientes.forEach(f => totalBytesToUpload += f.size);

        // FASE 1: SUBIR FRAGMENTOS AL CELULAR (CON REANUDACIÓN)
        for (let i = 0; i < afrArchivosPendientes.length; i++) {
            const file = afrArchivosPendientes[i];
            
            // Creamos una huella única para este archivo (eliminamos caracteres raros por si acaso)
            const safeName = file.name.replace(/[^a-zA-Z0-9_]/g, '');
            const fileHash = safeName + '_' + file.size;
            fileHashes.push(fileHash);

            // Preguntamos a Termux si ya tiene una parte de este archivo guardado
            let fdCheck = new FormData();
            fdCheck.append('action', 'check_resume');
            fdCheck.append('file_hash', fileHash);
            
            let resCheck = await fetch('api/afr_api.php', { method: 'POST', body: fdCheck });
            let jsonCheck = await resCheck.json();
            
            let existingSize = jsonCheck.size || 0;
            // Aseguramos empezar desde el múltiplo del chunk exacto
            let startOffset = Math.floor(existingSize / CHUNK_SIZE) * CHUNK_SIZE;
            
            bytesUploadedGlobal += startOffset; 
            const totalChunks = Math.ceil(file.size / CHUNK_SIZE);

            for (let offset = startOffset; offset < file.size; offset += CHUNK_SIZE) {
                let chunkIndex = Math.floor(offset / CHUNK_SIZE);
                let txtReanudado = startOffset > 0 ? `<br><span class="text-[8.5px] text-emerald-400">Reanudando desde la parte ${chunkIndex+1}...</span>` : '';
                
                txtProgreso.innerHTML = `Subiendo al Celular: Archivo ${i+1}/${afrArchivosPendientes.length} (Parte ${chunkIndex+1}/${totalChunks})${txtReanudado}`;
                
                const chunk = file.slice(offset, Math.min(offset + CHUNK_SIZE, file.size));
                let fdChunk = new FormData(); 
                fdChunk.append('action', 'upload_chunk'); 
                fdChunk.append('file_hash', fileHash); 
                fdChunk.append('offset', offset); 
                fdChunk.append('chunk', chunk);
                
                let resChunk = await fetch('api/afr_api.php', { method: 'POST', body: fdChunk });
                let textRes = await resChunk.text();
                
                try {
                    let jsonChunk = JSON.parse(textRes);
                    if (jsonChunk.status !== 'success') throw new Error(jsonChunk.message || "Fallo en chunk");
                } catch(e) {
                    throw new Error("Termux falló al recibir fragmento. Reintenta.");
                }

                bytesUploadedGlobal += chunk.size;
                let pct = Math.round((bytesUploadedGlobal / totalBytesToUpload) * 100);
                barra.style.width = pct + '%'; pctTxt.innerText = pct + '%';
            }
        }

        // FASE 2: TRANSFERENCIA FTP A LA CONSOLA Y ENSAMBLAJE
        txtProgreso.innerHTML = `Transferencia FTP hacia PS4...<br><b class='text-amber-400 mt-2 block'>Puede tardar minutos dependiendo del peso.</b>`;
        
        let fdFinal = new FormData(); 
        fdFinal.append('action', 'finalize_mod'); 
        fdFinal.append('host_ip', ip); 
        fdFinal.append('cusa_id', globalModJuegoActivo.id); 
        fdFinal.append('mod_id', afrUploadState.mod_id); 
        fdFinal.append('total_files', afrArchivosPendientes.length);
        fdFinal.append('file_hashes', JSON.stringify(fileHashes)); 
        
        let resFinal = await fetch('api/afr_api.php', { method: 'POST', body: fdFinal });
        let textFinal = await resFinal.text();
        
        try {
            let jsonFinal = JSON.parse(textFinal);
            if (jsonFinal.status === 'success') {
                afrDatosBoveda.mods.push({ id: afrUploadState.mod_id, id_categoria: afrUploadState.idCat, id_grupo: afrUploadState.finalGrupoId, nombre: afrUploadState.nombre, tipo: afrUploadState.finalTipo, archivos_off: jsonFinal.archivos_off, archivos_pak: [], activo: false });
                await sincronizarDbLocal();
                cargarBovedaAFR_V3();
                if(typeof ps5Notification === 'function') ps5Notification("ÉXITO", "Transferencia FTP completada.", "fa-solid fa-check");
                
                modalProgreso.classList.remove('opacity-100'); setTimeout(() => modalProgreso.classList.add('hidden'), 300);
                afrUploadState = null; // Limpiamos el estado al terminar
            } else {
                abrirModalPeligro("ERROR FTP", jsonFinal.message, () => {
                    ejecutarBucleSubidaAFR(); // Callback de Reintentar
                }, "REINTENTAR", () => {
                    modalProgreso.classList.remove('opacity-100'); setTimeout(() => modalProgreso.classList.add('hidden'), 300);
                    afrUploadState = null; // Callback de Cancelar
                }, "CANCELAR");
            }
        } catch(e) {
            abrirModalPeligro("ERROR DE SISTEMA", "Termux devolvió un error inesperado.", () => {
                ejecutarBucleSubidaAFR();
            }, "REINTENTAR", () => {
                modalProgreso.classList.remove('opacity-100'); setTimeout(() => modalProgreso.classList.add('hidden'), 300);
                afrUploadState = null;
            }, "CANCELAR");
        }

    } catch (error) { 
        // 🔥 SI ALGO FALLA, LE DAMOS LA OPCIÓN DE REINTENTAR O CANCELAR CORRECTAMENTE
        abrirModalPeligro("ERROR EN SUBIDA", error.message, () => {
            ejecutarBucleSubidaAFR(); // Reintenta usando el mismo afrUploadState
        }, "REINTENTAR", () => {
            modalProgreso.classList.remove('opacity-100'); setTimeout(() => modalProgreso.classList.add('hidden'), 300);
            afrUploadState = null; // Limpia todo
        }, "CANCELAR");
    }
};

// ==========================================
// EDICIÓN AVANZADA DE MODS 
// ==========================================
window.abrirModalEdicionAvanzada = function(id, isSuelto = false) {
    if(typeof emitirEfectoSonidoNativo === 'function') emitirEfectoSonidoNativo('click');
    
    const mod = afrDatosBoveda.mods.find(m => m.id === id);
    if (!mod) return;

    document.getElementById('edit-avanzado-id').value = mod.id;
    document.getElementById('edit-avanzado-is-suelto').value = isSuelto ? 'true' : 'false';
    document.getElementById('edit-avanzado-name').value = mod.nombre;

    const selCat = document.getElementById('edit-avanzado-cat');
    selCat.innerHTML = '';
    afrDatosBoveda.categorias.forEach(cat => {
        selCat.innerHTML += `<option value="${cat.id}">${cat.nombre}</option>`;
    });
    selCat.value = mod.id_categoria;

    actualizarDesplegableGruposEdit(mod.id_grupo);

    document.getElementById('edit-avanzado-tipo').value = mod.tipo;

    const modal = document.getElementById('modal-afr-edit-avanzado');
    modal.classList.remove('hidden'); 
    setTimeout(() => { 
        modal.classList.add('opacity-100'); 
        modal.querySelector('div').classList.remove('scale-95'); 
    }, 10);
};

window.actualizarDesplegableGruposEdit = function(selectGroupId = null) {
    const idCat = document.getElementById('edit-avanzado-cat').value;
    const selGrp = document.getElementById('edit-avanzado-grupo');
    selGrp.innerHTML = `<option value="loose" class="font-bold text-indigo-400">📦 MOD SUELTO (Sin Grupo)</option>`;
    
    const grupos = afrDatosBoveda.grupos.filter(g => g.id_categoria === idCat);
    if (grupos.length > 0) {
        const optG = document.createElement('optgroup'); optG.label = "GRUPOS EXISTENTES:";
        grupos.forEach(g => { optG.innerHTML += `<option value="${g.id}">📁 ${g.nombre}</option>`; });
        selGrp.appendChild(optG);
    }

    if (selectGroupId && selectGroupId !== 'null') {
        selGrp.value = selectGroupId;
    } else {
        selGrp.value = 'loose';
    }
};

window.cerrarModalEdicionAvanzada = function() {
    const modal = document.getElementById('modal-afr-edit-avanzado');
    modal.classList.remove('opacity-100'); 
    modal.querySelector('div').classList.add('scale-95');
    setTimeout(() => modal.classList.add('hidden'), 300);
};

window.guardarEdicionAvanzadaMod = async function() {
    const id = document.getElementById('edit-avanzado-id').value;
    const nombre = document.getElementById('edit-avanzado-name').value.trim();
    const idCat = document.getElementById('edit-avanzado-cat').value;
    const destinoVal = document.getElementById('edit-avanzado-grupo').value;
    const tipoVal = document.getElementById('edit-avanzado-tipo').value;

    if (!nombre) { abrirModalPeligro("ERROR", "El nombre no puede estar vacío.", null, "ENTENDIDO"); return; }

    const mod = afrDatosBoveda.mods.find(m => m.id === id);
    if (mod) {
        mod.nombre = nombre;
        mod.id_categoria = idCat;
        mod.id_grupo = (destinoVal === 'loose') ? null : destinoVal;
        mod.tipo = tipoVal;
    }

    cerrarModalEdicionAvanzada();
    await sincronizarDbLocal();
    
    renderizarBovedaAFR_Liquida();
    
    if (afrGrupoActivoPopup) {
        if (mod.id_grupo !== afrGrupoActivoPopup) {
            cerrarPopupGrupoAFR(null, true);
        } else {
            refrescarPopupGrupo();
        }
    }
};

// ==========================================
// CRUD BÁSICO DE CATEGORÍAS Y GRUPOS
// ==========================================
window.crearNuevaCategoriaFlujo = function() {
    abrirModalTexto("Nueva Categoría", "Ej: Mapas HD...", "", async (val) => {
        afrDatosBoveda.categorias.push({ id: 'cat_' + Date.now(), nombre: val });
        await sincronizarDbLocal(); renderizarBovedaAFR_Liquida();
    });
};
window.editarNombreCategoria = function(id) {
    let cat = afrDatosBoveda.categorias.find(c => c.id === id);
    abrirModalTexto("Renombrar Categoría", "", cat.nombre, async (val) => {
        cat.nombre = val; await sincronizarDbLocal(); renderizarBovedaAFR_Liquida();
    });
};
window.editarNombreGrupo = function(id) {
    let grp = afrDatosBoveda.grupos.find(g => g.id === id);
    abrirModalTexto("Renombrar Grupo", "", grp.nombre, async (val) => {
        grp.nombre = val; await sincronizarDbLocal(); renderizarBovedaAFR_Liquida();
    });
};
window.eliminarCategoria = function(id) {
    abrirModalPeligro("Destruir Categoría", "Si borras la categoría, se perderán los grupos y mods que contenga.", async () => {
        afrDatosBoveda.categorias = afrDatosBoveda.categorias.filter(c => c.id !== id);
        await sincronizarDbLocal(); renderizarBovedaAFR_Liquida();
    }, "SÍ, DESTRUIR");
};
window.eliminarGrupo = function(id) {
    abrirModalPeligro("Destruir Grupo", "Perderás todos los mods dentro de esta carpeta.", async () => {
        afrDatosBoveda.grupos = afrDatosBoveda.grupos.filter(g => g.id !== id);
        await sincronizarDbLocal(); renderizarBovedaAFR_Liquida();
    }, "SÍ, DESTRUIR");
};
window.eliminarModCompleto = function(id) {
    abrirModalPeligro("Destruir Archivos", "Se borrará de forma permanente de tu PS4.", async () => {
        const mod = afrDatosBoveda.mods.find(m => m.id === id);
        if(!mod) return;
        const ip = localStorage.getItem('sebas_ip_final_libre') || '192.168.1.28';
        let fd = new FormData(); fd.append('action', 'delete_mod_files'); fd.append('host_ip', ip); fd.append('cusa_id', globalModJuegoActivo.id); fd.append('archivos', JSON.stringify(mod.activo ? mod.archivos_pak : mod.archivos_off));
        await fetch('api/afr_api.php', { method: 'POST', body: fd });
        afrDatosBoveda.mods = afrDatosBoveda.mods.filter(m => m.id !== id);
        await sincronizarDbLocal(); 
        if (afrGrupoActivoPopup) { refrescarPopupGrupo(); } else { renderizarBovedaAFR_Liquida(); }
    }, "SÍ, DESTRUIR");
};
