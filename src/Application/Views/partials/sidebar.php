<?php
// src/Application/Views/partials/sidebar.php
// Se incluye al principio de cada vista "interna" del sistema.
// Requiere que la vista defina (opcional) $seccion_activa con el nombre
// de la action actual, para resaltar el ítem correspondiente del menú.
$seccion_activa = $seccion_activa ?? ($_GET['action'] ?? '');
$rol = $_SESSION['rol_id'] ?? 99;

function macaro_link($action, $icono, $texto, $activa) {
    $clase = ($activa === $action) ? 'sidebar-link active' : 'sidebar-link';
    echo "<a href=\"index.php?action={$action}\" class=\"{$clase}\"><span class=\"ico\">{$icono}</span> {$texto}</a>";
}
?>
<button class="sidebar-toggle" onclick="document.querySelector('.sidebar').classList.toggle('abierto')">☰</button>

<aside class="sidebar">
    <div class="sidebar-brand">
        <img src="img/logo_icon.png" alt="MACARO">
        <div class="sidebar-brand-text">
            <div class="name">MACARO</div>
            <div class="tag">Auditorías &amp; Stock</div>
        </div>
    </div>

    <?php if (isset($_SESSION['cliente_nombre'])): ?>
    <div class="sidebar-cliente">
        <span class="lbl">Cliente activo</span>
        <span class="val"><?php echo htmlspecialchars($_SESSION['cliente_nombre']); ?></span>
        <a href="index.php?action=clientes_gestion">Cambiar cliente →</a>
    </div>
    <?php elseif ($rol == 1): ?>
    <div class="sidebar-cliente">
        <span class="lbl">Sin cliente activo</span>
        <a href="index.php?action=clientes_gestion">Elegir cliente →</a>
    </div>
    <?php endif; ?>

    <nav class="sidebar-nav">
        <div class="sidebar-section-label">Operación</div>
        <?php macaro_link('dashboard', '🏠', 'Inicio', $seccion_activa); ?>
        <?php macaro_link('piqueo_config', '📦', 'Piqueo', $seccion_activa); ?>

        <?php if ($rol <= 2): ?>
        <div class="sidebar-section-label">Gestión</div>
        <?php macaro_link('catalogo', '📋', 'Catálogo', $seccion_activa); ?>
        <?php macaro_link('monitor_zonas', '📊', 'Monitor', $seccion_activa); ?>
        <?php macaro_link('inventario_menu', '📥', 'Inventario', $seccion_activa); ?>
        <?php macaro_link('actas_menu', '📝', 'Actas', $seccion_activa); ?>
        <?php macaro_link('calendario', '🗓️', 'Calendario', $seccion_activa); ?>
        <?php macaro_link('menu_graficos', '📈', 'Gráficos', $seccion_activa); ?>
        <?php endif; ?>

        <?php if ($rol == 1): ?>
        <div class="sidebar-section-label">Administración</div>
        <?php macaro_link('clientes_gestion', '🏬', 'Clientes', $seccion_activa); ?>
        <?php macaro_link('ajustes_menu', '⚙️', 'Ajustes', $seccion_activa); ?>
        <?php macaro_link('usuarios_gestion', '👥', 'Usuarios', $seccion_activa); ?>
        <?php endif; ?>
    </nav>

    <div class="sidebar-footer">
        <div class="sidebar-user">
            👋 <?php echo htmlspecialchars($_SESSION['nombre_completo'] ?? ''); ?>
            <span class="rol"><?php echo $rol == 1 ? 'Administrador' : ($rol == 2 ? 'Encargado' : 'Piqueo'); ?></span>
        </div>
        <a href="index.php?action=logout" class="sidebar-logout">Cerrar sesión</a>
    </div>
</aside>
