<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Visualizar - MACARO</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f6f9; margin: 0; padding: 0; }
        .app-header { background: #6200ea; color: white; padding: 15px; display: flex; justify-content: space-between; align-items: center; font-size: 18px; font-weight: bold; position: sticky; top: 0; }
        .back-btn { background: #fff; color: #6200ea; text-decoration: none; padding: 8px 15px; border-radius: 5px; font-size: 14px; }
        
        .list-container { padding: 10px; }
        .list-item { background: white; border-radius: 8px; padding: 15px; margin-bottom: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); border-left: 5px solid #6200ea; display: flex; justify-content: space-between; align-items: center; }
        .list-item.desconocido { border-left-color: #ff5252; }
        
        .item-info { flex: 1; }
        .item-code { font-size: 14px; color: #666; margin-bottom: 5px; font-weight: bold; }
        .item-desc { font-size: 16px; color: #333; margin-bottom: 5px; }
        .item-qty { font-size: 18px; font-weight: bold; color: #00bfa5; }
        
        .btn-delete { background: #ffebee; color: #d32f2f; border: 1px solid #ffcdd2; padding: 15px; font-size: 20px; border-radius: 8px; text-decoration: none; display: flex; align-items: center; justify-content: center; margin-left: 10px; }
        
        .empty-msg { text-align: center; color: #888; padding: 30px 20px; font-size: 16px; }
    </style>
</head>
<body>

<div class="app-header">
    <div>👁️ Zona: <?php echo htmlspecialchars($zona_nombre); ?></div>
    <a href="index.php?action=piqueo_escaner" class="back-btn">Volver al Escáner</a>
</div>

<div class="list-container">
    <?php if (empty($lista_escaneados)): ?>
        <div class="empty-msg">No has escaneado ningún producto en esta zona todavía.</div>
    <?php else: ?>
        
        <?php foreach ($lista_escaneados as $item): ?>
            <div class="list-item <?php echo empty($item['descripcion']) ? 'desconocido' : ''; ?>">
                <div class="item-info">
                    <div class="item-code">Cód: <?php echo htmlspecialchars($item['codigo_barras']); ?></div>
                    <div class="item-desc">
                        <?php echo !empty($item['descripcion']) ? htmlspecialchars($item['descripcion']) : '⚠️ PRODUCTO DESCONOCIDO'; ?>
                        <?php if(!empty($item['sku'])) echo " <small style='color:#999;'>(SKU: ".htmlspecialchars($item['sku']).")</small>"; ?>
                    </div>
                    <div class="item-qty">Cant: <?php echo number_format($item['cantidad'], 2, '.', ''); ?></div>
                </div>
                
                <a href="index.php?action=piqueo_borrar_conteo&id=<?php echo $item['id']; ?>" class="btn-delete" onclick="return confirm('¿Seguro que querés borrar este escaneo específico?');">
                    🗑️
                </a>
            </div>
        <?php endforeach; ?>

    <?php endif; ?>
</div>

</body>
</html>