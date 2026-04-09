<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Producto - MACARO</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .form-group { display: flex; flex-direction: column; }
        .form-group label { font-weight: bold; margin-bottom: 5px; color: #444; font-size: 14px; }
        .form-group input, .form-group select { padding: 10px; border: 1px solid #ccc; border-radius: 6px; font-size: 15px; }
        .full-width { grid-column: span 2; }
        @media (max-width: 600px) { .form-grid { grid-template-columns: 1fr; } .full-width { grid-column: span 1; } }
    </style>
</head>
<body class="dashboard-container">

<div class="header">
    <div class="user-info">✏️ Editando: <?php echo htmlspecialchars($p['descripcion']); ?></div>
    <div>
        <a href="index.php?action=productos_gestion" class="btn-primario" style="background: #6c757d; text-decoration:none;">Cancelar y Volver</a>
    </div>
</div>

<div style="background: white; padding: 30px; border-radius: 10px; max-width: 800px; margin: 30px auto; box-shadow: 0 4px 10px rgba(0,0,0,0.05);">
    
    <?php if (isset($error)) echo "<div style='background:#ffebee; color:#c62828; padding:15px; border-radius:6px; margin-bottom:20px;'>$error</div>"; ?>
    <?php if (isset($exito)) echo "<div style='background:#e8f5e9; color:#2e7d32; padding:15px; border-radius:6px; margin-bottom:20px;'>$exito</div>"; ?>
    
    <form method="POST" action="index.php?action=productos_editar&id=<?php echo $p['id']; ?>">
        
        <div class="form-grid">
            <div class="form-group full-width">
                <label>Descripción *</label>
                <input type="text" name="descripcion" required value="<?php echo htmlspecialchars($p['descripcion']); ?>" style="text-transform: uppercase;">
            </div>

            <div class="form-group">
                <label>Código de Barras *</label>
                <input type="text" name="codigo_barras" required value="<?php echo htmlspecialchars($p['codigo_barras']); ?>">
            </div>

            <div class="form-group">
                <label>SKU (Código Interno) *</label>
                <input type="text" name="sku" required value="<?php echo htmlspecialchars($p['sku']); ?>">
            </div>

            <div class="form-group">
                <label>Marca</label>
                <input type="text" name="marca" value="<?php echo htmlspecialchars($p['marca'] ?? ''); ?>" style="text-transform: uppercase;">
            </div>

            <div class="form-group">
                <label>Categoría</label>
                <select name="categoria_id">
                    <option value="">-- Sin categoría --</option>
                    <?php foreach ($listaCategorias as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>" <?php echo ($p['categoria_id'] == $cat['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Distribuidor</label>
                <select name="distribuidor_id">
                    <option value="">-- Sin distribuidor --</option>
                    <?php foreach ($listaDistribuidores as $dist): ?>
                        <option value="<?php echo $dist['id']; ?>" <?php echo ($p['distribuidor_id'] == $dist['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($dist['nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Precio de Compra ($)</label>
                <input type="number" step="0.01" name="precio_compra" value="<?php echo $p['precio_compra']; ?>">
            </div>

            <div class="form-group">
                <label>Precio de Venta ($)</label>
                <input type="number" step="0.01" name="precio_venta" value="<?php echo $p['precio_venta']; ?>">
            </div>
        </div>

        <div style="margin-top: 30px; text-align: right; border-top: 1px solid #eee; padding-top: 20px;">
            <button type="submit" class="btn-primario" style="background: #4caf50; font-size: 16px; padding: 12px 24px;">💾 Guardar Cambios</button>
        </div>
    </form>
</div>

</body>
</html>