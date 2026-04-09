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

<div class="filtro-caja">
    <form method="GET" action="index.php" style="display: flex; gap: 15px; align-items: flex-end; flex-wrap: wrap;">
        <input type="hidden" name="action" value="monitor_zonas">
        <div style="flex: 1; min-width: 200px;">
            <label>Inventario (Local)</label>
            <select name="local_id" required style="width: 100%; padding: 10px;"><option value="">Seleccione...</option>
                <?php foreach($locales as $l): ?><option value="<?php echo $l['id']; ?>" <?php echo ($local_id == $l['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($l['nombre']); ?></option><?php endforeach; ?>
            </select>
        </div>
        <div style="flex: 1; min-width: 200px;">
            <label>Sector</label>
            <select name="sector_id" required style="width: 100%; padding: 10px;"><option value="">Seleccione...</option>
                <?php foreach($sectores as $s): ?><option value="<?php echo $s['id']; ?>" <?php echo ($sector_id == $s['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($s['nombre']); ?></option><?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="btn-primario" style="padding: 10px 20px;">🔍 Buscar Zonas</button>
    </form>
</div>

<?php if ($local_id && $sector_id): ?>
    
    <!-- Mini formulario para agregar zona rápida -->
    <div style="background: #e8f5e9; padding: 15px; border-radius: 8px; margin-bottom: 20px; display: flex; align-items: center; gap: 15px;">
        <strong style="color: #2e7d32;">➕ Agregar Nueva Zona al Sistema:</strong>
        <form method="POST" action="index.php" style="display: flex; gap: 10px;">
            <input type="hidden" name="action" value="zonas_crear_rapido">
            <input type="hidden" name="local_id" value="<?php echo $local_id; ?>">
            <input type="hidden" name="sector_id" value="<?php echo $sector_id; ?>">
            <input type="text" name="nuevo_codigo" required placeholder="Ej: A0500" style="padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
            <button type="submit" class="btn-primario" style="padding: 8px 15px; width: auto;">Guardar Zona</button>
        </form>
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
                    <tr>
                        <td><a href="index.php?action=monitor_detalle_zona&local_id=<?php echo $local_id; ?>&sector_id=<?php echo $sector_id; ?>&zona_id=<?php echo $fila['zona_id']; ?>" class="link-zona">📍 <?php echo htmlspecialchars($fila['zona_nombre']); ?></a></td>
                        
                        <?php if($fila['bloqueada']): ?>
                            <td class="estado-cerrada">🔒 CERRADA</td>
                        <?php else: ?>
                            <td class="estado-abierta">🔓 ABIERTA</td>
                        <?php endif; ?>
                        
                        <td style="font-size: 16px;"><?php echo $fila['total_unidades'] ? number_format($fila['total_unidades'], 2) : '0.00'; ?></td>
                        
                        <!-- NUEVO: MUESTRA QUIÉN ESTÁ USANDO LA ZONA AHORA -->
                        <td style="color: #1976d2; font-weight: bold;">
                            <?php echo (!$fila['bloqueada'] && $fila['en_uso_por']) ? '👨‍💻 ' . htmlspecialchars($fila['en_uso_por']) : '-'; ?>
                        </td>

                        <td style="color: #666; font-style: italic;"><?php echo $fila['cerrado_por'] ? htmlspecialchars($fila['cerrado_por']) : '-'; ?></td>
                        
                        <td>
                            <?php if($fila['bloqueada']): ?>
                                <a href="index.php?action=monitor_reabrir_zona&local_id=<?php echo $local_id; ?>&sector_id=<?php echo $sector_id; ?>&zona_id=<?php echo $fila['zona_id']; ?>" style="color: #d32f2f; font-weight: bold; text-decoration: none;" onclick="return confirm('¿Reabrir zona? Los datos contados NO se borrarán.');">🔓 Reabrir</a>
                            <?php endif; ?>
                            
                            <!-- NUEVO: BOTÓN PARA VACIAR -->
                            <a href="index.php?action=monitor_vaciar_zona&local_id=<?php echo $local_id; ?>&sector_id=<?php echo $sector_id; ?>&zona_id=<?php echo $fila['zona_id']; ?>" style="color: #ff9800; font-weight: bold; text-decoration: none; margin-left: 15px;" onclick="return confirm('⚠️ ATENCIÓN EXTREMA: \n\n¿Seguro que querés VACIAR esta zona? \nSe borrarán TODOS los productos contados en ella y quedará en 0. \n\n¡Esta acción NO se puede deshacer!');">🗑️ Vaciar</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

</body>
</html>