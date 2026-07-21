<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso - MACARO</title>
    <link rel="icon" href="img/logo_icon.png">
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="login-container">

<div class="login-box">
    <img src="img/logo_icon.png" alt="MACARO" class="login-logo">
    <h2>MACARO</h2>
    <p class="login-subtitle">Auditorías &amp; Control de Stock</p>

    <div class="error-msg">
        <?php echo !empty($error) ? $error : ''; ?>
    </div>

    <form method="POST" action="index.php">
<?php echo \App\Infrastructure\Security::campoCSRF(); ?>
        <div class="input-group">
            <label for="usuario">Usuario</label>
            <input type="text" id="usuario" name="usuario" required autocomplete="off">
        </div>
        <div class="input-group">
            <label for="password">Contraseña</label>
            <input type="password" id="password" name="password" required>
        </div>
        <button type="submit" class="btn-primario">Ingresar</button>
    </form>
</div>

</body>
</html>