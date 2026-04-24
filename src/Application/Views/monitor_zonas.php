<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Monitor - MACARO</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .filtro-caja { background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .tabla-contenedor { overflow-x: auto; background: white; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .tabla-monitor { width: 100%; border-collapse: collapse; min-width: 800px; }
        .tabla-monitor th { background: #004d40; color: white; padding: 15px; text-align: left; white-space: nowrap; }
        .tabla-monitor td { padding: 12px 15px; border-bottom: 1px solid #eee; white-space: nowrap; }
        .estado-abierta { color: #2e7d32; font-weight: bold; }
        .estado-cerrada { color: #d32f2f; font-weight: bold; }
        .link-zona { color: #1976d2; font-weight: bold; text-decoration: underline; font-size: 16px;}
    </style>
</head>
<body class="dashboard-container">

<div class="header">
    <div class="user-info">📊 Monitor de Zonas en Vivo</div>
    <a href="index.php?action=dashboard" class="logout-btn" style="background:#666;">Volver</a>
</div>

<?php if (isset($_GET['error']) && $_GET['error'] === 'zona_duplicada'): ?>
    <div id="alertaZonaDuplicada" style="background-color: #ffebee; color: #c62828; padding: 15px 20px; border-radius: 8px; margin-bottom: 20px; font-weight: bold; border-left: 6px solid #c62828; box-shadow: 0 4px 6px rgba(0,0,0,0.1); display: flex; justify-content: space-between; align-items: center;">
        <span>⚠️ <strong>¡Atención!</strong> La zona que intentaste crear ya existe en este sector. Ingresá un código distinto.</span>
        <button onclick="document.getElementById('alertaZonaDuplicada').style.display='none'" style="background: none; border: none; color: #c62828; font-size: 20px; cursor: pointer;">&times;</button>
    </div>
    <script>setTimeout(function() { document.getElementById('alertaZonaDuplicada').style.display = 'none'; }, 5000);</script>
<?php endif; ?>

<div class="filtro-caja">
    <form method="GET" action="index.php" style="display: flex; gap: 15px; align-items: flex-end; flex-wrap: wrap;">
        <input type="hidden" name="action" value="monitor_zonas">
        
        <div style="flex: 1; min-width: 200px;">
            <label>Inventario (Local)</label>
            <select name="local_id" id="localSelect" required style="width: 100%; padding: 10px;">
                <option value="">Seleccione...</option>
                <?php foreach($locales as $l): ?>
                    <option value="<?php echo $l['id']; ?>" <?php echo ($local_id == $l['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($l['nombre']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div style="flex: 1; min-width: 200px;">
            <label>Sector</label>
            <select name="sector_id" id="sectorSelect" required style="width: 100%; padding: 10px;">
                <option value="">Primero seleccione un Local...</option>
            </select>
        </div>

        <div style="flex: 1; min-width: 150px;">
            <label>Estado de Zona</label>
            <select name="estado" id="estadoFiltro" style="width: 100%; padding: 10px; border: 1px solid #2196f3; border-radius: 4px; font-weight: bold;">
                <option value="todas" <?php echo (isset($_GET['estado']) && $_GET['estado'] == 'todas') ? 'selected' : ''; ?>>Ver Todas</option>
                <option value="abiertas" <?php echo (isset($_GET['estado']) && $_GET['estado'] == 'abiertas') ? 'selected' : ''; ?>>Solo Abiertas 🔓</option>
                <option value="cerradas" <?php echo (isset($_GET['estado']) && $_GET['estado'] == 'cerradas') ? 'selected' : ''; ?>>Solo Cerradas 🔒</option>
            </select>
        </div>
        
        <button type="submit" class="btn-primario" style="padding: 10px 20px;">🔍 Buscar</button>
    </form>
</div>

<?php if ($local_id && $sector_id): ?>
    
    <div style="background: #e8f5e9; padding: 15px; border-radius: 8px; margin-bottom: 20px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 15px;">
        
        <div style="display: flex; align-items: center; gap: 15px;">
            <strong style="color: #2e7d32;">➕ Agregar Nueva Zona al Sistema:</strong>
            <form method="POST" action="index.php" style="display: flex; gap: 10px;">
                <input type="hidden" name="action" value="zonas_crear_rapido">
                <input type="hidden" name="local_id" value="<?php echo $local_id; ?>">
                <input type="hidden" name="sector_id" value="<?php echo $sector_id; ?>">
                <input type="text" name="nuevo_codigo" required placeholder="Ej: A0500" style="padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                <button type="submit" class="btn-primario" style="padding: 8px 15px; width: auto;">Guardar Zona</button>
            </form>
        </div>

        <a href="index.php?action=imprimir_zonas&local_id=<?php echo $local_id; ?>&sector_id=<?php echo $sector_id; ?>" target="_blank" style="background-color: #673ab7; color: white; padding: 10px 20px; text-decoration: none; font-weight: bold; border-radius: 6px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            🖨️ Imprimir Tarjetas del Sector
        </a>

    </div>

    <div class="tabla-contenedor">
        <table class="tabla-monitor">
            <thead>
                <tr>
                    <th>Zona</th>
                    <th>Estado</th>
                    <th>Unidades Contadas</th>
                    <th>En Uso Por</th>
                    <th>Cerrado Por</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($datos_tabla as $fila): ?>
                    <tr class="fila-zona-monitor" data-estado="<?php echo $fila['bloqueada'] ? 'cerradas' : 'abiertas'; ?>">
                        <td><a href="index.php?action=monitor_detalle_zona&local_id=<?php echo $local_id; ?>&sector_id=<?php echo $sector_id; ?>&zona_id=<?php echo $fila['zona_id']; ?>" class="link-zona">📍 <?php echo htmlspecialchars($fila['zona_nombre']); ?></a></td>
                        
                        <?php if($fila['bloqueada']): ?>
                            <td class="estado-cerrada">🔒 CERRADA</td>
                        <?php else: ?>
                            <td class="estado-abierta">🔓 ABIERTA</td>
                        <?php endif; ?>
                        
                        <td style="font-size: 16px;"><?php echo $fila['total_unidades'] ? number_format($fila['total_unidades'], 2) : '0.00'; ?></td>
                        
                        <td style="color: #1976d2; font-weight: bold;">
                            <?php echo (!$fila['bloqueada'] && $fila['en_uso_por']) ? '👨‍💻 ' . htmlspecialchars($fila['en_uso_por']) : '-'; ?>
                        </td>

                        <td style="color: #666; font-style: italic;"><?php echo $fila['cerrado_por'] ? htmlspecialchars($fila['cerrado_por']) : '-'; ?></td>
                        
                        
                        <td>
                            <?php if($fila['bloqueada']): ?>
                                <a href="index.php?action=monitor_reabrir_zona&local_id=<?php echo $local_id; ?>&sector_id=<?php echo $sector_id; ?>&zona_id=<?php echo $fila['zona_id']; ?>" style="color: #d32f2f; font-weight: bold; text-decoration: none;" onclick="return confirm('¿Reabrir zona? Los datos contados NO se borrarán.');">🔓 Reabrir</a>
                            <?php endif; ?>
                            
                            <a href="index.php?action=monitor_vaciar_zona&local_id=<?php echo $local_id; ?>&sector_id=<?php echo $sector_id; ?>&zona_id=<?php echo $fila['zona_id']; ?>" style="color: #ff9800; font-weight: bold; text-decoration: none; margin-left: 15px;" onclick="return confirm('⚠️ ATENCIÓN: \n\n¿Seguro que querés VACIAR esta zona? \nSe borrarán los productos pero la zona seguirá existiendo.');">🗑️ Vaciar</a>
                                
                            <a href="index.php?action=monitor_eliminar_zona&local_id=<?php echo $local_id; ?>&sector_id=<?php echo $sector_id; ?>&zona_id=<?php echo $fila['zona_id']; ?>" style="color: #c62828; font-weight: bold; text-decoration: none; margin-left: 15px;" onclick="return confirm('🚨 PELIGRO EXTREMO: \n\n¿Seguro que querés ELIMINAR COMPLETAMENTE esta zona del sistema? \nDesaparecerá de la lista y se borrarán sus conteos. \n\n¡Esta acción NO se puede deshacer!');">❌ Borrar</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<script>
    // === LÓGICA DE SECTORES ORIGINAL ===
    const todosLosSectores = <?php echo $json_sectores; ?>;
    const sectorSeleccionado = "<?php echo $sector_id; ?>"; 
    
    const localSelect = document.getElementById('localSelect');
    const sectorSelect = document.getElementById('sectorSelect');

    function actualizarSectores() {
        const localId = localSelect.value;
        sectorSelect.innerHTML = '<option value="">Seleccione...</option>';
        if (localId) {
            todosLosSectores.forEach(sector => {
                if (sector.local_id == localId) {
                    const opt = document.createElement('option');
                    opt.value = sector.id;
                    opt.textContent = sector.nombre;
                    if (sector.id == sectorSeleccionado) { opt.selected = true; }
                    sectorSelect.appendChild(opt);
                }
            });
        } else {
            sectorSelect.innerHTML = '<option value="">Primero seleccione un Local...</option>';
        }
    }

    localSelect.addEventListener('change', actualizarSectores);
    document.addEventListener('DOMContentLoaded', actualizarSectores);

    // ==============================================================
    // === NUEVA LÓGICA: FILTRO DE ESTADO EN TIEMPO REAL          ===
    // ==============================================================
    function aplicarFiltroEstado() {
        const estadoSeleccionado = document.getElementById('estadoFiltro').value;
        const filas = document.querySelectorAll('.fila-zona-monitor');
        
        filas.forEach(fila => {
            const estadoFila = fila.getAttribute('data-estado');
            // Si elige "Todas" o si el estado de la fila coincide con lo que eligió, la mostramos
            if (estadoSeleccionado === 'todas' || estadoSeleccionado === estadoFila) {
                fila.style.display = ''; 
            } else {
                // Si no coincide, la ocultamos instantáneamente
                fila.style.display = 'none'; 
            }
        });
    }

    // Le decimos que filtre ni bien carga la pantalla por si el botón trajo un filtro
    document.addEventListener('DOMContentLoaded', aplicarFiltroEstado);
    
    // Le decimos que escuche cada vez que tocás el desplegable para filtrar al instante
    const estadoFiltro = document.getElementById('estadoFiltro');
    if(estadoFiltro) {
        estadoFiltro.addEventListener('change', aplicarFiltroEstado);
    }
</script>

</body>
</html>