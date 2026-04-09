<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Catálogo - MACARO</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .tabla-gestion th { background: #f8f9fa; padding: 12px; border-bottom: 2px solid #ccc; font-size: 14px; }
        .tabla-gestion td { padding: 12px; border-bottom: 1px solid #eee; font-size: 14px; }
        .tabla-gestion tr:hover { background-color: #f1f8ff; }
        .link-editar { color: #2196f3; font-weight: bold; text-decoration: none; border-bottom: 1px dashed #2196f3; }
        .btn-eliminar { color: #d32f2f; text-decoration: none; font-size: 18px; padding: 5px 10px; border-radius: 4px; transition: background 0.2s; }
        .btn-eliminar:hover { background: #ffebee; }
    </style>
</head>
<body class="dashboard-container">

<div class="header">
    <div class="user-info">🔎 Gestión de Catálogo</div>
    <div><a href="index.php?action=productos" class="btn-primario" style="background: #6c757d; text-decoration:none;">Volver</a></div>
</div>

<div style="background: white; padding: 30px; border-radius: 10px; max-width: 1300px; margin: 30px auto; box-shadow: 0 4px 10px rgba(0,0,0,0.05);">
    
    <?php if (isset($_GET['msj'])): ?>
        <?php 
            $config = [
                'editado' => ['txt' => '✅ Producto actualizado correctamente.', 'bg' => '#e8f5e9', 'col' => '#2e7d32'],
                'creado'  => ['txt' => '🎉 Producto creado y guardado en el catálogo.', 'bg' => '#e3f2fd', 'col' => '#1565c0'],
                'eliminado'=> ['txt' => '🗑️ Producto eliminado satisfactoriamente.', 'bg' => '#fafafa', 'col' => '#616161'],
                'error_borrado' => ['txt' => '❌ No se pudo eliminar. El producto podría estar vinculado a otros registros.', 'bg' => '#ffebee', 'col' => '#c62828']
            ];
            $msj = $config[$_GET['msj']] ?? null;
        ?>
        <?php if ($msj): ?>
            <div id="alertaGeneral" style="background:<?php echo $msj['bg']; ?>; color:<?php echo $msj['col']; ?>; padding:15px; border-radius:8px; margin-bottom:20px; font-weight:bold; border-left: 5px solid <?php echo $msj['col']; ?>; display: flex; justify-content: space-between; align-items: center;">
                <span><?php echo $msj['txt']; ?></span>
                <button onclick="this.parentElement.style.display='none'" style="background:none; border:none; color:<?php echo $msj['col']; ?>; cursor:pointer; font-size:18px;">&times;</button>
            </div>
            <script>setTimeout(() => { document.getElementById('alertaGeneral').style.display='none'; }, 4000);</script>
        <?php endif; ?>
    <?php endif; ?>

    <div style="margin-bottom: 25px;">
        <input type="text" id="buscadorProductos" placeholder="Buscar por Código, SKU o Descripción..." style="width: 100%; padding: 15px; border: 2px solid #2196f3; border-radius: 8px; font-size: 16px; outline: none;">
    </div>

    <div style="overflow-x: auto;">
        <table class="tabla-gestion" style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr>
                    <th>Código de Barras</th>
                    <th>SKU</th>
                    <th>Descripción</th>
                    <th>Marca</th>
                    <th>Categoría</th>
                    <th>Distribuidor</th>
                    <th>P. Venta</th>
                    <th style="text-align: center;">Acciones</th>
                </tr>
            </thead>
            <tbody id="cuerpoTabla">
                <?php foreach ($listaProductos as $p): ?>
                <tr class="fila-producto">
                    <td>
                        <a href="index.php?action=productos_editar&id=<?php echo $p['id']; ?>" class="link-editar">
                            <?php echo htmlspecialchars($p['codigo_barras']); ?>
                        </a>
                    </td>
                    <td><strong><?php echo htmlspecialchars($p['sku'] ?? '-'); ?></strong></td>
                    <td><strong><?php echo htmlspecialchars($p['descripcion']); ?></strong></td>
                    <td><?php echo htmlspecialchars($p['marca'] ?? '-'); ?></td>
                    <td><?php echo htmlspecialchars($p['categoria_nombre'] ?? '-'); ?></td>
                    <td><?php echo htmlspecialchars($p['distribuidor_nombre'] ?? '-'); ?></td>
                    <td><?php echo !empty($p['precio_venta']) ? '$' . number_format($p['precio_venta'], 2) : '-'; ?></td>
                    
                    <td style="text-align: center;">
                        <a href="index.php?action=productos_eliminar&id=<?php echo $p['id']; ?>" 
                           class="btn-eliminar" 
                           onclick="return confirm('⚠️ ¿Estás seguro de que deseas eliminar este producto?\n\nEsta acción no se puede deshacer.');" 
                           title="Eliminar">🗑️</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
// (Aquí mantené el mismo script del buscador en tiempo real que te pasé antes)
document.addEventListener('DOMContentLoaded', function() {
    const buscador = document.getElementById('buscadorProductos');
    const filas = document.querySelectorAll('.fila-producto');

    function filtrar() {
        const texto = buscador.value.toLowerCase().trim();
        filas.forEach((fila, index) => {
            if (texto === '') {
                fila.style.display = (index < 10) ? '' : 'none';
                return;
            }
            const contenido = fila.textContent.toLowerCase();
            fila.style.display = contenido.includes(texto) ? '' : 'none';
        });
    }
    
    // Iniciar con los primeros 10
    filtrar();
    buscador.addEventListener('keyup', filtrar);
});
</script>

</body>
</html>