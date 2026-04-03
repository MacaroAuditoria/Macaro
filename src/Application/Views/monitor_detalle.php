<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Detalle de Zona - MACARO</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .detalle-box { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        table { width: 100%; border-collapse: collapse; margin-top: 20px;}
        th { background: #f8f9fa; padding: 12px; text-align: left; border-bottom: 2px solid #ddd;}
        td { padding: 12px; border-bottom: 1px solid #eee; }
    </style>
</head>
<body class="dashboard-container">

<div class="header">
    <div class="user-info">📦 Detalle de Zona: <?php echo htmlspecialchars($zona_nombre); ?></div>
    <a href="javascript:history.back()" class="btn-primario" style="background: #6c757d; text-decoration:none;">Volver al Monitor</a>
</div>

<div class="detalle-box">
    <h3 style="margin-top: 0;">📍 Inventario: <?php echo htmlspecialchars($local_nombre); ?></h3>
    <p style="color: #666;">Productos contados dentro de esta zona. Se agrupan los códigos iguales.</p>

    <?php if(empty($detalles)): ?>
        <div style="padding: 20px; text-align: center; color: #888; background: #f9f9f9; border-radius: 5px;">No hay productos contados en esta zona todavía.</div>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Código de Barras</th>
                    <th>Descripción</th>
                    <th>Total Físico Contado</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($detalles as $d): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($d['codigo_barras']); ?></strong></td>
                        <td><?php echo !empty($d['descripcion']) ? htmlspecialchars($d['descripcion']) : '<span style="color:red;">⚠️ Desconocido</span>'; ?></td>
                        <td style="font-size: 18px; font-weight: bold; color: #004d40;"><?php echo number_format($d['total_producto'], 2); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

</body>
</html>