<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Detalle de Zona - MACARO</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .detalle-box { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        table { width: 100%; border-collapse: collapse; margin-top: 20px;}
        th { background: #f8f9fa; padding: 12px; text-align: left; border-bottom: 2px solid #ddd; font-size: 14px;}
        td { padding: 12px; border-bottom: 1px solid #eee; font-size: 14px;}
        tr:hover { background-color: #f1f8ff; }
        .btn-borrar { color: #d32f2f; text-decoration: none; font-weight: bold; padding: 5px 10px; border-radius: 4px; transition: 0.2s; }
        .btn-borrar:hover { background: #ffebee; }
    </style>
</head>
<body class="dashboard-container">

<div class="header">
    <div class="user-info">📍 Detalle de Zona: <?php echo htmlspecialchars($zona_nombre); ?></div>
    <a href="index.php?action=monitor_zonas&local_id=<?php echo $local_id; ?>&sector_id=<?php echo $sector_id; ?>" class="btn-primario" style="background:#666; text-decoration:none;">Volver al Monitor</a>
</div>

<div class="detalle-box">
    <div style="margin-bottom: 20px;">
        <label style="display: block; font-weight: bold; margin-bottom: 8px; color: #555;">🔍 Filtrar en esta zona:</label>
        <input type="text" id="filtroDetalle" placeholder="Buscar por código, descripción o SKU..." style="width: 100%; padding: 12px; border: 2px solid #004d40; border-radius: 8px; font-size: 16px; outline: none;">
    </div>

    <?php if (isset($_GET['msj']) && $_GET['msj'] === 'borrado'): ?>
        <div style="background:#e8f5e9; color:#2e7d32; padding:10px; border-radius:5px; margin-bottom:15px; font-weight:bold;">🗑️ Registro eliminado correctamente.</div>
    <?php endif; ?>

    <div style="overflow-x: auto;">
        <table>
            <thead>
                <tr>
                    <th>Código de Barras</th>
                    <th>Descripción</th>
                    <th>Cantidad</th>
                    <th>Usuario</th>
                    <th style="text-align: center;">Acciones</th>
                </tr>
            </thead>
            <tbody id="tablaCuerpo">
                <?php foreach ($detalles as $d): ?>
                <tr class="fila-dato">
                    <td><?php echo htmlspecialchars($d['codigo_barras']); ?></td>
                    <td><strong><?php echo htmlspecialchars($d['descripcion'] ?? 'DESCONOCIDO'); ?></strong></td>
                    <td style="font-size: 16px; font-weight: bold; color: #004d40;"><?php echo number_format($d['cantidad'], 2); ?></td>
                    <td><?php echo htmlspecialchars($d['nombre_usuario'] ?? 'Desconocido'); ?></td>
                    <td style="text-align: center;">
                        <a href="index.php?action=monitor_borrar_item&id_conteo=<?php echo $d['id']; ?>&local_id=<?php echo $local_id; ?>&sector_id=<?php echo $sector_id; ?>&zona_id=<?php echo $_GET['zona_id']; ?>" 
                           class="btn-borrar" 
                           onclick="return confirm('¿Seguro que querés borrar este piqueo específico?');">🗑️ Borrar</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    // SCRIPT DEL BUSCADOR EN TIEMPO REAL
    document.getElementById('filtroDetalle').addEventListener('keyup', function() {
        const busqueda = this.value.toLowerCase().trim();
        const filas = document.querySelectorAll('.fila-dato');

        filas.forEach(fila => {
            // Buscamos en toda la fila (Código + Descripción)
            const contenido = fila.textContent.toLowerCase();
            if (contenido.includes(busqueda)) {
                fila.style.display = '';
            } else {
                fila.style.display = 'none';
            }
        });
    });
</script>

</body>
</html>