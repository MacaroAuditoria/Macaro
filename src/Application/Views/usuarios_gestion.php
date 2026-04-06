<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Usuarios - MACARO</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="dashboard-container">

<div class="header">
    <div class="user-info">👥 Gestión de Usuarios y Permisos</div>
    <a href="index.php?action=dashboard" class="logout-btn" style="background:#666;">Volver al Inicio</a>
</div>

<div class="container-abm">
    <div class="card-form">
        <h3>Alta de Nuevo Usuario</h3>
        
        <?php if(!empty($error)): ?>
            <div style="background: #ffebee; color: #c62828; padding: 10px; border-radius: 5px; margin-bottom: 15px; font-weight: bold;">
                <?php echo $error; ?>
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
            <div style="background: #f9f9f9; padding: 15px; border-radius: 5px; margin-bottom: 15px; border: 1px dashed #ccc;">
    <p style="margin-top:0; font-size: 13px; color: #666;"><strong>Datos de contacto (Opcionales)</strong></p>
    <div class="form-group">
        <label>Teléfono / Celular:</label>
        <input type="text" name="telefono" placeholder="Ej: 099 123 456">
    </div>
    <div class="form-group">
        <label>Email:</label>
        <input type="email" name="email" placeholder="ejemplo@correo.com">
    </div>
    <div class="form-group">
        <label>Fecha de Nacimiento:</label>
        <input type="date" name="fecha_nacimiento">
    </div>
</div>
            
            
            <button type="submit" class="btn-primario">Crear Usuario</button>
        </form>
    </div>

    <div class="card-table">
        <h3>Usuarios en el Sistema</h3>
        <table class="tabla-gestion">
            <thead>
                <tr>
                    <th>Nombre Completo</th>
                    <th>Usuario (Login)</th>
                    <th>Rol</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($listaUsuarios as $u): ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($u['nombre_completo']); ?></strong></td>
                    <td><?php echo htmlspecialchars($u['usuario']); ?></td>
                    <td>
                        <span style="background: #e3f2fd; padding: 4px 8px; border-radius: 4px; font-size: 12px; color: #1565c0; font-weight: bold;">
                            <?php echo htmlspecialchars($u['rol_nombre']); ?>
                        </span>
                    </td>
                    <td>
                        <?php if($u['estado'] == 1): ?>
                            <span style="color: #2e7d32; font-weight: bold;">✔️ Activo</span>
                        <?php else: ?>
                            <span style="color: #c62828; font-weight: bold;">❌ Inactivo</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="index.php?action=editar_usuario&id=<?php echo $u['id']; ?>" class="btn-edit">Editar</a>
                        <?php if($u['id'] != $_SESSION['usuario_id']): ?>
                            <a href="index.php?action=eliminar_usuario&id=<?php echo $u['id']; ?>" class="btn-delete" onclick="return confirm('¿Eliminar definitivamente este usuario?')">Borrar</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>