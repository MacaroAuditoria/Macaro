<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ranking de Piqueadores - MACARO</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="dashboard-container">

<div class="app-shell">
<?php $seccion_activa = 'menu_graficos'; require __DIR__ . '/partials/sidebar.php'; ?>
<main class="main-content">

<div class="header">
    <div class="user-info">🏆 Ranking de Productividad</div>
    <a href="index.php?action=menu_graficos" class="logout-btn" style="background:#666;">Volver</a>
</div>

<div class="container-abm" style="display: flex; gap: 20px; flex-wrap: wrap; justify-content: center;">
    
    <div class="card-table" style="flex: 1; min-width: 350px;">
        <h3>Top Piqueadores</h3>
        <table class="tabla-gestion">
            <thead>
                <tr>
                    <th>Pos.</th>
                    <th>Nombre</th>
                    <th>Artículos</th>
                    <th>Zonas</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($ranking as $index => $p): ?>
                <tr>
                    <td>
                        <?php 
                        if($index == 0) echo "🥇";
                        elseif($index == 1) echo "🥈";
                        elseif($index == 2) echo "🥉";
                        else echo ($index + 1);
                        ?>
                    </td>
                    <td><strong><?php echo htmlspecialchars($p['nombre_completo']); ?></strong></td>
                    <td><?php echo number_format($p['total_articulos'] ?? 0); ?></td>
                    <td><?php echo $p['total_zonas']; ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="card-table" style="width: 400px; text-align: center; padding: 20px;">
        <h3>Distribución de Trabajo</h3>
        <p style="font-size: 12px; color: #666;">Basado en el total de artículos escaneados</p>
        <canvas id="chartPiqueo"></canvas>
    </div>

</div>

<script>
    const ctx = document.getElementById('chartPiqueo').getContext('2d');
    
    // Extraemos datos de PHP a JS
    const nombres = <?php echo json_encode(array_column($ranking, 'nombre_completo')); ?>;
    const totales = <?php echo json_encode(array_column($ranking, 'total_articulos')); ?>;

    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: nombres,
            datasets: [{
                data: totales,
                backgroundColor: [
                    '#4caf50', '#2196f3', '#ff9800', '#f44336', '#9c27b0', '#00bcd4'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });
</script>

</main>
</div>

</body>
</html>