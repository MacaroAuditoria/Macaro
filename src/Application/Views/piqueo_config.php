<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Nuevo Conteo</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f6f9; margin: 0; padding: 0; }
        .app-header { background: white; padding: 15px; border-bottom: 1px solid #ddd; display: flex; align-items: center; font-size: 18px; font-weight: bold;}
        .back-btn { text-decoration: none; color: #333; font-size: 24px; margin-right: 15px; }
        .container { padding: 20px; }
        .form-group { margin-bottom: 25px; }
        .form-label { display: block; font-size: 14px; color: #666; margin-bottom: 5px; font-weight: bold;}
        
        /* Estilo gigante para la Zebra */
        select { 
            width: 100%; 
            padding: 15px; 
            font-size: 18px; 
            border: 1px solid #ccc; 
            border-radius: 8px; 
            background: white;
            appearance: none; /* Quita la flechita por defecto para poner una propia o dejarlo limpio */
        }
        
        .btn-start {
            background-color: #00897b; /* Un verde profesional tipo app de logística */
            color: white;
            border: none;
            width: 100%;
            padding: 20px;
            font-size: 20px;
            font-weight: bold;
            border-radius: 50px;
            margin-top: 30px;
        }
    </style>
</head>
<body>

<div class="app-header">
    <a href="index.php" class="back-btn">←</a>
    Nuevo Conteo
</div>

<div class="container">
    <?php if (!empty($error)) echo "<div style='background:#ff5252; color:white; padding:15px; border-radius:8px; margin-bottom:20px; font-weight:bold;'>$error</div>"; ?>
    <form method="POST" action="index.php?action=piqueo_config">
        
        <div class="form-group">
            <label class="form-label">Inventario (Local)</label>
            <select name="local_id" required>
                <option value="">(Ninguno)</option>
                <?php foreach ($locales as $l): ?>
                    <option value="<?php echo $l['id']; ?>"><?php echo htmlspecialchars($l['nombre']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label class="form-label">Sector</label>
            <select name="sector_id">
                <option value="">(Ninguno)</option>
                <?php foreach ($sectores as $s): ?>
                    <option value="<?php echo $s['id']; ?>"><?php echo htmlspecialchars($s['nombre']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label class="form-label">Zona</label>
            <select name="zona_id" required>
                <option value="">(Ninguno)</option>
                <?php foreach ($zonas as $z): ?>
                    <option value="<?php echo $z['id']; ?>"><?php echo htmlspecialchars($z['codigo']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <button type="submit" class="btn-start">COMENZAR PIQUEO ✓</button>
    </form>
</div>

</body>
</html>