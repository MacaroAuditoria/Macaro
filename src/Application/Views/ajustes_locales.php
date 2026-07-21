<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Locales - MACARO</title>
    <link rel="stylesheet" href="css/style.css">
    
    <style>
        .container-abm {
            display: grid;
            grid-template-columns: 350px 1fr;
            gap: 25px;
            padding: 20px;
            max-width: 1400px;
            margin: 0 auto;
            align-items: start;
        }

        .card-form, .card-table {
            background: #ffffff;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
            border: 1px solid #eaeaea;
        }

        .card-form h3, .card-table h3 {
            margin-top: 0;
            margin-bottom: 20px;
            color: #333;
            font-size: 18px;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 10px;
        }

        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; font-weight: bold; margin-bottom: 5px; color: #555; font-size: 13px; }
        .form-group input, .form-group select { 
            width: 100%; padding: 10px 12px; border: 1px solid #ccc; border-radius: 6px; font-size: 14px; box-sizing: border-box; transition: border-color 0.3s;
        }
        .form-group input:focus, .form-group select:focus { border-color: #00897b; outline: none; box-shadow: 0 0 5px rgba(0, 137, 123, 0.2); }

        .btn-primario { 
            width: 100%; padding: 12px; background: #00897b; color: white; border: none; border-radius: 6px; font-size: 16px; font-weight: bold; cursor: pointer; transition: background 0.3s; margin-top: 10px;
        }
        .btn-primario:hover { background: #00695c; }

        .tabla-gestion { width: 100%; border-collapse: collapse; }
        .tabla-gestion th { background: #f8f9fa; padding: 12px; border-bottom: 2px solid #ccc; text-align: left; font-size: 14px; color: #444; }
        .tabla-gestion td { padding: 12px; border-bottom: 1px solid #eee; font-size: 14px; color: #333; }
        .tabla-gestion tr:hover { background-color: #f9f9f9; }

        .btn-edit { background: #2196f3; color: white; padding: 6px 12px; border-radius: 4px; text-decoration: none; font-size: 12px; font-weight: bold; transition: background 0.2s;}
        .btn-edit:hover { background: #1976d2; }
        .btn-delete { background: #d32f2f; color: white; padding: 6px 12px; border-radius: 4px; text-decoration: none; font-size: 12px; font-weight: bold; margin-left: 5px; transition: background 0.2s;}
        .btn-delete:hover { background: #c62828; }

        @media (max-width: 950px) { .container-abm { grid-template-columns: 1fr; } }
    </style>
</head>
<body class="dashboard-container">

<div class="app-shell">
<?php $seccion_activa = 'ajustes_menu'; require __DIR__ . '/partials/sidebar.php'; ?>
<main class="main-content">

<div class="header">
    <div class="user-info">🏢 Gestión de Inventarios (Locales) — Cliente: <?php echo htmlspecialchars($_SESSION['cliente_nombre'] ?? '-'); ?></div>
    <div>
        <a href="index.php?action=clientes_gestion" class="logout-btn" style="background:#ff9800; margin-right:8px;">Cambiar Cliente</a>
        <a href="index.php?action=ajustes_menu" class="logout-btn" style="background:#666;">Volver a Ajustes</a>
    </div>
</div>

<div class="container-abm">
    <div class="card-form">
        <h3>✨ Nuevo Local / Inventario</h3>
        
        <?php if (isset($_GET['error']) && $_GET['error'] === 'local_duplicado'): ?>
            <div id="alertaLocal" style="background-color: #ffebee; color: #c62828; padding: 12px; border-radius: 6px; margin-bottom: 15px; font-weight: bold; font-size: 13px; border-left: 4px solid #c62828;">
                ⚠️ Error: Ya existe un Inventario o Local con ese nombre exacto.
            </div>
            <script>
                setTimeout(function() { 
                    var alerta = document.getElementById('alertaLocal');
                    if (alerta) alerta.style.display = 'none'; 
                }, 5000);
            </script>
        <?php endif; ?>

        <form method="POST" action="index.php?action=ajustes_locales">
<?php echo \App\Infrastructure\Security::campoCSRF(); ?>
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
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 15px; border-bottom: 2px solid #f0f0f0; padding-bottom: 15px;">
            <h3 style="margin: 0; color: #333; border: none; padding: 0;">📋 Locales Registrados</h3>
            
            <div style="display: flex; gap: 10px; align-items: center;">
                <input type="text" id="buscadorLocal" placeholder="🔍 Buscar local..." style="padding: 10px 12px; border: 1px solid #ccc; border-radius: 6px; font-size: 14px; width: 220px; outline: none;">
                <select id="filtroEncargado" style="padding: 10px 12px; border: 1px solid #ccc; border-radius: 6px; font-size: 14px; outline: none; background: white; cursor: pointer;">
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
                    <th style="text-align: right;">Acciones</th>
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
                        $estado_actual = isset($l['estado']) ? $l['estado'] : 1; 
                        if($estado_actual == 1): 
                        ?>
                            <a href="index.php?action=toggle_estado_local&id=<?php echo $l['id']; ?>&st=1" style="text-decoration:none; background:#4caf50; color:white; padding:5px 12px; border-radius:20px; font-size:11px; font-weight:bold; display: inline-block; width: 60px; text-align: center;" onclick="return confirm('¿Desactivar este local? No aparecerá en los menús.')">ACTIVO</a>
                        <?php else: ?>
                            <a href="index.php?action=toggle_estado_local&id=<?php echo $l['id']; ?>&st=0" style="text-decoration:none; background:#f44336; color:white; padding:5px 12px; border-radius:20px; font-size:11px; font-weight:bold; display: inline-block; width: 60px; text-align: center;" onclick="return confirm('¿Volver a Activar este local?')">INACTIVO</a>
                        <?php endif; ?>
                    </td>
                    <td style="text-align: right;">
                        <a href="index.php?action=editar_local&id=<?php echo $l['id']; ?>" class="btn-edit">✏️ Editar</a>
                        <a href="index.php?action=eliminar_local&id=<?php echo $l['id']; ?>&csrf=<?php echo urlencode($_SESSION['csrf_token']); ?>" class="btn-delete" onclick="return confirm('¿Eliminar este local?')">🗑️ Borrar</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
// La lógica del buscador que funcionaba perfecto queda igual
document.addEventListener('DOMContentLoaded', function() {
    const inputBusqueda = document.getElementById('buscadorLocal');
    const selectEncargado = document.getElementById('filtroEncargado');
    const filasTabla = document.querySelectorAll('.tabla-gestion tbody tr');

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

    function filtrarTabla() {
        const texto = inputBusqueda.value.toLowerCase().trim();
        const encargadoFiltro = selectEncargado.value.toLowerCase().trim();
        const estaVacio = (texto === "" && encargadoFiltro === "");

        filasTabla.forEach((fila, index) => {
            if(fila.cells.length < 3) return;

            const nombreLocal = fila.cells[0].textContent.toLowerCase();
            const nombreEncargado = fila.cells[2].textContent.toLowerCase();

            const coincideTexto = nombreLocal.includes(texto);
            const coincideEncargado = (encargadoFiltro === "" || nombreEncargado === encargadoFiltro);

            if (estaVacio) {
                fila.style.display = (index < 7) ? '' : 'none';
            } else {
                fila.style.display = (coincideTexto && coincideEncargado) ? '' : 'none';
            }
        });
    }

    filtrarTabla();
    inputBusqueda.addEventListener('keyup', filtrarTabla);
    selectEncargado.addEventListener('change', filtrarTabla);
});
</script>

</main>
</div>

</body>
</html>