<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ajustes - MACARO</title>
    <link rel="stylesheet" href="css/style.css">
    
    <style>
        .menu-card {
            display: flex;
            flex-direction: column;
            justify-content: center; /* Esto es la magia que centra verticalmente */
            align-items: center;     /* Esto centra horizontalmente */
            text-align: center;
            min-height: 160px;       /* Altura mínima para que queden cuadraditas y prolijas */
            padding: 20px;
            box-sizing: border-box;
            text-decoration: none;   /* Quita la línea de abajo de los links */
            border-radius: 8px;      /* Bordes un poco redondeados */
            transition: transform 0.2s, box-shadow 0.2s; /* Efecto suave de movimiento */
            background-color: #fff;
        }

        /* Efecto al pasar el mouse por arriba */
        .menu-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0,0,0,0.1);
        }

        .menu-icon {
            font-size: 45px; /* Íconos un poco más grandes para que resalten */
            margin-bottom: 10px;
        }

        .menu-title {
            margin: 0;
            color: #333;
            font-size: 18px;
        }
    </style>
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

    <a href="index.php?action=importar_csv" class="menu-card" style="border: 1px solid #ccc;">
        <span class="menu-icon">📁</span>
        <h3 class="menu-title">Cargar Inventario CSV</h3>
        <p style="color: #666; font-size: 13px; margin-top: 8px; margin-bottom: 0;">Importar base de datos de clientes.</p>
    </a>
    <?php endif; ?>

    <a href="index.php?action=ajustes_sectores" class="menu-card" style="border: 1px solid #00897b;">
        <span class="menu-icon">🏷️</span>
        <h3 class="menu-title">Sectores</h3>
    </a>
    
    <a href="index.php?action=calendario" class="menu-card" style="border-top: 4px solid #673ab7; border-left: 1px solid #eee; border-right: 1px solid #eee; border-bottom: 1px solid #eee;">
        <span class="menu-icon">📅</span>
        <h3 class="menu-title">Calendario</h3>
    </a>
</div>

</body>
</html>