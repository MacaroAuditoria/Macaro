<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ajustes - MACARO</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="dashboard-container">

<div class="header">
    <div class="user-info">⚙️ Panel de Ajustes del Sistema</div>
    <a href="index.php?action=dashboard" class="logout-btn" style="background:#666;">Volver</a>
</div>

<div class="menu-grid">
    <?php if ($_SESSION['rol_id'] == 1): ?>
    <a href="index.php?action=ajustes_locales" class="menu-card" style="border: 1px solid #00897b;">
        <span class="menu-icon">🏢</span>
        <h3 class="menu-title">Inventarios (Locales)</h3>
    </a>

    <a href="index.php?action=importar_csv" class="menu-card" style="text-decoration: none;">
        <span class="menu-icon" style="font-size: 40px;">📁</span>
        <h3 class="menu-title">Cargar Inventario CSV</h3>
        <p style="color: #666; font-size: 13px;">Importar base de datos de clientes.</p>
    </a>
    
    <?php endif; ?>

    <a href="index.php?action=ajustes_sectores" class="menu-card" style="border: 1px solid #00897b;">
        <span class="menu-icon">🏷️</span>
        <h3 class="menu-title">Sectores</h3>
    </a>
    <a href="index.php?action=calendario" class="menu-card" style="border-top: 4px solid #673ab7;">
    <span class="menu-icon">📅</span>
    <h3 class="menu-title">Calendario</h3>
</a>
</div>

</body>
</html>