<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ver Acta - MACARO</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .acta-box { background: white; max-width: 600px; margin: 20px auto; padding: 30px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); border: 1px solid #ccc; }
        .row-dato { margin-bottom: 15px; border-bottom: 1px dashed #eee; padding-bottom: 10px; }
        .estrellas-ver { color: #ffca28; font-size: 20px; letter-spacing: 2px; }
    </style>
</head>
<body class="dashboard-container">

<div class="header">
    <div class="user-info">📄 Acta N° <?php echo $acta['id']; ?></div>
    <a href="index.php?action=actas_buscar" class="logout-btn" style="background:#666;">Volver al Buscador</a>
</div>

<div class="acta-box">
    <h2 style="text-align: center; color: #004d40; border-bottom: 2px solid #004d40; padding-bottom: 10px;">Acta de Conformidad de Servicio</h2>
    
    <div class="row-dato"><strong>Local Auditado:</strong> <?php echo htmlspecialchars($acta['local_nombre']); ?></div>
    <div class="row-dato"><strong>Fecha y Hora de Cierre:</strong> <?php echo date('d/m/Y H:i:s', strtotime($acta['fecha_cierre'])); ?></div>
    <div class="row-dato"><strong>Encargado de MACARO:</strong> <?php echo htmlspecialchars($acta['encargado_nombre']); ?></div>
    <div class="row-dato"><strong>Firma otorgada por:</strong> <?php echo htmlspecialchars($acta['nombre_evaluador']); ?></div>

    <h3 style="margin-top: 30px;">Calificaciones:</h3>
    <div class="row-dato">⏰ Puntualidad: <span class="estrellas-ver"><?php echo str_repeat('★', $acta['estrellas_puntualidad']) . str_repeat('☆', 5 - $acta['estrellas_puntualidad']); ?></span></div>
    <div class="row-dato">📦 Organización: <span class="estrellas-ver"><?php echo str_repeat('★', $acta['estrellas_organizacion']) . str_repeat('☆', 5 - $acta['estrellas_organizacion']); ?></span></div>
    <div class="row-dato">✨ Prolijidad: <span class="estrellas-ver"><?php echo str_repeat('★', $acta['estrellas_prolijidad']) . str_repeat('☆', 5 - $acta['estrellas_prolijidad']); ?></span></div>
    <div class="row-dato">🤝 Trato: <span class="estrellas-ver"><?php echo str_repeat('★', $acta['estrellas_trato']) . str_repeat('☆', 5 - $acta['estrellas_trato']); ?></span></div>

    <?php if(!empty($acta['comentario'])): ?>
        <div class="row-dato" style="background: #f9f9f9; padding: 10px; border-left: 4px solid #ccc;">
            <strong>Comentario del Cliente:</strong><br>
            <i>"<?php echo nl2br(htmlspecialchars($acta['comentario'])); ?>"</i>
        </div>
    <?php endif; ?>

    <h3 style="margin-top: 30px;">Firma Digital:</h3>
    <div style="border: 1px solid #ccc; padding: 10px; text-align: center; background: #fafafa;">
        <img src="<?php echo $acta['firma_base64']; ?>" style="max-width: 100%; height: auto; border: 1px dashed #999;">
    </div>
</div>

</body>
</html>