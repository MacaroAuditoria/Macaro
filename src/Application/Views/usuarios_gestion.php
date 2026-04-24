<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Usuarios - MACARO</title>
    <link rel="stylesheet" href="css/style.css">
    
    <style>
        /* Contenedor principal dividido en dos columnas (Formulario y Tabla) */
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
            border-color: #2196f3; 
            outline: none; 
            box-shadow: 0 0 5px rgba(33, 150, 243, 0.2);
        }

        /* Botón de Guardar gigante y moderno */
        .btn-primario { 
            width: 100%; 
            padding: 12px; 
            background: #2e7d32; 
            color: white; 
            border: none; 
            border-radius: 6px; 
            font-size: 16px; 
            font-weight: bold; 
            cursor: pointer; 
            transition: background 0.3s;
            margin-top: 10px;
        }
        .btn-primario:hover { background: #1b5e20; }

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

<div class="header">
    <div class="user-info">👥 Gestión de Usuarios y Permisos</div>
    <a href="index.php?action=dashboard" class="logout-btn" style="background:#666;">Volver al Inicio</a>
</div>

<div class="container-abm">
    
    <div class="card-form">
        <h3>✨ Alta de Nuevo Usuario</h3>
        
        <?php if(!empty($error)): ?>
            <div style="background: #ffebee; color: #c62828; padding: 10px; border-radius: 5px; margin-bottom: 15px; font-weight: bold; font-size: 14px; border-left: 4px solid #c62828;">
                ⚠️ <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="index.php?action=usuarios_gestion">
            <div class="form-group">
                <label>Nombre y Apellido (Visible):</label>
                <input type="text" name="nombre_completo" placeholder="Ej: Juan Pérez" required>
            </div>
            <div class="form-group">
                <label>Usuario (Para iniciar sesión):</label>
                <input type="text" name="usuario" placeholder="Ej: juan.perez" required>
            </div>
            <div class="form-group">
                <label>Contraseña:</label>
                <input type="password" name="password" placeholder="Mínimo 4 caracteres..." required>
            </div>
            <div class="form-group">
                <label>Rol / Nivel de Acceso:</label>
                <select name="rol_id" required>
                    <option value="">-- Seleccionar Rol --</option>
                    <?php foreach($listaRoles as $r): ?>
                        <option value="<?php echo $r['id']; ?>"><?php echo htmlspecialchars($r['nombre']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div style="background: #f9f9f9; padding: 15px; border-radius: 8px; margin-bottom: 15px; border: 1px dashed #ccc;">
                <p style="margin-top:0; font-size: 13px; color: #666; text-transform: uppercase; letter-spacing: 0.5px;"><strong>Datos de contacto (Opcionales)</strong></p>
                <div class="form-group">
                    <label>Teléfono / Celular:</label>
                    <input type="text" name="telefono" placeholder="Ej: 099 123 456">
                </div>
                <div class="form-group">
                    <label>Email:</label>
                    <input type="email" name="email" placeholder="ejemplo@correo.com">
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label>Fecha de Nacimiento:</label>
                    <input type="date" name="fecha_nacimiento">
                </div>
            </div>
            
            <button type="submit" class="btn-primario">Crear Usuario</button>
        </form>
    </div>

    <div class="card-table">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 15px; border-bottom: 2px solid #f0f0f0; padding-bottom: 15px;">
            <h3 style="margin: 0; color: #333; border: none; padding: 0;">📋 Usuarios Registrados</h3>
            
            <div style="display: flex; gap: 10px; align-items: center;">
                <input type="text" id="buscadorNombre" placeholder="🔍 Buscar por nombre..." style="padding: 10px 12px; border: 1px solid #ccc; border-radius: 6px; font-size: 14px; width: 220px; outline: none;">
                <select id="buscadorRol" style="padding: 10px 12px; border: 1px solid #ccc; border-radius: 6px; font-size: 14px; outline: none; background: white;">
                    <option value="">Todos los Roles</option>
                    <?php foreach($listaRoles as $r): ?>
                        <option value="<?php echo htmlspecialchars(strtolower($r['nombre'])); ?>"><?php echo htmlspecialchars($r['nombre']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        
        <table class="tabla-gestion">
            <thead>
                <tr>
                    <th>Nombre Completo</th>
                    <th>Usuario (Login)</th>
                    <th>Rol</th>
                    <th>Estado</th>
                    <th style="text-align: right;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($listaUsuarios as $u): ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($u['nombre_completo']); ?></strong></td>
                    <td><?php echo htmlspecialchars($u['usuario']); ?></td>
                    <td>
                        <span style="background: #e3f2fd; padding: 5px 10px; border-radius: 20px; font-size: 12px; color: #1565c0; font-weight: bold;">
                            <?php echo htmlspecialchars($u['rol_nombre']); ?>
                        </span>
                    </td>
                    <td>
                        <?php if($u['estado'] == 1): ?>
                            <span style="color: #2e7d32; font-weight: bold; font-size: 13px;">✔️ Activo</span>
                        <?php else: ?>
                            <span style="color: #c62828; font-weight: bold; font-size: 13px;">❌ Inactivo</span>
                        <?php endif; ?>
                    </td>
                    <td style="text-align: right;">
                        <a href="index.php?action=editar_usuario&id=<?php echo $u['id']; ?>" class="btn-edit">✏️ Editar</a>
                        <?php if($u['id'] != $_SESSION['usuario_id']): ?>
                            <a href="index.php?action=eliminar_usuario&id=<?php echo $u['id']; ?>" class="btn-delete" onclick="return confirm('¿Eliminar definitivamente este usuario?')">🗑️ Borrar</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
// (El script de búsqueda quedó intacto porque ya funcionaba perfecto)
document.addEventListener('DOMContentLoaded', function() {
    const inputBusqueda = document.getElementById('buscadorNombre');
    const selectRol = document.getElementById('buscadorRol');
    const filasTabla = document.querySelectorAll('.tabla-gestion tbody tr');

    function filtrarTabla() {
        const texto = inputBusqueda.value.toLowerCase().trim();
        const rolSeleccionado = selectRol.value.toLowerCase().trim();
        const estaVacio = (texto === "" && rolSeleccionado === "");

        filasTabla.forEach((fila, index) => {
            if(fila.cells.length < 3) return; 

            const nombre = fila.cells[0].textContent.toLowerCase();
            const rol = fila.cells[2].textContent.toLowerCase();

            const coincideTexto = nombre.includes(texto);
            const coincideRol = (rolSeleccionado === "" || rol.includes(rolSeleccionado));

            if (estaVacio) {
                if (index < 7) {
                    fila.style.display = '';
                } else {
                    fila.style.display = 'none';
                }
            } else {
                if (coincideTexto && coincideRol) {
                    fila.style.display = '';
                } else {
                    fila.style.display = 'none';
                }
            }
        });
    }

    filtrarTabla();
    inputBusqueda.addEventListener('keyup', filtrarTabla);
    selectRol.addEventListener('change', filtrarTabla);
});
</script>
</body>
</html>