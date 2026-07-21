<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Sectores - MACARO</title>
    <link rel="stylesheet" href="css/style.css">
    
    <style>
        /* Contenedor principal dividido en dos columnas */
        .container-abm {
            display: grid;
            grid-template-columns: 350px 1fr; /* Izquierda 350px, Derecha el resto */
            gap: 25px;
            padding: 20px;
            max-width: 1400px;
            margin: 0 auto;
            align-items: start;
        }

        /* Estilo de Tarjetas blancas con sombra */
        .card-form, .card-table {
            background: #ffffff;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
            border: 1px solid #eaeaea;
        }

        /* Títulos de las tarjetas */
        .card-form h3, .card-table h3 {
            margin-top: 0;
            margin-bottom: 20px;
            color: #333;
            font-size: 18px;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 10px;
        }

        /* Estilos del formulario */
        .form-group { margin-bottom: 15px; }
        .form-group label { 
            display: block; 
            font-weight: bold; 
            margin-bottom: 5px; 
            color: #555; 
            font-size: 13px; 
        }
        .form-group input, .form-group select { 
            width: 100%; 
            padding: 10px 12px; 
            border: 1px solid #ccc; 
            border-radius: 6px; 
            font-size: 14px; 
            box-sizing: border-box; 
            transition: border-color 0.3s;
        }
        .form-group input:focus, .form-group select:focus { 
            border-color: #00897b; 
            outline: none; 
            box-shadow: 0 0 5px rgba(0, 137, 123, 0.2);
        }

        /* Botón de Guardar */
        .btn-primario { 
            width: 100%; 
            padding: 12px; 
            background: #00897b; 
            color: white; 
            border: none; 
            border-radius: 6px; 
            font-size: 16px; 
            font-weight: bold; 
            cursor: pointer; 
            transition: background 0.3s;
            margin-top: 10px;
        }
        .btn-primario:hover { background: #00695c; }

        /* Estética de la Tabla */
        .tabla-gestion { width: 100%; border-collapse: collapse; }
        .tabla-gestion th { 
            background: #f8f9fa; 
            padding: 12px; 
            border-bottom: 2px solid #ccc; 
            text-align: left; 
            font-size: 14px;
            color: #444;
        }
        .tabla-gestion td { 
            padding: 12px; 
            border-bottom: 1px solid #eee; 
            font-size: 14px;
            color: #333;
        }
        .tabla-gestion tr:hover { background-color: #f9f9f9; }

        /* Botones de acción en la tabla */
        .btn-edit { background: #2196f3; color: white; padding: 6px 12px; border-radius: 4px; text-decoration: none; font-size: 12px; font-weight: bold; transition: background 0.2s;}
        .btn-edit:hover { background: #1976d2; }
        
        .btn-delete { background: #d32f2f; color: white; padding: 6px 12px; border-radius: 4px; text-decoration: none; font-size: 12px; font-weight: bold; margin-left: 5px; transition: background 0.2s;}
        .btn-delete:hover { background: #c62828; }

        /* Responsive: Si la pantalla es chica, apilar uno arriba del otro */
        @media (max-width: 950px) {
            .container-abm { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body class="dashboard-container">

<div class="app-shell">
<?php $seccion_activa = 'ajustes_menu'; require __DIR__ . '/partials/sidebar.php'; ?>
<main class="main-content">

<div class="header">
    <div class="user-info">🏷️ Gestión de Sectores</div>
    <a href="index.php?action=ajustes_menu" class="logout-btn" style="background:#666;">Volver a Ajustes</a>
</div>

<div class="container-abm">
    
    <div class="card-form">
        <h3>✨ Nuevo Sector</h3>
        
        <?php if (isset($_GET['error']) && $_GET['error'] === 'sector_duplicado'): ?>
            <div id="alertaSector" style="background-color: #ffebee; color: #c62828; padding: 12px; border-radius: 6px; margin-bottom: 15px; font-weight: bold; font-size: 13px; border-left: 4px solid #c62828;">
                ⚠️ Error: Ya existe un sector con ese nombre en el inventario seleccionado.
            </div>
            <script>
                setTimeout(function() { 
                    var alerta = document.getElementById('alertaSector');
                    if (alerta) alerta.style.display = 'none'; 
                }, 5000);
            </script>
        <?php endif; ?>
        <form method="POST" action="index.php?action=ajustes_sectores">
<?php echo \App\Infrastructure\Security::campoCSRF(); ?>
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
                <input type="text" name="nombre" placeholder="Ej: Depósito Principal, Salón..." required>
            </div>
            
            <button type="submit" class="btn-primario">Guardar Sector</button>
        </form>
    </div>

    <div class="card-table">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 15px; border-bottom: 2px solid #f0f0f0; padding-bottom: 15px;">
            <h3 style="margin: 0; color: #333; border: none; padding: 0;">📋 Sectores Registrados</h3>
            
            <div style="display: flex; gap: 10px; align-items: center;">
                <input type="text" id="buscadorSector" placeholder="🔍 Buscar sector..." style="padding: 10px 12px; border: 1px solid #ccc; border-radius: 6px; font-size: 14px; width: 220px; outline: none;">
                <select id="filtroLocal" style="padding: 10px 12px; border: 1px solid #ccc; border-radius: 6px; font-size: 14px; outline: none; background: white; cursor: pointer;">
                    <option value="">Todos los Inventarios</option>
                </select>
            </div>
        </div>
        
        <table class="tabla-gestion">
            <thead>
                <tr>
                    <th>Inventario / Local</th>
                    <th>Nombre del Sector</th>
                    <th style="text-align: right;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($listaSectores as $s): ?>
                <tr>
                    <td><span style="background: #e0f2f1; padding: 5px 10px; border-radius: 20px; font-size: 12px; color: #00695c; font-weight: bold;"><?php echo htmlspecialchars($s['local_nombre'] ?? 'Desconocido'); ?></span></td>
                    <td><strong><?php echo htmlspecialchars($s['nombre']); ?></strong></td>
                    <td style="text-align: right;">
                        <a href="index.php?action=editar_sector&id=<?php echo $s['id']; ?>" class="btn-edit">✏️ Editar</a>
                        <a href="index.php?action=eliminar_sector&id=<?php echo $s['id']; ?>&csrf=<?php echo urlencode($_SESSION['csrf_token']); ?>" class="btn-delete" onclick="return confirm('¿Eliminar definitivamente este sector?')">🗑️ Borrar</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
// El script queda exactamente igual porque la lógica funciona perfecto
document.addEventListener('DOMContentLoaded', function() {
    const inputBusqueda = document.getElementById('buscadorSector');
    const selectLocal = document.getElementById('filtroLocal');
    const filasTabla = document.querySelectorAll('.tabla-gestion tbody tr');

    // 1. Extraemos los locales de la Columna 0 (Inventario / Local)
    const localesUnicos = new Set();
    filasTabla.forEach(fila => {
        if(fila.cells.length > 1) {
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

            const nombreLocal = fila.cells[0].textContent.toLowerCase(); // Columna 0
            const nombreSector = fila.cells[1].textContent.toLowerCase(); // Columna 1

            const coincideTexto = nombreSector.includes(texto);
            const coincideLocal = (localFiltro === "" || nombreLocal === localFiltro);

            if (estaVacio) {
                if (index < 7) {
                    fila.style.display = '';
                } else {
                    fila.style.display = 'none';
                }
            } else {
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
</main>
</div>

</body>
</html>