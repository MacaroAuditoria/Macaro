<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Cierre de Actas - MACARO</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f9; margin: 0; padding: 15px; color: #333; }
        .eval-container { background: white; max-width: 600px; margin: 0 auto; padding: 20px; border-radius: 12px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); border-top: 5px solid #004d40; }
        .header-eval { text-align: center; margin-bottom: 20px; border-bottom: 2px dashed #eee; padding-bottom: 10px; }
        .header-eval h2 { color: #004d40; margin: 0; }
        .header-eval p { color: #d32f2f; font-weight: bold; font-size: 13px; }
        
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; font-weight: bold; margin-bottom: 5px; font-size: 14px; }
        input[type="text"], textarea { width: 100%; box-sizing: border-box; padding: 12px; border: 1px solid #ccc; border-radius: 6px; font-size: 16px; font-family: inherit; }
        
        .categoria-estrellas { display: flex; justify-content: space-between; align-items: center; background: #fafafa; padding: 10px; border-radius: 6px; margin-bottom: 5px; border: 1px solid #eee; }
        .categoria-estrellas span { font-weight: bold; font-size: 14px; }
        .estrellas { display: flex; flex-direction: row-reverse; gap: 5px; }
        .estrellas input { display: none; }
        .estrellas label { font-size: 30px; color: #ddd; cursor: pointer; line-height: 1; }
        .estrellas input:checked ~ label { color: #ffca28; }
        
        .firma-box { border: 2px dashed #999; border-radius: 8px; background: #fff; position: relative; touch-action: none; }
        canvas { width: 100%; height: 180px; display: block; border-radius: 8px; }
        .btn-limpiar { position: absolute; top: 10px; right: 10px; background: #e0e0e0; border: none; padding: 5px 10px; border-radius: 4px; font-size: 12px; cursor: pointer; }
        
        .btn-enviar { background: #1b5e20; color: white; border: none; padding: 15px; font-size: 18px; font-weight: bold; border-radius: 8px; cursor: pointer; width: 100%; display: block; margin-top: 20px; text-transform: uppercase; }
        #mensajeOffline { display: none; background: #fff3cd; color: #856404; padding: 15px; border-radius: 6px; text-align: center; margin-top: 15px; font-weight: bold; border: 2px solid #ffeeba; }
    </style>
</head>
<body>

<div class="eval-container">
    <div class="header-eval">
        <h2>Acta de Conformidad</h2>
        <p>🔒 Documento Confidencial - Uso exclusivo del Cliente</p>
        <span style="display:block; margin-top:5px; color:#666;">Inventario: <strong><?php echo htmlspecialchars($evaluacion['local_nombre']); ?></strong></span>
    </div>

    <form id="formEvaluacion" method="POST" action="">
        <div class="form-group">
            <label>Nombre y Apellido de quien firma: <span style="color:red">*</span></label>
            <input type="text" name="nombre_evaluador" id="nombre_evaluador" placeholder="Ej: María González (Gerenta)" required>
        </div>

        <h3 style="font-size: 15px; margin-bottom: 10px; border-bottom: 1px solid #ccc; padding-bottom: 5px;">Calificación del Servicio</h3>
        
        <!-- 1. Puntualidad -->
        <div class="categoria-estrellas">
            <span>⏰ Puntualidad / Organización</span>
            <div class="estrellas">
                <input type="radio" id="p5" name="est_puntualidad" value="5"><label for="p5">★</label>
                <input type="radio" id="p4" name="est_puntualidad" value="4"><label for="p4">★</label>
                <input type="radio" id="p3" name="est_puntualidad" value="3"><label for="p3">★</label>
                <input type="radio" id="p2" name="est_puntualidad" value="2"><label for="p2">★</label>
                <input type="radio" id="p1" name="est_puntualidad" value="1"><label for="p1">★</label>
            </div>
        </div>

        <!-- 2. Organización -->
        <div class="categoria-estrellas">
            <span>📦 Organización del Equipo</span>
            <div class="estrellas">
                <input type="radio" id="o5" name="est_organizacion" value="5"><label for="o5">★</label>
                <input type="radio" id="o4" name="est_organizacion" value="4"><label for="o4">★</label>
                <input type="radio" id="o3" name="est_organizacion" value="3"><label for="o3">★</label>
                <input type="radio" id="o2" name="est_organizacion" value="2"><label for="o2">★</label>
                <input type="radio" id="o1" name="est_organizacion" value="1"><label for="o1">★</label>
            </div>
        </div>

        <!-- 3. Prolijidad -->
        <div class="categoria-estrellas">
            <span>✨ Prolijidad en el Local</span>
            <div class="estrellas">
                <input type="radio" id="pr5" name="est_prolijidad" value="5"><label for="pr5">★</label>
                <input type="radio" id="pr4" name="est_prolijidad" value="4"><label for="pr4">★</label>
                <input type="radio" id="pr3" name="est_prolijidad" value="3"><label for="pr3">★</label>
                <input type="radio" id="pr2" name="est_prolijidad" value="2"><label for="pr2">★</label>
                <input type="radio" id="pr1" name="est_prolijidad" value="1"><label for="pr1">★</label>
            </div>
        </div>

        <!-- 4. Trato -->
        <div class="categoria-estrellas">
            <span>🤝 Trato y Profesionalismo</span>
            <div class="estrellas">
                <input type="radio" id="t5" name="est_trato" value="5"><label for="t5">★</label>
                <input type="radio" id="t4" name="est_trato" value="4"><label for="t4">★</label>
                <input type="radio" id="t3" name="est_trato" value="3"><label for="t3">★</label>
                <input type="radio" id="t2" name="est_trato" value="2"><label for="t2">★</label>
                <input type="radio" id="t1" name="est_trato" value="1"><label for="t1">★</label>
            </div>
        </div>

        <div class="form-group" style="margin-top: 15px;">
            <label>Observaciones o Comentarios (Opcional):</label>
            <textarea name="comentario" rows="2" placeholder="Si desea dejarnos algún mensaje..."></textarea>
        </div>

        <div class="form-group">
            <label>Firma de Conformidad: <span style="color:red">*</span></label>
            <div class="firma-box">
                <button type="button" class="btn-limpiar" onclick="limpiarFirma()">Borrar</button>
                <canvas id="pizarra"></canvas>
            </div>
        </div>
        
        <input type="hidden" name="firma" id="firmaBase64">

        <button type="button" class="btn-enviar" id="btnSubmit" onclick="procesarEnvio()">Cerrar Acta</button>
        
        <!-- Cartel de alerta si no hay internet -->
        <div id="mensajeOffline">
            📶 Sin conexión a Internet.<br>Por favor, camine hacia un lugar con señal. El acta se enviará automáticamente.
        </div>
    </form>
</div>

<script>
    // --- LÓGICA DE LA PIZARRA DE FIRMA ---
    const canvas = document.getElementById('pizarra');
    const ctx = canvas.getContext('2d');
    let dibujando = false;

    function ajustarCanvas() {
        const rect = canvas.parentElement.getBoundingClientRect();
        canvas.width = rect.width;
        canvas.height = 180;
        ctx.lineWidth = 3;
        ctx.lineCap = 'round';
        ctx.strokeStyle = '#000000';
    }
    window.onload = ajustarCanvas;

    canvas.addEventListener('touchstart', (e) => { e.preventDefault(); dibujando = true; const pos = getPos(e); ctx.beginPath(); ctx.moveTo(pos.x, pos.y); }, {passive: false});
    canvas.addEventListener('touchmove', (e) => { e.preventDefault(); if(!dibujando) return; const pos = getPos(e); ctx.lineTo(pos.x, pos.y); ctx.stroke(); }, {passive: false});
    canvas.addEventListener('touchend', () => dibujando = false);
    
    // Soporte para PC/Mouse
    canvas.addEventListener('mousedown', (e) => { dibujando = true; const pos = getPos(e); ctx.beginPath(); ctx.moveTo(pos.x, pos.y); });
    canvas.addEventListener('mousemove', (e) => { if(!dibujando) return; const pos = getPos(e); ctx.lineTo(pos.x, pos.y); ctx.stroke(); });
    canvas.addEventListener('mouseup', () => dibujando = false);

    function getPos(e) {
        const rect = canvas.getBoundingClientRect();
        return e.touches ? { x: e.touches[0].clientX - rect.left, y: e.touches[0].clientY - rect.top } : { x: e.clientX - rect.left, y: e.clientY - rect.top };
    }

    function limpiarFirma() { ctx.clearRect(0, 0, canvas.width, canvas.height); }

    // --- LÓGICA DE ENVÍO Y PROTECCIÓN OFFLINE ---
    function procesarEnvio() {
        // 1. Validaciones
        const nombre = document.getElementById('nombre_evaluador').value;
        const pts = document.querySelector('input[name="est_puntualidad"]:checked');
        const org = document.querySelector('input[name="est_organizacion"]:checked');
        const pro = document.querySelector('input[name="est_prolijidad"]:checked');
        const tra = document.querySelector('input[name="est_trato"]:checked');

        if(!nombre || !pts || !org || !pro || !tra) {
            alert("Por favor, complete su nombre y marque las 4 categorías de estrellas.");
            return;
        }

        const canvasVacio = document.createElement('canvas');
        canvasVacio.width = canvas.width; canvasVacio.height = canvas.height;
        if (canvas.toDataURL() === canvasVacio.toDataURL()) {
            alert("Por favor, firme en el recuadro.");
            return;
        }

        // Cargar firma en el input oculto
        document.getElementById('firmaBase64').value = canvas.toDataURL();

        // 2. Comprobar Internet (El truco del sótano)
        if (navigator.onLine) {
            document.getElementById('formEvaluacion').submit();
        } else {
            // Se quedó sin internet. Mostramos cartel y bloqueamos el botón.
            document.getElementById('btnSubmit').style.display = 'none';
            document.getElementById('mensajeOffline').style.display = 'block';
            
            // Le decimos a la máquina: "Avisame apenar recuperes el internet"
            window.addEventListener('online', function() {
                document.getElementById('mensajeOffline').innerHTML = "✔️ Conexión recuperada. Enviando...";
                document.getElementById('mensajeOffline').style.background = "#d4edda";
                document.getElementById('mensajeOffline').style.color = "#155724";
                document.getElementById('formEvaluacion').submit();
            });
        }
    }
</script>

</body>
</html>