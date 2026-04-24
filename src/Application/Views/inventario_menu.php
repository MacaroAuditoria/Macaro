<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cierre de Inventario - MACARO</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .caja-exportacion { background: white; padding: 40px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); max-width: 600px; margin: 40px auto; text-align: center; }
        .select-gigante { width: 100%; padding: 15px; font-size: 18px; border: 2px solid #ccc; border-radius: 8px; margin-bottom: 20px; box-sizing: border-box; }
        .btn-excel { background: #1b5e20; color: white; border: none; padding: 15px 30px; font-size: 18px; font-weight: bold; border-radius: 8px; cursor: pointer; display: flex; align-items: center; justify-content: center; width: 100%; transition: background 0.3s; box-sizing: border-box;}
        .btn-excel:hover { background: #2e7d32; }
    </style>
</head>
<body class="dashboard-container">

<div class="header">
    <div class="user-info">📥 Cierre y Exportación de Inventarios</div>
    <a href="index.php?action=dashboard" class="logout-btn" style="background:#666;">Volver</a>
</div>

<div class="caja-exportacion">
    <h2 style="margin-top: 0; color: #004d40;">Exportar a Excel (CSV)</h2>
    <p style="color: #666; margin-bottom: 30px;">Seleccione el inventario y el tipo de reporte que desea generar.</p>

    <?php if (empty($locales_activos)): ?>
        <div style="background: #fff3cd; color: #856404; padding: 15px; border-radius: 5px;">
            Aún no hay ningún inventario con productos escaneados.
        </div>
    <?php else: ?>
        <form method="POST" action="index.php">
            <input type="hidden" name="action" value="inventario_exportar">
            
            <label style="display:block; text-align:left; font-weight:bold; color:#333; margin-bottom:5px;">1. Seleccionar Local</label>
            <select name="local_id" class="select-gigante" required>
                <option value="">Seleccione el Inventario...</option>
                <?php foreach($locales_activos as $l): ?>
                    <option value="<?php echo $l['id']; ?>"><?php echo htmlspecialchars($l['nombre']); ?></option>
                <?php endforeach; ?>
            </select>

            <label style="display:block; text-align:left; font-weight:bold; color:#333; margin-bottom:5px;">2. Tipo de Reporte</label>
            <select name="tipo_reporte" class="select-gigante" required>
                <option value="detallado">📊 Detallado (Separado por Sector y Zona)</option>
                <option value="unificado">📦 Unificado (Suma Total del Local, sin Zonas)</option>
                <option value="datos">⚙️ Datos Crudos (Para Importar a otros Sistemas)</option>
            </select>
            
            <button type="submit" class="btn-excel">
                <span style="font-size: 24px; margin-right: 10px;">📥</span> Descargar Archivo CSV
            </button>
        </form>
    <?php endif; ?>
</div>

</body>
</html>