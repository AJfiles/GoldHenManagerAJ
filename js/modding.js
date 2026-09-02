/**
 * ====================================================================
 * GOLD HEN SUITE PRO 🚀 - CONTROLADOR DE MODDING
 * DEVELOPED By SeBaS - RUTA: js/modding.js
 * ====================================================================
 */

let moddingArchivoPendiente = null;
let moddingSourceType = 'file'; 
let moddingServerPath = ''; 
let moddingJuegoActivo = null;

let listaBackupsServer = [];
let listaCarpetaLocal = []; 
let moddingTabActiva = 'backups';
let observerModding = null; 

// Variables del Gestor Inteligente
let smartFilesArray = []; 
let moddingSelectorTarget = 'manual'; 

document.addEventListener("DOMContentLoaded", () => {
    const capaModding = document.getElementById('layer-modding');
    if (capaModding) {
        const observadorCapa = new MutationObserver(() => {
            if (capaModding.classList.contains('active')) { 
                // 🔥 FIX UX: Forzamos la pestaña "Manual" silenciosamente cada vez que se abre el módulo
                if (typeof switchModdingTab === 'function') {
                    switchModdingTab('manual', false); 
                }
                
                inicializarModuloModding(); 
                cargarGaleriaDesdeServidor('listar_backups'); 
                cargarGaleriaDesdeServidor('listar_local'); 
            } else {
                limpiarPreviewModding();
                if (observerModding) observerModding.disconnect();
            }
        });
        observadorCapa.observe(capaModding, { attributes: true, attributeFilter: ['class'] });
    }
    
    observerModding = new IntersectionObserver((entries, obs) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const div = entry.target;
                const url = div.getAttribute('data-src');
                if (url) {
                    div.style.backgroundImage = `url('${url}')`;
                    div.classList.remove('skeleton-modding');
                    div.removeAttribute('data-src');
                }
                obs.unobserve(div);
            }
        });
    }, { root: document.getElementById('galeria-modding-grid'), threshold: 0.1 });
});

// Función: Rompe la caché del navegador para actualizar fotos en vivo
function forzarActualizacionDeCacheVisual() {
    const timestamp = new Date().getTime();
    if (typeof listadoJuegos !== 'undefined' && listadoJuegos.length > 0) {
        listadoJuegos.forEach(jg => {
            if (jg.img) {
                let baseUrl = jg.img.split('?v=')[0];
                jg.img = `${baseUrl}?v=${timestamp}`;
            }
        });
    }
    
    if (typeof renderizarBiblioteca === 'function' && typeof listadoJuegos !== 'undefined') {
        const contenedorBiblio = document.getElementById('grid-biblioteca');
        if (contenedorBiblio) {
            renderizarBiblioteca(listadoJuegos);
        }
    }
}

async function inicializarModuloModding() {
    if (typeof listadoJuegos === 'undefined' || listadoJuegos.length === 0) {
        if (typeof levantarCacheLocalBiblioteca === 'function') {
            await levantarCacheLocalBiblioteca();
        }
    }

    const cusaActivo = localStorage.getItem('modding_cusa_activo');
    if (!cusaActivo) {
        document.getElementById('modding-title').innerText = "Toca para seleccionar juego";
        document.getElementById('modding-cusa').innerText = "---";
        const avatar = document.getElementById('modding-avatar');
        if(avatar) avatar.style.backgroundImage = 'none';
        const bgBlur = document.getElementById('modding-bg-blur');
        if(bgBlur) bgBlur.style.backgroundImage = 'none';
        return;
    }

    if (typeof listadoJuegos !== 'undefined') {
        moddingJuegoActivo = listadoJuegos.find(j => j.id === cusaActivo);
    }

    if (moddingJuegoActivo) {
        document.getElementById('modding-title').innerText = moddingJuegoActivo.nombre;
        document.getElementById('modding-cusa').innerText = moddingJuegoActivo.id;
        
        const avatar = document.getElementById('modding-avatar');
        const bgBlur = document.getElementById('modding-bg-blur');
        
        const freshUrl = moddingJuegoActivo.img.includes('?v=') ? moddingJuegoActivo.img : `${moddingJuegoActivo.img}?v=${new Date().getTime()}`;
        
        if (avatar) avatar.style.backgroundImage = `url('${freshUrl}')`;
        if (bgBlur) bgBlur.style.backgroundImage = `url('${freshUrl}')`;
    }
}

function limpiarPreviewModding() {
    moddingArchivoPendiente = null;
    moddingSourceType = 'file';
    moddingServerPath = '';
    
    const previewImg = document.getElementById('modding-preview-img');
    if (previewImg) previewImg.setAttribute('src', '');
    
    document.getElementById('input-modding-file').value = "";
}

async function abrirSelectorJuegosModding(target = 'manual') {
    moddingSelectorTarget = target;
    
    const container = document.getElementById('lista-juegos-modding');
    if (!container) return;

    if (typeof listadoJuegos === 'undefined' || listadoJuegos.length === 0) {
        if (typeof levantarCacheLocalBiblioteca === 'function') {
            await levantarCacheLocalBiblioteca();
        }
    }

    const buscador = document.getElementById('buscador-juegos-modding');
    if (buscador) buscador.value = '';

    let juegosOrdenados = [...(listadoJuegos || [])].sort((a, b) => a.nombre.localeCompare(b.nombre));
    renderizarListaJuegosModding(juegosOrdenados);

    const modal = document.getElementById('modal-selector-juegos-modding');
    if (modal) {
        modal.classList.remove('hidden');
        setTimeout(() => {
            modal.classList.add('opacity-100');
            document.getElementById('modal-selector-juegos-content').classList.remove('translate-y-full');
        }, 10);
    }
}

function cerrarSelectorJuegosModding(e) {
    if (e && e.target !== document.getElementById('modal-selector-juegos-modding')) return;
    document.getElementById('modal-selector-juegos-content').classList.add('translate-y-full');
    const modal = document.getElementById('modal-selector-juegos-modding');
    if (modal) {
        modal.classList.remove('opacity-100');
        setTimeout(() => modal.classList.add('hidden'), 300);
    }
}

function renderizarListaJuegosModding(juegos) {
    const container = document.getElementById('lista-juegos-modding');
    if (!container) return;
    container.innerHTML = '';

    if (!juegos || juegos.length === 0) {
        container.innerHTML = `<div class="py-6 text-center text-[10px] font-black tracking-widest text-gray-500 uppercase">Sin resultados en caché</div>`;
        return;
    }

    juegos.forEach(jg => {
        const isActive = moddingJuegoActivo && moddingJuegoActivo.id === jg.id && moddingSelectorTarget === 'manual';
        const activeClasses = isActive ? 'bg-purple-500/10 border-purple-500/30' : 'bg-[#111827] border-white/5 hover:bg-white/5 hover:border-purple-500/20';
        const textClasses = isActive ? 'text-purple-400' : 'text-gray-200';

        const div = document.createElement('div');
        div.className = `flex items-center gap-3 p-3 rounded-xl cursor-pointer transition-all active:scale-[0.98] border ${activeClasses}`;
        div.onclick = () => seleccionarJuegoModding(jg.id);

        div.innerHTML = `
            <div class="w-10 h-10 rounded-lg bg-cover bg-center border border-white/10 shrink-0" style="background-image: url('${jg.img}')"></div>
            <div class="flex flex-col flex-1 overflow-hidden">
                <span class="text-[11px] font-black uppercase tracking-widest ${textClasses} truncate">${jg.nombre}</span>
                <span class="text-[9px] font-mono text-gray-500 mt-0.5">${jg.id}</span>
            </div>
            ${isActive ? '<i class="fa-solid fa-check-circle text-purple-400 text-lg shrink-0"></i>' : ''}
        `;
        container.appendChild(div);
    });
}

function filtrarSelectorJuegosModding() {
    const query = document.getElementById('buscador-juegos-modding').value.toLowerCase().trim();
    const filtrados = (listadoJuegos || []).filter(j => 
        j.nombre.toLowerCase().includes(query) || j.id.toLowerCase().includes(query)
    ).sort((a, b) => a.nombre.localeCompare(b.nombre));
    
    renderizarListaJuegosModding(filtrados);
}

function seleccionarJuegoModding(cusa) {
    cerrarSelectorJuegosModding();
    const jg = listadoJuegos.find(j => j.id === cusa);
    if (!jg) return;

    if (moddingSelectorTarget === 'manual') {
        localStorage.setItem('modding_cusa_activo', cusa);
        inicializarModuloModding(); 
        limpiarPreviewModding(); 
    } else {
        asignarJuegoASmartFile(moddingSelectorTarget, jg);
    }
}

async function cargarGaleriaDesdeServidor(accion) {
    try {
        let res = await fetch(`api/modding_api.php?action=${accion}`);
        let json = await res.json();
        if (json && json.status === 'success') {
            if (accion === 'listar_backups') listaBackupsServer = json.data || [];
            if (accion === 'listar_local') listaCarpetaLocal = json.data || [];
            renderizarGaleriaModding();
        }
    } catch(e) {}
}

function forzarRecargaGaleriasModding() {
    cargarGaleriaDesdeServidor('listar_backups');
    cargarGaleriaDesdeServidor('listar_local');
    if(typeof ps5Notification === 'function') ps5Notification("SISTEMA", "Buscando imágenes en Termux...", "fa-solid fa-sync-alt");
}

async function respaldarPortadaActual() {
    if (!moddingJuegoActivo) {
        if(typeof ps5Notification === 'function') ps5Notification("ERROR", "Selecciona un juego primero.", "fa-solid fa-exclamation-triangle");
        return;
    }
    
    if(typeof ps5Notification === 'function') ps5Notification("EXTRAYENDO", "Descargando portada de la consola...", "fa-solid fa-download");
    const ip = localStorage.getItem('sebas_ip_final_libre') || '192.168.1.28';
    
    let fd = new FormData();
    fd.append('action', 'respaldar');
    fd.append('host_ip', ip);
    fd.append('cusa_id', moddingJuegoActivo.id);
    
    try {
        let res = await fetch('api/modding_api.php', { method: 'POST', body: fd });
        let json = await res.json();
        if (json && json.status === 'success') {
            if(typeof ps5Notification === 'function') ps5Notification("ÉXITO", "Portada respaldada correctamente.", "fa-solid fa-check");
            await cargarGaleriaDesdeServidor('listar_backups'); 
            cambiarTabGaleria('backups');
        } else {
            if(typeof ps5Notification === 'function') ps5Notification("ERROR", json.message || "Fallo al extraer.", "fa-solid fa-times");
        }
    } catch(e) {
        if(typeof ps5Notification === 'function') ps5Notification("ERROR", "Se perdió la conexión.", "fa-solid fa-wifi");
    }
}

function cambiarTabGaleria(tab) {
    moddingTabActiva = tab;
    
    const tabBackups = document.getElementById('tab-backups');
    const tabLocal = document.getElementById('tab-local');
    
    const claseActiva = "text-[9px] font-black tracking-widest uppercase px-4 py-1.5 rounded-lg bg-purple-500/20 text-purple-400 transition-all shadow-sm";
    const claseInactiva = "text-[9px] font-black tracking-widest uppercase px-4 py-1.5 rounded-lg text-gray-500 hover:text-gray-300 transition-all bg-transparent shadow-none";
    
    if (tab === 'backups') {
        if (tabBackups) tabBackups.className = claseActiva;
        if (tabLocal) tabLocal.className = claseInactiva;
    } else {
        if (tabLocal) tabLocal.className = claseActiva;
        if (tabBackups) tabBackups.className = claseInactiva;
    }
    
    document.getElementById('contenedor-galeria-modding').classList.remove('hidden');
    renderizarGaleriaModding();
}

function renderizarGaleriaModding() {
    const grid = document.getElementById('galeria-modding-grid');
    const cont = document.getElementById('contador-galeria-modding');
    if (!grid) return;
    
    grid.innerHTML = '';
    if (observerModding) observerModding.disconnect();

    const listaActiva = (moddingTabActiva === 'backups') ? listaBackupsServer : listaCarpetaLocal;
    
    if (cont) cont.innerText = listaActiva.length;

    if (listaActiva.length === 0) {
        if (moddingTabActiva === 'backups') {
            grid.innerHTML = `<div class="col-span-full flex flex-col items-center justify-center h-[80px] w-full opacity-60"><span class="text-[9px] text-gray-500 font-mono tracking-widest uppercase text-center">No hay backups guardados.</span></div>`;
        } else {
            grid.innerHTML = `
            <div class="col-span-full flex flex-col items-center justify-center h-[80px] w-full opacity-60">
                <i class="fa-solid fa-folder-open text-2xl text-gray-500 mb-1"></i>
                <span class="text-[8px] text-gray-400 font-bold uppercase tracking-widest text-center leading-tight mt-1">
                    Carpeta Vacía<br>
                    <span class="text-[7px] text-purple-400 font-mono lowercase">user/portadas_custom/</span>
                </span>
            </div>`;
        }
        return;
    }
    
    listaActiva.forEach((img) => {
        const div = document.createElement('div');
        div.className = "w-[80px] h-[80px] shrink-0 rounded-2xl border border-white/5 cursor-pointer active:scale-90 transition-all shadow-md bg-cover bg-center skeleton-modding hover:border-purple-500/50";
        div.setAttribute('data-src', img.full_url);
        div.onclick = () => {
            moddingSourceType = 'server';
            moddingServerPath = img.path_relativo;
            document.getElementById('modding-preview-img').src = img.full_url;
            if(typeof ps5Notification === 'function') ps5Notification("LISTO", "Imagen seleccionada.", "fa-solid fa-image");
        };
        grid.appendChild(div);
        observerModding.observe(div);
    });
}

function procesarImagenConLienzo(srcUrl) {
    return new Promise((resolve, reject) => {
        const img = new Image();
        img.crossOrigin = "Anonymous"; 
        img.onload = () => {
            const canvas = document.createElement('canvas');
            canvas.width = 512;
            canvas.height = 512;
            const ctx = canvas.getContext('2d');
            
            ctx.imageSmoothingEnabled = true;
            ctx.imageSmoothingQuality = 'high';

            const minDim = Math.min(img.width, img.height);
            const srcX = (img.width - minDim) / 2;
            const srcY = (img.height - minDim) / 2;
            
            ctx.drawImage(img, srcX, srcY, minDim, minDim, 0, 0, 512, 512);
            
            canvas.toBlob((blob) => {
                if (blob) resolve(blob);
                else reject(new Error("Error de Canvas"));
            }, 'image/png');
        };
        img.onerror = () => reject(new Error("Error al leer imagen"));
        img.src = srcUrl;
    });
}

async function prepararImagenYRecortar(urlArchivo) {
    if (!moddingJuegoActivo) {
        if(typeof ps5Notification === 'function') ps5Notification("ERROR", "Selecciona un juego primero.", "fa-solid fa-exclamation-triangle");
        return;
    }

    if(typeof ps5Notification === 'function') ps5Notification("PREPARANDO", "Ajustando formato...", "fa-solid fa-crop");
    
    try {
        const PNG_Blob = await procesarImagenConLienzo(urlArchivo);
        moddingArchivoPendiente = PNG_Blob;
        
        const finalUrl = URL.createObjectURL(PNG_Blob);
        document.getElementById('modding-preview-img').src = finalUrl;
        
        if(typeof ps5Notification === 'function') ps5Notification("LISTO", "Imagen optimizada y lista para inyectar.", "fa-solid fa-check");
    } catch (error) {
        if(typeof ps5Notification === 'function') ps5Notification("ERROR", "Fallo al redimensionar la imagen.", "fa-solid fa-bug");
    }
}

function procesarImagenSubida(event) {
    const file = event.target.files[0];
    if (!file) return;
    const tempUrl = URL.createObjectURL(file);
    prepararImagenYRecortar(tempUrl);
    event.target.value = '';
}

async function inyectarPortadaEnConsola() {
    if (!moddingJuegoActivo) return;

    const btn = document.getElementById('btn-inyectar-portada');
    const loader = document.getElementById('modding-inyectando-loader');
    const ip = localStorage.getItem('sebas_ip_final_libre') || '192.168.1.28';
    
    if (moddingSourceType === 'server' && !moddingArchivoPendiente) {
        const previewImg = document.getElementById('modding-preview-img');
        if (previewImg && previewImg.src && !previewImg.src.endsWith("modding.php")) {
            await prepararImagenYRecortar(previewImg.src);
        }
    }

    if (!moddingArchivoPendiente) {
        if(typeof ps5Notification === 'function') ps5Notification("ERROR", "No has elegido ninguna portada para inyectar.", "fa-solid fa-exclamation-triangle");
        return;
    }
    
    btn.style.opacity = '0.5';
    btn.style.pointerEvents = 'none';
    loader.classList.remove('hidden');
    loader.classList.add('flex');

    try {
        const archivoFinalProcesado = new File([moddingArchivoPendiente], `${moddingJuegoActivo.id}_icon0.png`, { type: "image/png" });

        let fd = new FormData();
        fd.append('host_ip', ip);
        fd.append('cusa_id', moddingJuegoActivo.id);
        fd.append('cover_image', archivoFinalProcesado);

        if(typeof ps5Notification === 'function') ps5Notification("INYECTANDO", "Enviando paquete a PS4...", "fa-solid fa-bolt");
        
        let res = await fetch('api/inyector_portadas.php', { method: 'POST', body: fd });
        let rawText = await res.text(); 
        
        try {
            let data = JSON.parse(rawText);
            
            if (data && data.status === 'success') {
                if(typeof ps5Notification === 'function') ps5Notification("MODDING", "Portada inyectada con éxito en la PS4.", "fa-solid fa-magic");
                
                setTimeout(async () => { 
                    if (typeof levantarCacheLocalBiblioteca === 'function') {
                        await levantarCacheLocalBiblioteca(); 
                    }
                    forzarActualizacionDeCacheVisual(); 
                    inicializarModuloModding(); 
                    limpiarPreviewModding();
                }, 800);
            } else {
                if(typeof ps5Notification === 'function') ps5Notification("ERROR", data.message || "Fallo en la inyección FTP.", "fa-solid fa-exclamation-triangle");
            }
        } catch (parseError) {
            console.error("Respuesta cruda de PHP:", rawText);
            if(typeof ps5Notification === 'function') ps5Notification("ERROR", "El servidor devolvió un error inesperado.", "fa-solid fa-bug");
        }

    } catch (e) {
        if(typeof ps5Notification === 'function') ps5Notification("ERROR", "El servidor no responde.", "fa-solid fa-wifi");
    } finally {
        btn.style.opacity = '1';
        btn.style.pointerEvents = 'auto';
        loader.classList.add('hidden');
        loader.classList.remove('flex');
    }
}

/* ====================================================================
 * GESTOR INTELIGENTE V3 (EXTRACCIÓN DE ZIP + CARGA DE IMÁGENES)
 * ==================================================================== */

// 🔥 FIX UX: Agregamos parámetro silencioso para que no suene doble click al saltar desde biblioteca
function switchModdingTab(tab, playSound = true) {
    if(playSound && typeof emitirEfectoSonidoNativo === 'function') emitirEfectoSonidoNativo('click');
    
    const tabManual = document.getElementById('tab-mod-manual');
    const tabSmart = document.getElementById('tab-mod-smart');
    const viewManual = document.getElementById('modding-view-manual');
    const viewSmart = document.getElementById('modding-view-smart');

    if (tab === 'manual') {
        tabManual.className = "flex-1 p-3 rounded-xl bg-purple-600/20 border border-purple-500/50 text-purple-400 text-[10px] font-black uppercase tracking-wider transition-all shadow-[0_0_10px_rgba(168,85,247,0.1)]";
        tabSmart.className = "flex-1 p-3 rounded-xl bg-[#111827] border border-white/5 text-gray-400 text-[10px] font-black uppercase tracking-wider transition-all";
        
        viewSmart.classList.add('hidden');
        viewSmart.classList.remove('opacity-100');
        viewManual.classList.remove('hidden');
        setTimeout(() => viewManual.classList.add('opacity-100'), 50);
    } else {
        tabSmart.className = "flex-1 p-3 rounded-xl bg-purple-600/20 border border-purple-500/50 text-purple-400 text-[10px] font-black uppercase tracking-wider transition-all shadow-[0_0_10px_rgba(168,85,247,0.1)]";
        tabManual.className = "flex-1 p-3 rounded-xl bg-[#111827] border border-white/5 text-gray-400 text-[10px] font-black uppercase tracking-wider transition-all";
        
        viewManual.classList.add('hidden');
        viewManual.classList.remove('opacity-100');
        viewSmart.classList.remove('hidden');
        setTimeout(() => viewSmart.classList.add('opacity-100'), 50);
    }
}

function limpiarGestorInteligente() {
    if(typeof emitirEfectoSonidoNativo === 'function') emitirEfectoSonidoNativo('click');
    smartFilesArray = [];
    const listContainer = document.getElementById('smart-list-container');
    listContainer.innerHTML = `
        <div class="w-full py-6 flex flex-col items-center justify-center text-gray-600 border border-dashed border-white/10 rounded-xl bg-black/20">
            <i class="fa-solid fa-box-open text-2xl mb-2 opacity-50"></i>
            <span class="text-[9px] font-mono uppercase tracking-widest">No hay archivos cargados</span>
        </div>`;
    document.getElementById('input-smart-files').value = '';
}

function removerSmartFile(fileId) {
    if(typeof emitirEfectoSonidoNativo === 'function') emitirEfectoSonidoNativo('click');
    smartFilesArray = smartFilesArray.filter(f => f.id !== fileId);
    
    const el = document.getElementById(fileId);
    if (el) el.remove();
    
    if (smartFilesArray.length === 0) {
        limpiarGestorInteligente();
    }
}

async function processSmartFilesUI(event) {
    const files = event.target.files;
    if (!files || files.length === 0) return;
    if(typeof emitirEfectoSonidoNativo === 'function') emitirEfectoSonidoNativo('click');

    const listContainer = document.getElementById('smart-list-container');
    if (smartFilesArray.length === 0) {
        listContainer.innerHTML = ''; 
    }

    let hasZip = Array.from(files).some(f => f.name.toLowerCase().endsWith('.zip'));
    if (hasZip && typeof JSZip === 'undefined') {
        if(typeof ps5Notification === 'function') ps5Notification("SISTEMA", "Iniciando motor de extracción ZIP...", "fa-solid fa-file-zipper");
        await new Promise((resolve, reject) => {
            const script = document.createElement('script');
            script.src = 'https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js';
            script.onload = resolve;
            script.onerror = reject;
            document.head.appendChild(script);
        });
    }

    for (let i = 0; i < files.length; i++) {
        let file = files[i];

        if (file.name.toLowerCase().endsWith('.zip')) {
            if(typeof ps5Notification === 'function') ps5Notification("EXTRAYENDO", `Analizando ${file.name}...`, "fa-solid fa-box-open");
            try {
                const zip = new JSZip();
                const contents = await zip.loadAsync(file);
                
                for (const relativePath of Object.keys(contents.files)) {
                    const zipEntry = contents.files[relativePath];
                    if (zipEntry.dir) continue; 
                    
                    if (relativePath.match(/\.(png|jpe?g|webp)$/i)) {
                        const blob = await zipEntry.async("blob");
                        const fileName = zipEntry.name.split('/').pop();
                        const extractedFile = new File([blob], fileName, { type: blob.type || "image/png" });
                        
                        let autoCusa = "";
                        const match = relativePath.match(/CUSA\d{5}/i);
                        if (match) autoCusa = match[0].toUpperCase();

                        agregarItemAlGestorHTML(extractedFile, autoCusa);
                    }
                }
                if(typeof ps5Notification === 'function') ps5Notification("ÉXITO", "Pack ZIP cargado correctamente.", "fa-solid fa-check");
            } catch(e) {
                if(typeof ps5Notification === 'function') ps5Notification("ERROR", "No se pudo leer el archivo ZIP.", "fa-solid fa-bug");
            }
        } 
        else {
            let autoCusa = "";
            const match = file.name.match(/CUSA\d{5}/i);
            if (match) autoCusa = match[0].toUpperCase();
            
            agregarItemAlGestorHTML(file, autoCusa);
        }
    }
    
    event.target.value = '';
}

function agregarItemAlGestorHTML(file, autoCusa) {
    let uniqueIndex = new Date().getTime() + Math.floor(Math.random() * 10000);
    let fileId = 'smart-file-' + uniqueIndex;
    let defaultLabel = "-- ASIGNAR JUEGO --";
    let borderClass = "border-white/10";
    let textClass = "text-gray-400";

    if (autoCusa !== "") {
        const isDuplicate = smartFilesArray.some(f => f.cusa === autoCusa);
        if (isDuplicate) {
            autoCusa = ""; 
        } else {
            let jg = (typeof listadoJuegos !== 'undefined') ? listadoJuegos.find(j => j.id === autoCusa) : null;
            if (jg) defaultLabel = `[${autoCusa}] ${jg.nombre}`;
            else defaultLabel = `[${autoCusa}] Auto-Detectado`;
            
            borderClass = "border-purple-500/30";
            textClass = "text-purple-400";
        }
    }
    
    smartFilesArray.push({ id: fileId, file: file, cusa: autoCusa });

    const reader = new FileReader();
    reader.onload = function(e) {
        const imgSrc = e.target.result;
        let itemHTML = `
        <div id="${fileId}" class="flex items-center gap-3 bg-black/40 border border-white/5 p-2.5 rounded-xl z-0">
            <div class="w-12 h-12 bg-gray-800 rounded-lg overflow-hidden shrink-0 border border-white/10 relative">
                <img src="${imgSrc}" class="w-full h-full object-cover opacity-80">
            </div>
            <div class="flex flex-col flex-1 min-w-0 gap-1.5">
                <div class="flex justify-between items-center w-full mb-0.5">
                    <span class="text-[10px] text-gray-300 font-bold truncate pr-2">${file.name}</span>
                    <button onclick="removerSmartFile('${fileId}')" class="w-5 h-5 rounded-md bg-red-500/10 text-red-400 flex items-center justify-center hover:bg-red-500/20 active:scale-90 transition-all shrink-0 border border-red-500/20">
                        <i class="fa-solid fa-xmark text-[9px]"></i>
                    </button>
                </div>
                <button onclick="abrirSelectorJuegosModding('${fileId}')" class="smart-btn-asignar w-full bg-[#111827] border ${borderClass} ${textClass} text-[9px] font-mono font-bold rounded-lg px-3 py-2 flex justify-between items-center shadow-inner active:scale-95 transition-all">
                    <span class="truncate">${defaultLabel}</span>
                    <i class="fa-solid fa-magnifying-glass text-purple-400 text-[10px] ml-2 shrink-0"></i>
                </button>
            </div>
        </div>`;
        document.getElementById('smart-list-container').insertAdjacentHTML('beforeend', itemHTML);
    }
    reader.readAsDataURL(file);
}

function asignarJuegoASmartFile(fileId, jg) {
    const duplicado = smartFilesArray.find(f => f.cusa === jg.id && f.id !== fileId);
    if (duplicado) {
        if(typeof ps5Notification === 'function') ps5Notification("JUEGO REPETIDO", `El juego ${jg.id} ya está asignado a otra foto.`, "fa-solid fa-triangle-exclamation");
        return;
    }

    let obj = smartFilesArray.find(f => f.id === fileId);
    if (obj) obj.cusa = jg.id;

    const container = document.getElementById(fileId);
    if (container) {
        const btn = container.querySelector('.smart-btn-asignar');
        if (btn) {
            btn.innerHTML = `<span class="truncate">[${jg.id}] ${jg.nombre}</span> <i class="fa-solid fa-pen text-purple-400 text-[10px] ml-2 shrink-0"></i>`;
            btn.classList.remove('border-white/10', 'text-gray-400');
            btn.classList.add('border-purple-500/30', 'text-purple-400');
        }
    }
}

async function inyectarLoteEnConsola() {
    if(typeof emitirEfectoSonidoNativo === 'function') emitirEfectoSonidoNativo('click');
    
    const validTargets = smartFilesArray.filter(f => f.cusa !== '');
    if (validTargets.length === 0) {
        if(typeof ps5Notification === 'function') ps5Notification("ERROR", "Asigna al menos un juego a las fotos.", "fa-solid fa-list");
        return;
    }

    const ip = localStorage.getItem('sebas_ip_final_libre') || '192.168.1.28';
    const progressContainer = document.getElementById('smart-progress-container');
    const progressStatus = document.getElementById('smart-progress-status');
    const progressTxt = document.getElementById('smart-progress-txt');
    const progressBar = document.getElementById('smart-progress-bar');
    
    progressContainer.classList.remove('hidden');
    progressContainer.classList.add('flex');

    for (let i = 0; i < validTargets.length; i++) {
        let target = validTargets[i];
        progressStatus.innerText = `INYECTANDO [${i+1}/${validTargets.length}]: ${target.cusa}`;
        
        let fileBlob = target.file;
        if (target.file.type !== 'image/png') {
            const url = URL.createObjectURL(target.file);
            fileBlob = await procesarImagenConLienzo(url);
        }

        const archivoFinalProcesado = new File([fileBlob], `${target.cusa}_icon0.png`, { type: "image/png" });

        let fd = new FormData();
        fd.append('host_ip', ip);
        fd.append('cusa_id', target.cusa);
        fd.append('cover_image', archivoFinalProcesado);

        try {
            await fetch('api/inyector_portadas.php', { method: 'POST', body: fd });
        } catch(e) {
            console.error(`Error de red en ${target.cusa}`);
        }

        let p = Math.round(((i + 1) / validTargets.length) * 100);
        progressTxt.innerText = p + '%';
        progressBar.style.width = p + '%';
    }

    if(typeof ps5Notification === 'function') ps5Notification("MODDING COMPLETADO", `Se inyectaron ${validTargets.length} portadas en la consola.`, "fa-solid fa-check-double");
    
    setTimeout(async () => {
        progressContainer.classList.add('hidden');
        progressContainer.classList.remove('flex');
        progressBar.style.width = '0%';
        progressTxt.innerText = '0%';
        
        if (typeof levantarCacheLocalBiblioteca === 'function') {
            await levantarCacheLocalBiblioteca(); 
        }
        
        forzarActualizacionDeCacheVisual();
        inicializarModuloModding();
        limpiarGestorInteligente(); 
    }, 2000);
}

async function crearPackZIP() {
    if(typeof emitirEfectoSonidoNativo === 'function') emitirEfectoSonidoNativo('click');
    
    const validTargets = smartFilesArray.filter(f => f.cusa !== '');
    if (validTargets.length === 0) {
        if(typeof ps5Notification === 'function') ps5Notification("ERROR", "Asigna al menos un juego a las fotos.", "fa-solid fa-list");
        return;
    }

    if(typeof ps5Notification === 'function') ps5Notification("COMPRIMIENDO", "Generando archivo ZIP...", "fa-solid fa-box-archive");

    if (typeof JSZip === 'undefined') {
        await new Promise((resolve, reject) => {
            const script = document.createElement('script');
            script.src = 'https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js';
            script.onload = resolve;
            script.onerror = reject;
            document.head.appendChild(script);
        });
    }

    try {
        const zip = new JSZip();
        
        for (let i = 0; i < validTargets.length; i++) {
            let target = validTargets[i];
            let fileBlob = target.file;
            
            if (target.file.type !== 'image/png') {
                const url = URL.createObjectURL(target.file);
                fileBlob = await procesarImagenConLienzo(url);
            }
            
            zip.folder(target.cusa).file("icon0.png", fileBlob);
        }

        const content = await zip.generateAsync({type:"blob"});
        
        const link = document.createElement('a');
        link.href = URL.createObjectURL(content);
        link.download = `GoldHEN_Portadas_${new Date().getTime()}.zip`;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
        if(typeof ps5Notification === 'function') ps5Notification("ÉXITO", "Pack ZIP descargado en tu celular.", "fa-solid fa-check-double");
    } catch(e) {
        if(typeof ps5Notification === 'function') ps5Notification("ERROR", "Fallo al generar el archivo ZIP.", "fa-solid fa-bug");
    }
}
