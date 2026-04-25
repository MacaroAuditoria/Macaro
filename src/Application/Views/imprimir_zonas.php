<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Imprimir Zonas - MACARO</title>
    <link href="https://fonts.googleapis.com/css2?family=Libre+Barcode+39+Text&display=swap" rel="stylesheet">
    <style>
        /* Estilos base */
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f2f5;
            margin: 0;
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .btn-imprimir {
            background-color: #2196f3;
            color: white;
            border: none;
            padding: 12px 25px;
            font-size: 16px;
            font-weight: bold;
            border-radius: 8px;
            cursor: pointer;
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .btn-imprimir:hover { background-color: #1976d2; }

        /* Configuración de la Hoja A4 */
        .hoja-a4 {
            background-color: white;
            width: 210mm;
            height: 297mm; /* Alto fijo estricto para evitar que nada sobresalga */
            padding: 5mm;
            box-sizing: border-box;
            box-shadow: 0 0 10px rgba(0,0,0,0.2);
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            grid-template-rows: repeat(5, 56mm);
            gap: 2mm;
            margin-bottom: 20px; /* Separación visual en la pantalla para ver dónde termina una hoja y empieza la otra */
            
            /* LA MAGIA PARA LA IMPRESORA: Obliga a saltar de página */
            page-break-after: always; 
            break-after: page;
        }
        
        /* Para evitar que la impresora saque una última hoja en blanco al final de todo */
        .hoja-a4:last-child {
            page-break-after: auto;
            break-after: auto;
        }

        .tarjeta-zona {
            border: 1px dashed #666;
            padding: 8px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            overflow: hidden; /* Corta cualquier cosa que intente salir del borde */
        }

        .header-tarjeta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #000;
            padding-bottom: 3px;
        }

        .logo-macaro { font-size: 11px; font-weight: 900; }
        .header-label {
            font-size: 9px; /* Lo subimos un poquito para que se lea mejor */
            font-weight: bold;
            text-transform: uppercase; /* Para que siempre quede prolijo en mayúsculas */
            max-width: 120px; /* Le damos un límite para que no empuje al logo */
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis; /* Si el nombre es larguísimo, pone tres puntitos (...) */
        }
        .centro-tarjeta {
            text-align: center;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            gap: 2px;
        }

        .codigo-barras {
            font-family: 'Libre Barcode 39 Text', cursive;
            font-size: 42px; 
            margin: 0;
            line-height: 0.7;
            color: #000;
        }

        .zona-texto {
            font-size: 20px;
            font-weight: bold;
            margin: 0;
            color: #000;
        }

        .contenedor-cantidad {
            margin-top: 20px;
            /* border: 1px solid #000; */
            padding: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 16px;
        }

        .label-cantidad { font-size: 17px; font-weight: bold; }
        .espacio-cantidad { width: 45px; border-bottom: 1.5px solid #000; height: 18px; }

        /* Reglas de impresión */
        @media print {
            body { background: white; padding: 0; }
            .btn-imprimir { display: none; }
            .hoja-a4 { box-shadow: none; margin: 0; border: none; }
            @page { size: A4; margin: 0; }
        }
    </style>
</head>
<body>

    <button class="btn-imprimir" onclick="window.print()">🖨️ Imprimir Etiquetas</button>

    <?php 
    // EL TRUCO: Cortamos la lista grande (ej: 200 zonas) en "paquetes" de 20 zonas cada uno
    $paginas = array_chunk($lista_zonas, 20);

    // BUCLE PRINCIPAL: Recorremos cada "paquete" y creamos una Hoja A4 nueva
    foreach($paginas as $pagina_actual): 
    ?>
    
    <div class="hoja-a4">
        
        <?php foreach($pagina_actual as $zona): ?>
        <div class="tarjeta-zona">
            <div class="header-tarjeta">
                <div class="logo-macaro">MACARO</div>
                <div class="header-label"><?php echo htmlspecialchars($sector_nombre); ?></div>
            </div>
            
            <div class="centro-tarjeta">
                <p class="codigo-barras">*Z-<?php echo $zona['id']; ?>*</p>
                <p class="zona-texto"><?php echo htmlspecialchars($zona['codigo']); ?></p>
                
                <div class="contenedor-cantidad">
                    <span class="label-cantidad">CANT:</span>
                    <div class="espacio-cantidad"></div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>

    </div>
    
    <?php endforeach; ?>

</body>
</html>