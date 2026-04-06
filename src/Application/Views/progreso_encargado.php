<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Progreso de Encargado - MACARO</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="dashboard-container">

<div class="header">
    <div class="user-info">📈 Análisis de Rendimiento</div>
    <a href="index.php?action=menu_graficos" class="logout-btn" style="background:#666;">Volver</a>
</div>

<div class="container-abm">
    <div class="card-form" style="max-width: 500px; margin: 0 auto 20px auto; background: #fff3e0;">
        <form method="GET" action="index.php">
            <input type="hidden" name="action" value="progreso_encargado">
            <label style="font-weight: bold; color: #e65100;">Seleccione un Encargado para analizar:</label>
            <div style="display: flex; gap: 10px; margin-top: 10px;">
                <select name="encargado_id" required style="flex: 1; padding: 10px;">
                    <option value="">-- Seleccionar --</option>
                    <?php foreach($encargados as $e): ?>
                        <option value="<?php echo $e['id']; ?>" <?php echo ((isset($_GET['encargado_id']) && $_GET['encargado_id'] == $e['id']) ? 'selected' : ''); ?>>
                            <?php echo htmlspecialchars($e['nombre_completo'] ?? ''); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn-primario" style="background: #e65100;">Generar Gráfico</button>
            </div>
        </form>
    </div>

    <?php if($datos_grafico && $datos_grafico['total_actas'] > 0): ?>
        <div class="card-table" style="max-width: 600px; margin: 0 auto; text-align: center; padding: 20px;">
            <h2 style="color: #333;">Desempeño de: <span style="color: #1976d2;"><?php echo htmlspecialchars($encargado_seleccionado ?? ''); ?></span></h2>
            <p style="color: #666; font-weight: bold;">Basado en <?php echo (int)$datos_grafico['total_actas']; ?> actas firmadas por clientes.</p>
            
            <div style="position: relative; height:400px; width:100%; max-width:400px; margin: 0 auto;">
                <canvas id="graficoRadar"></canvas>
            </div>
        </div>

        <script>
            // Usamos un bloque de script limpio para que el editor no se maree
            document.addEventListener('DOMContentLoaded', function() {
                const ctx = document.getElementById('graficoRadar').getContext('2d');
                new window.Chart(ctx, {
                    type: 'radar',
                    data: {
                        labels: ['⏰ Puntualidad', '📦 Organización', '✨ Prolijidad', '🤝 Trato al Cliente'],
                        datasets: [{
                            label: 'Promedio de Estrellas (Max 5)',
                            data: [
                                <?php echo number_format((float)$datos_grafico['prom_puntualidad'], 2); ?>,
                                <?php echo number_format((float)$datos_grafico['prom_organizacion'], 2); ?>,
                                <?php echo number_format((float)$datos_grafico['prom_prolijidad'], 2); ?>,
                                <?php echo number_format((float)$datos_grafico['prom_trato'], 2); ?>
                            ],
                            fill: true,
                            backgroundColor: 'rgba(54, 162, 235, 0.2)',
                            borderColor: 'rgb(54, 162, 235)',
                            pointBackgroundColor: 'rgb(54, 162, 235)',
                            pointBorderColor: '#fff',
                            pointHoverBackgroundColor: '#fff',
                            pointHoverBorderColor: 'rgb(54, 162, 235)'
                        }]
                    },
                    options: {
                        scales: {
                            r: {
                                angleLines: { display: true },
                                suggestedMin: 0,
                                suggestedMax: 5,
                                ticks: { stepSize: 1 }
                            }
                        }
                    }
                });
            });
        </script>

    <?php elseif(isset($_GET['encargado_id'])): ?>
        <div style="text-align: center; padding: 20px; background: #ffebee; color: #c62828; border-radius: 8px; max-width: 500px; margin: 0 auto;">
            Este encargado aún no tiene actas finalizadas para generar un gráfico.
        </div>
    <?php endif; ?>
</div>

</body>
</html>