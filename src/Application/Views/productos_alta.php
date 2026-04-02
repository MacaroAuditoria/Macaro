<!DOCTYPE html>
<html lang="es"><head><meta charset="UTF-8"><title>Alta Rápida - MACARO</title><link rel="stylesheet" href="css/style.css"></head>
<body class="dashboard-container">
<div class="header"><div class="user-info">➕ Alta Rápida de Producto</div>
<div><a href="index.php?action=productos" class="btn-primario" style="background: #6c757d; text-decoration:none;">Volver</a></div></div>
<div style="background: white; padding: 30px; border-radius: 10px; max-width: 600px; margin: 0 auto;">
    <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
    <?php if (isset($exito)) echo "<p style='color:green;'>$exito</p>"; ?>
    <form method="POST" action="index.php?action=productos_alta">
        <label>Código de Barras *</label>
        <input type="text" name="codigo_barras" required autofocus style="width: 100%; padding: 10px; margin-bottom: 15px;">
        <label>Descripción *</label>
        <input type="text" name="descripcion" required style="width: 100%; padding: 10px; margin-bottom: 15px;">
        <button type="submit" class="btn-primario">Guardar y Escanear Siguiente</button>
    </form>
</div>
</body></html>