<?php
// 1. Iniciamos el sistema de memoria temporal (Sesiones)
session_start();

require_once __DIR__ . '/../vendor/autoload.php';
use App\Infrastructure\Database;

// ------------------------------------------------
// 2. Si el usuario pide cerrar sesión
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy();
    header("Location: index.php");
    exit;
}

// --- RUTA PARA EL SUBMENÚ DE CATÁLOGO ---
if (isset($_GET['action']) && $_GET['action'] === 'catalogo' && isset($_SESSION['usuario_id'])) {
    if ($_SESSION['rol_id'] > 2) { die("Acceso denegado."); }
    
    require_once __DIR__ . '/../src/Application/Views/catalogo.php';
    exit;
}
// ==========================================
// MÓDULO DE CATEGORÍAS (ABM COMPLETO)
// ==========================================

// 1. ALTA Y LECTURA
if (isset($_GET['action']) && $_GET['action'] === 'categorias' && isset($_SESSION['usuario_id'])) {
    if ($_SESSION['rol_id'] > 2) { die("Acceso denegado."); }
    $db = (new Database())->getConnection();

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['nueva_categoria'])) {
        try {
            $stmt = $db->prepare("INSERT INTO categorias (nombre) VALUES (:nombre)");
            $stmt->execute(['nombre' => trim($_POST['nueva_categoria'])]);
            header("Location: index.php?action=categorias");
            exit;
        } catch (Exception $e) {
            $error = "La categoría ya existe o hubo un error.";
        }
    }
    $stmt = $db->query("SELECT id, nombre FROM categorias ORDER BY nombre ASC");
    $listaCategorias = $stmt->fetchAll();
    require_once __DIR__ . '/../src/Application/Views/categorias.php';
    exit;
}

// 2. MODIFICACIÓN (EDICIÓN)
if (isset($_GET['action']) && $_GET['action'] === 'editar_categoria' && isset($_SESSION['usuario_id'])) {
    if ($_SESSION['rol_id'] > 2) { die("Acceso denegado."); }
    $db = (new Database())->getConnection();

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['nombre']) && !empty($_POST['id'])) {
        try {
            $stmt = $db->prepare("UPDATE categorias SET nombre = :nombre WHERE id = :id");
            $stmt->execute(['nombre' => trim($_POST['nombre']), 'id' => $_POST['id']]);
            header("Location: index.php?action=categorias"); 
            exit;
        } catch (Exception $e) {
            $error = "La categoría ya existe o hubo un error.";
        }
    }
    if (isset($_GET['id'])) {
        $stmt = $db->prepare("SELECT id, nombre FROM categorias WHERE id = :id");
        $stmt->execute(['id' => $_GET['id']]);
        $catActual = $stmt->fetch();
        if ($catActual) {
            require_once __DIR__ . '/../src/Application/Views/editar_categoria.php';
            exit;
        }
    }
    header("Location: index.php?action=categorias");
    exit;
}

// 3. BAJA (ELIMINACIÓN)
if (isset($_GET['action']) && $_GET['action'] === 'eliminar_categoria' && isset($_SESSION['usuario_id'])) {
    if ($_SESSION['rol_id'] > 2) { die("Acceso denegado."); }
    if (isset($_GET['id'])) {
        $db = (new Database())->getConnection();
        try {
            $stmt = $db->prepare("DELETE FROM categorias WHERE id = :id");
            $stmt->execute(['id' => $_GET['id']]);
        } catch (Exception $e) {
            die("<div style='padding:20px; font-family:Arial;'><h3>❌ Error</h3><p>No se puede eliminar esta categoría porque tiene productos asociados.</p><a href='index.php?action=categorias'>Volver atrás</a></div>");
        }
    }
    header("Location: index.php?action=categorias");
    exit;
}
// ==========================================
// MÓDULO DE DISTRIBUIDORES (ABM COMPLETO)
// ==========================================

// 1. ALTA Y LECTURA
if (isset($_GET['action']) && $_GET['action'] === 'distribuidores' && isset($_SESSION['usuario_id'])) {
    if ($_SESSION['rol_id'] > 2) { die("Acceso denegado."); }
    $db = (new Database())->getConnection();

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['nuevo_distribuidor'])) {
        try {
            $stmt = $db->prepare("INSERT INTO distribuidores (nombre) VALUES (:nombre)");
            $stmt->execute(['nombre' => trim($_POST['nuevo_distribuidor'])]);
            header("Location: index.php?action=distribuidores");
            exit;
        } catch (Exception $e) {
            $error = "El distribuidor ya existe o hubo un error.";
        }
    }
    $stmt = $db->query("SELECT id, nombre FROM distribuidores ORDER BY nombre ASC");
    $listaDistribuidores = $stmt->fetchAll();
    require_once __DIR__ . '/../src/Application/Views/distribuidores.php';
    exit;
}

// 2. MODIFICACIÓN (EDICIÓN)
if (isset($_GET['action']) && $_GET['action'] === 'editar_distribuidor' && isset($_SESSION['usuario_id'])) {
    if ($_SESSION['rol_id'] > 2) { die("Acceso denegado."); }
    $db = (new Database())->getConnection();

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['nombre']) && !empty($_POST['id'])) {
        try {
            $stmt = $db->prepare("UPDATE distribuidores SET nombre = :nombre WHERE id = :id");
            $stmt->execute(['nombre' => trim($_POST['nombre']), 'id' => $_POST['id']]);
            header("Location: index.php?action=distribuidores"); 
            exit;
        } catch (Exception $e) {
            $error = "El distribuidor ya existe o hubo un error.";
        }
    }
    if (isset($_GET['id'])) {
        $stmt = $db->prepare("SELECT id, nombre FROM distribuidores WHERE id = :id");
        $stmt->execute(['id' => $_GET['id']]);
        $distActual = $stmt->fetch();
        if ($distActual) {
            require_once __DIR__ . '/../src/Application/Views/editar_distribuidor.php';
            exit;
        }
    }
    header("Location: index.php?action=distribuidores");
    exit;
}

// 3. BAJA (ELIMINACIÓN)
if (isset($_GET['action']) && $_GET['action'] === 'eliminar_distribuidor' && isset($_SESSION['usuario_id'])) {
    if ($_SESSION['rol_id'] > 2) { die("Acceso denegado."); }
    if (isset($_GET['id'])) {
        $db = (new Database())->getConnection();
        try {
            $stmt = $db->prepare("DELETE FROM distribuidores WHERE id = :id");
            $stmt->execute(['id' => $_GET['id']]);
        } catch (Exception $e) {
            die("<div style='padding:20px; font-family:Arial;'><h3>❌ Error</h3><p>No se puede eliminar este distribuidor porque ya tiene productos asociados en el catálogo.</p><a href='index.php?action=distribuidores'>Volver atrás</a></div>");
        }
    }
    header("Location: index.php?action=distribuidores");
    exit;
}

// ==========================================
// MÓDULO: MONITOR DE AUDITORÍA (ENCARGADOS)
// ==========================================

// 1. VER EL MONITOR PRINCIPAL
if (isset($_GET['action']) && $_GET['action'] === 'monitor_zonas' && isset($_SESSION['usuario_id'])) {
    if ($_SESSION['rol_id'] > 2) { die("Acceso denegado."); }
    
    $db = (new Database())->getConnection();
    $locales = $db->query("SELECT id, nombre FROM locales ORDER BY nombre ASC")->fetchAll();
    $sectores = $db->query("SELECT id, nombre FROM sectores ORDER BY nombre ASC")->fetchAll();
    
    $local_id = isset($_GET['local_id']) ? $_GET['local_id'] : null;
    $sector_id = isset($_GET['sector_id']) ? $_GET['sector_id'] : null;
    $datos_tabla = [];

    if ($local_id && $sector_id) {
        $sql = "SELECT z.id AS zona_id, z.codigo AS zona_nombre,
                    (SELECT id FROM zonas_cerradas WHERE zona_id = z.id AND local_id = :loc1 AND sector_id = :sec1 AND estado = 'cerrada' LIMIT 1) AS bloqueada,
                    (SELECT u.nombre_completo FROM zonas_cerradas zc JOIN usuarios u ON zc.usuario_id = u.id WHERE zc.zona_id = z.id AND zc.local_id = :loc2 AND zc.sector_id = :sec2 AND zc.estado = 'cerrada' LIMIT 1) AS cerrado_por,
                    (SELECT u.nombre_completo FROM zonas_cerradas zc JOIN usuarios u ON zc.usuario_id = u.id WHERE zc.zona_id = z.id AND zc.local_id = :loc3 AND zc.sector_id = :sec3 AND zc.estado = 'en_uso' LIMIT 1) AS en_uso_por,
                    (SELECT SUM(cantidad) FROM conteos c WHERE c.zona_id = z.id AND c.local_id = :loc4 AND c.sector_id = :sec4) AS total_unidades
                FROM zonas z
                WHERE (z.local_id = :loc5 AND z.sector_id = :sec5) OR z.local_id IS NULL
                ORDER BY z.codigo ASC";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([
            'loc1' => $local_id, 'sec1' => $sector_id, 
            'loc2' => $local_id, 'sec2' => $sector_id, 
            'loc3' => $local_id, 'sec3' => $sector_id, 
            'loc4' => $local_id, 'sec4' => $sector_id,
            'loc5' => $local_id, 'sec5' => $sector_id // Filtro nuevo para que no se mezclen locales
        ]);
        $datos_tabla = $stmt->fetchAll();
    }
    require_once __DIR__ . '/../src/Application/Views/monitor_zonas.php';
    exit;
}

// 2. CREAR ZONA RÁPIDA DESDE EL MONITOR
if (isset($_POST['action']) && $_POST['action'] === 'zonas_crear_rapido' && isset($_SESSION['usuario_id'])) {
    $db = (new Database())->getConnection();
    $codigo_nuevo = trim(strtoupper($_POST['nuevo_codigo'])); 
    
    // Ahora guardamos la zona ATADA al local y sector exactos
    $stmt = $db->prepare("INSERT INTO zonas (codigo, local_id, sector_id) VALUES (?, ?, ?)");
    $stmt->execute([$codigo_nuevo, $_POST['local_id'], $_POST['sector_id']]);
    
    header("Location: index.php?action=monitor_zonas&local_id=" . $_POST['local_id'] . "&sector_id=" . $_POST['sector_id']);
    exit;
}

// 3. REABRIR ZONA (SACA EL CANDADO PERO NO BORRA DATOS)
if (isset($_GET['action']) && $_GET['action'] === 'monitor_reabrir_zona' && isset($_SESSION['usuario_id'])) {
    $db = (new Database())->getConnection();
    // Solo borramos el registro del bloqueo, los datos de la tabla 'conteos' quedan intactos
    $stmt = $db->prepare("DELETE FROM zonas_cerradas WHERE local_id = ? AND sector_id = ? AND zona_id = ?");
    $stmt->execute([$_GET['local_id'], $_GET['sector_id'], $_GET['zona_id']]);
    
    header("Location: index.php?action=monitor_zonas&local_id=" . $_GET['local_id'] . "&sector_id=" . $_GET['sector_id']);
    exit;
}

// 4. VER EL DETALLE DE LO CONTADO EN UNA ZONA
if (isset($_GET['action']) && $_GET['action'] === 'monitor_detalle_zona' && isset($_SESSION['usuario_id'])) {
    $db = (new Database())->getConnection();
    
    // Agrupamos todos los conteos de ese producto en esa zona
    $stmt = $db->prepare("
        SELECT c.codigo_barras, p.descripcion, SUM(c.cantidad) as total_producto
        FROM conteos c
        LEFT JOIN productos p ON c.codigo_barras = p.codigo_barras
        WHERE c.local_id = ? AND c.sector_id = ? AND c.zona_id = ?
        GROUP BY c.codigo_barras, p.descripcion
        ORDER BY p.descripcion ASC
    ");
    $stmt->execute([$_GET['local_id'], $_GET['sector_id'], $_GET['zona_id']]);
    $detalles = $stmt->fetchAll();
    
    // Nombres para el título
    $local_nombre = $db->query("SELECT nombre FROM locales WHERE id = " . intval($_GET['local_id']))->fetchColumn();
    $zona_nombre = $db->query("SELECT codigo FROM zonas WHERE id = " . intval($_GET['zona_id']))->fetchColumn();

    require_once __DIR__ . '/../src/Application/Views/monitor_detalle.php';
    exit;
}

// 5. NUEVA RUTA: VACIAR ZONA (BORRAR CONTENIDO)
if (isset($_GET['action']) && $_GET['action'] === 'monitor_vaciar_zona' && isset($_SESSION['usuario_id'])) {
    $db = (new Database())->getConnection();
    
    // 1. Borramos todos los productos contados en esa zona específica
    $stmt = $db->prepare("DELETE FROM conteos WHERE local_id = ? AND sector_id = ? AND zona_id = ?");
    $stmt->execute([$_GET['local_id'], $_GET['sector_id'], $_GET['zona_id']]);
    
    // 2. Si estaba bloqueada, le sacamos el candado para que puedan volver a contar
    $stmt2 = $db->prepare("DELETE FROM zonas_cerradas WHERE local_id = ? AND sector_id = ? AND zona_id = ?");
    $stmt2->execute([$_GET['local_id'], $_GET['sector_id'], $_GET['zona_id']]);
    
    header("Location: index.php?action=monitor_zonas&local_id=" . $_GET['local_id'] . "&sector_id=" . $_GET['sector_id']);
    exit;
}

// ==========================================
// MÓDULO DE PIQUEO (ZEBRA TC21)
// ==========================================

// 1. Pantalla de Configuración
if (isset($_GET['action']) && $_GET['action'] === 'piqueo_config' && isset($_SESSION['usuario_id'])) {
    $db = (new Database())->getConnection();
    $error = "";
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $local = $_POST['local_id'];
        $sector = !empty($_POST['sector_id']) ? $_POST['sector_id'] : null;
        $zona = $_POST['zona_id'];

        // Revisamos cómo está el candado de esta zona en la base de datos
        $check_estado = $db->prepare("SELECT estado, usuario_id FROM zonas_cerradas WHERE local_id = ? AND sector_id <=> ? AND zona_id = ?");
        $check_estado->execute([$local, $sector, $zona]);
        $estado_zona = $check_estado->fetch();
        
        if ($estado_zona) {
            if ($estado_zona['estado'] === 'cerrada') {
                $error = "❌ Esa Zona ya fue completada y cerrada en este Sector.";
            } elseif ($estado_zona['estado'] === 'en_uso' && $estado_zona['usuario_id'] != $_SESSION['usuario_id']) {
                // CONDICIÓN CUMPLIDA: Mensaje rojo y no lo deja pasar
                $error = "❌ Esta zona se encuentra ABIERTA y en uso por otro operario.";
            } else {
                // Si el estado es "en_uso" pero el ID es el del mismo usuario, lo dejamos volver a entrar
                $max_id = $db->query("SELECT MAX(id) FROM conteos")->fetchColumn();
                $_SESSION['piqueo'] = ['local_id' => $local, 'sector_id' => $sector, 'zona_id' => $zona, 'start_id' => $max_id ? $max_id : 0];
                header("Location: index.php?action=piqueo_escaner"); exit;
            }
        } else {
            // EL CANDADO INVISIBLE: La zona está libre. La marcamos como "en_uso" en el momento de darle Comenzar.
            $stmt_uso = $db->prepare("INSERT INTO zonas_cerradas (local_id, sector_id, zona_id, usuario_id, estado) VALUES (?, ?, ?, ?, 'en_uso')");
            $stmt_uso->execute([$local, $sector, $zona, $_SESSION['usuario_id']]);
            
            $max_id = $db->query("SELECT MAX(id) FROM conteos")->fetchColumn();
            $_SESSION['piqueo'] = ['local_id' => $local, 'sector_id' => $sector, 'zona_id' => $zona, 'start_id' => $max_id ? $max_id : 0];
            header("Location: index.php?action=piqueo_escaner"); exit;
        }
    }

    $locales = $db->query("SELECT id, nombre FROM locales ORDER BY nombre ASC")->fetchAll();
    $sectores = $db->query("SELECT id, nombre FROM sectores ORDER BY nombre ASC")->fetchAll();
    
    // NUEVO: Traemos las zonas con sus dueños (local_id y sector_id)
    $zonas_db = $db->query("SELECT id, codigo, local_id, sector_id FROM zonas ORDER BY codigo ASC")->fetchAll(PDO::FETCH_ASSOC);
    $json_todas_las_zonas = json_encode($zonas_db);
    
    $zonas_cerradas = $db->query("SELECT local_id, sector_id, zona_id FROM zonas_cerradas WHERE estado = 'cerrada'")->fetchAll(PDO::FETCH_ASSOC);
    $json_cerradas = json_encode($zonas_cerradas);
    
    require_once __DIR__ . '/../src/Application/Views/piqueo_config.php';
    exit;
}

// 2. Pantalla de Batalla (Escáner)
if (isset($_GET['action']) && $_GET['action'] === 'piqueo_escaner' && isset($_SESSION['usuario_id'])) {
    if (!isset($_SESSION['piqueo'])) { header("Location: index.php?action=piqueo_config"); exit; }
    
    $db = (new Database())->getConnection();
    $alerta_sonido = false;
    $mensaje_estado = "";

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['codigo_barras'])) {
        $codigo = trim(strtoupper($_POST['codigo_barras'])); // Lo pasamos a mayúsculas por si acaso
        $cantidad = !empty($_POST['cantidad']) ? $_POST['cantidad'] : 1;

       // 1. ¿ES UN CÓDIGO DE ZONA? (SALTO MÁGICO CON CONFIRMACIÓN)
        // Busca la zona asegurando que pertenezca al local/sector en el que estoy parado
        $check_zona = $db->prepare("SELECT id, codigo FROM zonas WHERE codigo = ? AND (local_id = ? OR local_id IS NULL) AND (sector_id <=> ? OR sector_id IS NULL) LIMIT 1");
        $check_zona->execute([$codigo, $_SESSION['piqueo']['local_id'], $_SESSION['piqueo']['sector_id']]);
        $zona_escaneada = $check_zona->fetch();

        if ($zona_escaneada) {
            $nueva_zona_id = $zona_escaneada['id'];
            
            if ($nueva_zona_id == $_SESSION['piqueo']['zona_id']) {
                $mensaje_estado = "<div class='success-card'>Ya estás dentro de la Zona: {$zona_escaneada['codigo']}</div>";
            } else {
                // Verificamos si la NUEVA zona está disponible ANTES de preguntar
                $check_estado = $db->prepare("SELECT estado, usuario_id FROM zonas_cerradas WHERE local_id = ? AND sector_id <=> ? AND zona_id = ?");
                $check_estado->execute([$_SESSION['piqueo']['local_id'], $_SESSION['piqueo']['sector_id'], $nueva_zona_id]);
                $estado_nueva = $check_estado->fetch();

                if ($estado_nueva && $estado_nueva['estado'] === 'cerrada') {
                    $alerta_sonido = true;
                    $mensaje_estado = "<div class='error-card'>❌ La zona {$zona_escaneada['codigo']} ya fue CERRADA.</div>";
                } elseif ($estado_nueva && $estado_nueva['estado'] === 'en_uso' && $estado_nueva['usuario_id'] != $_SESSION['usuario_id']) {
                    $alerta_sonido = true;
                    $mensaje_estado = "<div class='error-card'>❌ La zona {$zona_escaneada['codigo']} está en uso por otro.</div>";
                } else {
                    // LA MAGIA DE LA CONFIRMACIÓN: Le mostramos una tarjeta amarilla con dos botones
                    $alerta_sonido = true; // Hacemos que suene para que mire la pantalla
                    $mensaje_estado = "
                    <div style='background: #ffb300; padding: 15px; border-radius: 5px; border-left: 5px solid #f57c00; margin-bottom: 20px; color: #000;'>
                        <div style='font-size: 18px; font-weight: bold; margin-bottom: 10px;'>⚠️ ¿Cambiar de Zona?</div>
                        <p style='margin: 0 0 15px 0; font-size: 15px;'>Escaneaste la zona <strong>{$zona_escaneada['codigo']}</strong>. ¿Querés terminar la actual y saltar a esta?</p>
                        <div style='display: flex; gap: 10px;'>
                            <a href='index.php?action=piqueo_escaner' style='flex: 1; padding: 15px; text-align: center; background: #fff; color: #000; text-decoration: none; border-radius: 5px; font-weight: bold; border: 1px solid #ccc;'>Cancelar</a>
                            
                            <a href='index.php?action=piqueo_ejecutar_salto&zona_id={$nueva_zona_id}' style='flex: 1; padding: 15px; text-align: center; background: #d32f2f; color: #fff; text-decoration: none; border-radius: 5px; font-weight: bold;'>SÍ, SALTAR</a>
                        </div>
                    </div>";
                }
            }
        } else {
            // 2. NO ES ZONA, ES UN PRODUCTO NORMAL
            $stmt = $db->prepare("INSERT INTO conteos (local_id, sector_id, zona_id, usuario_id, codigo_barras, cantidad) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$_SESSION['piqueo']['local_id'], $_SESSION['piqueo']['sector_id'], $_SESSION['piqueo']['zona_id'], $_SESSION['usuario_id'], $codigo, $cantidad]);

            $check_prod = $db->prepare("SELECT descripcion FROM productos WHERE codigo_barras = ?");
            $check_prod->execute([$codigo]);
            $prod = $check_prod->fetch();

            if ($prod) {
                // Calculamos total
                $stmt_item = $db->prepare("SELECT SUM(cantidad) FROM conteos WHERE local_id = ? AND sector_id <=> ? AND zona_id = ? AND codigo_barras = ?");
                $stmt_item->execute([$_SESSION['piqueo']['local_id'], $_SESSION['piqueo']['sector_id'], $_SESSION['piqueo']['zona_id'], $codigo]);
                $total_item = number_format($stmt_item->fetchColumn(), 2, '.', '');

                $mensaje_estado = "
                <div class='success-card'>
                    <div style='font-size: 18px; font-weight: bold; margin-bottom: 8px;'>" . htmlspecialchars($prod['descripcion']) . "</div>
                    <div style='display: flex; justify-content: space-between; align-items: center;'>
                        <span style='font-size: 26px; color: #fff;'>Total: <strong>{$total_item}</strong></span>
                        <span style='font-size: 16px; color: #a5d6a7;'>(+{$cantidad}) <br><small>Cód: {$codigo}</small></span>
                    </div>
                </div>";
            } else {
                $alerta_sonido = true;
                $mensaje_estado = "
                <div class='error-card'>
                    <div style='font-size: 18px; margin-bottom: 8px;'>⚠️ PRODUCTO DESCONOCIDO</div>
                    <div style='display: flex; justify-content: space-between; align-items: center;'>
                        <span style='font-size: 26px; color: #fff;'>Guardado</span>
                        <span style='font-size: 16px; color: #ffcdd2;'>(+{$cantidad}) <br><small>Cód: {$codigo}</small></span>
                    </div>
                </div>";
            }
        }
    }

    // NUEVO: Atrapamos el mensaje si acaba de saltar de zona
    if (isset($_GET['jump'])) {
        $mensaje_estado = "<div class='success-card' style='background: #1565c0; border-left-color: #64b5f6;'>🚀 Salto exitoso. ¡Bienvenido a la Zona " . htmlspecialchars($_GET['jump']) . "!</div>";
    }

    $local_nombre = $db->query("SELECT nombre FROM locales WHERE id = " . $_SESSION['piqueo']['local_id'])->fetchColumn();
    $zona_nombre = $db->query("SELECT codigo FROM zonas WHERE id = " . $_SESSION['piqueo']['zona_id'])->fetchColumn();
    $sector_nombre = null;
    if ($_SESSION['piqueo']['sector_id']) {
        $sector_nombre = $db->query("SELECT nombre FROM sectores WHERE id = " . $_SESSION['piqueo']['sector_id'])->fetchColumn();
    }
    
    // --- NUEVO CÁLCULO DEL TOTAL (Aislado por Local, Zona Y Sector) ---
    $stmt_total = $db->prepare("SELECT SUM(c.cantidad) FROM conteos c 
                                INNER JOIN productos p ON c.codigo_barras = p.codigo_barras 
                                WHERE c.zona_id = ? AND c.local_id = ? AND c.sector_id <=> ?");
    $stmt_total->execute([
        $_SESSION['piqueo']['zona_id'], 
        $_SESSION['piqueo']['local_id'], 
        $_SESSION['piqueo']['sector_id']
    ]);
    
    $total_zona = $stmt_total->fetchColumn();
    $total_zona = $total_zona ? number_format($total_zona, 2, '.', '') : "0.00";
    // ------------------------------------------------------------------

    require_once __DIR__ . '/../src/Application/Views/piqueo_escaner.php';
    exit;
}

// 3. NUEVA RUTA: SALIR SIN GUARDAR (CON LIMPIEZA POR CHECKPOINT)
if (isset($_GET['action']) && $_GET['action'] === 'piqueo_salir_zona' && isset($_SESSION['usuario_id'])) {
    if (isset($_SESSION['piqueo'])) {
        $db = (new Database())->getConnection();
        
        // 1. Borramos la basura escaneada
        $stmt = $db->prepare("DELETE FROM conteos WHERE local_id = ? AND sector_id <=> ? AND zona_id = ? AND usuario_id = ? AND id > ?");
        $stmt->execute([$_SESSION['piqueo']['local_id'], $_SESSION['piqueo']['sector_id'], $_SESSION['piqueo']['zona_id'], $_SESSION['usuario_id'], $_SESSION['piqueo']['start_id']]);
        
        // 2. ROMPEMOS EL CANDADO INVISIBLE para que quede libre otra vez
        $stmt_unlock = $db->prepare("DELETE FROM zonas_cerradas WHERE local_id = ? AND sector_id <=> ? AND zona_id = ? AND usuario_id = ? AND estado = 'en_uso'");
        $stmt_unlock->execute([$_SESSION['piqueo']['local_id'], $_SESSION['piqueo']['sector_id'], $_SESSION['piqueo']['zona_id'], $_SESSION['usuario_id']]);
        
        unset($_SESSION['piqueo']);
    }
    header("Location: index.php?action=piqueo_config");
    exit;
}

// 4. Terminar y Bloquear Zona (CAMBIA DE EN_USO A CERRADA)
if (isset($_GET['action']) && $_GET['action'] === 'piqueo_terminar_zona' && isset($_SESSION['usuario_id'])) {
    if (isset($_SESSION['piqueo']['zona_id'])) {
        $db = (new Database())->getConnection();
        
        // Convertimos el candado invisible en un candado real
        $stmt = $db->prepare("UPDATE zonas_cerradas SET estado = 'cerrada' WHERE local_id = ? AND sector_id <=> ? AND zona_id = ? AND usuario_id = ?");
        $stmt->execute([$_SESSION['piqueo']['local_id'], $_SESSION['piqueo']['sector_id'], $_SESSION['piqueo']['zona_id'], $_SESSION['usuario_id']]);
        
        unset($_SESSION['piqueo']);
    }
    header("Location: index.php?action=piqueo_config");
    exit;
}

// 5. NUEVA RUTA: VISUALIZAR LO ESCANEADO EN LA SESIÓN ACTUAL
if (isset($_GET['action']) && $_GET['action'] === 'piqueo_visualizar' && isset($_SESSION['usuario_id'])) {
    if (!isset($_SESSION['piqueo'])) { header("Location: index.php?action=piqueo_config"); exit; }

    $db = (new Database())->getConnection();

    // Traemos la lista exacta de lo que escaneó, ordenado del último al primero
    $stmt = $db->prepare("
        SELECT c.id, c.codigo_barras, c.cantidad, p.descripcion, p.sku 
        FROM conteos c
        LEFT JOIN productos p ON c.codigo_barras = p.codigo_barras
        WHERE c.local_id = ? AND c.sector_id <=> ? AND c.zona_id = ? AND c.usuario_id = ? AND c.id > ?
        ORDER BY c.id DESC
    ");
    $stmt->execute([
        $_SESSION['piqueo']['local_id'],
        $_SESSION['piqueo']['sector_id'],
        $_SESSION['piqueo']['zona_id'],
        $_SESSION['usuario_id'],
        $_SESSION['piqueo']['start_id']
    ]);
    
    $lista_escaneados = $stmt->fetchAll();
    $zona_nombre = $db->query("SELECT codigo FROM zonas WHERE id = " . $_SESSION['piqueo']['zona_id'])->fetchColumn();

    require_once __DIR__ . '/../src/Application/Views/piqueo_visualizar.php';
    exit;
}

// 6. NUEVA RUTA: BORRAR UN ESCANEO ESPECÍFICO
if (isset($_GET['action']) && $_GET['action'] === 'piqueo_borrar_conteo' && isset($_SESSION['usuario_id'])) {
    if (isset($_GET['id']) && isset($_SESSION['piqueo'])) {
        $db = (new Database())->getConnection();
        
        // Borramos el registro exacto, pero por seguridad verificamos que sea de SU zona y SU usuario
        $stmt = $db->prepare("DELETE FROM conteos WHERE id = ? AND usuario_id = ? AND zona_id = ?");
        $stmt->execute([$_GET['id'], $_SESSION['usuario_id'], $_SESSION['piqueo']['zona_id']]);
    }
    // Lo devolvemos a la misma lista para que vea que se borró
    header("Location: index.php?action=piqueo_visualizar");
    exit;
}

// 7. NUEVA RUTA: EJECUTAR EL SALTO DE ZONA CONFIRMADO
if (isset($_GET['action']) && $_GET['action'] === 'piqueo_ejecutar_salto' && isset($_SESSION['usuario_id'])) {
    if (isset($_GET['zona_id']) && isset($_SESSION['piqueo'])) {
        $db = (new Database())->getConnection();
        $nueva_zona_id = intval($_GET['zona_id']);

        // Buscamos el nombre de la nueva zona para darle la bienvenida
        $codigo_nueva_zona = $db->query("SELECT codigo FROM zonas WHERE id = " . $nueva_zona_id)->fetchColumn();

        if ($codigo_nueva_zona) {
            // A) Cerramos y bloqueamos la zona vieja
            $stmt_close = $db->prepare("UPDATE zonas_cerradas SET estado = 'cerrada' WHERE local_id = ? AND sector_id <=> ? AND zona_id = ? AND usuario_id = ?");
            $stmt_close->execute([$_SESSION['piqueo']['local_id'], $_SESSION['piqueo']['sector_id'], $_SESSION['piqueo']['zona_id'], $_SESSION['usuario_id']]);
            
            // B) Revisamos si la nueva tiene candado. Si no, se lo ponemos.
            $check_estado = $db->prepare("SELECT id FROM zonas_cerradas WHERE local_id = ? AND sector_id <=> ? AND zona_id = ?");
            $check_estado->execute([$_SESSION['piqueo']['local_id'], $_SESSION['piqueo']['sector_id'], $nueva_zona_id]);
            
            if (!$check_estado->fetch()) {
                $stmt_uso = $db->prepare("INSERT INTO zonas_cerradas (local_id, sector_id, zona_id, usuario_id, estado) VALUES (?, ?, ?, ?, 'en_uso')");
                $stmt_uso->execute([$_SESSION['piqueo']['local_id'], $_SESSION['piqueo']['sector_id'], $nueva_zona_id, $_SESSION['usuario_id']]);
            }
            
            // C) Actualizamos la memoria del sistema (viaje en el tiempo)
            $_SESSION['piqueo']['zona_id'] = $nueva_zona_id;
            $max_id = $db->query("SELECT MAX(id) FROM conteos")->fetchColumn();
            $_SESSION['piqueo']['start_id'] = $max_id ? $max_id : 0;
            
            // Redireccionamos con éxito
            header("Location: index.php?action=piqueo_escaner&jump=" . urlencode($codigo_nueva_zona));
            exit;
        }
    }
    header("Location: index.php?action=piqueo_escaner");
    exit;
}
// ==========================================
// ==========================================
// ==========================================
// MÓDULO DE INVENTARIO (CIERRE Y EXPORTACIÓN)
// ==========================================

// 1. Pantalla principal de Inventario
if (isset($_GET['action']) && $_GET['action'] === 'inventario_menu' && isset($_SESSION['usuario_id'])) {
    if ($_SESSION['rol_id'] > 2) { die("Acceso denegado."); }
    
    $db = (new Database())->getConnection();
    // Traemos solo los locales que tienen al menos 1 producto contado
    $locales_activos = $db->query("SELECT DISTINCT l.id, l.nombre FROM locales l INNER JOIN conteos c ON l.id = c.local_id ORDER BY l.nombre ASC")->fetchAll();
    
    require_once __DIR__ . '/../src/Application/Views/inventario_menu.php';
    exit;
}

// 2. Acción: Generar y Descargar CSV
if (isset($_POST['action']) && $_POST['action'] === 'inventario_exportar' && isset($_SESSION['usuario_id'])) {
    if ($_SESSION['rol_id'] > 2) { die("Acceso denegado."); }
    
    $db = (new Database())->getConnection();
    $local_id = intval($_POST['local_id']);
    $tipo_reporte = $_POST['tipo_reporte'] ?? 'detallado';
    
    $local_nombre = $db->query("SELECT nombre FROM locales WHERE id = $local_id")->fetchColumn();
    $nombre_limpio = preg_replace('/[^a-zA-Z0-9]+/', '_', strtolower($local_nombre)); 
    
    $etiqueta_tipo = ($tipo_reporte === 'unificado') ? '_Unificado' : (($tipo_reporte === 'datos') ? '_Importacion' : '_Detallado');
    $filename = date('Ymd') . '_' . $nombre_limpio . $etiqueta_tipo . '_Macaro.csv';
    
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    $output = fopen('php://output', 'w');
    fputs($output, "\xEF\xBB\xBF"); // Truco BOM para tildes en Excel

    if ($tipo_reporte === 'unificado') {
        // ========================================================
        // OPCIÓN 2: UNIFICADO (Suma total del local, sin Zonas)
        // ========================================================
        fputcsv($output, ['Código de Barras', 'SKU', 'Nombre del Producto', 'Marca', 'Cantidad Total', 'Precio Compra', 'Precio Venta', 'Categoría', 'Distribuidor'], ';');
        
        $sql = "SELECT 
                    c.codigo_barras, p.sku, p.descripcion, p.marca,
                    SUM(c.cantidad) as cantidad_total,
                    p.precio_compra, p.precio_venta, cat.nombre as categoria_nombre, dist.nombre as distribuidor_nombre
                FROM conteos c
                LEFT JOIN productos p ON c.codigo_barras = p.codigo_barras
                LEFT JOIN categorias cat ON p.categoria_id = cat.id
                LEFT JOIN distribuidores dist ON p.distribuidor_id = dist.id
                WHERE c.local_id = ?
                GROUP BY c.codigo_barras, p.sku, p.descripcion, p.marca, p.precio_compra, p.precio_venta, cat.nombre, dist.nombre
                ORDER BY p.descripcion ASC";
                
        $stmt = $db->prepare($sql);
        $stmt->execute([$local_id]);
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $codigo_excel = '="' . $row['codigo_barras'] . '"';
            $sku = !empty($row['sku']) ? $row['sku'] : '-';
            $marca = !empty($row['marca']) ? $row['marca'] : '-';
            $precio_c = !empty($row['precio_compra']) ? $row['precio_compra'] : '0.00';
            $precio_v = !empty($row['precio_venta']) ? $row['precio_venta'] : '0.00';
            $categoria = !empty($row['categoria_nombre']) ? $row['categoria_nombre'] : 'Sin Categoría';
            $distribuidor = !empty($row['distribuidor_nombre']) ? $row['distribuidor_nombre'] : 'Sin Distribuidor';
            
            fputcsv($output, [$codigo_excel, $sku, $row['descripcion'], $marca, number_format($row['cantidad_total'], 2, '.', ''), $precio_c, $precio_v, $categoria, $distribuidor], ';'); 
        }

    } elseif ($tipo_reporte === 'datos') {
        // ========================================================
        // OPCIÓN 3: DATOS CRUDOS (Para importación)
        // Pedido: Código de Barras, SKU, Cantidad, Precio Venta, Precio Compra, Categoría, Marca
        // ========================================================
        fputcsv($output, ['Código de Barras', 'SKU', 'Cantidad', 'Precio Venta', 'Precio Compra', 'Categoría', 'Marca'], ';');
        
        $sql = "SELECT 
                    c.codigo_barras, 
                    p.sku, 
                    SUM(c.cantidad) as cantidad_total, 
                    p.precio_venta, 
                    p.precio_compra, 
                    cat.nombre as categoria_nombre, 
                    p.marca 
                FROM conteos c
                LEFT JOIN productos p ON c.codigo_barras = p.codigo_barras
                LEFT JOIN categorias cat ON p.categoria_id = cat.id
                WHERE c.local_id = ? 
                GROUP BY c.codigo_barras, p.sku, p.precio_venta, p.precio_compra, cat.nombre, p.marca";
                
        $stmt = $db->prepare($sql);
        $stmt->execute([$local_id]);
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $codigo_excel = '="' . $row['codigo_barras'] . '"'; 
            $sku = !empty($row['sku']) ? $row['sku'] : '-';
            $marca = !empty($row['marca']) ? $row['marca'] : '-';
            $precio_v = !empty($row['precio_venta']) ? $row['precio_venta'] : '0.00';
            $precio_c = !empty($row['precio_compra']) ? $row['precio_compra'] : '0.00';
            $categoria = !empty($row['categoria_nombre']) ? $row['categoria_nombre'] : 'Sin Categoría';

            fputcsv($output, [
                $codigo_excel, 
                $sku, 
                number_format($row['cantidad_total'], 2, '.', ''), 
                $precio_v, 
                $precio_c, 
                $categoria, 
                $marca
            ], ';');
        }

    } else {
        // ========================================================
        // OPCIÓN 1: DETALLADO (Separado por Zona y Sector)
        // ========================================================
        fputcsv($output, ['Código de Barras', 'SKU', 'Nombre del Producto', 'Marca', 'Cantidad Total', 'Sector', 'Zona', 'Precio Compra', 'Precio Venta', 'Categoría', 'Distribuidor'], ';');
        
        $sql = "SELECT 
                    c.codigo_barras, p.sku, p.descripcion, p.marca,
                    SUM(c.cantidad) as cantidad_total,
                    s.nombre as sector_nombre, z.codigo as zona_codigo,
                    p.precio_compra, p.precio_venta, cat.nombre as categoria_nombre, dist.nombre as distribuidor_nombre
                FROM conteos c
                LEFT JOIN productos p ON c.codigo_barras = p.codigo_barras
                LEFT JOIN zonas z ON c.zona_id = z.id
                LEFT JOIN sectores s ON c.sector_id = s.id
                LEFT JOIN categorias cat ON p.categoria_id = cat.id
                LEFT JOIN distribuidores dist ON p.distribuidor_id = dist.id
                WHERE c.local_id = ?
                GROUP BY c.zona_id, c.sector_id, c.codigo_barras, p.sku, p.descripcion, p.marca, z.codigo, s.nombre, p.precio_compra, p.precio_venta, cat.nombre, dist.nombre
                ORDER BY s.nombre ASC, z.codigo ASC, p.descripcion ASC";
                
        $stmt = $db->prepare($sql);
        $stmt->execute([$local_id]);
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $codigo_excel = '="' . $row['codigo_barras'] . '"';
            $sku = !empty($row['sku']) ? $row['sku'] : '-';
            $marca = !empty($row['marca']) ? $row['marca'] : '-';
            $precio_c = !empty($row['precio_compra']) ? $row['precio_compra'] : '0.00';
            $precio_v = !empty($row['precio_venta']) ? $row['precio_venta'] : '0.00';
            $categoria = !empty($row['categoria_nombre']) ? $row['categoria_nombre'] : 'Sin Categoría';
            $distribuidor = !empty($row['distribuidor_nombre']) ? $row['distribuidor_nombre'] : 'Sin Distribuidor';
            
            fputcsv($output, [$codigo_excel, $sku, $row['descripcion'], $marca, number_format($row['cantidad_total'], 2, '.', ''), $row['sector_nombre'], $row['zona_codigo'], $precio_c, $precio_v, $categoria, $distribuidor], ';'); 
        }
    }
    
    fclose($output);
    exit;
}
// ==========================================
// MÓDULO DE PRODUCTOS (REFACTORIZADO)
// ==========================================

// 1. EL SUBMENÚ
if (isset($_GET['action']) && $_GET['action'] === 'productos' && isset($_SESSION['usuario_id'])) {
    if ($_SESSION['rol_id'] > 2) { die("Acceso denegado."); }
    require_once __DIR__ . '/../src/Application/Views/productos_menu.php';
    exit;
}

// 2. PANTALLA DE ALTA RÁPIDA
if (isset($_GET['action']) && $_GET['action'] === 'productos_alta' && isset($_SESSION['usuario_id'])) {
    if ($_SESSION['rol_id'] > 2) { die("Acceso denegado."); }
    $db = (new Database())->getConnection();

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['codigo_barras'])) {
        try {
            $sql = "INSERT INTO productos (codigo_barras, descripcion, marca, categoria_id, distribuidor_id) 
                    VALUES (:codigo_barras, :descripcion, :marca, :categoria_id, :distribuidor_id)";
            $stmt = $db->prepare($sql);
            $stmt->execute([
                'codigo_barras' => trim($_POST['codigo_barras']),
                'descripcion' => trim($_POST['descripcion']),
                'marca' => !empty($_POST['marca']) ? trim($_POST['marca']) : null,
                'categoria_id' => !empty($_POST['categoria_id']) ? $_POST['categoria_id'] : null,
                'distribuidor_id' => !empty($_POST['distribuidor_id']) ? $_POST['distribuidor_id'] : null
            ]);
            $exito = "Producto guardado. ¡Escaneá el siguiente!";
        } catch (Exception $e) { $error = "Error o código duplicado."; }
    }

    $listaCategorias = $db->query("SELECT id, nombre FROM categorias ORDER BY nombre ASC")->fetchAll();
    $listaDistribuidores = $db->query("SELECT id, nombre FROM distribuidores ORDER BY nombre ASC")->fetchAll();
    require_once __DIR__ . '/../src/Application/Views/productos_alta.php';
    exit;
}

// 3. PANTALLA DE GESTIÓN (BUSCADOR + LISTA LIMITADA)
if (isset($_GET['action']) && $_GET['action'] === 'productos_gestion' && isset($_SESSION['usuario_id'])) {
    if ($_SESSION['rol_id'] > 2) { die("Acceso denegado."); }
    $db = (new Database())->getConnection();

    $termino = isset($_GET['busqueda']) ? "%" . $_GET['busqueda'] . "%" : null;

    if ($termino) {
        $sql = "SELECT p.*, c.nombre as categoria_nombre FROM productos p 
                LEFT JOIN categorias c ON p.categoria_id = c.id 
                WHERE p.codigo_barras LIKE :t OR p.descripcion LIKE :t ORDER BY p.id DESC LIMIT 20";
        $stmt = $db->prepare($sql);
        $stmt->execute(['t' => $termino]);
        $listaProductos = $stmt->fetchAll();
    } else {
        // Por defecto, solo los últimos 10 para que cargue instantáneo
        $listaProductos = $db->query("SELECT p.*, c.nombre as categoria_nombre FROM productos p 
                                     LEFT JOIN categorias c ON p.categoria_id = c.id 
                                     ORDER BY p.id DESC LIMIT 10")->fetchAll();
    }

    require_once __DIR__ . '/../src/Application/Views/productos_gestion.php';
    exit;
}

// ==========================================
//  LOGIN Y PANEL PRINCIPAL
// ==========================================
// 3. Si el usuario YA está logueado, le mostramos el panel principal
if (isset($_SESSION['usuario_id'])) {
    require_once __DIR__ . '/../src/Application/Views/dashboard.php';
    exit; // Detenemos la ejecución para que no se muestre el login
}

// 4. Lógica para procesar el formulario cuando hacen clic en "Ingresar"
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuarioInput = trim($_POST['usuario'] ?? '');
    $passwordInput = $_POST['password'] ?? '';

    try {
        $db = (new Database())->getConnection();
        
        // Buscamos al usuario en la base de datos (y nos aseguramos de que esté activo)
        $stmt = $db->prepare("SELECT id, nombre_completo, password, rol_id FROM usuarios WHERE usuario = :usuario AND estado = 1");
        $stmt->execute(['usuario' => $usuarioInput]);
        $user = $stmt->fetch();

        // Si el usuario existe y la contraseña encriptada coincide con la que escribió
        if ($user && password_verify($passwordInput, $user['password'])) {
            // Guardamos sus datos en la "credencial" de la sesión
            $_SESSION['usuario_id'] = $user['id'];
            $_SESSION['rol_id'] = $user['rol_id'];
            $_SESSION['nombre_completo'] = $user['nombre_completo'];
            
            // Recargamos la página para que entre al panel
            header("Location: index.php");
            exit;
        } else {
            $error = "Usuario o contraseña incorrectos.";
        }
    } catch (Exception $e) {
        $error = "Error del sistema al intentar conectar.";
    }
}

// 5. Si no está logueado y no mandó el formulario, le mostramos la pantalla de login
require_once __DIR__ . '/../src/Application/Views/login.php';