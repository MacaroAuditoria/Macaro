<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Distribuidores - MACARO</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="dashboard-container">

<div class="header">
    <div class="user-info">🚚 Gestión de Distribuidores</div>
    <div>
        <a href="index.php?action=catalogo" class="btn-primario" style="text-decoration: none; padding: 10px 15px; background: #6c757d; margin-right: 10px;">Volver al Catálogo</a>
        <a href="?action=logout" class="logout-btn">Salir</a>
    </div>
</div>

<div style="background: var(--fondo-tarjetas); padding: 30px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); max-width: 600px; margin: 0 auto;">
    
    <h3 style="margin-top: 0;">Agregar Nuevo Distribuidor</h3>
    
    <?php if (isset($error)): ?>
        <p style="color: var(--color-peligro); background: #f8d7da; padding: 10px; border-radius: 5px;"><?php echo $error; ?></p>
    <?php endif; ?>

    <form method="POST" action="index.php?action=distribuidores" style="display: flex; gap: 10px; margin-bottom: 30px;">
        <input type="text" name="nuevo_distribuidor" placeholder="Ej: Almena S.A." required style="flex: 1; padding: 10px; border: 1px solid #ccc; border-radius: 5px; font-size: 16px;">
        <button type="submit" class="btn-primario" style="width: auto;">Guardar</button>
    </form>

    <hr style="border: 0; border-top: 1px solid #eee; margin-bottom: 20px;">

    <h3>Distribuidores Registrados</h3>
    <ul style="list-style: none; padding: 0;">
        <?php if (empty($listaDistribuidores)): ?>
            <li style="color: var(--color-texto-claro);">No hay distribuidores registrados.</li>
        <?php else: ?>
            <?php foreach ($listaDistribuidores as $dist): ?>
                <li style="padding: 10px; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center;">
                    <strong><?php echo htmlspecialchars($dist['nombre']); ?></strong>
                    <div>
                        <span style="color: #ccc; margin-right: 15px; font-size: 14px;">ID: <?php echo $dist['id']; ?></span>
                        
                        <a href="?action=editar_distribuidor&id=<?php echo $dist['id']; ?>" class="btn-primario" style="padding: 5px 10px; text-decoration: none; font-size: 14px; background-color: #ffc107; color: #000;">✏️</a>
                        
                        <a href="?action=eliminar_distribuidor&id=<?php echo $dist['id']; ?>" class="btn-primario" style="padding: 5px 10px; text-decoration: none; font-size: 14px; background-color: var(--color-peligro); margin-left: 5px;" onclick="return confirm('¿Seguro que querés eliminar este distribuidor?');">🗑️</a>
                    </div>
                </li>
            <?php endforeach; ?>
        <?php endif; ?>
    </ul>
</div>

</body>
</html>