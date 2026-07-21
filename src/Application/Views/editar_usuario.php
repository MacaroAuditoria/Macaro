<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Usuario - MACARO</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="dashboard-container">

<div class="app-shell">
<?php $seccion_activa = 'usuarios_gestion'; require __DIR__ . '/partials/sidebar.php'; ?>
<main class="main-content">

<div class="header">
    <div class="user-info">✏️ Editando a: <?php echo htmlspecialchars($usuario_editar['nombre_completo']); ?></div>
    <a href="index.php?action=usuarios_gestion" class="logout-btn" style="background:#666;">Cancelar</a>
</div>

<div class="container-abm">
    <div class="card-form" style="max-width: 500px; margin: 0 auto;">
        
        <?php if(!empty($error)): ?>
            <div style="background: #ffebee; color: #c62828; padding: 10px; border-radius: 5px; margin-bottom: 15px; font-weight: bold;">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="index.php?action=editar_usuario">
<?php echo \App\Infrastructure\Security::campoCSRF(); ?>
            <input type="hidden" name="id" value="<?php echo $usuario_editar['id']; ?>">
            
            <div class="form-group">
                <label>Nombre y Apellido:</label>
                <input type="text" name="nombre_completo" value="<?php echo htmlspecialchars($usuario_editar['nombre_completo']); ?>" required>
            </div>
            
            <div class="form-group">
                <label>Usuario (Login):</label>
                <input type="text" name="usuario" value="<?php echo htmlspecialchars($usuario_editar['usuario']); ?>" required>
            </div>

            <div class="form-group">
                <label>Nueva Contraseña:</label>
                <input type="password" name="password" placeholder="Dejar en blanco para mantener la actual...">
                <small style="color: #666; display: block; margin-top: 5px;">Si no querés cambiar la clave, dejá este campo vacío.</small>
            </div>
            <div class="form-group">
    <label>Teléfono:</label>
    <input type="text" name="telefono" value="<?php echo htmlspecialchars($usuario_editar['telefono'] ?? ''); ?>">
</div>
<div class="form-group">
    <label>Email:</label>
    <input type="email" name="email" value="<?php echo htmlspecialchars($usuario_editar['email'] ?? ''); ?>">
</div>
<div class="form-group">
    <label>Fecha de Nacimiento:</label>
    <input type="date" name="fecha_nacimiento" value="<?php echo $usuario_editar['fecha_nacimiento']; ?>">
</div>

            <div class="form-group">
                <label>Rol de Sistema:</label>
                <select name="rol_id" required>
                    <?php foreach($listaRoles as $r): ?>
                        <option value="<?php echo $r['id']; ?>" <?php echo ($usuario_editar['rol_id'] == $r['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($r['nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Estado de la Cuenta:</label>
                <select name="estado" required>
                    <option value="1" <?php echo ($usuario_editar['estado'] == 1) ? 'selected' : ''; ?>>✔️ Activo (Puede entrar)</option>
                    <option value="0" <?php echo ($usuario_editar['estado'] == 0) ? 'selected' : ''; ?>>❌ Inactivo (Bloqueado)</option>
                </select>
            </div>
            
            <button type="submit" class="btn-primario">Actualizar Usuario</button>
        </form>
    </div>
</div>

</main>
</div>

</body>
</html>