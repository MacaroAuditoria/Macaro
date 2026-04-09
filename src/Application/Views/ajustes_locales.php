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
        
        <div style="display: flex; justify-content: space-between; align-items: center; background: #f8f9fa; padding: 15px 20px; border-radius: 8px; margin-bottom: 20px; flex-wrap: wrap; gap: 15px;">
            <h3 style="margin: 0; color: #333; font-size: 18px;">📋 Locales Registrados</h3>
            
            <div style="display: flex; gap: 10px;">
                <div style="position: relative;">
                    <span style="position: absolute; left: 12px; top: 9px; color: #adb5bd;">🔍</span>
                    <input type="text" id="buscadorLocal" placeholder="Buscar local..." style="padding: 8px 15px 8px 35px; border: 1px solid #ced4da; border-radius: 6px; font-size: 14px; width: 180px; outline: none;">
                </div>
                
                <select id="filtroEncargado" style="padding: 8px 15px; border: 1px solid #ced4da; border-radius: 6px; font-size: 14px; outline: none; background: white; cursor: pointer;">
                    <option value="">Todos los Encargados</option>
                    </select>
            </div>
        </div>

        <table class="tabla-gestion">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Dirección</th>
                    <th>Encargado</th>
                    <th style="text-align: center;">Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($listaLocales as $l): ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($l['nombre']); ?></strong></td>
                    
                    <td><?php echo htmlspecialchars($l['direccion']); ?></td>
                    
                    <td><span style="background: #e3f2fd; padding: 4px 8px; border-radius: 4px; font-size: 12px; color: #1565c0; font-weight: bold;"><?php echo htmlspecialchars($l['encargado_nombre'] ?? 'Sin asignar'); ?></span></td>
                    
                    <td style="text-align: center;">
                        <?php 
                        // Verificamos si la variable estado existe, por defecto asumimos 1 (Activo)
                        $estado_actual = isset($l['estado']) ? $l['estado'] : 1; 
                        if($estado_actual == 1): 
                        ?>
                            <a href="index.php?action=toggle_estado_local&id=<?php echo $l['id']; ?>&st=1" style="text-decoration:none; background:#4caf50; color:white; padding:5px 12px; border-radius:20px; font-size:11px; font-weight:bold; display: inline-block; width: 60px; text-align: center;" onclick="return confirm('¿Desactivar este local? No aparecerá en los menús.')">ACTIVO</a>
                        <?php else: ?>
                            <a href="index.php?action=toggle_estado_local&id=<?php echo $l['id']; ?>&st=0" style="text-decoration:none; background:#f44336; color:white; padding:5px 12px; border-radius:20px; font-size:11px; font-weight:bold; display: inline-block; width: 60px; text-align: center;" onclick="return confirm('¿Volver a Activar este local?')">INACTIVO</a>
                        <?php endif; ?>
                    </td>

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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const inputBusqueda = document.getElementById('buscadorLocal');
    const selectEncargado = document.getElementById('filtroEncargado');
    const filasTabla = document.querySelectorAll('.tabla-gestion tbody tr');

    // 1. Extraemos los encargados de la tabla para llenar el ComboBox (Columna 2)
    const encargadosUnicos = new Set();
    filasTabla.forEach(fila => {
        if(fila.cells.length > 2) {
            const encargado = fila.cells[2].textContent.trim();
            if(encargado !== 'Sin asignar' && encargado !== '') {
                encargadosUnicos.add(encargado);
            }
        }
    });
    
    [...encargadosUnicos].sort().forEach(encargado => {
        const option = document.createElement('option');
        option.value = encargado.toLowerCase();
        option.textContent = encargado;
        selectEncargado.appendChild(option);
    });

    // 2. Lógica del Filtro y Límite de 7
    function filtrarTabla() {
        const texto = inputBusqueda.value.toLowerCase().trim();
        const encargadoFiltro = selectEncargado.value.toLowerCase().trim();
        const estaVacio = (texto === "" && encargadoFiltro === "");

        filasTabla.forEach((fila, index) => {
            if(fila.cells.length < 3) return;

            // Columna 0: Nombre del Local | Columna 2: Encargado
            const nombreLocal = fila.cells[0].textContent.toLowerCase();
            const nombreEncargado = fila.cells[2].textContent.toLowerCase();

            const coincideTexto = nombreLocal.includes(texto);
            const coincideEncargado = (encargadoFiltro === "" || nombreEncargado === encargadoFiltro);

            if (estaVacio) {
                // Mostrar solo los últimos 7
                fila.style.display = (index < 7) ? '' : 'none';
            } else {
                // Mostrar si coinciden las búsquedas
                fila.style.display = (coincideTexto && coincideEncargado) ? '' : 'none';
            }
        });
    }

    filtrarTabla();

    inputBusqueda.addEventListener('keyup', filtrarTabla);
    selectEncargado.addEventListener('change', filtrarTabla);
});
</script>

</body>
</html>