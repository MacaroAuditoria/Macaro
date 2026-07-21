<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gráficos y Actas - MACARO</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .submenu-container { display: flex; gap: 20px; justify-content: center; margin-top: 40px; flex-wrap: wrap; }
        .submenu-card { 
            background: #fff; 
            border-radius: 12px; 
            padding: 30px 20px; 
            text-align: center; 
            width: 200px; 
            text-decoration: none; 
            color: #333; 
            box-shadow: 0 4px 6px rgba(0,0,0,0.05); 
            border: 1px solid #eaeaea;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .submenu-card:hover { transform: translateY(-5px); box-shadow: 0 8px 15px rgba(0,0,0,0.1); }
        .submenu-card .icon { font-size: 40px; display: block; margin-bottom: 15px; }
        .submenu-card h3 { margin: 0; font-size: 18px; color: #444; }
    </style>
</head>
<body class="dashboard-container">

<div class="app-shell">
<?php $seccion_activa = 'menu_graficos'; require __DIR__ . '/partials/sidebar.php'; ?>
<main class="main-content">

<div class="header">
    <div class="user-info">📊 Panel de Gráficos y Auditoría</div>
    <a href="index.php?action=dashboard" class="logout-btn" style="background:#666;">Volver al Inicio</a>
</div>

<div class="submenu-container">
    <a href="index.php?action=progreso_encargado" class="submenu-card" style="border-top: 4px solid #ff9800;">
        <span class="icon">📈</span>
        <h3>Rendimiento</h3>
    </a>

    <a href="index.php?action=ranking_piqueadores" class="submenu-card" style="border-top: 4px solid #4caf50;">
        <span class="icon">🏆</span>
        <h3>Ranking Piqueadores</h3>
    </a>
</div>

</main>
</div>

</body>
</html>