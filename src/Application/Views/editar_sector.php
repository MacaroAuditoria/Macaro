<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Sector - MACARO</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="dashboard-container">

<div class="app-shell">
<?php $seccion_activa = 'ajustes_menu'; require __DIR__ . '/partials/sidebar.php'; ?>
<main class="main-content">

<div class="header">
    <div class="user-info">✏️ Editando Sector: <?php echo htmlspecialchars($sector['nombre']); ?></div>
    <a href="index.php?action=ajustes_sectores" class="logout-btn" style="background:#666;">Cancelar</a>
</div>

<div class="container-abm">
    <div class="card-form" style="max-width: 500px; margin: 0 auto;">
        <form method="POST" action="index.php?action=editar_sector">
<?php echo \App\Infrastructure\Security::campoCSRF(); ?>
            <input type="hidden" name="id" value="<?php echo $sector['id']; ?>">
            
            <div class="form-group">
                <label>Inventario (Local) al que pertenece:</label>
                <select name="local_id" required>
                    <?php foreach($listaLocales as $l): ?>
                        <option value="<?php echo $l['id']; ?>" <?php echo ($sector['local_id'] == $l['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($l['nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Nombre del Sector:</label>
                <input type="text" name="nombre" value="<?php echo htmlspecialchars($sector['nombre']); ?>" required>
            </div>
            
            <button type="submit" class="btn-primario">Actualizar Cambios</button>
        </form>
    </div>
</div>

</main>
</div>

</body>
</html>