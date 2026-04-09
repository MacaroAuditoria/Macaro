<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Nuevo Conteo - MACARO</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f6f9; margin: 0; padding: 0; }
        .app-header { background: white; padding: 15px; border-bottom: 1px solid #ddd; display: flex; align-items: center; font-size: 18px; font-weight: bold;}
        .back-btn { text-decoration: none; color: #333; font-size: 24px; margin-right: 15px; }
        .container { padding: 20px; }
        .form-group { margin-bottom: 25px; }
        .form-label { display: block; font-size: 14px; color: #666; margin-bottom: 5px; font-weight: bold;}
        
        select { 
            width: 100%; padding: 15px; font-size: 18px; border: 1px solid #ccc; 
            border-radius: 8px; background: white; appearance: none; box-sizing: border-box;
        }
        
        .btn-start {
            background-color: #00897b; color: white; border: none; width: 100%;
            padding: 20px; font-size: 20px; font-weight: bold; border-radius: 50px; margin-top: 30px;
        }
    </style>
</head>
<body>

<div class="app-header">
    <a href="index.php" class="back-btn">←</a>
    Nuevo Conteo
</div>

<div class="container">
    <?php if (!empty($error)) echo "<div style='background:#ff5252; color:white; padding:15px; border-radius:8px; margin-bottom:20px; font-weight:bold;'>$error</div>"; ?>

    <form method="POST" action="index.php?action=piqueo_config">
        
        <div class="form-group">
            <label class="form-label">Inventario (Local)</label>
            <select name="local_id" id="local_id" required>
                <option value="">(Seleccionar)</option>
                <?php foreach ($locales as $l): ?>
                    <option value="<?php echo $l['id']; ?>"><?php echo htmlspecialchars($l['nombre']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label class="form-label">Sector</label>
            <select name="sector_id" id="sector_id" required>
                <option value="">Primero seleccione un Local...</option>
            </select>
        </div>

        <div class="form-group">
            <label class="form-label">Zona</label>
            <select name="zona_id" id="zona_id" required>
                <option value="">Primero seleccione Local y Sector...</option>
            </select>
        </div>

        <button type="submit" class="btn-start">COMENZAR PIQUEO ✓</button>
    </form>
</div>

<script>
    // 1. Recibimos todos los datos desde el index.php
    const todosLosSectores = <?php echo $json_sectores; ?>; 
    const zonasCerradas = <?php echo $json_cerradas; ?>;
    const todasLasZonasData = <?php echo $json_todas_las_zonas; ?>; 
    
    const localSelect = document.getElementById('local_id');
    const sectorSelect = document.getElementById('sector_id');
    const zonaSelect = document.getElementById('zona_id');

    // 2. MAGIA NUEVA: Filtrar los Sectores según el Local
    function actualizarSectoresDisponibles() {
        const localId = localSelect.value;
        
        // Limpiamos la lista de sectores
        sectorSelect.innerHTML = '<option value="">(Seleccionar)</option>';
        
        if (localId) {
            todosLosSectores.forEach(sector => {
                // Solo agregamos el sector si pertenece al local elegido
                if (sector.local_id == localId) {
                    const opt = document.createElement('option');
                    opt.value = sector.id;
                    opt.textContent = sector.nombre;
                    sectorSelect.appendChild(opt);
                }
            });
        } else {
            sectorSelect.innerHTML = '<option value="">Primero seleccione un Local...</option>';
        }
        
        // Al cambiar de local o sector, las zonas también deben resetearse
        actualizarZonasDisponibles();
    }

    // 3. Tu lógica original para las Zonas (intacta)
    function actualizarZonasDisponibles() {
        const localId = localSelect.value;
        const sectorId = sectorSelect.value;

        zonaSelect.innerHTML = '<option value="">(Seleccionar)</option>';

        if (localId && sectorId) {
            todasLasZonasData.forEach(zona => {
                const esGlobal = zona.local_id === null;
                const esDeEsteSector = zona.local_id == localId && zona.sector_id == sectorId;

                if (esGlobal || esDeEsteSector) {
                    const estaCerrada = zonasCerradas.some(zc => 
                        zc.local_id == localId && zc.sector_id == sectorId && zc.zona_id == zona.id
                    );

                    if (!estaCerrada) {
                        const opt = document.createElement('option');
                        opt.value = zona.id;
                        opt.textContent = zona.codigo;
                        zonaSelect.appendChild(opt);
                    }
                }
            });
        } else {
            zonaSelect.innerHTML = '<option value="">Primero seleccione Local y Sector...</option>';
        }
    }

    // 4. Los "Escuchadores" que detonan las funciones cuando el usuario toca algo
    localSelect.addEventListener('change', actualizarSectoresDisponibles);
    sectorSelect.addEventListener('change', actualizarZonasDisponibles);
</script>

</body>
</html>