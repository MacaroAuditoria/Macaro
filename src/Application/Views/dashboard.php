<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel - MACARO</title>
    <link rel="icon" href="img/logo_icon.png">
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="dashboard-container">

<div class="app-shell">
    <?php $seccion_activa = 'dashboard'; require __DIR__ . '/partials/sidebar.php'; ?>

    <main class="main-content">
        <div class="header">
            <div class="user-info">
                👋 Hola, <?php echo htmlspecialchars($_SESSION['nombre_completo']); ?>
                <span style="font-size: 14px; color: var(--color-texto-claro); font-weight: normal;">
                    (Rol: <?php echo $_SESSION['rol_id'] == 1 ? 'Admin' : ($_SESSION['rol_id'] == 2 ? 'Encargado' : 'Piqueo'); ?>)
                </span>
            </div>
        </div>

        <div class="menu-grid">
            <a href="?action=piqueo_config" class="menu-card">
                <span class="menu-icon">📦</span>
                <h3 class="menu-title">Piqueo</h3>
            </a>

            <?php if ($_SESSION['rol_id'] <= 2): ?>

                <a href="?action=catalogo" class="menu-card">
                    <span class="menu-icon">📋</span>
                    <h3 class="menu-title">Catálogo</h3>
                </a>

                <a href="index.php?action=monitor_zonas" class="menu-card">
                    <span class="menu-icon">📊</span>
                    <h3 class="menu-title">Monitor</h3>
                </a>

                <a href="index.php?action=inventario_menu" class="menu-card">
                    <span class="menu-icon">📥</span>
                    <h3 class="menu-title">Inventario</h3>
                </a>

                <a href="?action=actas_menu" class="menu-card">
                    <span class="menu-icon">📝</span>
                    <h3 class="menu-title">Actas</h3>
                </a>

                <a href="index.php?action=menu_graficos" class="menu-card">
                    <span class="menu-icon">📈</span>
                    <h3 class="menu-title">Gráficos</h3>
                </a>

                <a href="index.php?action=ajustes_menu" class="menu-card">
                    <span class="menu-icon">⚙️</span>
                    <h3 class="menu-title">Ajustes</h3>
                </a>
            <?php endif; ?>

            <?php if ($_SESSION['rol_id'] == 1): ?>
                <a href="index.php?action=clientes_gestion" class="menu-card">
                    <span class="menu-icon">🏬</span>
                    <h3 class="menu-title">Clientes</h3>
                </a>

                <a href="index.php?action=usuarios_gestion" class="menu-card">
                    <span class="menu-icon">👥</span>
                    <h3 class="menu-title">Usuarios</h3>
                </a>
            <?php endif; ?>
        </div>
    </main>
</div>

</body>
</html>
