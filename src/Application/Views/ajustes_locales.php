<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Locales - MACARO</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="dashboard-container">

<div class="header">
    <div class="user-info">🏢 Gestión de Inventarios (Locales)</div>
    <a href="index.php?action=ajustes_menu" class="logout-btn" style="background:#666;">Volver a Ajustes</a>
</div>

<div class="container-abm">
    <div class="card-form">
        <h3>Nuevo Local / Inventario</h3>
        <form method="POST" action="index.php?action=ajustes_locales">
            <div class="form-group">
                <label>Nombre del Local:</label>
                <input type="text" name="nombre" placeholder="Ej: Kiosco Centro" required>
            </div>
            <div class="form-group">
                <label>Dirección:</label>
                <input type="text" name="direccion" placeholder="Ej: Av. 18 de Julio 1234">
            </div>
            <div class="form-group">
                <label>Encargado Responsable:</label>
                <select name="encargado_id">
                    <option value="">-- Seleccionar Encargado --</option>
                    <?php foreach($listaUsuarios as $u): ?>
                        <option value="<?php echo $u['id']; ?>"><?php echo htmlspecialchars($u['nombre_completo']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn-primario">Guardar Local</button>
        </form>
    </div>

    <div class="card-table">
        <h3>Locales Registrados</h3>
        <table class="tabla-gestion">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Dirección</th>
                    <th>Encargado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($listaLocales as $l): ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($l['nombre']); ?></strong></td>
                    <td><?php echo htmlspecialchars($l['direccion']); ?></td>
                    <td><?php echo htmlspecialchars($l['encargado_nombre'] ?? 'Sin asignar'); ?></td>
                    <td>
                        <a href="index.php?action=editar_local&id=<?php echo $l['id']; ?>" class="btn-edit">Editar</a>
                        <a href="index.php?action=eliminar_local&id=<?php echo $l['id']; ?>" class="btn-delete" onclick="return confirm('¿Eliminar este local?')">Borrar</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>