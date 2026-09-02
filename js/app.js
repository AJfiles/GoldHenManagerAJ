/**
 * ====================================================================
 * GOLDHEN MANAGER V3.0 🚀 (PS4) - NÚCLEO DE CONTROL CENTRAL (CORE)
 * DEVELOPED By SeBaS - RUTA: js/app.js
 * ====================================================================
 */
const CREATOR_ATTRIBUTION = "by SeBaS";
console.log(`%c GOLDHEN MANAGER V3.0 - Developed ${CREATOR_ATTRIBUTION} `, "background: #111827; color: #22d3ee; font-weight: bold; padding: 4px;");

if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js').catch(() => {});
    });
}

let globalAppConfig = {
    ipConsola: '192.168.1.28',
    portFTP: 2121,
    statusConexion: false,
    alertasActivas: true,
    sonidosActivos: true,
    volumenSfx: 0.5
};

let globalAudioCtx = null; 

const introNamesMap = {
    'none': 'Sin Intro (Rápido)',
    'intro-ps4': 'PlayStation 4',
    'intro-glitch': 'Glitch Hacker',
    'intro-ps2': 'PlayStation 2 Clásica',
    'intro-hud': 'Sci-Fi HUD',
    'intro-neon': 'Cyberpunk Neon',
    'intro-decrypt': 'Decrypt System',
    'intro-arcade': 'Retro Arcade',
    'intro-matrix-rain': 'Matrix Rain',
    'intro-crt': 'Terminal CRT (Boot)',
    'intro-gb': 'Game Boy Clásica',
    'intro-breach': 'System Breach'
};

document.addEventListener("DOMContentLoaded", () => {
    cargarConfiguracionesLocales();
    configurarEventosDashboard();
    verificarRadarInicial();
    inicializarValoresInterfazAjustes();

    if (typeof bootSelectedIntro === 'function') {
        bootSelectedIntro();
    } else {
        const wrap = document.getElementById('intro-wrapper');
        if (wrap) wrap.style.display = 'none';
    }
});

function cargarConfiguracionesLocales() {
    const ipGuardada = localStorage.getItem('sebas_ip_final_libre');
    const portGuardado = localStorage.getItem('sebas_port_libre') || '2121';
    
    globalAppConfig.alertasActivas = localStorage.getItem('cfg_sebas_alertas') !== 'false';
    globalAppConfig.sonidosActivos = localStorage.getItem('cfg_sebas_sonidos') !== 'false';
    
    const volGuardado = localStorage.getItem('cfg_sebas_volumen_sfx');
    globalAppConfig.volumenSfx = volGuardado ? parseFloat(volGuardado) : 0.5;

    if (ipGuardada) {
        globalAppConfig.ipConsola = ipGuardada;
        const inputIP = document.getElementById('ps-ip-full-input');
        if (inputIP) inputIP.value = ipGuardada;
    }

    globalAppConfig.portFTP = parseInt(portGuardado, 10);

    const bgGuardado = localStorage.getItem('ps4_dynamic_bg') || 'bg-ps4';
    if (typeof changeDynamicWallpaper === 'function') {
        setTimeout(() => changeDynamicWallpaper(bgGuardado), 300);
    }
}

function inicializarValoresInterfazAjustes() {
    setTimeout(() => {
        const swAlertas = document.getElementById('cfg-switch-notificaciones');
        if(swAlertas) swAlertas.checked = globalAppConfig.alertasActivas;

        const swSonidos = document.getElementById('cfg-switch-sonidos');
        if(swSonidos) swSonidos.checked = globalAppConfig.sonidosActivos;

        const sliderVol = document.getElementById('cfg-slider-volumen');
        const lblVol = document.getElementById('lbl-volumen-sfx');
        if(sliderVol && lblVol) {
            let valPorcentaje = Math.round(globalAppConfig.volumenSfx * 100);
            sliderVol.value = valPorcentaje;
            lblVol.innerText = valPorcentaje + '%';
        }

        const selectWall = document.getElementById('custom-select-label');
        if (selectWall) {
            const bgNamesMap = { 'none': 'Apagar Fondos', 'bg-ps4': 'Olas Líquidas (PS4)', 'bg-ps2': 'Cubos 3D (PS2)', 'bg-matrix': 'Lluvia de Código (Matrix)', 'bg-warp': 'Velocidad Warp (Espacio)', 'bg-plasma': 'Fluido Plasma', 'bg-network': 'Red Neuronal (Network)' };
            const bgGuardado = localStorage.getItem('ps4_dynamic_bg') || 'bg-ps4';
            selectWall.innerText = bgNamesMap[bgGuardado] || 'Olas Líquidas (PS4)';
        }

        const lblIntro = document.getElementById('custom-select-intro-label');
        if (lblIntro) {
            const introGuardada = localStorage.getItem('ps4_selected_intro') || 'none';
            lblIntro.innerText = introNamesMap[introGuardada] || 'Sin Intro (Rápido)';
        }
    }, 200);
}

function inicializarContextoAudio() {
    if (!globalAudioCtx) {
        const AudioContext = window.AudioContext || window.webkitAudioContext;
        globalAudioCtx = new AudioContext();
    }
    if (globalAudioCtx.state === 'suspended') {
        globalAudioCtx.resume();
    }
}

function emitirEfectoSonidoNativo(tipo) {
    if (!globalAppConfig.sonidosActivos || globalAppConfig.volumenSfx <= 0) return;

    try {
        inicializarContextoAudio();
        let ctx = globalAudioCtx;
        let osc = ctx.createOscillator();
        let gain = ctx.createGain();
        let volumenReal = 0.3 * globalAppConfig.volumenSfx;
        
        if (tipo === 'click') {
            osc.type = 'sine';
            osc.frequency.setValueAtTime(600, ctx.currentTime);
            osc.frequency.exponentialRampToValueAtTime(150, ctx.currentTime + 0.08);
            gain.gain.setValueAtTime(volumenReal, ctx.currentTime);
            gain.gain.linearRampToValueAtTime(0.001, ctx.currentTime + 0.08);
            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.start();
            osc.stop(ctx.currentTime + 0.08);
        } 
        else if (tipo === 'ps-ui') {
            osc.type = 'sine';
            osc.frequency.setValueAtTime(880, ctx.currentTime);
            osc.frequency.exponentialRampToValueAtTime(1760, ctx.currentTime + 0.2);
            gain.gain.setValueAtTime(0, ctx.currentTime);
            gain.gain.linearRampToValueAtTime(volumenReal, ctx.currentTime + 0.05); 
            gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.4); 
            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.start(ctx.currentTime);
            osc.stop(ctx.currentTime + 0.4);
        }
    } catch (e) {}
}

function cambiarVolumenSFX(valorStr) {
    let porcentaje = parseInt(valorStr, 10);
    globalAppConfig.volumenSfx = porcentaje / 100;
    localStorage.setItem('cfg_sebas_volumen_sfx', globalAppConfig.volumenSfx.toString());
    document.getElementById('lbl-volumen-sfx').innerText = porcentaje + '%';
    emitirEfectoSonidoNativo('click');
}

function guardarAjustesNotificaciones(element) {
    globalAppConfig.alertasActivas = element.checked;
    localStorage.setItem('cfg_sebas_alertas', element.checked ? 'true' : 'false');
    emitirEfectoSonidoNativo('click');
}

function guardarAjustesSonidos(element) {
    globalAppConfig.sonidosActivos = element.checked;
    localStorage.setItem('cfg_sebas_sonidos', element.checked ? 'true' : 'false');
    if(element.checked) emitirEfectoSonidoNativo('click');
}

function toggleCustomSelect() {
    emitirEfectoSonidoNativo('click');
    const options = document.getElementById('custom-select-options');
    const icon = document.getElementById('custom-select-icon');
    
    if (options.classList.contains('scale-y-0')) {
        options.classList.remove('scale-y-0', 'opacity-0', 'pointer-events-none');
        icon.style.transform = 'rotate(180deg)';
    } else {
        options.classList.add('scale-y-0', 'opacity-0', 'pointer-events-none');
        icon.style.transform = 'rotate(0deg)';
    }
}

function seleccionarFondoCustom(idFondo, nombreVisible) {
    document.getElementById('custom-select-label').innerText = nombreVisible;
    toggleCustomSelect();
    emitirEfectoSonidoNativo('ps-ui'); 
    if (typeof changeDynamicWallpaper === 'function') {
        changeDynamicWallpaper(idFondo);
        sysNotification("SISTEMA VISUAL", `Fondo dinámico cargado correctamente.`, "fa-desktop");
    }
}

function toggleCustomSelectIntro() {
    emitirEfectoSonidoNativo('click');
    const options = document.getElementById('custom-select-intro-options');
    const icon = document.getElementById('custom-select-intro-icon');
    
    if (options.classList.contains('scale-y-0')) {
        options.classList.remove('scale-y-0', 'opacity-0', 'pointer-events-none');
        icon.style.transform = 'rotate(180deg)';
    } else {
        options.classList.add('scale-y-0', 'opacity-0', 'pointer-events-none');
        icon.style.transform = 'rotate(0deg)';
    }
}

function seleccionarIntroCustom(idIntro, nombreVisible) {
    document.getElementById('custom-select-intro-label').innerText = nombreVisible;
    toggleCustomSelectIntro();
    localStorage.setItem('ps4_selected_intro', idIntro);
    emitirEfectoSonidoNativo('ps-ui');
    sysNotification("AJUSTES", `Intro seleccionada. Se verá al reiniciar la app.`, "fa-play");
}

function configurarEventosDashboard() {
    const inputIP = document.getElementById('ps-ip-full-input');
    if (inputIP) {
        inputIP.addEventListener('change', (e) => {
            const nuevaIP = e.target.value.trim();
            if (validarEstructuraIP(nuevaIP)) {
                globalAppConfig.ipConsola = nuevaIP;
                localStorage.setItem('sebas_ip_final_libre', nuevaIP);
                sysNotification("AJUSTES", "Dirección IP actualizada.", "fa-network-wired");
                verificarRadarInicial();
            } else { e.target.value = globalAppConfig.ipConsola; }
        });
    }
    document.querySelectorAll('.launcher-card').forEach(card => {
        card.addEventListener('click', () => { emitirEfectoSonidoNativo('click'); });
    });
}

function abrirModulo(moduloId) {
    if (typeof window.abrirModuloNativo === 'function') {
        window.abrirModuloNativo(moduloId);
    } else {
        document.querySelectorAll('.app-layer').forEach(layer => {
            layer.classList.remove('active', 'flex');
            layer.classList.add('hidden');
        });
        const target = document.getElementById('layer-' + moduloId);
        if (target) {
            target.classList.remove('hidden');
            setTimeout(() => target.classList.add('active', 'flex'), 10);
        }
    }
}

function volverAlLauncher() {
    emitirEfectoSonidoNativo('click');
    if (typeof window.volverAlLauncher === 'function') {
        history.back(); 
    } else {
        document.querySelectorAll('.app-layer').forEach(layer => {
            layer.classList.remove('active', 'flex');
            layer.classList.add('hidden');
        });
        const main = document.getElementById('layer-launcher');
        if (main) {
            main.classList.remove('hidden');
            main.classList.add('active', 'flex');
        }
    }
}

async function conectarIPManualValidando() {
    emitirEfectoSonidoNativo('click');
    const inputIP = document.getElementById('ps-ip-full-input');
    const inputPort = document.getElementById('ps-port-input');
    if (!inputIP) return;

    const ip = inputIP.value.trim();
    const port = inputPort ? inputPort.value.trim() : '2121';

    if (!validarEstructuraIP(ip)) { sysNotification("ERROR", "IP inválida.", "fa-circle-xmark"); return; }

    localStorage.setItem('sebas_ip_final_libre', ip);
    localStorage.setItem('sebas_port_libre', port);
    globalAppConfig.ipConsola = ip;
    globalAppConfig.portFTP = parseInt(port, 10);

    sysNotification("ENLACE", "Sincronizando...", "fa-rotate");
    await verificarRadarInicial();
}

async function verificarRadarInicial() {
    try {
        const lblStatus = document.getElementById('console-status-label');
        const pingIndicator = document.getElementById('connection-ping-indicator');

        let response = await fetch(`api/scanner.php?ip=${globalAppConfig.ipConsola}&port=${globalAppConfig.portFTP}`);
        let json = await response.json();

        if (json && json.status === 'success') {
            globalAppConfig.statusConexion = true;
            if (lblStatus) { lblStatus.innerText = "CONECTADO"; lblStatus.className = "text-[9px] font-mono text-emerald-400 font-bold uppercase"; }
            if (pingIndicator) { pingIndicator.className = "w-2 h-2 bg-emerald-500 rounded-full shadow-[0_0_8px_#10b981]"; }
        } else { throw new Error("Offline"); }
    } catch (e) {
        globalAppConfig.statusConexion = false;
        const lblStatus = document.getElementById('console-status-label');
        const pingIndicator = document.getElementById('connection-ping-indicator');
        if (lblStatus) { lblStatus.innerText = "DESCONECTADO"; lblStatus.className = "text-[9px] font-mono text-red-400 font-bold uppercase"; }
        if (pingIndicator) { pingIndicator.className = "w-2 h-2 bg-red-500 rounded-full shadow-[0_0_8px_#ef4444]"; }
    }
}

function validarEstructuraIP(ipString) {
    return /^(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/.test(ipString);
}

function sysNotification(titulo, mensaje, iconoClase = "fa-info-circle") {
    if (!globalAppConfig.alertasActivas) return;

    let contenedorNotificaciones = document.getElementById('sys-notification-holder');
    if (!contenedorNotificaciones) {
        contenedorNotificaciones = document.createElement('div');
        contenedorNotificaciones.id = 'sys-notification-holder';
        contenedorNotificaciones.className = "fixed top-4 right-4 z-[9999] flex flex-col gap-2 pointer-events-none";
        document.body.appendChild(contenedorNotificaciones);
    }

    const burbuja = document.createElement('div');
    burbuja.className = "sys-toast-pill flex items-center gap-3 p-3 bg-[#0d1321]/95 backdrop-blur-md border border-white/10 rounded-2xl shadow-[0_10px_30px_rgba(0,0,0,0.5)] pointer-events-auto transform translate-x-full transition-all duration-400 cubic-bezier(0.175, 0.885, 0.32, 1.275)";
    burbuja.style.maxHeight = "65px";

    burbuja.innerHTML = `
        <div class="flex items-center justify-center w-8 h-8 rounded-xl bg-gradient-to-br from-cyan-500/20 to-purple-500/20 border border-cyan-500/30 text-cyan-400">
            <i class="fa-solid ${iconoClase} text-xs"></i>
        </div>
        <div class="flex flex-col flex-1 min-w-0">
            <span class="text-[8px] font-black tracking-widest text-cyan-400 uppercase">${titulo}</span>
            <span class="text-[9px] font-bold text-gray-200 truncate mt-0.5">${mensaje}</span>
        </div>
    `;
    contenedorNotificaciones.appendChild(burbuja);
    setTimeout(() => { burbuja.classList.remove('translate-x-full'); }, 50);
    setTimeout(() => {
        burbuja.classList.add('translate-x-full');
        burbuja.style.opacity = "0";
        setTimeout(() => { burbuja.remove(); }, 400);
    }, 4000);
}

async function lanzarRadarVentanaEmergente() {
    emitirEfectoSonidoNativo('ps-ui'); 
    const modal = document.getElementById('modal-radar-emergente');
    const caja = document.getElementById('radar-caja');
    const logTerm = document.getElementById('radar-log-terminal');
    const subLabel = document.getElementById('radar-subnet-txt');

    if (!modal || !logTerm) return;

    modal.classList.remove('hidden');
    setTimeout(() => { modal.classList.add('opacity-100'); if (caja) { caja.classList.remove('scale-90'); } }, 10);

    logTerm.innerHTML = `<p class="text-emerald-600">[SYS] Iniciando Protocolo de Detección Zero-Delay...</p>`;
    if (subLabel) subLabel.innerText = `ANALYZING.NETWORK`;

    try {
        logTerm.innerHTML += `<p class="text-gray-500">Verificando IP guardada: ${globalAppConfig.ipConsola}...</p>`;
        logTerm.scrollTop = logTerm.scrollHeight;

        let resCache = await fetch(`api/scanner.php?ip=${globalAppConfig.ipConsola}&port=${globalAppConfig.portFTP}`);
        let dataCache = await resCache.json();

        if (dataCache && dataCache.status === 'success') {
            logTerm.innerHTML += `<p class="text-white font-bold bg-emerald-950/50 px-1 border border-emerald-500/20">🚀 PS4 HALLADA EN CACHÉ: ${globalAppConfig.ipConsola}</p>`;
            if (subLabel) subLabel.innerText = `LINK.ESTABLISHED`;
            await verificarRadarInicial();
            setTimeout(() => { abortarYEstabilizarRadar(); }, 1200);
            return;
        }

        logTerm.innerHTML += `<p class="text-yellow-500">Caché offline o cambio de red detectado.</p>`;
        logTerm.innerHTML += `<p class="text-emerald-400">Aislando nueva subred y lanzando Escaneo Masivo Paralelo...</p>`;
        logTerm.scrollTop = logTerm.scrollHeight;

        let resRadar = await fetch('api/radar_api.php');
        let dataRadar = await resRadar.json();

        if (dataRadar && dataRadar.status === 'success') {
            if (subLabel) subLabel.innerText = `SCANNING.${dataRadar.segmento}`;
            logTerm.innerHTML += `<p class="text-gray-500">Barriendo 254 IPs simultáneas en subred ${dataRadar.segmento} (Timeout 200ms)...</p>`;
            logTerm.scrollTop = logTerm.scrollHeight;

            if (dataRadar.ps4_ips && dataRadar.ps4_ips.length > 0) {
                let nuevaIP = dataRadar.ps4_ips[0];
                logTerm.innerHTML += `<p class="text-white font-bold bg-emerald-950/50 px-1 border border-emerald-500/20">🎯 NUEVA CONSOLA HALLADA EN: ${nuevaIP}</p>`;
                
                globalAppConfig.ipConsola = nuevaIP;
                localStorage.setItem('sebas_ip_final_libre', nuevaIP);
                const inputIP = document.getElementById('ps-ip-full-input');
                if (inputIP) inputIP.value = nuevaIP;

                await verificarRadarInicial();
                setTimeout(() => { abortarYEstabilizarRadar(); }, 2000);
            } else {
                logTerm.innerHTML += `<p class="text-red-400">[ERROR] Ninguna PS4 respondió en la red ${dataRadar.segmento}.</p>`;
                if (subLabel) subLabel.innerText = `SCAN.FAILED`;
            }
        } else {
            logTerm.innerHTML += `<p class="text-red-500">[ERROR] ${dataRadar.message || 'Fallo de interfaz de red.'}</p>`;
        }
    } catch (e) {
        logTerm.innerHTML += `<p class="text-red-500">[ERROR CRÍTICO] Servidor Termux no responde.</p>`;
    }
    logTerm.scrollTop = logTerm.scrollHeight;
}

function abortarYEstabilizarRadar() {
    const modal = document.getElementById('modal-radar-emergente');
    const caja = document.getElementById('radar-caja');
    if (modal) {
        if (caja) caja.classList.add('scale-90');
        modal.classList.remove('opacity-100');
        setTimeout(() => { modal.classList.add('hidden'); }, 300);
    }
}

window.sysNotification = sysNotification;
window.abrirModulo = abrirModulo;
window.volverAlLauncher = volverAlLauncher;

let deferredPrompt;

window.addEventListener('beforeinstallprompt', (e) => {
    e.preventDefault();
    deferredPrompt = e;

    if (!document.getElementById('btn-pwa-install')) {
        const btn = document.createElement('button');
        btn.id = 'btn-pwa-install';
        
        btn.className = "fixed bottom-10 left-1/2 transform -translate-x-1/2 z-[9999] bg-gradient-to-r from-cyan-500 to-blue-600 text-white font-black tracking-widest text-[11px] uppercase px-6 py-4 rounded-full shadow-[0_10px_30px_rgba(6,182,212,0.6)] flex items-center gap-3 animate-bounce active:scale-95 transition-all";
        btn.innerHTML = `<i class="fa-solid fa-download text-lg"></i> Instalar App en el Celular`;
        
        btn.onclick = async () => {
            btn.style.display = 'none';
            deferredPrompt.prompt();
            const { outcome } = await deferredPrompt.userChoice;
            if (outcome === 'accepted') {
                btn.remove(); 
            } else {
                btn.style.display = 'flex'; 
            }
            deferredPrompt = null;
        };
        document.body.appendChild(btn);
    }
});

window.addEventListener('appinstalled', () => {
    const btn = document.getElementById('btn-pwa-install');
    if (btn) btn.remove();
    if (typeof sysNotification === 'function') {
        sysNotification("SISTEMA", "¡Instalación completada! Abre la app desde tu cajón de aplicaciones.", "fa-check");
    }
});

