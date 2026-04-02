<!DOCTYPE html>
<html lang="es"><head><meta charset="UTF-8"><title>Gestión - MACARO</title><link rel="stylesheet" href="css/style.css"></head>
<body class="dashboard-container">
<div class="header"><div class="user-info">🔎 Buscar / Gestionar Productos</div>
<div><a href="index.php?action=productos" class="btn-primario" style="background: #6c757d; text-decoration:none;">Volver</a></div></div>
<div style="background: white; padding: 30px; border-radius: 10px; max-width: 900px; margin: 0 auto;">
    <form method="GET" action="index.php" style="display:flex; gap:10px; margin-bottom: 20px;">
        <input type="hidden" name="action" value="productos_gestion">
        <input type="text" name="busqueda" placeholder="Buscar código o descripción..." style="flex:1; padding:10px;">
        <button type="submit" class="btn-primario" style="width:auto;">Buscar</button>
    </form>
    <table style="width: 100%; border-collapse: collapse; text-align: left;">
        <tr style="background: #eee;"><th>Código</th><th>Descripción</th><th>Categoría</th></tr>
        <?php foreach ($listaProductos as $p): ?>
        <tr style="border-bottom: 1px solid #ccc;">
            <td style="padding:10px;"><strong><?php echo $p['codigo_barras']; ?></strong></td>
            <td style="padding:10px;"><?php echo $p['descripcion']; ?></td>
            <td style="padding:10px;"><?php echo $p['categoria_nombre'] ?? '-'; ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>
</body></html>