<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Centro de Actas - MACARO</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .modal-overlay {
            display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.5); align-items: center; justify-content: center; z-index: 1000;
        }
        .modal-box {
            background: white; padding: 30px; border-radius: 10px; width: 400px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3); text-align: center;
        }
    </style>
</head>
<body class="dashboard-container">

<div class="app-shell">
<?php $seccion_activa = 'actas_menu'; require __DIR__ . '/partials/sidebar.php'; ?>
<main class="main-content">

<div class="header">
    <div class="user-info">📝 Centro de Actas</div>
    <a href="index.php" class="logout-btn" style="background:#666;">Volver al Inicio</a>
</div>

<div class="menu-grid" style="margin-top: 40px; display: flex; gap: 20px; justify-content: center;">
    
    <a href="#" class="menu-card" onclick="document.getElementById('modalLocal').style.display='flex';" style="text-decoration: none; width: 250px;">
        <span class="menu-icon" style="font-size: 40px;">✍️</span>
        <h3 class="menu-title">Nueva Acta</h3>
        <p style="color: #666; font-size: 13px;">Comenzar carga de datos.</p>
    </a>

    <a href="index.php?action=actas_buscar" class="menu-card" style="text-decoration: none; width: 250px;">
    <span class="menu-icon" style="font-size: 40px;">📂</span>
    <h3 class="menu-title">Listado de Actas</h3>
    <p style="color: #666; font-size: 13px;">Ver y gestionar archivos.</p>
</a>
    
</div>

<div id="modalLocal" class="modal-overlay">
    <div class="modal-box" style="border-radius: 15px; padding: 40px; text-align: left;">
        <h2 style="margin-top: 0; color: #333; font-size: 24px; margin-bottom: 20px;">Nueva Acta</h2>
        
        <form method="GET" action="index.php">
            <input type="hidden" name="action" value="generar_acta"> 
            
            <label style="font-weight: bold; color: #555; display: block; margin-bottom: 8px;">Seleccionar Encargado</label>
            <select name="encargado_id" required style="width: 100%; padding: 12px; margin-bottom: 20px; border: 1px solid #ccc; border-radius: 8px; font-size: 15px; background-color: #f8f9fa;">
                <option value="">-- Seleccione un Encargado --</option>
                <?php foreach($listaEncargados as $enc): ?>
                    <option value="<?php echo $enc['id']; ?>"><?php echo htmlspecialchars($enc['nombre_completo']); ?></option>
                <?php endforeach; ?>
            </select>

            <label style="font-weight: bold; color: #555; display: block; margin-bottom: 8px;">Seleccionar Inventario</label>
            <select name="local_id" required style="width: 100%; padding: 12px; margin-bottom: 30px; border: 1px solid #ccc; border-radius: 8px; font-size: 15px; background-color: #f8f9fa;">
                <option value="">-- Seleccione un Local --</option>
                <?php foreach($listaLocales as $local): ?>
                    <option value="<?php echo $local['id']; ?>"><?php echo htmlspecialchars($local['nombre']); ?></option>
                <?php endforeach; ?>
            </select>
            
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <a href="#" onclick="document.getElementById('modalLocal').style.display='none'; return false;" style="color: #666; text-decoration: none; font-weight: bold;">Cancelar</a>
                <button type="submit" style="background: #2196f3; color: white; padding: 12px 24px; border-radius: 8px; border: none; cursor: pointer; font-weight: bold; font-size: 16px;">Comenzar Acta ➔</button>
            </div>
        </form>
    </div>
</div>

</main>
</div>

</body>
</html>