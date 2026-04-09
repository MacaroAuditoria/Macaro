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
        <div style="display: flex; justify-content: space-between; align-items: center; background: #f8f9fa; padding: 15px 20px; border-radius: 8px; margin-bottom: 20px; flex-wrap: wrap; gap: 15px;">
        <h3 style="margin: 0; color: #333; font-size: 18px;">📋 Sectores Registrados</h3>
        
        <div style="display: flex; gap: 10px;">
            <div style="position: relative;">
                <span style="position: absolute; left: 12px; top: 9px; color: #adb5bd;">🔍</span>
                <input type="text" id="buscadorSector" placeholder="Buscar sector..." style="padding: 8px 15px 8px 35px; border: 1px solid #ced4da; border-radius: 6px; font-size: 14px; width: 180px; outline: none;">
            </div>
            
            <select id="filtroLocal" style="padding: 8px 15px; border: 1px solid #ced4da; border-radius: 6px; font-size: 14px; outline: none; background: white; cursor: pointer;">
                <option value="">Todos los Inventarios</option>
                </select>
        </div>
    </div>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const inputBusqueda = document.getElementById('buscadorSector');
    const selectLocal = document.getElementById('filtroLocal');
    const filasTabla = document.querySelectorAll('.tabla-gestion tbody tr');

    // 1. Extraemos los locales de la Columna 0 (Inventario / Local)
    const localesUnicos = new Set();
    filasTabla.forEach(fila => {
        if(fila.cells.length > 1) {
            // ---> CORRECCIÓN ACÁ: El local está en la columna 0
            localesUnicos.add(fila.cells[0].textContent.trim());
        }
    });
    
    // Rellenamos el filtro desplegable
    [...localesUnicos].sort().forEach(local => {
        if(local) {
            const option = document.createElement('option');
            option.value = local.toLowerCase();
            option.textContent = local;
            selectLocal.appendChild(option);
        }
    });

    // 2. Lógica del filtro en vivo
    function filtrarTabla() {
        const texto = inputBusqueda.value.toLowerCase().trim();
        const localFiltro = selectLocal.value.toLowerCase().trim();
        const estaVacio = (texto === "" && localFiltro === "");

        filasTabla.forEach((fila, index) => {
            if(fila.cells.length < 2) return;

            // ---> CORRECCIÓN ACÁ: Apuntamos a las columnas correctas según tu HTML
            const nombreLocal = fila.cells[0].textContent.toLowerCase(); // Columna 0
            const nombreSector = fila.cells[1].textContent.toLowerCase(); // Columna 1

            const coincideTexto = nombreSector.includes(texto);
            const coincideLocal = (localFiltro === "" || nombreLocal === localFiltro);

            if (estaVacio) {
                // MODO REPOSO: Mostrar solo los últimos 7
                if (index < 7) {
                    fila.style.display = '';
                } else {
                    fila.style.display = 'none';
                }
            } else {
                // MODO BÚSQUEDA
                if (coincideTexto && coincideLocal) {
                    fila.style.display = '';
                } else {
                    fila.style.display = 'none';
                }
            }
        });
    }

    filtrarTabla();

    inputBusqueda.addEventListener('keyup', filtrarTabla);
    selectLocal.addEventListener('change', filtrarTabla);
});
</script>
</body>
</html>