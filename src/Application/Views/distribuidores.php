<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Distribuidores - MACARO</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        /* Un par de estilos rápidos para que se vea premium */
        .tarjeta-modulo {
            background: #ffffff; 
            padding: 30px; 
            border-radius: 12px; 
            box-shadow: 0 8px 20px rgba(0,0,0,0.08); 
            max-width: 700px; 
            margin: 40px auto;
        }
        .item-distribuidor {
            padding: 15px 20px; 
            border: 1px solid #eaeaea; 
            border-radius: 8px; 
            margin-bottom: 10px; 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            background: #fff; 
            transition: all 0.2s ease;
        }
        .item-distribuidor:hover {
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
            border-color: #d1c4e9;
        }
        .badge-id {
            background: #f1f3f5; 
            color: #6c757d; 
            padding: 4px 10px; 
            border-radius: 20px; 
            font-size: 12px; 
            font-weight: bold;
        }
    </style>
</head>
<body class="dashboard-container">

<div class="header">
    <div class="user-info">🚚 Gestión de Distribuidores</div>
    <div>
        <a href="index.php?action=catalogo" class="btn-secundario" style="background:#6c757d; color: white; padding: 10px 15px; text-decoration: none; border-radius: 6px; font-weight: bold;">Volver al Catálogo</a>
    </div>
</div>

<div class="tarjeta-modulo">
    
    <h3 style="margin-top: 0; color: #333; border-bottom: 2px solid #673ab7; padding-bottom: 10px;">Agregar Nuevo Distribuidor</h3>
    
    <?php if (isset($error)): ?>
        <p style="color: #842029; background: #f8d7da; padding: 12px; border-radius: 6px; border: 1px solid #f5c2c7;"><?php echo $error; ?></p>
    <?php endif; ?>

    <form method="POST" action="index.php?action=distribuidores" style="display: flex; gap: 15px; margin-bottom: 35px; margin-top: 20px;">
        <input type="text" name="nuevo_distribuidor" placeholder="Ej: Almena S.A." required style="flex: 1; padding: 12px 15px; border: 1px solid #ced4da; border-radius: 6px; font-size: 15px; outline: none;">
        <button type="submit" class="btn-primario" style="padding: 12px 25px; border: none; border-radius: 6px; background: #673ab7; color: white; font-weight: bold; cursor: pointer; font-size: 15px;">💾 Guardar</button>
    </form>

    <div style="display: flex; justify-content: space-between; align-items: center; background: #f8f9fa; padding: 15px 20px; border-radius: 8px; margin-bottom: 20px;">
        <h3 style="margin: 0; color: #333; font-size: 18px;">📋 Distribuidores Registrados</h3>
        <div style="position: relative;">
            <span style="position: absolute; left: 12px; top: 9px; color: #adb5bd;">🔍</span>
            <input type="text" id="buscadorDistribuidor" placeholder="Buscar distribuidor..." style="padding: 8px 15px 8px 35px; border: 1px solid #ced4da; border-radius: 20px; font-size: 14px; width: 200px; outline: none; transition: border-color 0.2s;">
        </div>
    </div>

    <ul style="list-style: none; padding: 0; margin: 0;" id="listaDistribuidoresUI">
        <?php if (empty($listaDistribuidores)): ?>
            <li style="color: #6c757d; text-align: center; padding: 20px; background: #f8f9fa; border-radius: 8px;">No hay distribuidores registrados.</li>
        <?php else: ?>
            <?php foreach ($listaDistribuidores as $dist): ?>
                <li class="item-distribuidor">
                    <strong class="nombre-dist" style="font-size: 16px; color: #2b2b2b;"><?php echo htmlspecialchars($dist['nombre']); ?></strong>
                    
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <span class="badge-id">ID: <?php echo $dist['id']; ?></span>
                        
                        <a href="?action=editar_distribuidor&id=<?php echo $dist['id']; ?>" style="padding: 6px 12px; text-decoration: none; background-color: #ffc107; color: #000; border-radius: 6px; font-size: 14px; font-weight: bold;">✏️</a>
                        
                        <a href="?action=eliminar_distribuidor&id=<?php echo $dist['id']; ?>" style="padding: 6px 12px; text-decoration: none; background-color: #dc3545; color: white; border-radius: 6px; font-size: 14px; font-weight: bold;" onclick="return confirm('¿Seguro que querés eliminar este distribuidor?');">🗑️</a>
                    </div>
                </li>
            <?php endforeach; ?>
        <?php endif; ?>
    </ul>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const inputBusqueda = document.getElementById('buscadorDistribuidor');
    // Buscamos todos los elementos <li> que tengan la clase 'item-distribuidor'
    const listaItems = document.querySelectorAll('.item-distribuidor');

    function filtrarLista() {
        const texto = inputBusqueda.value.toLowerCase().trim();
        const estaVacio = (texto === "");

        listaItems.forEach((item, index) => {
            // Atrapamos el texto del nombre del distribuidor
            const nombre = item.querySelector('.nombre-dist').textContent.toLowerCase();
            const coincide = nombre.includes(texto);

            if (estaVacio) {
                // MODO REPOSO: Mostrar solo los últimos 7
                if (index < 7) {
                    item.style.display = 'flex'; 
                } else {
                    item.style.display = 'none';
                }
            } else {
                // MODO BÚSQUEDA: Mostrar todo lo que coincida
                if (coincide) {
                    item.style.display = 'flex';
                } else {
                    item.style.display = 'none';
                }
            }
        });
    }

    // Ejecutamos la función apenas carga para esconder del 8 en adelante
    filtrarLista();

    // Le damos un "efecto foco" a la barra de búsqueda
    inputBusqueda.addEventListener('focus', function() { this.style.borderColor = '#673ab7'; });
    inputBusqueda.addEventListener('blur', function() { this.style.borderColor = '#ced4da'; });

    // Activamos el sensor para que filtre al instante cuando escribís
    inputBusqueda.addEventListener('keyup', filtrarLista);
});
</script>

</body>
</html>