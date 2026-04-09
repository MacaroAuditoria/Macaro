<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Escáner - MACARO</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #2b2b2b; color: #fff; margin: 0; padding: 0; }
        
        .header-bar { background: #6200ea; padding: 15px; display: flex; justify-content: space-between; align-items: center; font-size: 18px; font-weight: bold; }
        
        /* Estilos para el menú de 3 puntos */
        .menu-dots { background: transparent; border: none; color: white; font-size: 26px; cursor: pointer; padding: 0 10px; font-weight: bold; }
        .dropdown-menu { display: none; position: absolute; right: 10px; top: 55px; background: #333; border: 1px solid #555; border-radius: 8px; box-shadow: 0 8px 16px rgba(0,0,0,0.5); z-index: 1000; overflow: hidden; }
        .dropdown-menu a { display: block; padding: 18px 25px; color: white; text-decoration: none; border-bottom: 1px solid #444; font-size: 16px; font-weight: normal; }
        .dropdown-menu a:last-child { border-bottom: none; }
        
        .info-panel { background: #1e1e1e; padding: 15px; font-size: 14px; border-bottom: 1px solid #444; color: #aaa; }
        .info-panel span { display: block; margin-bottom: 5px; }
        
        /* --- AJUSTES DE CENTRADO AQUÍ --- */
        .scanner-area { 
            padding: 20px; 
            max-width: 400px; /* Evita que se estire demasiado en pantallas más grandes */
            margin: 0 auto; /* Centra todo el bloque en la pantalla */
            display: flex;
            flex-direction: column;
            align-items: center; /* Centra los elementos internos */
        }
        
        #scanForm {
            width: 100%; /* El formulario usa el 100% del espacio centrado */
        }
        
        .input-group { 
            display: flex; 
            gap: 10px; 
            justify-content: center; /* Centra el input y el botón horizontalmente */
            margin-bottom: 20px;
            width: 100%;
        }
        /* -------------------------------- */
        
        .input-barcode { width: 100%; padding: 15px; font-size: 24px; font-weight: bold; border: none; border-radius: 5px; background: #fff; text-align: center; box-sizing: border-box; }
        .input-qty { width: 100px; padding: 15px; font-size: 24px; font-weight: bold; border: none; border-radius: 5px; text-align: center; background: #e0e0e0; color: #555; }
        
        .btn-lock { background: #444; border: none; color: white; padding: 15px; font-size: 20px; border-radius: 5px; cursor: pointer; min-width: 60px; }
        .btn-lock.unlocked { background: #ff9800; color: black; }
        
        .success-card { background: #004d40; padding: 15px; border-radius: 5px; border-left: 5px solid #00bfa5; margin-bottom: 20px; font-size: 16px; width: 100%; box-sizing: border-box;}
        .error-card { background: #b71c1c; padding: 15px; border-radius: 5px; border-left: 5px solid #ff5252; margin-bottom: 20px; font-size: 16px; font-weight: bold; width: 100%; box-sizing: border-box;}
        
        .total-box { text-align: center; font-size: 20px; margin-top: 30px; color: #00e676; width: 100%; }
        .total-box span { font-size: 36px; font-weight: bold; display: block; }
        
        /* Centrar los textos de arriba de las cajas */
        .etiqueta-centrada { color: #aaa; font-size: 12px; margin-bottom: 5px; display:block; text-align: center; font-weight: bold; letter-spacing: 1px; }
    </style>
</head>
<body>

<div class="header-bar">
    <div>≡ <?php echo htmlspecialchars($local_nombre); ?></div>
    
    <div>
        <button class="menu-dots" onclick="toggleMenu()">⋮</button>
        <div id="dropMenu" class="dropdown-menu">
            <a href="index.php?action=piqueo_visualizar">👁️ Visualizar lo escaneado</a>
            
            <a href="#" onclick="document.getElementById('modalNuevoProducto').style.display='flex'; return false;" style="display: block; padding: 15px; text-decoration: none; border-bottom: 1px solid #eee; font-weight: bold;">
                ➕ Agregar Producto
            </a>

            <a href="index.php?action=piqueo_salir_zona" onclick="return confirm('¿Seguro que deseas SALIR SIN BLOQUEAR? \n\nLa zona quedará disponible para que puedas volver a entrar más tarde.');">🔙 Salir sin bloquear</a>
            
            <a href="index.php?action=piqueo_terminar_zona" onclick="return confirm('⚠️ ATENCIÓN \n\n¿Seguro que deseas TERMINAR y BLOQUEAR esta zona definitivamente?');" style="color: #ff5252; font-weight: bold;">🔴 Terminar y Bloquear</a>
        </div>
    </div>
</div>

<div class="info-panel">
    <span>👤 Operario: <?php echo htmlspecialchars($_SESSION['nombre_completo']); ?></span>
    <?php if ($sector_nombre): ?>
        <span>🏷️ Sector: <strong style="color:#fff; font-size:16px;"><?php echo htmlspecialchars($sector_nombre); ?></strong></span>
    <?php endif; ?>
    <span>📍 Zona Activa: <strong style="color:#fff; font-size:16px;"><?php echo htmlspecialchars($zona_nombre); ?></strong></span>
</div>

<div class="scanner-area">
    <?php echo $mensaje_estado;
    if (isset($_GET['prod_creado'])) { $mensaje_estado = "<div class='success-card'>✅ Producto " . htmlspecialchars($_GET['prod_creado']) . " creado con éxito. Ya podés escanearlo.</div>"; } ?>
    
    <form method="POST" action="index.php?action=piqueo_escaner" id="scanForm">
        <label class="etiqueta-centrada">CÓDIGO DE BARRAS</label>
        <div class="input-group">
            <input type="text" name="codigo_barras" id="codigo" class="input-barcode" required autocomplete="off">
        </div>

        <label class="etiqueta-centrada">CANTIDAD</label>
        <div class="input-group">
            <input type="number" step="0.01" name="cantidad" id="cantidad" class="input-qty" value="1" readonly>
            <button type="button" id="lockBtn" class="btn-lock">🔒</button>
            <button type="submit" style="display:none;">Oculto</button>
        </div>
    </form>

    <div class="total-box">
        Total en esta Zona
        <span><?php echo $total_zona; ?></span>
    </div>
</div>

<div id="modalNuevoProducto" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 9999; justify-content: center; align-items: center;">
    <div style="background: white; padding: 25px; border-radius: 12px; width: 90%; max-width: 400px; box-shadow: 0 10px 25px rgba(0,0,0,0.5);">
        <h3 style="margin-top: 0; color: #333; border-bottom: 2px solid #00897b; padding-bottom: 10px; font-size: 20px;">📦 Alta Rápida</h3>
        
        <form action="index.php?action=piqueo_crear_producto" method="POST">
            <label style="display: block; font-weight: bold; margin-bottom: 5px; color: #555; font-size: 14px;">Código de Barras *</label>
            <input type="text" name="codigo_barras" required placeholder="Ej: 7730000000000" style="width: 100%; padding: 12px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 6px; box-sizing: border-box; font-size: 16px;">

            <label style="display: block; font-weight: bold; margin-bottom: 5px; color: #555; font-size: 14px;">SKU (Opcional)</label>
            <input type="text" name="sku" placeholder="Ej: ART-123" style="width: 100%; padding: 12px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 6px; box-sizing: border-box; font-size: 16px;">

            <label style="display: block; font-weight: bold; margin-bottom: 5px; color: #555; font-size: 14px;">Descripción *</label>
            <input type="text" name="descripcion" required placeholder="Nombre del producto..." style="width: 100%; padding: 12px; margin-bottom: 25px; border: 1px solid #ccc; border-radius: 6px; box-sizing: border-box; font-size: 16px; text-transform: uppercase;">

            <div style="display: flex; gap: 10px;">
                <button type="button" onclick="document.getElementById('modalNuevoProducto').style.display='none';" style="flex: 1; padding: 15px; background: #e0e0e0; color: #333; border: none; border-radius: 6px; font-weight: bold; font-size: 16px; cursor: pointer;">Cancelar</button>
                <button type="submit" style="flex: 1; padding: 15px; background: #00897b; color: white; border: none; border-radius: 6px; font-weight: bold; font-size: 16px; cursor: pointer;">Crear Producto</button>
            </div>
        </form>
    </div>
</div>

<script>
    // Foco automático en el escáner
    document.getElementById('codigo').focus();

    // Lógica del candado
    const lockBtn = document.getElementById('lockBtn');
    const inputQty = document.getElementById('cantidad');
    const inputCode = document.getElementById('codigo');

    lockBtn.addEventListener('click', function() {
        if (inputQty.hasAttribute('readonly')) {
            inputQty.removeAttribute('readonly');
            inputQty.style.background = '#fff';
            inputQty.style.color = '#000';
            lockBtn.innerHTML = '🔓';
            lockBtn.classList.add('unlocked');
        } else {
            inputQty.setAttribute('readonly', 'true');
            inputQty.style.background = '#e0e0e0';
            inputQty.style.color = '#555';
            inputQty.value = '1';
            lockBtn.innerHTML = '🔒';
            lockBtn.classList.remove('unlocked');
            inputCode.focus();
        }
    });

    // Desplegar menú de opciones
    function toggleMenu() {
        const menu = document.getElementById('dropMenu');
        menu.style.display = (menu.style.display === 'block') ? 'none' : 'block';
    }

    // El BEEP de error
    <?php if($alerta_sonido): ?>
    let context = new (window.AudioContext || window.webkitAudioContext)();
    let osc = context.createOscillator();
    let gain = context.createGain();
    osc.connect(gain);
    gain.connect(context.destination);
    osc.type = 'square';
    osc.frequency.value = 300;
    gain.gain.setValueAtTime(0.1, context.currentTime);
    osc.start(context.currentTime);
    osc.stop(context.currentTime + 0.5);
    <?php endif; ?>
</script>

</body>
</html>