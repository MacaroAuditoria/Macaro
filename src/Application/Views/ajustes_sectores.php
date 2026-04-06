<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Sectores - MACARO</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="dashboard-container">

<div class="header">
    <div class="user-info">🏷️ Gestión de Sectores</div>
    <a href="index.php?action=ajustes_menu" class="logout-btn" style="background:#666;">Volver a Ajustes</a>
</div>

<div class="container-abm">
    <div class="card-form">
        <h3>Nuevo Sector</h3>
        <form method="POST" action="index.php?action=ajustes_sectores">
            <div class="form-group">
                <label>Inventario (Local) al que pertenece:</label>
                <select name="local_id" required>
                    <option value="">-- Seleccionar Inventario --</option>
                    <?php foreach($listaLocales as $l): ?>
                        <option value="<?php echo $l['id']; ?>"><?php echo htmlspecialchars($l['nombre']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Nombre del Sector:</label>
                <input type="text" name="nombre" placeholder="Ej: Depósito Principal, Salón Comercial..." required>
            </div>
            
            <button type="submit" class="btn-primario">Guardar Sector</button>
        </form>
    </div>

    <div class="card-table">
        <h3>Sectores Registrados</h3>
        <table class="tabla-gestion">
            <thead>
                <tr>
                    <th>Inventario / Local</th>
                    <th>Nombre del Sector</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($listaSectores as $s): ?>
                <tr>
                    <td><span style="background: #e0f2f1; padding: 4px 8px; border-radius: 4px; font-size: 12px; color: #00695c; font-weight: bold;"><?php echo htmlspecialchars($s['local_nombre'] ?? 'Desconocido'); ?></span></td>
                    <td><strong><?php echo htmlspecialchars($s['nombre']); ?></strong></td>
                    <td>
                        <a href="index.php?action=editar_sector&id=<?php echo $s['id']; ?>" class="btn-edit">Editar</a>
                        <a href="index.php?action=eliminar_sector&id=<?php echo $s['id']; ?>" class="btn-delete" onclick="return confirm('¿Eliminar este sector?')">Borrar</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>