<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Local - MACARO</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="dashboard-container">

<div class="header">
    <div class="user-info">✏️ Editando: <?php echo htmlspecialchars($local['nombre']); ?></div>
    <a href="index.php?action=ajustes_locales" class="logout-btn" style="background:#666;">Cancelar</a>
</div>

<div class="container-abm">
    <div class="card-form" style="max-width: 500px; margin: 0 auto;">
        <form method="POST" action="index.php?action=editar_local">
            <input type="hidden" name="id" value="<?php echo $local['id']; ?>">
            
            <div class="form-group">
                <label>Nombre del Local:</label>
                <input type="text" name="nombre" value="<?php echo htmlspecialchars($local['nombre']); ?>" required>
            </div>
            <div class="form-group">
                <label>Dirección:</label>
                <input type="text" name="direccion" value="<?php echo htmlspecialchars($local['direccion']); ?>">
            </div>
            <div class="form-group">
                <label>Encargado Responsable:</label>
                <select name="encargado_id">
                    <option value="">-- Sin asignar --</option>
                    <?php foreach($listaUsuarios as $u): ?>
                        <option value="<?php echo $u['id']; ?>" <?php echo ($local['encargado_id'] == $u['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($u['nombre_completo']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn-primario">Actualizar Cambios</button>
        </form>
    </div>
</div>

</body>
</html>