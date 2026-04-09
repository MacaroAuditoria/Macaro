<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Categorías - MACARO</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="dashboard-container">

<div class="header">
    <div class="user-info">📁 Gestión de Categorías</div>
    <div>
        <a href="index.php?action=catalogo" class="btn-primario" style="text-decoration: none; padding: 10px 15px; background: #6c757d; margin-right: 10px;">Volver al Catálogo</a>
    </div>
</div>

<div style="background: var(--fondo-tarjetas); padding: 30px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); max-width: 600px; margin: 0 auto;">
    
    <h3 style="margin-top: 0;">Agregar Nueva Categoría</h3>
    
    <?php if (isset($error)): ?>
        <p style="color: var(--color-peligro); background: #f8d7da; padding: 10px; border-radius: 5px;"><?php echo $error; ?></p>
    <?php endif; ?>

    <form method="POST" action="index.php?action=categorias" style="display: flex; gap: 10px; margin-bottom: 30px;">
        <input type="text" name="nueva_categoria" placeholder="Ej: Bebidas sin alcohol" required style="flex: 1; padding: 10px; border: 1px solid #ccc; border-radius: 5px; font-size: 16px;">
        <button type="submit" class="btn-primario" style="width: auto;">Guardar Categoría</button>
    </form>

    <hr style="border: 0; border-top: 1px solid #eee; margin-bottom: 20px;">

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; flex-wrap: wrap; gap: 15px;">
        <h3 style="margin: 0;">Categorías Registradas</h3>
        <input type="text" id="buscadorCategoria" placeholder="🔍 Buscar categoría..." style="padding: 8px 12px; border: 1px solid #ccc; border-radius: 4px; font-size: 14px; width: 250px;">
    </div>

    <ul style="list-style: none; padding: 0;" id="listaCategoriasUI">
        <?php if (empty($listaCategorias)): ?>
            <li style="color: var(--color-texto-claro);">No hay categorías registradas.</li>
        <?php else: ?>
            <?php foreach ($listaCategorias as $categoria): ?>
                <li class="item-categoria" style="padding: 10px; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center;">
                    <strong class="nombre-cat"><?php echo htmlspecialchars($categoria['nombre']); ?></strong>
                    <div>
                        <span style="color: #ccc; margin-right: 15px; font-size: 14px;">ID: <?php echo $categoria['id']; ?></span>
                        <a href="?action=editar_categoria&id=<?php echo $categoria['id']; ?>" class="btn-primario" style="padding: 5px 10px; text-decoration: none; font-size: 14px; background-color: #ffc107; color: #000;">✏️</a>
                        <a href="?action=eliminar_categoria&id=<?php echo $categoria['id']; ?>" class="btn-primario" style="padding: 5px 10px; text-decoration: none; font-size: 14px; background-color: var(--color-peligro); margin-left: 5px;" onclick="return confirm('¿Seguro que querés eliminar esta categoría?');">🗑️</a>
                    </div>
                </li>
            <?php endforeach; ?>
        <?php endif; ?>
    </ul>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const inputBusqueda = document.getElementById('buscadorCategoria');
    // Buscamos todos los elementos <li> que tengan la clase 'item-categoria'
    const listaItems = document.querySelectorAll('.item-categoria');

    function filtrarLista() {
        const texto = inputBusqueda.value.toLowerCase().trim();
        const estaVacio = (texto === "");

        listaItems.forEach((item, index) => {
            // Atrapamos el texto del nombre de la categoría
            const nombre = item.querySelector('.nombre-cat').textContent.toLowerCase();
            const coincide = nombre.includes(texto);

            if (estaVacio) {
                // MODO REPOSO: Mostrar solo los últimos 7
                if (index < 7) {
                    item.style.display = 'flex'; // Usamos flex porque tu <li> tiene display: flex
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

    // Ejecutamos la función apenas carga la página para esconder del 8 en adelante
    filtrarLista();

    // Activamos el "sensor" para que filtre al instante cuando escribís
    inputBusqueda.addEventListener('keyup', filtrarLista);
});
</script>
</body>
</html>