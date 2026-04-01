<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Marca - MACARO</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="dashboard-container">

<div class="header">
    <div class="user-info">✏️ Editar Marca</div>
    <div>
        <a href="?action=marcas" class="btn-primario" style="text-decoration: none; padding: 10px 15px; background: #6c757d;">Cancelar y Volver</a>
    </div>
</div>

<div style="background: var(--fondo-tarjetas); padding: 30px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); max-width: 600px; margin: 0 auto;">
    
    <?php if (isset($error)): ?>
        <p style="color: var(--color-peligro); background: #f8d7da; padding: 10px; border-radius: 5px;"><?php echo $error; ?></p>
    <?php endif; ?>

    <form method="POST" action="index.php?action=editar_marca">
        <input type="hidden" name="id" value="<?php echo $marcaActual['id']; ?>">
        
        <div style="margin-bottom: 20px;">
            <label style="font-weight: bold; margin-bottom: 5px; display: block;">Nombre de la Marca:</label>
            <input type="text" name="nombre" value="<?php echo htmlspecialchars($marcaActual['nombre']); ?>" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; font-size: 16px; box-sizing: border-box;">
        </div>
        
        <button type="submit" class="btn-primario">Actualizar Marca</button>
    </form>

</div>

</body>
</html>