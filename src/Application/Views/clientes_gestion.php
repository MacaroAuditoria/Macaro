<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Clientes - MACARO</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .container-abm { display: grid; grid-template-columns: 350px 1fr; gap: 25px; padding: 20px; max-width: 1400px; margin: 0 auto; align-items: start; }
        .card-form, .card-table { background: #ffffff; padding: 25px; border-radius: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); border: 1px solid #eaeaea; }
        .card-form h3, .card-table h3 { margin-top: 0; margin-bottom: 20px; color: #333; font-size: 18px; border-bottom: 2px solid #f0f0f0; padding-bottom: 10px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; font-weight: bold; margin-bottom: 5px; color: #555; font-size: 13px; }
        .form-group input { width: 100%; padding: 10px 12px; border: 1px solid #ccc; border-radius: 6px; font-size: 14px; box-sizing: border-box; }
        .btn-primario { width: 100%; padding: 12px; background: #00897b; color: white; border: none; border-radius: 6px; font-size: 16px; font-weight: bold; cursor: pointer; margin-top: 10px; }
        .btn-primario:hover { background: #00695c; }
        .tabla-gestion { width: 100%; border-collapse: collapse; }
        .tabla-gestion th { background: #f8f9fa; padding: 12px; border-bottom: 2px solid #ccc; text-align: left; font-size: 14px; color: #444; }
        .tabla-gestion td { padding: 12px; border-bottom: 1px solid #eee; font-size: 14px; color: #333; }
        .btn-edit { background: #2196f3; color: white; padding: 6px 12px; border-radius: 4px; text-decoration: none; font-size: 12px; font-weight: bold; }
        .btn-select { background: #ff9800; color: white; padding: 6px 12px; border-radius: 4px; text-decoration: none; font-size: 12px; font-weight: bold; }
        @media (max-width: 950px) { .container-abm { grid-template-columns: 1fr; } }
    </style>
</head>
<body class="dashboard-container">

<div class="app-shell">
<?php $seccion_activa = 'clientes_gestion'; require __DIR__ . '/partials/sidebar.php'; ?>
<main class="main-content">

<div class="header">
    <div class="user-info">🏬 Gestión de Clientes</div>
    <a href="index.php?action=dashboard" class="logout-btn" style="background:#666;">Volver</a>
</div>

<div class="container-abm">
    <div class="card-form">
        <h3>✨ Nuevo Cliente</h3>

        <?php if (isset($_GET['error']) && $_GET['error'] === 'duplicado'): ?>
            <div style="background:#ffebee; color:#c62828; padding:12px; border-radius:6px; margin-bottom:15px; font-weight:bold; font-size:13px;">
                ⚠️ Ya existe un cliente con ese nombre.
            </div>
        <?php endif; ?>

        <form method="POST" action="index.php?action=clientes_gestion">
<?php echo \App\Infrastructure\Security::campoCSRF(); ?>
            <div class="form-group">
                <label>Nombre del Cliente / Negocio:</label>
                <input type="text" name="nombre" placeholder="Ej: Supermercado Don José" required>
            </div>
            <div class="form-group">
                <label>Contacto:</label>
                <input type="text" name="contacto" placeholder="Ej: Juan Pérez">
            </div>
            <div class="form-group">
                <label>Teléfono:</label>
                <input type="text" name="telefono">
            </div>
            <div class="form-group">
                <label>Email:</label>
                <input type="email" name="email">
            </div>
            <button type="submit" class="btn-primario">Guardar Cliente</button>
        </form>
    </div>

    <div class="card-table">
        <h3>📋 Clientes Registrados</h3>
        <?php if (isset($_SESSION['cliente_id'])): ?>
            <p style="font-size:13px; color:#00695c; background:#e0f2f1; padding:10px; border-radius:6px;">
                Cliente activo ahora mismo: <strong><?php echo htmlspecialchars($_SESSION['cliente_nombre']); ?></strong>
            </p>
        <?php endif; ?>
        <table class="tabla-gestion">
            <thead>
                <tr><th>Nombre</th><th>Contacto</th><th>Alta</th><th style="text-align:right;">Acciones</th></tr>
            </thead>
            <tbody>
                <?php foreach ($listaClientes as $c): ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($c['nombre']); ?></strong></td>
                    <td><?php echo htmlspecialchars($c['contacto'] ?? '-'); ?></td>
                    <td><?php echo htmlspecialchars(date('d/m/Y', strtotime($c['fecha_alta']))); ?></td>
                    <td style="text-align:right;">
                        <a href="index.php?action=seleccionar_cliente&id=<?php echo $c['id']; ?>" class="btn-select">✅ Usar este cliente</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

</main>
</div>

</body>
</html>
