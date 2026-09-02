<style>
    /* CONTENEDOR GLOBAL */
    #bg-container { position: fixed; inset: 0; z-index: -2; pointer-events: none; }
    .bg-layer { position: absolute; inset: 0; display: none; } 
    .bg-layer.active { display: block; animation: fadeInBg 0.8s ease-in-out; }
    .bg-layer canvas { display: block; width: 100%; height: 100%; }
    @keyframes fadeInBg { from { opacity: 0; } to { opacity: 1; } }

    /* ESTILOS DE LOS FONDOS ANIMADOS */
    #bg-ps5 { background: transparent; }
    .orb-1-bg { position: absolute; width: 90vw; height: 90vw; background: radial-gradient(circle, rgba(34,211,238,0.25) 0%, transparent 60%); border-radius: 50%; top: -20%; left: -20%; filter: blur(50px); animation: floatOrbBg 8s ease-in-out infinite alternate; }
    .orb-2-bg { position: absolute; width: 80vw; height: 80vw; background: radial-gradient(circle, rgba(168,85,247,0.25) 0%, transparent 60%); border-radius: 50%; bottom: 20%; right: -20%; filter: blur(50px); animation: floatOrbBg 10s ease-in-out infinite alternate-reverse; }
    @keyframes floatOrbBg { 0% { transform: translate(0, 0) scale(1); } 100% { transform: translate(40px, 40px) scale(1.1); } }
    .ps-dust { position: absolute; border-radius: 50%; animation: ps5DustAnim linear infinite; }
    @keyframes ps5DustAnim { 0% { opacity: 0; transform: translateY(0); } 10% { opacity: 0.8; } 90% { opacity: 0.8; } 100% { opacity: 0; transform: translateY(-100vh); } }

    #bg-ps5-gold { background: #0a0500; }
    .orb-gold-1 { position: absolute; width: 100vw; height: 100vw; background: radial-gradient(circle, rgba(255,180,0,0.2) 0%, transparent 60%); border-radius: 50%; top: -30%; left: -20%; filter: blur(60px); animation: floatOrbBg 9s ease-in-out infinite alternate; }
    .orb-gold-2 { position: absolute; width: 80vw; height: 80vw; background: radial-gradient(circle, rgba(255,100,0,0.15) 0%, transparent 60%); border-radius: 50%; bottom: 10%; right: -20%; filter: blur(50px); animation: floatOrbBg 11s ease-in-out infinite alternate-reverse; }

    #bg-ps4 { background: linear-gradient(135deg, #001233 0%, #003791 100%); overflow: hidden; }
    .ps4-liquid-1, .ps4-liquid-2 { position: absolute; width: 250vw; height: 250vw; top: 60%; left: 50%; margin-top: -125vw; margin-left: -125vw; border-radius: 40%; background: rgba(255,255,255,0.05); animation: liquidWave 12s infinite linear; }
    .ps4-liquid-2 { background: rgba(0,0,0,0.1); animation-duration: 18s; animation-direction: reverse; }
    @keyframes liquidWave { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
    .ps4-shape-bg { position: absolute; color: rgba(255,255,255,0.15); font-size: 3rem; animation: floatShapeBg linear infinite; }
    @keyframes floatShapeBg { 0% { transform: translateY(110vh) rotate(0deg) scale(0.8); opacity: 0; } 10% { opacity: 1; } 90% { opacity: 1; } 100% { transform: translateY(-20vh) rotate(360deg) scale(1.2); opacity: 0; } }

    #bg-ps3 { background: radial-gradient(circle at center, #2a2a35 0%, #000 100%); overflow: hidden; }
    .ps3-ribbon { position: absolute; width: 150vw; height: 40vh; left: -25vw; top: 30vh; border-top: 2px solid rgba(255,255,255,0.3); border-radius: 50%; background: radial-gradient(ellipse at top, rgba(255,255,255,0.08), transparent 50%); filter: blur(2px); animation: ribbonFlow 6s infinite alternate ease-in-out; transform-origin: center; }
    .ribbon-1 { animation-duration: 7s; }
    .ribbon-2 { opacity: 0.5; animation-duration: 9s; animation-delay: -2s; border-top-color: rgba(255,255,255,0.15); }
    .ribbon-3 { opacity: 0.3; animation-duration: 11s; animation-delay: -5s; border-top-color: rgba(255,255,255,0.1); }
    @keyframes ribbonFlow { 0% { transform: scaleY(0.8) translateY(-20px) rotate(-3deg); } 100% { transform: scaleY(1.2) translateY(20px) rotate(3deg); } }

    #bg-ps2 { background: radial-gradient(circle at 50% 120%, #003399 0%, #000 80%); perspective: 800px; }
    .ps2-mist { position: absolute; inset: 0; background: url('data:image/svg+xml,%3Csvg viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg"%3E%3Cfilter id="noiseFilter"%3E%3CfeTurbulence type="fractalNoise" baseFrequency="0.005" numOctaves="2" stitchTiles="stitch"/%3E%3C/filter%3E%3Crect width="100%25" height="100%25" filter="url(%23noiseFilter)" opacity="0.15"/%3E%3C/svg%3E'); animation: mistDrift 60s linear infinite; mix-blend-mode: overlay; }
    .ps2-cube-bg { position: absolute; border: 1px solid rgba(0, 150, 255, 0.4); background: rgba(0, 100, 255, 0.05); box-shadow: inset 0 0 20px rgba(0, 150, 255, 0.2); animation: ps2Rise linear infinite forwards; }
    @keyframes ps2Rise { 0% { transform: translateZ(-500px) translateY(110vh) rotate(0deg); opacity: 0; } 10% { opacity: 1; } 90% { opacity: 1; } 100% { transform: translateZ(200px) translateY(-20vh) rotate(360deg); opacity: 0; } }
    @keyframes mistDrift { 100% { background-position: 1000px 1000px; } }

    #bg-ps1 { background: radial-gradient(circle at center, #000015 0%, #000 100%); overflow: hidden; }
    .ps1-glow { position: absolute; top: 40%; left: 50%; transform: translate(-50%, -50%); width: 80vw; height: 80vw; background: radial-gradient(circle, rgba(255,100,0,0.2) 0%, rgba(200,50,0,0.1) 40%, transparent 70%); filter: blur(30px); animation: pulsePS1 4s infinite alternate; }
    .ps1-grid { position: absolute; bottom: -50%; left: -50%; width: 200%; height: 100%; background-image: linear-gradient(rgba(0,100,255,0.15) 2px, transparent 2px), linear-gradient(90deg, rgba(0,100,255,0.15) 2px, transparent 2px); background-size: 40px 40px; transform: perspective(500px) rotateX(75deg); animation: ps1GridMove 4s linear infinite; }
    @keyframes pulsePS1 { 0% { opacity: 0.6; transform: translate(-50%, -50%) scale(0.9); } 100% { opacity: 1; transform: translate(-50%, -50%) scale(1.1); } }
    @keyframes ps1GridMove { 100% { background-position: 0 40px; } }

    #bg-circuit { background: #020502; }
    .circuit-path { position: absolute; background: #0f0; opacity: 0; box-shadow: 0 0 8px #0f0; animation: circuitFlow 3s infinite linear; }
    .cp-1 { top: 20%; left: 0; width: 100%; height: 1px; }
    .cp-2 { top: 0; left: 30%; width: 1px; height: 100%; animation-delay: 1s; animation-name: circuitFlowY; }
    .cp-3 { top: 70%; left: 0; width: 100%; height: 1px; animation-delay: 0.5s; }
    .cp-4 { top: 0; left: 80%; width: 1px; height: 100%; animation-delay: 1.5s; animation-name: circuitFlowY; }
    .cp-5 { top: 40%; left: 0; width: 100%; height: 2px; animation-delay: 2s; background: #0ff; box-shadow: 0 0 8px #0ff; }
    @keyframes circuitFlow { 0% { transform: scaleX(0); transform-origin: left; opacity: 0; } 10% { opacity: 0.6; } 50% { transform: scaleX(1); transform-origin: left; opacity: 0.6; } 100% { transform: scaleX(1); opacity: 0; } }
    @keyframes circuitFlowY { 0% { transform: scaleY(0); transform-origin: top; opacity: 0; } 10% { opacity: 0.6; } 50% { transform: scaleY(1); transform-origin: top; opacity: 0.6; } 100% { transform: scaleY(1); opacity: 0; } }

    #bg-radar { background: #001100; overflow: hidden; }
    .radar-grid { position: absolute; inset: 0; background-image: linear-gradient(#003300 1px, transparent 1px), linear-gradient(90deg, #003300 1px, transparent 1px); background-size: 50px 50px; }
    .radar-sweep { position: absolute; top: 50%; left: 50%; width: 150vmax; height: 150vmax; transform: translate(-50%, -50%); background: conic-gradient(from 0deg, transparent 70%, rgba(0, 255, 0, 0.4) 100%); border-radius: 50%; animation: spinSweep 3s linear infinite; }
    @keyframes spinSweep { 100% { transform: translate(-50%, -50%) rotate(360deg); } }

    #bg-fiber { background: #000; }
    .fiber-line { position: absolute; height: 2px; width: 100%; background: linear-gradient(90deg, transparent, #0ff, transparent); opacity: 0; animation: fiberFlash 2s infinite; }
    @keyframes fiberFlash { 0% { transform: translateX(-100%); opacity: 0; } 50% { opacity: 0.8; } 100% { transform: translateX(100%); opacity: 0; } }

    #bg-synth { background: #050014; perspective: 600px; }
    .synth-grid { position: absolute; bottom: -50%; left: -50%; width: 200%; height: 150%; background-image: linear-gradient(rgba(255, 0, 255, 0.4) 2px, transparent 2px), linear-gradient(90deg, rgba(0, 255, 255, 0.4) 2px, transparent 2px); background-size: 40px 40px; transform: rotateX(75deg); animation: moveGrid 1.5s linear infinite; }
    .synth-sun { position: absolute; top: 20%; left: 50%; transform: translateX(-50%); width: 250px; height: 250px; background: linear-gradient(180deg, #ff00ff 0%, #ff8c00 100%); border-radius: 50%; box-shadow: 0 0 50px #ff00ff; }
    @keyframes moveGrid { 100% { background-position: 0 40px; } }

    #bg-sonar { background: #000510; overflow: hidden; }
    .sonar-ring { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); border-radius: 50%; border: 2px solid #00d2ff; opacity: 0; animation: sonarPing 4s infinite linear; }
    .sonar-ring:nth-child(2) { animation-delay: 1.3s; }
    .sonar-ring:nth-child(3) { animation-delay: 2.6s; }
    @keyframes sonarPing { 0% { width: 0; height: 0; opacity: 1; border-width: 4px; } 100% { width: 150vmax; height: 150vmax; opacity: 0; border-width: 1px; } }

    /* 🔥 CORRECCIÓN: Se cambió pb-1 por plasma-b-1 para evitar choque de nombres con la app */
    #bg-plasma { background: #000; overflow: hidden; }
    .plasma-container { position: absolute; inset: 0; filter: blur(25px); }
    .plasma-blob { position: absolute; mix-blend-mode: screen; animation: plasmaFluid 12s infinite alternate ease-in-out; }
    .plasma-b-1 { width: 80vw; height: 80vw; background: #5500ff; top: -10%; left: 0%; animation-duration: 14s; }
    .plasma-b-2 { width: 70vw; height: 70vw; background: #0088ff; bottom: -10%; right: -10%; animation-duration: 18s; animation-direction: alternate-reverse; }
    .plasma-b-3 { width: 60vw; height: 60vw; background: #ff00aa; top: 30%; left: 20%; animation-duration: 10s; }
    @keyframes plasmaFluid {
        0% { transform: translate(0, 0) scale(1) rotate(0deg); border-radius: 30% 70% 70% 30% / 30% 30% 70% 70%; }
        50% { transform: translate(15vw, 10vh) scale(1.1) rotate(180deg); border-radius: 70% 30% 30% 70% / 70% 70% 30% 30%; }
        100% { transform: translate(-10vw, 15vh) scale(0.9) rotate(360deg); border-radius: 50% 50% 50% 50% / 50% 50% 50% 50%; }
    }
</style>

<div id="bg-container">
    <div id="bg-ps5" class="bg-layer"><div class="orb-1-bg"></div><div class="orb-2-bg"></div><div id="ps5-dust-container"></div></div>
    <div id="bg-ps5-gold" class="bg-layer"><div class="orb-gold-1"></div><div class="orb-gold-2"></div><div id="ps5-gold-container"></div></div>
    <div id="bg-ps4" class="bg-layer"><div class="ps4-liquid-1"></div><div class="ps4-liquid-2"></div><div id="ps4-shapes-container"></div></div>
    <div id="bg-ps3" class="bg-layer"><div class="ps3-ribbon ribbon-1"></div><div class="ps3-ribbon ribbon-2"></div><div class="ps3-ribbon ribbon-3"></div></div>
    <div id="bg-ps2" class="bg-layer"><div class="ps2-mist"></div><div id="ps2-cubes-container-bg"></div></div>
    <div id="bg-ps1" class="bg-layer"><div class="ps1-glow"></div><div class="ps1-grid"></div></div>
    <div id="bg-matrix" class="bg-layer"><canvas id="canvas-matrix"></canvas></div>
    <div id="bg-binary" class="bg-layer"><canvas id="canvas-binary"></canvas></div>
    <div id="bg-network" class="bg-layer"><canvas id="canvas-network"></canvas></div>
    <div id="bg-circuit" class="bg-layer"><div class="circuit-path cp-1"></div><div class="circuit-path cp-2"></div><div class="circuit-path cp-3"></div><div class="circuit-path cp-4"></div><div class="circuit-path cp-5"></div></div>
    <div id="bg-starfield" class="bg-layer"><canvas id="canvas-starfield"></canvas></div>
    <div id="bg-warp" class="bg-layer"><canvas id="canvas-warp"></canvas></div>
    <div id="bg-radar" class="bg-layer"><div class="radar-grid"></div><div class="radar-sweep"></div></div>
    <div id="bg-synth" class="bg-layer"><div class="synth-sun"></div><div class="synth-grid"></div></div>
    <div id="bg-fiber" class="bg-layer"><div id="fiber-container"></div></div>
    <div id="bg-sonar" class="bg-layer"><div class="sonar-ring"></div><div class="sonar-ring"></div><div class="sonar-ring"></div></div>
    
    <div id="bg-plasma" class="bg-layer"><div class="plasma-container"><div class="plasma-blob plasma-b-1"></div><div class="plasma-blob plasma-b-2"></div><div class="plasma-blob plasma-b-3"></div></div></div>
</div>

<script>
    let currentAnimFrameBg = null;
    let animMatrixBg = null, animBinaryBg = null;

    function stopAllCanvas() {
        clearInterval(animMatrixBg); clearInterval(animBinaryBg);
        cancelAnimationFrame(currentAnimFrameBg);
    }

    function initPS5Dust(containerId, color) {
        const container = document.getElementById(containerId); if(!container) return; container.innerHTML = '';
        for(let i=0; i<25; i++) { 
            let div = document.createElement('div'); div.className = 'ps-dust';
            div.style.background = color; div.style.boxShadow = `0 0 10px ${color}`;
            let size = Math.random() * 3 + 1;
            div.style.width = size + 'px'; div.style.height = size + 'px';
            div.style.left = Math.random() * 100 + 'vw'; div.style.bottom = (Math.random() * -20) + 'vh';
            div.style.animationDuration = (Math.random() * 4 + 4) + 's'; div.style.animationDelay = (Math.random() * 3) + 's';
            container.appendChild(div);
        }
    }

    function initPS4Shapes() {
        const container = document.getElementById('ps4-shapes-container'); if(!container) return; container.innerHTML = '';
        const icons = ['fa-xmark', 'fa-circle', 'fa-square', 'fa-play']; 
        for(let i=0; i<15; i++) { 
            let icon = document.createElement('i');
            icon.className = `fa-solid ${icons[Math.floor(Math.random() * icons.length)]} ps4-shape-bg`;
            if(icon.classList.contains('fa-play')) icon.style.transform = 'rotate(-90deg)';
            icon.style.left = Math.random() * 100 + 'vw';
            icon.style.animationDuration = (Math.random() * 8 + 6) + 's'; icon.style.animationDelay = (Math.random() * -10) + 's';
            container.appendChild(icon);
        }
    }

    function initPS2CubesBg() {
        const container = document.getElementById('ps2-cubes-container-bg'); if(!container) return; container.innerHTML = '';
        for(let i=0; i<20; i++) { 
            let cube = document.createElement('div');
            let size = Math.random() * 40 + 20;
            cube.className = 'ps2-cube-bg';
            cube.style.width = size + 'px'; cube.style.height = size + 'px';
            cube.style.left = (Math.random() * 100) + 'vw';
            cube.style.animationDuration = (Math.random() * 15 + 10) + 's'; cube.style.animationDelay = (Math.random() * -15) + 's';
            container.appendChild(cube);
        }
    }

    function initFiber() {
        const container = document.getElementById('fiber-container'); if(!container) return; container.innerHTML = '';
        for(let i=0; i<15; i++) { 
            let line = document.createElement('div'); line.className = 'fiber-line';
            line.style.top = Math.random() * 100 + 'vh';
            line.style.animationDuration = (Math.random() * 1.5 + 0.8) + 's'; line.style.animationDelay = (Math.random() * 2) + 's';
            container.appendChild(line);
        }
    }

    function startMatrix() {
        const canvas = document.getElementById('canvas-matrix'); if(!canvas) return; const ctx = canvas.getContext('2d');
        canvas.width = window.innerWidth; canvas.height = window.innerHeight;
        const letters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ'.split('');
        const fontSize = 16; const columns = canvas.width / fontSize;
        const drops = []; for(let x=0; x<columns; x++) drops[x] = 1;
        animMatrixBg = setInterval(() => {
            ctx.fillStyle = 'rgba(0, 0, 0, 0.05)'; ctx.fillRect(0, 0, canvas.width, canvas.height);
            ctx.fillStyle = '#0F0'; ctx.font = fontSize + 'px monospace';
            for(let i=0; i<drops.length; i++) {
                const text = letters[Math.floor(Math.random() * letters.length)];
                ctx.fillText(text, i * fontSize, drops[i] * fontSize);
                if(drops[i] * fontSize > canvas.height && Math.random() > 0.975) drops[i] = 0;
                drops[i]++;
            }
        }, 35);
    }

    function startBinary() {
        const canvas = document.getElementById('canvas-binary'); if(!canvas) return; const ctx = canvas.getContext('2d');
        canvas.width = window.innerWidth; canvas.height = window.innerHeight;
        const fontSize = 18; const columns = canvas.width / fontSize;
        const drops = []; for(let x=0; x<columns; x++) drops[x] = Math.random() * -50;
        animBinaryBg = setInterval(() => {
            ctx.fillStyle = 'rgba(0, 0, 0, 0.1)'; ctx.fillRect(0, 0, canvas.width, canvas.height);
            ctx.fillStyle = 'rgba(57, 255, 20, 0.6)'; ctx.font = fontSize + 'px monospace';
            for(let i=0; i<drops.length; i++) {
                const text = Math.random() > 0.5 ? '1' : '0';
                ctx.fillText(text, i * fontSize, drops[i] * fontSize);
                if(drops[i] * fontSize > canvas.height && Math.random() > 0.95) drops[i] = 0;
                drops[i]++;
            }
        }, 50);
    }

    function startStarfield() {
        const canvas = document.getElementById('canvas-starfield'); if(!canvas) return; const ctx = canvas.getContext('2d');
        canvas.width = window.innerWidth; canvas.height = window.innerHeight;
        let stars = [];
        for(let i=0; i<100; i++) { stars.push({ x: Math.random() * canvas.width, y: Math.random() * canvas.height, size: Math.random() * 2 + 1, speed: Math.random() * 3 + 1 }); }
        function draw() {
            ctx.fillStyle = '#000'; ctx.fillRect(0, 0, canvas.width, canvas.height);
            ctx.fillStyle = '#fff';
            stars.forEach(star => {
                ctx.fillRect(star.x, star.y, star.size, star.size);
                star.x -= star.speed;
                if(star.x < 0) { star.x = canvas.width; star.y = Math.random() * canvas.height; }
            });
            currentAnimFrameBg = requestAnimationFrame(draw);
        }
        draw();
    }

    function startWarp() {
        const canvas = document.getElementById('canvas-warp'); if(!canvas) return; const ctx = canvas.getContext('2d');
        canvas.width = window.innerWidth; canvas.height = window.innerHeight;
        let stars = [];
        for(let i=0; i<150; i++) { stars.push({ x: Math.random() * canvas.width - canvas.width/2, y: Math.random() * canvas.height - canvas.height/2, z: Math.random() * 1000 }); }
        function draw() {
            ctx.fillStyle = 'rgba(0, 0, 0, 0.3)'; ctx.fillRect(0, 0, canvas.width, canvas.height);
            let cx = canvas.width/2; let cy = canvas.height/2;
            ctx.fillStyle = '#fff';
            stars.forEach(star => {
                star.z -= 15; 
                if(star.z <= 0) { star.x = Math.random()*canvas.width - cx; star.y = Math.random()*canvas.height - cy; star.z = 1000; }
                let px = cx + (star.x / star.z) * 1000; let py = cy + (star.y / star.z) * 1000;
                let size = (1 - star.z/1000) * 3;
                ctx.fillRect(px, py, size, size);
            });
            currentAnimFrameBg = requestAnimationFrame(draw);
        }
        draw();
    }

    function startNetwork() {
        const canvas = document.getElementById('canvas-network'); if(!canvas) return; const ctx = canvas.getContext('2d');
        canvas.width = window.innerWidth; canvas.height = window.innerHeight;
        let dots = [];
        for(let i=0; i<40; i++) { dots.push({ x: Math.random() * canvas.width, y: Math.random() * canvas.height, vx: (Math.random() - 0.5) * 1.5, vy: (Math.random() - 0.5) * 1.5, radius: Math.random() * 2 + 1 }); }
        function draw() {
            ctx.fillStyle = '#020617'; ctx.fillRect(0, 0, canvas.width, canvas.height);
            for(let i=0; i<dots.length; i++) {
                let d = dots[i];
                d.x += d.vx; d.y += d.vy;
                if(d.x < 0 || d.x > canvas.width) d.vx *= -1; if(d.y < 0 || d.y > canvas.height) d.vy *= -1;
                ctx.beginPath(); ctx.arc(d.x, d.y, d.radius, 0, Math.PI*2); ctx.fillStyle = '#38bdf8'; ctx.fill();
                for(let j=i+1; j<dots.length; j++) {
                    let d2 = dots[j];
                    let dist = Math.sqrt(Math.pow(d.x - d2.x, 2) + Math.pow(d.y - d2.y, 2));
                    if(dist < 120) { ctx.beginPath(); ctx.moveTo(d.x, d.y); ctx.lineTo(d2.x, d2.y); ctx.strokeStyle = `rgba(56, 189, 248, ${1 - dist/120})`; ctx.lineWidth = 0.5; ctx.stroke(); }
                }
            }
            currentAnimFrameBg = requestAnimationFrame(draw);
        }
        draw();
    }

    function changeDynamicWallpaper(bgId) {
        stopAllCanvas();
        document.querySelectorAll('.bg-layer').forEach(layer => layer.classList.remove('active'));
        localStorage.setItem('ps4_dynamic_bg', bgId);

        if (bgId === 'none' || !bgId) {
            const normalBg = document.getElementById('custom-bg-layer');
            const overlay = document.querySelector('.app-bg-overlay');
            if(normalBg) normalBg.style.display = 'block';
            if(overlay) overlay.style.display = 'block';
            return;
        }

        const normalBg = document.getElementById('custom-bg-layer');
        const overlay = document.querySelector('.app-bg-overlay');
        if(normalBg) normalBg.style.display = 'none';
        if(overlay) overlay.style.display = 'none';

        const layer = document.getElementById(bgId);
        if (layer) layer.classList.add('active');

        if(bgId === 'bg-ps5') initPS5Dust('ps5-dust-container', '#22d3ee');
        if(bgId === 'bg-ps5-gold') initPS5Dust('ps5-gold-container', '#ffb400');
        if(bgId === 'bg-ps4') initPS4Shapes();
        if(bgId === 'bg-ps2') initPS2CubesBg();
        if(bgId === 'bg-fiber') initFiber();
        if(bgId === 'bg-matrix') startMatrix();
        if(bgId === 'bg-binary') startBinary();
        if(bgId === 'bg-starfield') startStarfield(); 
        if(bgId === 'bg-warp') startWarp(); 
        if(bgId === 'bg-network') startNetwork();
    }
</script>
