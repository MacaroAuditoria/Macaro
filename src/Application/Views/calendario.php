<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Calendario de Auditorías - MACARO</title>
    <link rel="stylesheet" href="css/style.css">
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js'></script>
    
    <style>
        #calendar { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); max-width: 900px; margin: 0 auto; }
        .fc-toolbar-title { color: #333; text-transform: capitalize; }
        .fc-daygrid-day { cursor: pointer; transition: background 0.2s; }
        .fc-daygrid-day:hover { background: #f0f8ff !important; }
        .fc-event { white-space: normal !important; padding: 4px !important; margin-bottom: 3px !important; cursor: pointer; }
        .fc-event-title { font-size: 12px !important; line-height: 1.3 !important; font-weight: bold !important; }
        
        .modal-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; }
        .modal-box { background: white; width: 400px; margin: 100px auto; padding: 20px; border-radius: 8px; box-shadow: 0 5px 15px rgba(0,0,0,0.3); }
        .modal-box h3 { margin-top: 0; border-bottom: 2px solid #673ab7; padding-bottom: 10px; color: #333; }
        
        .estado-badge { padding: 4px 8px; border-radius: 12px; font-size: 12px; font-weight: bold; color: white; }
        .bg-pendiente { background-color: #2196f3; }
        .bg-completada { background-color: #4caf50; }
        .bg-cancelada { background-color: #f44336; }

        /* Estética de la Tabla y Botones */
        .tabla-gestion th, .tabla-gestion td {
            padding: 12px 15px;
            vertical-align: middle;
        }
        .grupo-botones {
            display: flex;
            gap: 8px;
            align-items: center;
        }
        .btn-accion {
            border: none;
            padding: 6px 12px;
            border-radius: 4px;
            color: white !important;
            font-size: 13px;
            font-weight: bold;
            cursor: pointer;
            text-decoration: none !important;
            transition: opacity 0.2s;
        }
        .btn-accion:hover {
            opacity: 0.8;
        }
        
        /* Estilos del Buscador */
        .barra-busqueda {
            display: flex;
            gap: 15px;
            align-items: center;
        }
        .barra-busqueda input, .barra-busqueda select {
            padding: 8px 12px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 14px;
        }
        .barra-busqueda input {
            width: 250px;
        }
    </style>
</head>
<body class="dashboard-container">

<div class="app-shell">
<?php $seccion_activa = 'calendario'; require __DIR__ . '/partials/sidebar.php'; ?>
<main class="main-content">

<div class="header">
    <div class="user-info">📅 Calendario de Auditorías</div>
    <a href="index.php?action=ajustes_menu" class="logout-btn" style="background:#666;">Volver a Ajustes</a>
</div>

<div class="container-abm">
    
    <?php if (isset($error) && $error === 'auditoria_duplicada'): ?>
        <div id="alertaCalendario" style="background-color: #ffebee; color: #c62828; padding: 15px; border-radius: 8px; margin-bottom: 20px; font-weight: bold; font-size: 14px; border-left: 5px solid #c62828; max-width: 900px; margin-left: auto; margin-right: auto; text-align: center; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
            ⚠️ <strong>Acción bloqueada:</strong> El Inventario seleccionado ya tiene una auditoría "Pendiente". <br>
            <span style="font-weight: normal; font-size: 13px;">Debe marcarla como Completada o Cancelada antes de agendar una nueva para ese mismo local.</span>
        </div>
        <script>
            setTimeout(function() { 
                var alerta = document.getElementById('alertaCalendario');
                if (alerta) alerta.style.display = 'none'; 
            }, 6000); // Lo dejamos 6 segundos para que de tiempo a leerlo bien
        </script>
    <?php endif; ?>
    <div id='calendar'></div>

    <div class="card-table" style="margin-top: 40px; border-top: 4px solid #673ab7; padding: 20px;">
        
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 15px;">
            <h3>Cronograma: <span style="color: #2196f3;"><?php echo htmlspecialchars($titulo_periodo ?? ''); ?></span></h3>            
            <div class="barra-busqueda">
                <input type="text" id="buscadorTexto" placeholder="🔍 Buscar por local o encargado...">
                <select id="buscadorEstado">
                    <option value="">Todos los Estados</option>
                    <option value="Pendiente">Pendiente</option>
                    <option value="Completada">Completada</option>
                    <option value="Cancelada">Cancelada</option>
                </select>
            </div>
        </div>

        <table class="tabla-gestion" id="tablaAuditorias">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Hora</th>
                    <th>Local</th>
                    <th>Encargado Asignado</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($eventos_db as $e): ?>
                <tr>
                    <td>
                        <?php echo date('d/m/Y', strtotime($e['fecha_auditoria'])); ?>
                        <?php 
                        // Solo agregamos el cartelito si está atrasada
                        if ($e['fecha_auditoria'] < date('Y-m-d') && $e['estado'] === 'Pendiente'): 
                        ?>
                            <br><span style="color: #f44336; font-size: 10px; font-weight: bold;">⚠️ ATRASADA</span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo date('H:i', strtotime($e['hora_auditoria'])); ?></td>
                    <td class="col-local"><?php echo htmlspecialchars($e['local_nombre']); ?></td>
                    <td class="col-encargado"><?php echo htmlspecialchars($e['encargado_nombre']); ?></td>
                    <td class="col-estado">
                        <span class="estado-badge bg-<?php echo strtolower($e['estado']); ?>">
                            <?php echo htmlspecialchars($e['estado']); ?>
                        </span>
                    </td>
                    <td>
                        <div class="grupo-botones">
                            <button onclick="abrirEditar(<?php echo $e['id']; ?>, <?php echo $e['local_id']; ?>, <?php echo $e['encargado_id']; ?>, '<?php echo $e['fecha_auditoria']; ?>', '<?php echo $e['hora_auditoria']; ?>', '<?php echo $e['estado']; ?>')" class="btn-accion" style="background:#ff9800;">✏️ Editar</button>
                            
                            <?php if($e['estado'] !== 'Cancelada'): ?>
                                <a href="index.php?action=cancelar_auditoria&id=<?php echo $e['id']; ?>" class="btn-accion" style="background:#f44336;" onclick="return confirm('¿Seguro que desea marcar esta auditoría como Cancelada?');">❌ Cancelar</a>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                
                <?php if(empty($eventos_db)): ?>
                    <tr id="filaVacia"><td colspan="6" style="text-align:center; padding: 20px;">No hay auditorías registradas en este período.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>

<div class="modal-overlay" id="modalAgendar">
    <div class="modal-box">
        <h3>Agendar Nueva Auditoría</h3>
        <form method="POST" action="index.php?action=guardar_auditoria">
<?php echo \App\Infrastructure\Security::campoCSRF(); ?>
            <input type="hidden" name="fecha" id="fecha_seleccionada">
            <p style="margin-bottom: 15px; color: #666;"><strong>Día seleccionado:</strong> <span id="mostrar_fecha"></span></p>

            <div class="form-group" style="margin-bottom: 15px;">
                <label style="font-weight: bold; display: block; margin-bottom: 5px;">Local:</label>
                <select name="local_id" required style="width: 100%; padding: 8px;">
                    <option value="">-- Elegir Local --</option>
                    <?php foreach($locales as $l): ?>
                        <option value="<?php echo $l['id']; ?>"><?php echo htmlspecialchars($l['nombre']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group" style="margin-bottom: 15px;">
                <label style="font-weight: bold; display: block; margin-bottom: 5px;">Encargado:</label>
                <select name="encargado_id" required style="width: 100%; padding: 8px;">
                    <option value="">-- Elegir Encargado --</option>
                    <?php foreach($encargados as $e): ?>
                        <option value="<?php echo $e['id']; ?>"><?php echo htmlspecialchars($e['nombre_completo']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group" style="margin-bottom: 20px;">
                <label style="font-weight: bold; display: block; margin-bottom: 5px;">Hora:</label>
                <input type="time" name="hora" required style="width: 100%; padding: 8px;">
            </div>

            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                <button type="button" class="btn-secundario" style="background: #999; color: white; border: none; padding: 10px 15px; border-radius: 4px; cursor: pointer;" onclick="cerrarModal('modalAgendar')">Cancelar</button>
                <button type="submit" class="btn-primario" style="background: #673ab7; color: white; border: none; padding: 10px 15px; border-radius: 4px; cursor: pointer;">Guardar</button>
            </div>
        </form>
    </div>
</div>

<div class="modal-overlay" id="modalEditar">
    <div class="modal-box" style="border-top: 4px solid #ff9800;">
        <h3>✏️ Editar Auditoría</h3>
        <form method="POST" action="index.php?action=actualizar_auditoria">
<?php echo \App\Infrastructure\Security::campoCSRF(); ?>
            <input type="hidden" name="auditoria_id" id="edit_id">
            
            <div class="form-group" style="margin-bottom: 15px;">
                <label style="font-weight: bold; display: block; margin-bottom: 5px;">Cambiar Fecha:</label>
                <input type="date" name="fecha" id="edit_fecha" required style="width: 100%; padding: 8px;">
            </div>

            <div class="form-group" style="margin-bottom: 15px;">
                <label style="font-weight: bold; display: block; margin-bottom: 5px;">Cambiar Hora:</label>
                <input type="time" name="hora" id="edit_hora" required style="width: 100%; padding: 8px;">
            </div>

            <div class="form-group" style="margin-bottom: 15px;">
                <label style="font-weight: bold; display: block; margin-bottom: 5px;">Local:</label>
                <select name="local_id" id="edit_local" required style="width: 100%; padding: 8px;">
                    <?php foreach($locales as $l): ?>
                        <option value="<?php echo $l['id']; ?>"><?php echo htmlspecialchars($l['nombre']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group" style="margin-bottom: 15px;">
                <label style="font-weight: bold; display: block; margin-bottom: 5px;">Encargado:</label>
                <select name="encargado_id" id="edit_encargado" required style="width: 100%; padding: 8px;">
                    <?php foreach($encargados as $e): ?>
                        <option value="<?php echo $e['id']; ?>"><?php echo htmlspecialchars($e['nombre_completo']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group" style="margin-bottom: 20px;">
                <label style="font-weight: bold; display: block; margin-bottom: 5px;">Estado de la Auditoría:</label>
                <select name="estado" id="edit_estado" required style="width: 100%; padding: 8px;">
                    <option value="Pendiente">Pendiente</option>
                    <option value="Completada">Completada</option>
                    <option value="Cancelada">Cancelada</option>
                </select>
            </div>

            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                <button type="button" class="btn-secundario" style="background: #999; color: white; border: none; padding: 10px 15px; border-radius: 4px; cursor: pointer;" onclick="cerrarModal('modalEditar')">Cancelar</button>
                <button type="submit" class="btn-primario" style="background: #ff9800; color: white; border: none; padding: 10px 15px; border-radius: 4px; cursor: pointer;">Actualizar</button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // --- INICIALIZACIÓN DEL CALENDARIO ---
        var calendarEl = document.getElementById('calendar');
        var eventosDesdePHP = <?php echo json_encode($eventos_js); ?>;

        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            locale: 'es',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek' 
            },
            dayMaxEvents: 2, 
            events: eventosDesdePHP,
            
            dateClick: function(info) {
                document.getElementById('fecha_seleccionada').value = info.dateStr;
                var partes = info.dateStr.split('-');
                document.getElementById('mostrar_fecha').innerText = partes[2] + '-' + partes[1] + '-' + partes[0];
                document.getElementById('modalAgendar').style.display = 'block';
            }
        });
        calendar.render();

        const params = new URLSearchParams(window.location.search);
        if (params.get('res') === 'creado') alert("✅ Auditoría agendada.");
        if (params.get('res') === 'editado') alert("✏️ Auditoría actualizada.");
        if (params.get('res') === 'cancelado') alert("❌ Auditoría cancelada.");

        // Limpiar URL si hay errores para que al refrescar no vuelva a saltar el cartel
        if (params.has('error')) {
            const nuevaUrl = window.location.protocol + "//" + window.location.host + window.location.pathname + "?action=calendario";
            window.history.replaceState({path: nuevaUrl}, '', nuevaUrl);
        }

        // --- LÓGICA DEL BUSCADOR EN VIVO ---
        const inputBusqueda = document.getElementById('buscadorTexto');
        const selectEstado = document.getElementById('buscadorEstado');
        const filasTabla = document.querySelectorAll('#tablaAuditorias tbody tr');

        function filtrarTabla() {
            const texto = inputBusqueda.value.toLowerCase();
            const estado = selectEstado.value.toLowerCase();

            filasTabla.forEach(fila => {
                if (fila.id === 'filaVacia') return;

                const nombreLocal = fila.querySelector('.col-local').textContent.toLowerCase();
                const nombreEncargado = fila.querySelector('.col-encargado').textContent.toLowerCase();
                const estadoLocal = fila.querySelector('.col-estado').textContent.toLowerCase().trim();

                const coincideTexto = nombreLocal.includes(texto) || nombreEncargado.includes(texto);
                const coincideEstado = (estado === "" || estadoLocal === estado);

                if (coincideTexto && coincideEstado) {
                    fila.style.display = '';
                } else {
                    fila.style.display = 'none';
                }
            });
        }

        inputBusqueda.addEventListener('keyup', filtrarTabla);
        selectEstado.addEventListener('change', filtrarTabla);
    });

    // --- FUNCIONES DE VENTANAS ---
    function cerrarModal(idModal) {
        document.getElementById(idModal).style.display = 'none';
    }

    function abrirEditar(id, local_id, encargado_id, fecha, hora, estado) {
        document.getElementById('edit_id').value = id;
        document.getElementById('edit_local').value = local_id;
        document.getElementById('edit_encargado').value = encargado_id;
        document.getElementById('edit_fecha').value = fecha;
        document.getElementById('edit_hora').value = hora;
        document.getElementById('edit_estado').value = estado;
        document.getElementById('modalEditar').style.display = 'block';
    }
</script>

</main>
</div>

</body>
</html>