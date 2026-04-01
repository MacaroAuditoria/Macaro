<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Marcas - MACARO</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="dashboard-container">

<div class="header">
    <div class="user-info">🏷️ Gestión de Marcas</div>
    <div>
        <a href="index.php?action=catalogo" class="btn-primario" style="text-decoration: none; padding: 10px 15px; background: #6c757d; margin-right: 10px;">Volver al Catálogo</a>
        <a href="?action=logout" class="logout-btn">Salir</a>
    </div>
</div>

<div style="background: var(--fondo-tarjetas); padding: 30px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); max-width: 600px; margin: 0 auto;">
    
    <h3 style="margin-top: 0;">Agregar Nueva Marca</h3>
    
    <?php if (isset($error)): ?>
        <p style="color: var(--color-peligro); background: #f8d7da; padding: 10px; border-radius: 5px;"><?php echo $error; ?></p>
    <?php endif; ?>

    <form method="POST" action="index.php?action=marcas" style="display: flex; gap: 10px; margin-bottom: 30px;">
        <input type="text" name="nueva_marca" placeholder="Ej: Coca-Cola" required style="flex: 1; padding: 10px; border: 1px solid #ccc; border-radius: 5px; font-size: 16px;">
        <button type="submit" class="btn-primario" style="width: auto;">Guardar Marca</button>
    </form>

    <hr style="border: 0; border-top: 1px solid #eee; margin-bottom: 20px;">

    <h3>Marcas Registradas</h3>
    <ul style="list-style: none; padding: 0;">
        <?php if (empty($listaMarcas)): ?>
            <li style="color: var(--color-texto-claro);">No hay marcas registradas todavía.</li>
        <?php else: ?>
            <?php foreach ($listaMarcas as $marca): ?>
                <li style="padding: 10px; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center;">
                    <strong><?php echo htmlspecialchars($marca['nombre']); ?></strong>
                    <div>
                        <span style="color: #ccc; margin-right: 15px; font-size: 14px;">ID: <?php echo $marca['id']; ?></span>
                        
                        <a href="?action=editar_marca&id=<?php echo $marca['id']; ?>" class="btn-primario" style="padding: 5px 10px; text-decoration: none; font-size: 14px; background-color: #ffc107; color: #000;">✏️</a>
                        
                        <a href="?action=eliminar_marca&id=<?php echo $marca['id']; ?>" class="btn-primario" style="padding: 5px 10px; text-decoration: none; font-size: 14px; background-color: var(--color-peligro); margin-left: 5px;" onclick="return confirm('¿Seguro que querés eliminar esta marca?');">🗑️</a>
                    </div>
                </li>
            <?php endforeach; ?>
        <?php endif; ?>
    </ul>

</div>

</body>
</html>