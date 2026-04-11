<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Importar Inventario - MACARO</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .upload-box {
            border: 2px dashed #2196f3;
            padding: 40px;
            text-align: center;
            border-radius: 10px;
            background-color: #f1f8ff;
            margin-bottom: 20px;
        }
        .upload-box input[type="file"] {
            font-size: 16px;
            margin-top: 15px;
        }
    </style>
</head>
<body class="dashboard-container">

<div class="header">
    <div class="user-info">📁 Cargar Inventario Cliente</div>
    <div><a href="index.php?action=dashboard" class="btn-primario" style="background: #6c757d; text-decoration:none;">Volver</a></div>
</div>

<div style="background: white; padding: 30px; border-radius: 10px; max-width: 600px; margin: 40px auto; box-shadow: 0 4px 10px rgba(0,0,0,0.05);">
    
    <h2 style="margin-top: 0; color: #333; border-bottom: 2px solid #2196f3; padding-bottom: 10px;">Importación desde Loyverse</h2>
    <p style="color: #666; margin-bottom: 25px;">Seleccione el archivo CSV exportado desde el sistema del cliente. MACARO extraerá automáticamente los Códigos de Barra, Descripciones y Precios para alimentar el catálogo.</p>

    <?php if (isset($error)) echo "<div style='background:#ffebee; color:#c62828; padding:15px; border-radius:6px; margin-bottom:20px; font-weight:bold;'>$error</div>"; ?>
    <?php if (isset($exito)) echo "<div style='background:#e8f5e9; color:#2e7d32; padding:15px; border-radius:6px; margin-bottom:20px; font-weight:bold;'>$exito</div>"; ?>

    <form method="POST" action="index.php?action=importar_csv" enctype="multipart/form-data">
        
        <div class="upload-box">
            <span style="font-size: 40px;">📄</span><br>
            <label style="font-weight: bold; color: #333; display: block; margin-top: 10px;">Seleccionar archivo .CSV</label>
            <input type="file" name="archivo_csv" accept=".csv" required>
        </div>

        <button type="submit" class="btn-primario" style="width: 100%; padding: 15px; font-size: 16px;">🚀 Procesar e Importar Productos</button>
    </form>
</div>

</body>
</html>