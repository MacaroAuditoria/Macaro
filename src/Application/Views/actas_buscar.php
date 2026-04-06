<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Buscador de Actas - MACARO</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="dashboard-container">

<div class="header">
    <div class="user-info">🔍 Buscador de Actas Históricas</div>
    <a href="index.php?action=menu_graficos" class="logout-btn" style="background:#666;">Volver</a>
</div>

<div class="container-abm">
    <!-- FILTROS -->
    <div class="card-form" style="margin-bottom: 20px; background: #f8f9fa;">
        <form method="GET" action="index.php" style="display: flex; gap: 15px; align-items: flex-end;">
            <input type="hidden" name="action" value="actas_buscar">
            
            <div style="flex: 1;">
                <label>Filtrar por Local:</label>
                <select name="local_id">
                    <option value="">-- Todos los Locales --</option>
                    <?php foreach($locales as $l): ?>
                        <option value="<?php echo $l['id']; ?>" <?php echo ($filtro_local == $l['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($l['nombre']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="flex: 1;">
                <label>Filtrar por Encargado:</label>
                <select name="encargado_id">
                    <option value="">-- Todos los Encargados --</option>
                    <?php foreach($encargados as $e): ?>
                        <option value="<?php echo $e['id']; ?>" <?php echo ($filtro_encargado == $e['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($e['nombre_completo']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit" class="btn-primario" style="padding: 10px 20px;">Buscar</button>
            <a href="index.php?action=actas_buscar" class="btn-secundario" style="padding: 10px 20px; background:#ccc; color:black; text-decoration:none; border-radius:4px;">Limpiar</a>
        </form>
    </div>

    <!-- TABLA DE RESULTADOS -->
    <div class="card-table">
        <table class="tabla-gestion">
            <thead>
                <tr>
                    <th>Fecha de Cierre</th>
                    <th>Local</th>
                    <th>Encargado</th>
                    <th>Firmante (Cliente)</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($actas)): ?>
                    <tr><td colspan="5" style="text-align:center;">No se encontraron actas con esos filtros.</td></tr>
                <?php else: ?>
                    <?php foreach ($actas as $a): ?>
                    <tr>
                        <td><?php echo date('d/m/Y H:i', strtotime($a['fecha_cierre'])); ?></td>
                        <td><strong><?php echo htmlspecialchars($a['local_nombre']); ?></strong></td>
                        <td><?php echo htmlspecialchars($a['encargado_nombre']); ?></td>
                        <td><?php echo htmlspecialchars($a['nombre_evaluador']); ?></td>
                        <td>
                            <a href="index.php?action=acta_ver&id=<?php echo $a['id']; ?>" class="btn-edit" style="background:#00897b;">Ver Acta</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>