<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Distribuidor - MACARO</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="dashboard-container">

<div class="header">
    <div class="user-info">✏️ Editar Distribuidor</div>
    <div>
        <a href="?action=distribuidores" class="btn-primario" style="text-decoration: none; padding: 10px 15px; background: #6c757d;">Cancelar</a>
    </div>
</div>

<div style="background: var(--fondo-tarjetas); padding: 30px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); max-width: 600px; margin: 0 auto;">
    
    <?php if (isset($error)): ?>
        <p style="color: var(--color-peligro); background: #f8d7da; padding: 10px; border-radius: 5px;"><?php echo $error; ?></p>
    <?php endif; ?>

    <form method="POST" action="index.php?action=editar_distribuidor">
        <input type="hidden" name="id" value="<?php echo $distActual['id']; ?>">
        
        <div style="margin-bottom: 20px;">
            <label style="font-weight: bold; margin-bottom: 5px; display: block;">Nombre del Distribuidor:</label>
            <input type="text" name="nombre" value="<?php echo htmlspecialchars($distActual['nombre']); ?>" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; font-size: 16px; box-sizing: border-box;">
        </div>
        
        <button type="submit" class="btn-primario">Actualizar Distribuidor</button>
    </form>

</div>

</body>
</html>