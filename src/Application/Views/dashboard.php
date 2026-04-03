<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel - MACARO</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="dashboard-container">

<div class="header">
    <div class="user-info">
        👋 Hola, <?php echo htmlspecialchars($_SESSION['nombre_completo']); ?> 
        <span style="font-size: 14px; color: #666; font-weight: normal;">
            (Rol: <?php echo $_SESSION['rol_id'] == 1 ? 'Admin' : ($_SESSION['rol_id'] == 2 ? 'Encargado' : 'Piqueo'); ?>)
        </span>
    </div>
    <a href="?action=logout" class="logout-btn">Salir</a>
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
    
    <!-- NUEVA TARJETA DE INVENTARIO CON EL FORMATO CORRECTO -->
    <a href="index.php?action=inventario_menu" class="menu-card" style="border: 2px solid #00897b; background: #e0f2f1;">
        <span class="menu-icon">📥</span>
        <h3 class="menu-title">Inventario</h3>
    </a>
    <?php endif; ?>

    <?php if ($_SESSION['rol_id'] == 1): ?>
    <a href="#" class="menu-card">
        <span class="menu-icon">👥</span>
        <h3 class="menu-title">Usuarios</h3>
    </a>
    <a href="#" class="menu-card">
        <span class="menu-icon">⚙️</span>
        <h3 class="menu-title">Ajustes</h3>
    </a>
    <?php endif; ?>
</div>

</body>
</html>