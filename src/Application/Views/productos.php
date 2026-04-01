<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Productos - MACARO</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="dashboard-container">

<div class="header">
    <div class="user-info">📦 Gestión de Productos (Catálogo Central)</div>
    <div>
        <a href="index.php?action=catalogo" class="btn-primario" style="text-decoration: none; padding: 10px 15px; background: #6c757d; margin-right: 10px;">Volver al Catálogo</a>
        <a href="?action=logout" class="logout-btn">Salir</a>
    </div>
</div>

<div style="background: var(--fondo-tarjetas); padding: 30px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); max-width: 900px; margin: 0 auto;">
    
    <h3 style="margin-top: 0;">Dar de Alta Nuevo Producto</h3>
    
    <?php if (isset($error)): ?>
        <p style="color: var(--color-peligro); background: #f8d7da; padding: 10px; border-radius: 5px;"><?php echo $error; ?></p>
    <?php endif; ?>
    <?php if (isset($exito)): ?>
        <p style="color: green; background: #d4edda; padding: 10px; border-radius: 5px;"><?php echo $exito; ?></p>
    <?php endif; ?>

    <form method="POST" action="index.php?action=productos" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 30px;">
        
        <div>
            <label style="font-weight: bold; font-size: 14px;">Código de Barras *</label>
            <input type="text" name="codigo_barras" required style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box;">
        </div>
        
        <div>
            <label style="font-weight: bold; font-size: 14px;">SKU (Interno - Opcional)</label>
            <input type="text" name="sku" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box;">
        </div>

        <div style="grid-column: span 2;">
            <label style="font-weight: bold; font-size: 14px;">Descripción del Producto *</label>
            <input type="text" name="descripcion" placeholder="Ej: Coca-Cola 600ml Normal" required style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box;">
        </div>

        <div>
            <label style="font-weight: bold; font-size: 14px;">Precio de Compra ($) *</label>
            <input type="number" step="0.01" name="precio_compra" required style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box;">
        </div>
        
        <div>
            <label style="font-weight: bold; font-size: 14px;">Precio de Venta ($) *</label>
            <input type="number" step="0.01" name="precio_venta" required style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box;">
        </div>

        <div>
            <label style="font-weight: bold; font-size: 14px;">Marca *</label>
            <select name="marca_id" required style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box;">
                <option value="">Seleccione...</option>
                <?php foreach ($listaMarcas as $m): ?>
                    <option value="<?php echo $m['id']; ?>"><?php echo htmlspecialchars($m['nombre']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label style="font-weight: bold; font-size: 14px;">Categoría *</label>
            <select name="categoria_id" required style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box;">
                <option value="">Seleccione...</option>
                <?php foreach ($listaCategorias as $c): ?>
                    <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['nombre']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div style="grid-column: span 2;">
            <label style="font-weight: bold; font-size: 14px;">Distribuidor *</label>
            <select name="distribuidor_id" required style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box;">
                <option value="">Seleccione...</option>
                <?php foreach ($listaDistribuidores as $d): ?>
                    <option value="<?php echo $d['id']; ?>"><?php echo htmlspecialchars($d['nombre']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div style="grid-column: span 2; text-align: right; margin-top: 10px;">
            <button type="submit" class="btn-primario" style="width: 200px;">💾 Guardar Producto</button>
        </div>
    </form>

    <hr style="border: 0; border-top: 1px solid #eee; margin-bottom: 20px;">

    <h3>Catálogo Actual</h3>
    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 14px;">
            <thead>
                <tr style="background-color: #f8f9fa; border-bottom: 2px solid #dee2e6;">
                    <th style="padding: 10px;">Código</th>
                    <th style="padding: 10px;">Descripción</th>
                    <th style="padding: 10px;">Marca</th>
                    <th style="padding: 10px;">Categoría</th>
                    <th style="padding: 10px;">Precio V.</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($listaProductos)): ?>
                    <tr><td colspan="5" style="padding: 10px; color: #666; text-align: center;">No hay productos en el catálogo.</td></tr>
                <?php else: ?>
                    <?php foreach ($listaProductos as $prod): ?>
                        <tr style="border-bottom: 1px solid #eee;">
                            <td style="padding: 10px;"><strong><?php echo htmlspecialchars($prod['codigo_barras']); ?></strong></td>
                            <td style="padding: 10px;"><?php echo htmlspecialchars($prod['descripcion']); ?></td>
                            <td style="padding: 10px;"><?php echo htmlspecialchars($prod['marca_nombre']); ?></td>
                            <td style="padding: 10px;"><?php echo htmlspecialchars($prod['categoria_nombre']); ?></td>
                            <td style="padding: 10px; color: green; font-weight: bold;">$<?php echo number_format($prod['precio_venta'], 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>

</body>
</html>