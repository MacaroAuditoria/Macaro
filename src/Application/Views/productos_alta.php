<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Alta de Producto - MACARO</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        .form-group {
            display: flex;
            flex-direction: column;
        }
        .form-group label {
            font-weight: bold;
            margin-bottom: 5px;
            color: #444;
            font-size: 14px;
        }
        .form-group input, .form-group select {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 15px;
            background-color: #f8f9fa;
        }
        .form-group input:focus, .form-group select:focus {
            border-color: #2196f3;
            outline: none;
            background-color: #fff;
        }
        .full-width {
            grid-column: span 2;
        }
        /* Responsivo para celulares: se pone de a 1 columna */
        @media (max-width: 600px) {
            .form-grid { grid-template-columns: 1fr; }
            .full-width { grid-column: span 1; }
        }
    </style>
</head>
<body class="dashboard-container">

<div class="header">
    <div class="user-info">📦 Alta de Producto</div>
    <div>
        <a href="index.php?action=productos" class="btn-primario" style="background: #6c757d; text-decoration:none;">Volver al Catálogo</a>
    </div>
</div>

<div style="background: white; padding: 30px; border-radius: 10px; max-width: 800px; margin: 30px auto; box-shadow: 0 4px 10px rgba(0,0,0,0.05);">
    
    <h2 style="margin-top: 0; border-bottom: 2px solid #2196f3; padding-bottom: 10px; margin-bottom: 25px; color: #333;">Detalles del Producto</h2>

    <?php if (isset($error)) echo "<div style='background:#ffebee; color:#c62828; padding:15px; border-radius:6px; margin-bottom:20px; font-weight:bold;'>$error</div>"; ?>
    <?php if (isset($exito)) echo "<div style='background:#e8f5e9; color:#2e7d32; padding:15px; border-radius:6px; margin-bottom:20px; font-weight:bold;'>$exito</div>"; ?>
    
    <form method="POST" action="index.php?action=productos_alta">
        
        <div class="form-grid">
            <div class="form-group full-width">
                <label>Descripción *</label>
                <input type="text" name="descripcion" required placeholder="Ej: COCA COLA 1.5L" style="text-transform: uppercase;">
            </div>

            <div class="form-group">
                <label>Código de Barras *</label>
                <input type="text" name="codigo_barras" required autofocus placeholder="Ej: 7730000000000">
            </div>

            <div class="form-group">
                <label>SKU (Código Interno) *</label>
                <input type="text" name="sku" required placeholder="Ej: BEB-001">
            </div>

            <div class="form-group">
                <label>Marca</label>
                <input type="text" name="marca" placeholder="Ej: COCA COLA" style="text-transform: uppercase;">
            </div>

            <div class="form-group">
                <label>Categoría</label>
                <select name="categoria_id">
                    <option value="">-- Sin categoría --</option>
                    <?php foreach ($listaCategorias as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['nombre']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div style="margin-top: 30px; text-align: right; border-top: 1px solid #eee; padding-top: 20px;">
            <p style="text-align: left; color: #888; font-size: 13px; margin: 0 0 15px 0;">Los campos marcados con (*) son obligatorios.</p>
            <button type="submit" class="btn-primario" style="background: #2196f3; font-size: 16px; padding: 12px 24px;">💾 Crear Producto</button>
        </div>
    </form>
</div>

</body>
</html>