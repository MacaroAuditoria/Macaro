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

// 1. ALTA Y LECTURA DE CATEGORÍAS
if (isset($_GET['action']) && $_GET['action'] === 'categorias' && isset($_SESSION['usuario_id'])) {
    if ($_SESSION['rol_id'] > 2) { die("Acceso denegado."); }
    $db = (new Database())->getConnection();
    $error = null;

    // Si mandan el formulario para crear una nueva
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
    
    // Cargamos la lista para mostrarla
    $listaCategorias = $db->query("SELECT id, nombre FROM categorias ORDER BY id DESC")->fetchAll();
    
    require_once __DIR__ . '/../src/Application/Views/categorias.php';
    exit;
}

// 2. EDICIÓN DE CATEGORÍAS
if (isset($_GET['action']) && $_GET['action'] === 'editar_categoria' && isset($_SESSION['usuario_id'])) {
    if ($_SESSION['rol_id'] > 2) { die("Acceso denegado."); }
    $db = (new Database())->getConnection();
    $error = null;

    // A. Cuando el usuario hace clic en "Guardar Cambios" (Envío del formulario POST)
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['nombre']) && !empty($_POST['id'])) {
        try {
            $stmt = $db->prepare("UPDATE categorias SET nombre = :nombre WHERE id = :id");
            $stmt->execute(['nombre' => trim($_POST['nombre']), 'id' => $_POST['id']]);
            // Volvemos a la lista de categorías
            header("Location: index.php?action=categorias"); 
            exit;
        } catch (Exception $e) {
            $error = "Hubo un error al actualizar la categoría o el nombre ya existe.";
        }
    }
    
    // B. Cuando el usuario entra a la pantalla para ver el formulario (GET)
    if (isset($_GET['id'])) {
        $stmt = $db->prepare("SELECT id, nombre FROM categorias WHERE id = :id");
        $stmt->execute(['id' => intval($_GET['id'])]);
        $catActual = $stmt->fetch();
        
        if ($catActual) {
            // Cargamos la vista correcta para editar la categoría
            require_once __DIR__ . '/../src/Application/Views/editar_categoria.php';
            exit;
        }
    }
    
    // Si no mandaron ID o no existe, lo pateamos de vuelta al menú
    header("Location: index.php?action=categorias");
    exit;
}

// 3. ELIMINAR CATEGORÍA
if (isset($_GET['action']) && $_GET['action'] === 'eliminar_categoria' && isset($_SESSION['usuario_id'])) {
    if ($_SESSION['rol_id'] > 2) { die("Acceso denegado."); }
    $db = (new Database())->getConnection();

    if (isset($_GET['id'])) {
        try {
            $stmt = $db->prepare("DELETE FROM categorias WHERE id = :id");
            $stmt->execute(['id' => intval($_GET['id'])]);
        } catch (Exception $e) {
            // Si la categoría ya tiene productos asociados, la base de datos va a bloquear el borrado 
            // por seguridad (Clave Foránea). Acá se ataja ese error silenciosamente.
        }
    }
    header("Location: index.php?action=categorias");
    exit;
}
// ==========================================
// MÓDULO: GESTIÓN DE ACTAS Y RENDIMIENTO (ADMIN)
// ==========================================
if (isset($_GET['action']) && in_array($_GET['action'], ['actas_buscar', 'acta_ver', 'progreso_encargado'])) {
    if ($_SESSION['rol_id'] != 1) { die("Acceso denegado. Solo Administradores."); }
    $db = (new Database())->getConnection();

    // 1. BUSCADOR DE ACTAS
    if ($_GET['action'] === 'actas_buscar') {
        $filtro_local = $_GET['local_id'] ?? '';
        $filtro_encargado = $_GET['encargado_id'] ?? '';

        $query = "SELECT e.*, l.nombre as local_nombre, u.nombre_completo as encargado_nombre 
                  FROM actas e 
                  JOIN locales l ON e.local_id = l.id 
                  JOIN usuarios u ON e.encargado_id = u.id 
                  WHERE e.completada = 1";
        $params = [];

        if ($filtro_local) { $query .= " AND e.local_id = ?"; $params[] = $filtro_local; }
        if ($filtro_encargado) { $query .= " AND e.encargado_id = ?"; $params[] = $filtro_encargado; }
        $query .= " ORDER BY e.fecha_cierre DESC";

        $stmt = $db->prepare($query);
        $stmt->execute($params);
        $actas = $stmt->fetchAll();

        // En vez de: SELECT id, nombre FROM locales
        // Tiene que ser:
        $locales = $db->query("SELECT id, nombre FROM locales WHERE estado = 1 ORDER BY nombre")->fetchAll();
       $encargados = $db->query("SELECT id, nombre_completo FROM usuarios WHERE rol_id = 2 AND estado = 1 ORDER BY nombre_completo")->fetchAll();
        require_once __DIR__ . '/../src/Application/Views/actas_buscar.php';
        exit;
    }

    // 2. VER UN ACTA ESPECÍFICA (SOLO LECTURA)
    if ($_GET['action'] === 'acta_ver') {
        $stmt = $db->prepare("SELECT e.*, l.nombre as local_nombre, u.nombre_completo as encargado_nombre 
                              FROM actas e 
                              JOIN locales l ON e.local_id = l.id 
                              JOIN usuarios u ON e.encargado_id = u.id 
                              WHERE e.id = ?");
        $stmt->execute([$_GET['id']]);
        $acta = $stmt->fetch();
        require_once __DIR__ . '/../src/Application/Views/acta_ver.php';
        exit;
    }

    // 3. PROGRESO Y GRÁFICO DEL ENCARGADO
    if ($_GET['action'] === 'progreso_encargado') {
        $encargados = $db->query("SELECT id, nombre_completo FROM usuarios ORDER BY nombre_completo")->fetchAll();
        $datos_grafico = null;
        $encargado_seleccionado = null;

        if (!empty($_GET['encargado_id'])) {
            $stmt = $db->prepare("SELECT 
                AVG(estrellas_puntualidad) as prom_puntualidad,
                AVG(estrellas_organizacion) as prom_organizacion,
                AVG(estrellas_prolijidad) as prom_prolijidad,
                AVG(estrellas_trato) as prom_trato,
                COUNT(id) as total_actas
                FROM actas
                WHERE encargado_id = ? AND completada = 1");
            $stmt->execute([$_GET['encargado_id']]);
            $datos_grafico = $stmt->fetch();
            
            $stmt_enc = $db->prepare("SELECT nombre_completo FROM usuarios WHERE id = ?");
            $stmt_enc->execute([$_GET['encargado_id']]);
            $encargado_seleccionado = $stmt_enc->fetchColumn();
        }
        
        require_once __DIR__ . '/../src/Application/Views/progreso_encargado.php';
        exit;
    }
}

// ==========================================
// MÓDULO: MONITOR DE AUDITORÍA (ENCARGADOS)
// ==========================================
// 1. VER EL MONITOR PRINCIPAL
if (isset($_GET['action']) && $_GET['action'] === 'monitor_zonas') {
    if ($_SESSION['rol_id'] > 2) { die("Acceso denegado."); }
    $db = (new Database())->getConnection();

    $locales = $db->query("SELECT id, nombre FROM locales WHERE estado = 1 ORDER BY nombre")->fetchAll();
    
    // 🔥 MAGIA 1: Traemos también el local_id
    $sectores = $db->query("SELECT id, nombre, local_id FROM sectores ORDER BY nombre ASC")->fetchAll();
    $json_sectores = json_encode($sectores); // Lo empaquetamos para JavaScript
    
    $local_id = isset($_GET['local_id']) ? $_GET['local_id'] : null;
    $sector_id = isset($_GET['sector_id']) ? $_GET['sector_id'] : null;
    $datos_tabla = [];
    
    if ($local_id && $sector_id) {
        // CORRECCIÓN: Ahora confiamos 100% en el ID único de la zona (z.id)
        $sql = "SELECT z.id AS zona_id, z.codigo AS zona_nombre,
                    (SELECT id FROM zonas_cerradas WHERE zona_id = z.id AND estado = 'cerrada' LIMIT 1) AS bloqueada,
                    (SELECT u.nombre_completo FROM zonas_cerradas zc JOIN usuarios u ON zc.usuario_id = u.id WHERE zc.zona_id = z.id AND zc.estado = 'cerrada' LIMIT 1) AS cerrado_por,
                    (SELECT u.nombre_completo FROM zonas_cerradas zc JOIN usuarios u ON zc.usuario_id = u.id WHERE zc.zona_id = z.id AND zc.estado = 'en_uso' LIMIT 1) AS en_uso_por,
                    (SELECT SUM(cantidad) FROM conteos c WHERE c.zona_id = z.id) AS total_unidades
                FROM zonas z
                WHERE z.local_id = :loc_id AND z.sector_id = :sec_id
                ORDER BY z.codigo ASC";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([
            'loc_id' => $local_id, 
            'sec_id' => $sector_id
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
    $local_id = $_POST['local_id'];
    $sector_id = $_POST['sector_id'];
    
    // 1. VERIFICACIÓN: Buscamos si ya existe esa zona en ese local y sector
    $check_stmt = $db->prepare("SELECT id FROM zonas WHERE codigo = ? AND local_id = ? AND sector_id = ?");
    $check_stmt->execute([$codigo_nuevo, $local_id, $sector_id]);
    
    if ($check_stmt->fetch()) {
        // Si ya existe, cancelamos la creación y redirigimos con un mensaje de error por URL
        header("Location: index.php?action=monitor_zonas&local_id=" . $local_id . "&sector_id=" . $sector_id . "&error=zona_duplicada");
        exit;
    }
    
    // 2. Si pasó la prueba (no existe), guardamos la zona
    $stmt = $db->prepare("INSERT INTO zonas (codigo, local_id, sector_id) VALUES (?, ?, ?)");
    $stmt->execute([$codigo_nuevo, $local_id, $sector_id]);
    
    header("Location: index.php?action=monitor_zonas&local_id=" . $local_id . "&sector_id=" . $sector_id);
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
    
    $local_id = $_GET['local_id'];
    $sector_id = !empty($_GET['sector_id']) ? $_GET['sector_id'] : null;
    $zona_id = $_GET['zona_id'];
    
    // Traemos CADA registro individual escaneado con el nombre de quien lo hizo
    $stmt = $db->prepare("
        SELECT c.id, c.codigo_barras, c.cantidad, p.descripcion, u.nombre_completo as nombre_usuario
        FROM conteos c
        LEFT JOIN productos p ON c.codigo_barras = p.codigo_barras
        LEFT JOIN usuarios u ON c.usuario_id = u.id
        WHERE c.local_id = ? AND c.sector_id <=> ? AND c.zona_id = ?
        ORDER BY c.id DESC
    ");
    $stmt->execute([$local_id, $sector_id, $zona_id]);
    $detalles = $stmt->fetchAll();
    
    // Nombres para el título
    $local_nombre = $db->query("SELECT nombre FROM locales WHERE id = " . intval($local_id))->fetchColumn();
    $zona_nombre = $db->query("SELECT codigo FROM zonas WHERE id = " . intval($zona_id))->fetchColumn();

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
// ==========================================
// MÓDULO: ELIMINAR ZONA (BORRADO COMPLETO)
// ==========================================
if (isset($_GET['action']) && $_GET['action'] === 'monitor_eliminar_zona' && isset($_SESSION['usuario_id'])) {
    if ($_SESSION['rol_id'] > 2) { die("Acceso denegado."); } // Solo admins o encargados
    $db = (new Database())->getConnection();
    
    $zona_id = intval($_GET['zona_id']);
    $local_id = intval($_GET['local_id']);
    $sector_id = intval($_GET['sector_id']);

    try {
        // Intentamos borrar la zona directamente.
        // Si tiene productos en la tabla 'conteos', MySQL nos va a frenar por seguridad.
        $stmt_zona = $db->prepare("DELETE FROM zonas WHERE id = ?");
        $stmt_zona->execute([$zona_id]);

        // Si se borró bien porque estaba vacía, volvemos al monitor
        header("Location: index.php?action=monitor_zonas&local_id=" . $local_id . "&sector_id=" . $sector_id);
        exit;

    } catch (PDOException $e) {
        // Atrapamos el error de MySQL y mostramos el cartel rojo, frenando el sistema (die)
        die("
        <div style='background: #f4f4f9; height: 100vh; display: flex; align-items: center; justify-content: center; font-family: sans-serif; margin: 0;'>
            <div style='background: white; padding: 40px; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); border-top: 5px solid #d32f2f; max-width: 500px; text-align: center;'>
                <div style='font-size: 50px; margin-bottom: 20px;'>⚠️</div>
                <h2 style='color: #d32f2f; margin-bottom: 15px; margin-top: 0;'>Zona con Datos</h2>
                <p style='color: #555; line-height: 1.6; font-size: 16px;'>
                    No se puede eliminar la zona porque <strong>contiene productos registrados</strong>. 
                </p>
                <div style='background: #fff8e1; border: 1px solid #ffe082; padding: 15px; border-radius: 8px; margin: 20px 0; text-align: left; font-size: 14px; color: #333;'>
                    <strong>💡 ¿Qué hacer?</strong><br>
                    Primero usá el botón <span style='color:#d32f2f; font-weight:bold;'>'Vaciar Zona'</span>. Una vez que esté en 0.00, vas a poder eliminarla.
                </div>
                <a href='index.php?action=monitor_zonas&local_id=$local_id&sector_id=$sector_id' 
                   style='display: inline-block; background: #2196f3; color: white; padding: 12px 30px; border-radius: 6px; text-decoration: none; font-weight: bold;'>
                   Volver al Monitor
                </a>
            </div>
        </div>
        ");
    }
}
// ==========================================
// MÓDULO: IMPRIMIR ETIQUETAS DE ZONAS
// ==========================================
if (isset($_GET['action']) && $_GET['action'] === 'imprimir_zonas' && isset($_SESSION['usuario_id'])) {
    $db = (new Database())->getConnection();
        
    $local_id = $_GET['local_id'];
    $sector_id = $_GET['sector_id'];

    // --- NUEVA LÍNEA: Traemos el nombre del sector ---
    $sector_nombre = $db->query("SELECT nombre FROM sectores WHERE id = " . intval($sector_id))->fetchColumn();

    $stmt = $db->prepare("SELECT id, codigo FROM zonas WHERE local_id = ? AND sector_id = ? ORDER BY codigo ASC");
    $stmt->execute([$local_id, $sector_id]);
    $lista_zonas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Si no hay zonas, le avisamos
    if (empty($lista_zonas)) {
        die("❌ No hay zonas creadas en este sector para imprimir. Volvé atrás y creá algunas primero.");
    }

    // Le pasamos la pelota al archivo visual que vos creaste
    require_once __DIR__ . '/../src/Application/Views/imprimir_zonas.php';
    exit;
}
// ==========================================
// ==========================================
// MÓDULO: MONITOR - BORRAR ITEM ESPECÍFICO
// ==========================================
if (isset($_GET['action']) && $_GET['action'] === 'monitor_borrar_item' && isset($_SESSION['usuario_id'])) {
    if ($_SESSION['rol_id'] > 2) { die("Acceso denegado."); }
    $db = (new Database())->getConnection();

    $id_conteo = $_GET['id_conteo'] ?? null;
    $local_id = $_GET['local_id'];
    $sector_id = $_GET['sector_id'];
    $zona_id = $_GET['zona_id'];

    if ($id_conteo) {
        $stmt = $db->prepare("DELETE FROM conteos WHERE id = ?");
        $stmt->execute([$id_conteo]);
    }

    // Volvemos a la misma pantalla de detalle donde estábamos
    header("Location: index.php?action=monitor_detalle_zona&local_id=$local_id&sector_id=$sector_id&zona_id=$zona_id&msj=borrado");
    exit;
}

// ==========================================
// MÓDULO DE PIQUEO (ZEBRA TC21)
// ==========================================

// 1. Pantalla de Configuración (Elegir Local, Sector y Zona)
if (isset($_GET['action']) && $_GET['action'] === 'piqueo_config' && isset($_SESSION['usuario_id'])) {
    $db = (new Database())->getConnection();
    $error = "";
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $local = $_POST['local_id'];
        $sector = !empty($_POST['sector_id']) ? $_POST['sector_id'] : null;
        $zona = $_POST['zona_id'];
        $usuario_actual = $_SESSION['usuario_id'];

        // MAGIA ANTI-FANTASMAS
        $stmt_abierta = $db->prepare("
            SELECT zc.local_id, zc.sector_id, zc.zona_id, l.nombre as local_nom, s.nombre as sector_nom, z.codigo as zona_cod
            FROM zonas_cerradas zc
            JOIN locales l ON zc.local_id = l.id
            LEFT JOIN sectores s ON zc.sector_id = s.id
            JOIN zonas z ON zc.zona_id = z.id
            WHERE zc.usuario_id = ? AND zc.estado = 'en_uso'
        ");
        $stmt_abierta->execute([$usuario_actual]);
        $zona_en_uso = $stmt_abierta->fetch();

        $bloquear_acceso = false;

        if ($zona_en_uso) {
            if ($zona_en_uso['local_id'] != $local || $zona_en_uso['sector_id'] != $sector || $zona_en_uso['zona_id'] != $zona) {
                $bloquear_acceso = true; 
                
                $nombre_lugar = htmlspecialchars($zona_en_uso['local_nom']);
                if ($zona_en_uso['sector_nom']) $nombre_lugar .= " - " . htmlspecialchars($zona_en_uso['sector_nom']);
                $nombre_lugar .= " - Zona " . htmlspecialchars($zona_en_uso['zona_cod']);

                // ---> CORRECCIÓN DE LA TRAMPA: Recuperar el inicio original de Carlos
                $start_id_existente = isset($_SESSION['piqueo']['start_id']) ? $_SESSION['piqueo']['start_id'] : 0;
                if ($start_id_existente === 0) {
                    $stmt_min = $db->prepare("SELECT MIN(id) FROM conteos WHERE local_id = ? AND sector_id <=> ? AND zona_id = ? AND usuario_id = ?");
                    $stmt_min->execute([$zona_en_uso['local_id'], $zona_en_uso['sector_id'], $zona_en_uso['zona_id'], $usuario_actual]);
                    $min_id = $stmt_min->fetchColumn();
                    $start_id_existente = $min_id ? ($min_id - 1) : 0;
                }

                $_SESSION['piqueo'] = [
                    'local_id' => $zona_en_uso['local_id'], 
                    'sector_id' => $zona_en_uso['sector_id'], 
                    'zona_id' => $zona_en_uso['zona_id'], 
                    'start_id' => $start_id_existente
                ];

                $error = "
                    <div style='background: #b71c1c; padding: 20px; border-radius: 8px; color: white; text-align: center; margin-bottom: 20px; border: 2px solid #ff5252;'>
                        <h3 style='margin-top:0;'>⚠️ ACCIÓN DENEGADA</h3>
                        <p>Ya tienes una zona abierta y sin terminar:</p>
                        <p style='font-size: 18px; font-weight: bold; color: #ffeb3b;'>{$nombre_lugar}</p>
                        <p style='font-size: 14px; margin-bottom: 15px;'>Debes volver a esa zona y elegir 'Terminar' o 'Salir sin bloquear' antes de abrir una nueva.</p>
                        <a href='index.php?action=piqueo_escaner' style='display: block; background: #ff9800; color: #000; padding: 15px 20px; font-size: 18px; font-weight: bold; text-decoration: none; border-radius: 5px; width: 100%; box-sizing: border-box;'>Ir a mi zona abierta ➔</a>
                    </div>
                ";
            }
        }

        if (!$bloquear_acceso) {
            $check_estado = $db->prepare("SELECT estado, usuario_id FROM zonas_cerradas WHERE local_id = ? AND sector_id <=> ? AND zona_id = ?");
            $check_estado->execute([$local, $sector, $zona]);
            $estado_zona = $check_estado->fetch();
            
            if ($estado_zona) {
                if ($estado_zona['estado'] === 'cerrada') {
                    $error = "❌ Esa Zona ya fue completada y cerrada por un operario.";
                } elseif ($estado_zona['estado'] === 'en_uso' && $estado_zona['usuario_id'] != $usuario_actual) {
                    $error = "❌ Esta zona se encuentra ABIERTA y en uso por otro operario.";
                } else {
                    // ---> CORRECCIÓN DE LA TRAMPA: Recuperar su inicio si vuelve legítimamente
                    $start_id_existente = isset($_SESSION['piqueo']['start_id']) ? $_SESSION['piqueo']['start_id'] : 0;
                    if ($start_id_existente === 0) {
                        $stmt_min = $db->prepare("SELECT MIN(id) FROM conteos WHERE local_id = ? AND sector_id <=> ? AND zona_id = ? AND usuario_id = ?");
                        $stmt_min->execute([$local, $sector, $zona, $usuario_actual]);
                        $min_id = $stmt_min->fetchColumn();
                        $start_id_existente = $min_id ? ($min_id - 1) : 0;
                    }
                    $_SESSION['piqueo'] = ['local_id' => $local, 'sector_id' => $sector, 'zona_id' => $zona, 'start_id' => $start_id_existente];
                    header("Location: index.php?action=piqueo_escaner"); exit;
                }
            } else {
                $stmt_uso = $db->prepare("INSERT INTO zonas_cerradas (local_id, sector_id, zona_id, usuario_id, estado) VALUES (?, ?, ?, ?, 'en_uso')");
                $stmt_uso->execute([$local, $sector, $zona, $usuario_actual]);
                
                $max_id = $db->query("SELECT MAX(id) FROM conteos")->fetchColumn();
                $_SESSION['piqueo'] = ['local_id' => $local, 'sector_id' => $sector, 'zona_id' => $zona, 'start_id' => $max_id ? $max_id : 0];
                header("Location: index.php?action=piqueo_escaner"); exit;
            }
        }
    }

$locales = $db->query("SELECT id, nombre FROM locales WHERE estado = 1 ORDER BY nombre ASC")->fetchAll();

    // AGREGAMOS local_id A LA CONSULTA
    $sectores = $db->query("SELECT id, nombre, local_id FROM sectores ORDER BY nombre ASC")->fetchAll();
    
    // Lo convertimos a JSON para que el JavaScript de la vista pueda leerlo fácil
    $json_sectores = json_encode($sectores);

    $zonas_db = $db->query("SELECT id, codigo, local_id, sector_id FROM zonas ORDER BY codigo ASC")->fetchAll(PDO::FETCH_ASSOC);    $json_todas_las_zonas = json_encode($zonas_db);
    $zonas_cerradas = $db->query("SELECT local_id, sector_id, zona_id FROM zonas_cerradas WHERE estado = 'cerrada'")->fetchAll(PDO::FETCH_ASSOC);
    $json_cerradas = json_encode($zonas_cerradas);
    
    require_once __DIR__ . '/../src/Application/Views/piqueo_config.php';
    exit;
}

// ==========================================
// 2. PANTALLA DE BATALLA (ESCÁNER)
// ==========================================
if (isset($_GET['action']) && $_GET['action'] === 'piqueo_escaner' && isset($_SESSION['usuario_id'])) {
    
    // --- ESCUDO ANTI-BOTÓN ATRÁS ---
    // Si la sesión no existe, significa que hizo trampa con el navegador. Lo echamos al menú.
    if (!isset($_SESSION['piqueo'])) {
        header("Location: index.php?action=piqueo_config");
        exit;
    }
    // -------------------------------
    
    $db = (new Database())->getConnection();
    $alerta_sonido = false;
    $mensaje_estado = "";

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['codigo_barras'])) {
        $codigo = trim(strtoupper($_POST['codigo_barras'])); // Lo pasamos a mayúsculas por si acaso
        $cantidad = !empty($_POST['cantidad']) ? $_POST['cantidad'] : 1;

        // 1. ¿ES UN CÓDIGO DE ZONA ÚNICO? (Detecta si el código empieza con Z-)
        if (substr($codigo, 0, 2) === 'Z-') {
            
            $zona_id_escaneada = (int) str_replace('Z-', '', $codigo);
            
            $check_zona = $db->prepare("SELECT id, codigo, local_id, sector_id FROM zonas WHERE id = ? LIMIT 1");
            $check_zona->execute([$zona_id_escaneada]);
            $zona_escaneada = $check_zona->fetch();

            if ($zona_escaneada) {
                $nueva_zona_id = $zona_escaneada['id'];
                
                if ($nueva_zona_id == $_SESSION['piqueo']['zona_id']) {
                    $mensaje_estado = "<div class='success-card'>Ya estás dentro de la Zona: {$zona_escaneada['codigo']}</div>";
                } else {
                    $check_estado = $db->prepare("SELECT estado, usuario_id FROM zonas_cerradas WHERE zona_id = ?");
                    $check_estado->execute([$nueva_zona_id]);
                    $estado_nueva = $check_estado->fetch();

                    if ($estado_nueva && $estado_nueva['estado'] === 'cerrada') {
                        $alerta_sonido = true;
                        $mensaje_estado = "<div class='error-card'>❌ La zona {$zona_escaneada['codigo']} ya fue CERRADA.</div>";
                    } elseif ($estado_nueva && $estado_nueva['estado'] === 'en_uso' && $estado_nueva['usuario_id'] != $_SESSION['usuario_id']) {
                        $alerta_sonido = true;
                        $mensaje_estado = "<div class='error-card'>❌ La zona {$zona_escaneada['codigo']} está en uso por otro.</div>";
                    } else {
                        // LA MAGIA: MOSTRAR CARTEL DE CONFIRMACIÓN PARA CERRAR Y SALTAR
                        $alerta_sonido = true; 
                        $mensaje_estado = "
                        <div style='background: #ffb300; padding: 15px; border-radius: 5px; border-left: 5px solid #f57c00; margin-bottom: 20px; color: #000;'>
                            <div style='font-size: 18px; font-weight: bold; margin-bottom: 10px;'>⚠️ ¿Cambiar a la Zona {$zona_escaneada['codigo']}?</div>
                            <p style='margin: 0 0 15px 0; font-size: 15px;'>Al aceptar, tu zona actual pasará a estado <strong>CERRADA</strong> y empezarás a piquear en la nueva.</p>
                            <div style='display: flex; gap: 10px;'>
                                <a href='index.php?action=piqueo_escaner' style='flex: 1; padding: 15px; text-align: center; background: #fff; color: #000; text-decoration: none; border-radius: 5px; font-weight: bold; border: 1px solid #ccc;'>Cancelar</a>
                                <a href='index.php?action=piqueo_ejecutar_salto&nueva_zona_id={$nueva_zona_id}&nuevo_local={$zona_escaneada['local_id']}&nuevo_sector={$zona_escaneada['sector_id']}' style='flex: 1; padding: 15px; text-align: center; background: #d32f2f; color: #fff; text-decoration: none; border-radius: 5px; font-weight: bold;'>SÍ, SALTAR</a>
                            </div>
                        </div>";
                    }
                }
            } else {
                $alerta_sonido = true;
                $mensaje_estado = "<div class='error-card'>❌ ZONA INVÁLIDA O ELIMINADA.</div>";
            }
            
        } else {
            // 2. NO ES ZONA, ES UN PRODUCTO NORMAL...
            
            // PRIMERO: Verificamos si el producto existe en el catálogo
            $check_prod = $db->prepare("SELECT descripcion FROM productos WHERE codigo_barras = ?");
            $check_prod->execute([$codigo]);
            $prod = $check_prod->fetch();

            if ($prod) {
                // SI EXISTE: Tenemos que averiguar a qué Auditoría pertenece este piqueo
                $auditoria_activa_id = null;
                
                // Buscamos si hay una auditoría "Pendiente" en este local
                $stmt_auditoria = $db->prepare("SELECT id FROM auditorias_programadas WHERE local_id = ? AND estado = 'Pendiente' LIMIT 1");
                $stmt_auditoria->execute([$_SESSION['piqueo']['local_id']]);
                $auditoria = $stmt_auditoria->fetch();
                
                if ($auditoria) {
                    $auditoria_activa_id = $auditoria['id'];
                }

                // Ahora sí lo guardamos en la base de datos (con el auditoria_id incluido)
                $stmt = $db->prepare("INSERT INTO conteos (auditoria_id, local_id, sector_id, zona_id, usuario_id, codigo_barras, cantidad) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $auditoria_activa_id,
                    $_SESSION['piqueo']['local_id'], 
                    $_SESSION['piqueo']['sector_id'], 
                    $_SESSION['piqueo']['zona_id'], 
                    $_SESSION['usuario_id'], 
                    $codigo, 
                    $cantidad
                ]);

                // Calculamos total de ese ítem (ahora filtramos también por la auditoría activa si la hay)
                if ($auditoria_activa_id) {
                    $stmt_item = $db->prepare("SELECT SUM(cantidad) FROM conteos WHERE auditoria_id = ? AND local_id = ? AND sector_id <=> ? AND zona_id = ? AND codigo_barras = ?");
                    $stmt_item->execute([$auditoria_activa_id, $_SESSION['piqueo']['local_id'], $_SESSION['piqueo']['sector_id'], $_SESSION['piqueo']['zona_id'], $codigo]);
                } else {
                    // Por las dudas, si alguien piquea sin auditoría (no debería pasar, pero para que no se rompa)
                    $stmt_item = $db->prepare("SELECT SUM(cantidad) FROM conteos WHERE local_id = ? AND sector_id <=> ? AND zona_id = ? AND codigo_barras = ?");
                    $stmt_item->execute([$_SESSION['piqueo']['local_id'], $_SESSION['piqueo']['sector_id'], $_SESSION['piqueo']['zona_id'], $codigo]);
                }
                
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
                // NO EXISTE: Hacemos sonar la chicharra y NO HACEMOS EL INSERT
                $alerta_sonido = true;
                $mensaje_estado = "
                <div class='error-card' style='background-color: #d32f2f; border-left: 5px solid #b71c1c;'>
                    <div style='font-size: 18px; margin-bottom: 8px; font-weight: bold;'>⚠️ PRODUCTO DESCONOCIDO</div>
                    <div style='display: flex; justify-content: space-between; align-items: center;'>
                        <span style='font-size: 24px; color: #fff;'>RECHAZADO</span>
                        <span style='font-size: 14px; color: #ffcdd2;'>No se guardó el conteo.<br><small>Cód: {$codigo}</small></span>
                    </div>
                </div>";
            }
        }
    }

    // Atrapamos el mensaje si acaba de saltar de zona
    if (isset($_GET['jump'])) {
        $mensaje_estado = "<div class='success-card' style='background: #1565c0; border-left-color: #64b5f6;'>🚀 Salto exitoso. ¡Bienvenido a la Zona " . htmlspecialchars($_GET['jump']) . "!</div>";
    }

    $local_nombre = $db->query("SELECT nombre FROM locales WHERE id = " . $_SESSION['piqueo']['local_id'])->fetchColumn();
    $zona_nombre = $db->query("SELECT codigo FROM zonas WHERE id = " . $_SESSION['piqueo']['zona_id'])->fetchColumn();
    $sector_nombre = null;
    if ($_SESSION['piqueo']['sector_id']) {
        $sector_nombre = $db->query("SELECT nombre FROM sectores WHERE id = " . $_SESSION['piqueo']['sector_id'])->fetchColumn();
    }
    
    // --- CÁLCULO DEL TOTAL ---
    // Buscamos de nuevo la auditoría pendiente (o podés usar la misma variable si la definís más arriba, pero así es más seguro)
    $stmt_aud = $db->prepare("SELECT id FROM auditorias_programadas WHERE local_id = ? AND estado = 'Pendiente' LIMIT 1");
    $stmt_aud->execute([$_SESSION['piqueo']['local_id']]);
    $aud_activa = $stmt_aud->fetch();
    
    if ($aud_activa) {
        $stmt_total = $db->prepare("SELECT SUM(c.cantidad) FROM conteos c 
                                    INNER JOIN productos p ON c.codigo_barras = p.codigo_barras 
                                    WHERE c.auditoria_id = ? AND c.zona_id = ? AND c.local_id = ? AND c.sector_id <=> ?");
        $stmt_total->execute([
            $aud_activa['id'],
            $_SESSION['piqueo']['zona_id'], 
            $_SESSION['piqueo']['local_id'], 
            $_SESSION['piqueo']['sector_id']
        ]);
    } else {
        $stmt_total = $db->prepare("SELECT SUM(c.cantidad) FROM conteos c 
                                    INNER JOIN productos p ON c.codigo_barras = p.codigo_barras 
                                    WHERE c.zona_id = ? AND c.local_id = ? AND c.sector_id <=> ?");
        $stmt_total->execute([
            $_SESSION['piqueo']['zona_id'], 
            $_SESSION['piqueo']['local_id'], 
            $_SESSION['piqueo']['sector_id']
        ]);
    }
    
    $total_zona = $stmt_total->fetchColumn();
    $total_zona = $total_zona ? number_format($total_zona, 2, '.', '') : "0.00";

    require_once __DIR__ . '/../src/Application/Views/piqueo_escaner.php';
    exit;
}

// ==========================================
// MÓDULO: ALTA RÁPIDA DE PRODUCTO (DESDE ESCÁNER)
// ==========================================
if (isset($_GET['action']) && $_GET['action'] === 'piqueo_crear_producto' && isset($_SESSION['usuario_id'])) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['codigo_barras']) && !empty($_POST['descripcion'])) {
        $db = (new Database())->getConnection();
        
        $codigo = trim(strtoupper($_POST['codigo_barras']));
        $sku = trim($_POST['sku'] ?? '');
        $descripcion = trim(strtoupper($_POST['descripcion'])); // Lo pasamos a mayúsculas para mantener el orden

        // 1. Verificamos que alguien no lo haya creado 2 segundos antes
        $check = $db->prepare("SELECT id FROM productos WHERE codigo_barras = ?");
        $check->execute([$codigo]);
        
        if (!$check->fetch()) {
            // 2. Si no existe, lo creamos rápido
            // Nota: Ajustá los nombres de las columnas si en tu base se llaman distinto
            $stmt = $db->prepare("INSERT INTO productos (codigo_barras, sku, descripcion) VALUES (?, ?, ?)");
            $stmt->execute([$codigo, $sku, $descripcion]);
        }
        
        // 3. Lo devolvemos al escáner con un mensaje de éxito
        header("Location: index.php?action=piqueo_escaner&prod_creado=" . urlencode($codigo));
        exit;
    }
}

// 3. NUEVA RUTA: SALIR SIN GUARDAR (CON LIMPIEZA POR CHECKPOINT)
// ==========================================
// ACCIÓN: SALIR SIN BLOQUEAR (Y BORRAR EL ERROR)
// ==========================================
if (isset($_GET['action']) && $_GET['action'] === 'piqueo_salir_zona' && isset($_SESSION['piqueo'])) {
    $db = (new Database())->getConnection();
    
    $local = $_SESSION['piqueo']['local_id'];
    $sector = $_SESSION['piqueo']['sector_id'];
    $zona = $_SESSION['piqueo']['zona_id'];
    $usuario = $_SESSION['usuario_id'];
    
    // Acá está la magia: El ID exacto en el que Carlos empezó a trabajar
    $start_id = $_SESSION['piqueo']['start_id'];

    // 1. ELIMINAMOS EL RASTRO: Borramos solo lo que Carlos escaneó en esta "sesión" de error
    $stmt_borrar = $db->prepare("DELETE FROM conteos WHERE id > ? AND local_id = ? AND sector_id <=> ? AND zona_id = ? AND usuario_id = ?");
    $stmt_borrar->execute([$start_id, $local, $sector, $zona, $usuario]);

    // 2. LIBERAMOS LA ZONA: Le sacamos la etiqueta de 'en_uso' para que otro pueda entrar
    $stmt_liberar = $db->prepare("DELETE FROM zonas_cerradas WHERE local_id = ? AND sector_id <=> ? AND zona_id = ? AND usuario_id = ? AND estado = 'en_uso'");
    $stmt_liberar->execute([$local, $sector, $zona, $usuario]);

    // 3. LIMPIAMOS AL USUARIO: Le sacamos la zona asignada para que pueda elegir una nueva
    unset($_SESSION['piqueo']);
    
    // Lo mandamos de vuelta al menú de zonas
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

// ==========================================
// 7. NUEVA RUTA: EJECUTAR EL SALTO DE ZONA CONFIRMADO
// ==========================================
if (isset($_GET['action']) && $_GET['action'] === 'piqueo_ejecutar_salto' && isset($_SESSION['usuario_id'])) {
    if (isset($_GET['nueva_zona_id']) && isset($_SESSION['piqueo'])) {
        $db = (new Database())->getConnection();
        
        $zona_vieja_id = $_SESSION['piqueo']['zona_id'];
        $nueva_zona_id = intval($_GET['nueva_zona_id']);
        $nuevo_local_id = intval($_GET['nuevo_local']);
        $nuevo_sector_id = intval($_GET['nuevo_sector']);

        // Buscamos el nombre de la nueva zona para el mensaje de éxito
        $codigo_nueva_zona = $db->query("SELECT codigo FROM zonas WHERE id = " . $nueva_zona_id)->fetchColumn();

        if ($codigo_nueva_zona) {
            // A) CERRAMOS LA ZONA VIEJA (Donde estaba el piqueador antes)
            // Usamos solo el ID de la zona porque ahora es ÚNICO.
            $stmt_close = $db->prepare("UPDATE zonas_cerradas SET estado = 'cerrada' WHERE zona_id = ?");
            $stmt_close->execute([$zona_vieja_id]);
            
            // B) ABRIMOS LA ZONA NUEVA (La ponemos en uso por este usuario)
            $check_estado = $db->prepare("SELECT id FROM zonas_cerradas WHERE zona_id = ?");
            $check_estado->execute([$nueva_zona_id]);
            
            if ($check_estado->fetch()) {
                // Si ya existe el registro, lo actualizamos a 'en_uso'
                $stmt_update = $db->prepare("UPDATE zonas_cerradas SET estado = 'en_uso', usuario_id = ?, local_id = ?, sector_id = ? WHERE zona_id = ?");
                $stmt_update->execute([$_SESSION['usuario_id'], $nuevo_local_id, $nuevo_sector_id, $nueva_zona_id]);
            } else {
                // Si es la primera vez que se toca, la insertamos
                $stmt_uso = $db->prepare("INSERT INTO zonas_cerradas (local_id, sector_id, zona_id, usuario_id, estado) VALUES (?, ?, ?, ?, 'en_uso')");
                $stmt_uso->execute([$nuevo_local_id, $nuevo_sector_id, $nueva_zona_id, $_SESSION['usuario_id']]);
            }
            
            // C) ACTUALIZAMOS LA BRÚJULA (SESIÓN)
            // Esto es vital para que no se mezclen los datos si saltó de un sector a otro
            $_SESSION['piqueo']['local_id'] = $nuevo_local_id;
            $_SESSION['piqueo']['sector_id'] = $nuevo_sector_id;
            $_SESSION['piqueo']['zona_id'] = $nueva_zona_id;

            // Resetamos el start_id para el monitor de la pantalla
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
// MÓDULO: ABM DE SECTORES
// ==========================================

// 1. LISTADO Y ALTA
if (isset($_GET['action']) && $_GET['action'] === 'ajustes_sectores' && isset($_SESSION['usuario_id'])) {
    // Permite acceso a Admin (1) y Encargados (2)
    if ($_SESSION['rol_id'] > 2) { die("Acceso denegado."); }
    $db = (new Database())->getConnection();

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['nombre']) && !empty($_POST['local_id'])) {
        $local_id = $_POST['local_id'];
        $nombre_sector = trim(strtoupper($_POST['nombre'])); // Forzamos a mayúsculas

        // BARRERA DE SEGURIDAD: Buscamos si ya existe en ese local
        $check_stmt = $db->prepare("SELECT id FROM sectores WHERE local_id = ? AND nombre = ?");
        $check_stmt->execute([$local_id, $nombre_sector]);
        
        if ($check_stmt->fetch()) {
            // Si encuentra uno igual, cancela y manda la señal de error a la pantalla
            header("Location: index.php?action=ajustes_sectores&error=sector_duplicado");
            exit;
        }

        // Si pasó la prueba (no existe), lo insertamos en la base de datos
        $stmt = $db->prepare("INSERT INTO sectores (nombre, local_id) VALUES (?, ?)");
        $stmt->execute([
            $nombre_sector,
            $local_id
        ]);
        header("Location: index.php?action=ajustes_sectores");
        exit;
    }

    // Traemos los sectores y le pegamos el nombre del local al que pertenecen
    $listaSectores = $db->query("SELECT s.*, l.nombre as local_nombre 
                                 FROM sectores s 
                                 LEFT JOIN locales l ON s.local_id = l.id 
                                 ORDER BY s.id DESC")->fetchAll();
    
    // Traemos los locales activos para armar el menú desplegable
    $listaLocales = $db->query("SELECT id, nombre FROM locales WHERE estado = 1 ORDER BY nombre ASC")->fetchAll();

    require_once __DIR__ . '/../src/Application/Views/ajustes_sectores.php';
    exit;
}

// 2. EDICIÓN
if (isset($_GET['action']) && $_GET['action'] === 'editar_sector' && isset($_SESSION['usuario_id'])) {
    if ($_SESSION['rol_id'] > 2) { die("Acceso denegado."); }
    $db = (new Database())->getConnection();

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['id']) && !empty($_POST['nombre']) && !empty($_POST['local_id'])) {
        $stmt = $db->prepare("UPDATE sectores SET nombre = ?, local_id = ? WHERE id = ?");
        $stmt->execute([
            trim(strtoupper($_POST['nombre'])), // También en mayúsculas al editar
            $_POST['local_id'],
            $_POST['id']
        ]);
        header("Location: index.php?action=ajustes_sectores");
        exit;
    }

    $stmt = $db->prepare("SELECT * FROM sectores WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $sector = $stmt->fetch();
    
    // Filtro de locales activos también en la ventana de edición
    $listaLocales = $db->query("SELECT id, nombre FROM locales WHERE estado = 1 ORDER BY nombre ASC")->fetchAll();

    require_once __DIR__ . '/../src/Application/Views/editar_sector.php';
    exit;
}

// 3. ELIMINACIÓN
if (isset($_GET['action']) && $_GET['action'] === 'eliminar_sector' && isset($_SESSION['usuario_id'])) {
    if ($_SESSION['rol_id'] > 2) { die("Acceso denegado."); }
    $db = (new Database())->getConnection();
    
    try {
        $stmt = $db->prepare("DELETE FROM sectores WHERE id = ?");
        $stmt->execute([$_GET['id']]);
    } catch (Exception $e) {
        die("<div style='padding:20px; font-family:Arial;'><h3>❌ Error</h3><p>No se puede eliminar este sector porque ya tiene zonas asignadas o conteos realizados.</p><a href='index.php?action=ajustes_sectores'>Volver atrás</a></div>");
    }
    
    header("Location: index.php?action=ajustes_sectores");
    exit;
}
// ==========================================
// MÓDULO: GESTIÓN DE LOCALES (INVENTARIOS)
// ==========================================

// 1. LISTADO Y ALTA
if (isset($_GET['action']) && $_GET['action'] === 'ajustes_locales' && isset($_SESSION['usuario_id'])) {
    if ($_SESSION['rol_id'] > 2) { die("Acceso denegado."); }
    $db = (new Database())->getConnection();

    // Procesar Alta de Local
    // Procesar Alta de Local
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['nombre'])) {
        $nombre_local = trim(strtoupper($_POST['nombre'])); // Forzamos mayúsculas para evitar dobles (ej: Centro y CENTRO)
        $direccion = trim($_POST['direccion']);
        $encargado_id = !empty($_POST['encargado_id']) ? $_POST['encargado_id'] : null;

        // BARRERA DE SEGURIDAD: Buscamos si ya existe
        $check_stmt = $db->prepare("SELECT id FROM locales WHERE nombre = ?");
        $check_stmt->execute([$nombre_local]);
        
        if ($check_stmt->fetch()) {
            // Si ya existe, abortamos y mandamos la señal de error
            header("Location: index.php?action=ajustes_locales&error=local_duplicado");
            exit;
        }

        // Si pasó la prueba, lo guardamos
        $stmt = $db->prepare("INSERT INTO locales (nombre, direccion, encargado_id, estado) VALUES (?, ?, ?, 1)");
        $stmt->execute([$nombre_local, $direccion, $encargado_id]);
        header("Location: index.php?action=ajustes_locales");
        exit;
    }

    // CORRECCIÓN 1: Cambiamos 'encargado_nom' por 'encargado_nombre'
    $listaLocales = $db->query("SELECT l.*, u.nombre_completo as encargado_nombre 
                                FROM locales l 
                                LEFT JOIN usuarios u ON l.encargado_id = u.id 
                                ORDER BY l.id DESC")->fetchAll();

    // CORRECCIÓN 2: Cambiamos la variable '$encargados' por '$listaUsuarios'
    $listaUsuarios = $db->query("SELECT id, nombre_completo FROM usuarios WHERE rol_id = 2 AND estado = 1 ORDER BY nombre_completo ASC")->fetchAll();

    require_once __DIR__ . '/../src/Application/Views/ajustes_locales.php';
    exit;
}

// 2. CAMBIAR ESTADO (ACTIVAR/DESACTIVAR)
if (isset($_GET['action']) && $_GET['action'] === 'toggle_estado_local' && isset($_GET['id'])) {
    if ($_SESSION['rol_id'] > 2) { die("Acceso denegado."); }
    $db = (new Database())->getConnection();
    
    $id = intval($_GET['id']);
    $nuevo_estado = intval($_GET['st']) === 1 ? 0 : 1; // Si es 1 pasa a 0, si es 0 pasa a 1

    $stmt = $db->prepare("UPDATE locales SET estado = ? WHERE id = ?");
    $stmt->execute([$nuevo_estado, $id]);

    header("Location: index.php?action=ajustes_locales");
    exit;
}

// 2. EDICIÓN
if (isset($_GET['action']) && $_GET['action'] === 'editar_local' && isset($_SESSION['usuario_id'])) {
    if ($_SESSION['rol_id'] != 1) { die("Acceso denegado."); }
    $db = (new Database())->getConnection();

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['id'])) {
        $stmt = $db->prepare("UPDATE locales SET nombre = ?, direccion = ?, encargado_id = ? WHERE id = ?");
        $stmt->execute([
            trim($_POST['nombre']),
            trim($_POST['direccion']),
            !empty($_POST['encargado_id']) ? $_POST['encargado_id'] : null,
            $_POST['id']
        ]);
        header("Location: index.php?action=ajustes_locales");
        exit;
    }

    $stmt = $db->prepare("SELECT * FROM locales WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $local = $stmt->fetch();
    
    $listaUsuarios = $db->query("SELECT id, nombre_completo FROM usuarios WHERE rol_id <= 2 AND estado = 1 ORDER BY nombre_completo ASC")->fetchAll();

    require_once __DIR__ . '/../src/Application/Views/editar_local.php';
    exit;
}

// 3. ELIMINACIÓN
if (isset($_GET['action']) && $_GET['action'] === 'eliminar_local' && isset($_SESSION['usuario_id'])) {
    if ($_SESSION['rol_id'] != 1) { die("Acceso denegado."); }
    $db = (new Database())->getConnection();
    
    try {
        $stmt = $db->prepare("DELETE FROM locales WHERE id = ?");
        $stmt->execute([$_GET['id']]);
    } catch (Exception $e) {
        die("<div style='padding:20px; font-family:Arial;'><h3>❌ Error</h3><p>No se puede eliminar este local porque tiene sectores o datos asociados.</p><a href='index.php?action=ajustes_locales'>Volver atrás</a></div>");
    }
    
    header("Location: index.php?action=ajustes_locales");
    exit;
}
// ==========================================
// --- MÓDULO DE AJUSTES ---
if (isset($_GET['action']) && $_GET['action'] === 'ajustes_menu' && isset($_SESSION['usuario_id'])) {
    if ($_SESSION['rol_id'] > 2) { die("Acceso denegado."); }
    
    // No necesitamos consultas a la base de datos aquí, solo cargar la vista
    require_once __DIR__ . '/../src/Application/Views/ajustes_menu.php';
    exit;
}
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
    
    // Limpiamos cualquier espacio en blanco en la memoria para que el Excel no se corrompa
    if (ob_get_level()) { ob_end_clean(); }
    
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
        // OPCIÓN 2: UNIFICADO (Catálogo completo, suma total del local)
        // ========================================================
        fputcsv($output, ['Código de Barras', 'SKU', 'Nombre del Producto', 'Marca', 'Cantidad Total', 'Categoría'], ';');
        
        $sql = "SELECT 
                    p.codigo_barras, p.sku, p.descripcion, p.marca,
                    COALESCE(c.total_contado, 0) as cantidad_total,
                    cat.nombre as categoria_nombre
                FROM productos p
                LEFT JOIN (
                    SELECT codigo_barras, SUM(cantidad) as total_contado 
                    FROM conteos 
                    WHERE local_id = ? 
                    GROUP BY codigo_barras
                ) c ON p.codigo_barras = c.codigo_barras
                LEFT JOIN categorias cat ON p.categoria_id = cat.id
                ORDER BY p.descripcion ASC";
                
        $stmt = $db->prepare($sql);
        $stmt->execute([$local_id]);
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $codigo_excel = '="' . $row['codigo_barras'] . '"';
            $sku = !empty($row['sku']) ? $row['sku'] : '-';
            $marca = !empty($row['marca']) ? $row['marca'] : '-';
            $categoria = !empty($row['categoria_nombre']) ? $row['categoria_nombre'] : 'Sin Categoría';
                        
            fputcsv($output, [$codigo_excel, $sku, $row['descripcion'], $marca, number_format($row['cantidad_total'], 2, '.', ''), $categoria], ';'); 
        }

    } elseif ($tipo_reporte === 'datos') {
        // ========================================================
        // OPCIÓN 3: DATOS CRUDOS (Para importación, Catálogo Completo)
        // Pedido: Código de Barras, SKU, Nombre, Cantidad, Categoría, Marca
        // ========================================================
        fputcsv($output, ['Código de Barras', 'SKU', 'Nombre', 'Cantidad', 'Categoría', 'Marca'], ';');
        
        $sql = "SELECT 
                    p.codigo_barras, 
                    p.sku, 
                    p.descripcion, 
                    COALESCE(c.total_contado, 0) as cantidad_total,  
                    cat.nombre as categoria_nombre, 
                    p.marca 
                FROM productos p
                LEFT JOIN (
                    SELECT codigo_barras, SUM(cantidad) as total_contado 
                    FROM conteos 
                    WHERE local_id = ? 
                    GROUP BY codigo_barras
                ) c ON p.codigo_barras = c.codigo_barras
                LEFT JOIN categorias cat ON p.categoria_id = cat.id
                ORDER BY p.descripcion ASC";
                
        $stmt = $db->prepare($sql);
        $stmt->execute([$local_id]);
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $codigo_excel = '="' . $row['codigo_barras'] . '"'; 
            $sku = !empty($row['sku']) ? $row['sku'] : '-';
            $marca = !empty($row['marca']) ? $row['marca'] : '-';
            $categoria = !empty($row['categoria_nombre']) ? $row['categoria_nombre'] : 'Sin Categoría';

            fputcsv($output, [
                $codigo_excel, 
                $sku, 
                $row['descripcion'], 
                number_format($row['cantidad_total'], 2, '.', ''), 
                $categoria, 
                $marca
            ], ';');
        }

    } else {
        // ========================================================
        // OPCIÓN 1: DETALLADO (Separado por Zona y Sector - Solo lo escaneado)
        // ========================================================
        fputcsv($output, ['Código de Barras', 'SKU', 'Nombre del Producto', 'Marca', 'Cantidad Total', 'Sector', 'Zona', 'Categoría'], ';');
        
        $sql = "SELECT 
                    c.codigo_barras, p.sku, p.descripcion, p.marca,
                    SUM(c.cantidad) as cantidad_total,
                    s.nombre as sector_nombre, z.codigo as zona_codigo,
                    cat.nombre as categoria_nombre
                FROM conteos c
                LEFT JOIN productos p ON c.codigo_barras = p.codigo_barras
                LEFT JOIN zonas z ON c.zona_id = z.id
                LEFT JOIN sectores s ON c.sector_id = s.id
                LEFT JOIN categorias cat ON p.categoria_id = cat.id
                WHERE c.local_id = ?
                GROUP BY c.zona_id, c.sector_id, c.codigo_barras, p.sku, p.descripcion, p.marca, z.codigo, s.nombre, cat.nombre
                ORDER BY s.nombre ASC, z.codigo ASC, p.descripcion ASC";
                
        $stmt = $db->prepare($sql);
        $stmt->execute([$local_id]);
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $codigo_excel = '="' . $row['codigo_barras'] . '"';
            $sku = !empty($row['sku']) ? $row['sku'] : '-';
            $marca = !empty($row['marca']) ? $row['marca'] : '-';
            $categoria = !empty($row['categoria_nombre']) ? $row['categoria_nombre'] : 'Sin Categoría';
            
            fputcsv($output, [$codigo_excel, $sku, $row['descripcion'], $marca, number_format($row['cantidad_total'], 2, '.', ''), $row['sector_nombre'], $row['zona_codigo'], $categoria], ';'); 
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

// ==========================================
// MÓDULO: ALTA DE PRODUCTO (COMPLETA)
// ==========================================
if (isset($_GET['action']) && $_GET['action'] === 'productos_alta' && isset($_SESSION['usuario_id'])) {
    if ($_SESSION['rol_id'] > 2) { die("Acceso denegado."); }
    $db = (new Database())->getConnection();

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['codigo_barras'])) {
        try {
            // Agregamos los nuevos campos a la consulta SQL
            $sql = "INSERT INTO productos (codigo_barras, sku, descripcion, marca, categoria_id) 
                    VALUES (:codigo_barras, :sku, :descripcion, :marca, :categoria_id)";
            $stmt = $db->prepare($sql);
            
            $stmt->execute([
                'codigo_barras' => trim($_POST['codigo_barras']),
                'sku' => trim($_POST['sku']), // Ahora es obligatorio
                'descripcion' => trim(strtoupper($_POST['descripcion'])), // Mayúsculas para mantener proljo el catálogo
                'marca' => !empty($_POST['marca']) ? trim(strtoupper($_POST['marca'])) : null,
                'categoria_id' => !empty($_POST['categoria_id']) ? intval($_POST['categoria_id']) : null
            ]);
            $exito = "✅ Producto guardado exitosamente. ¡Podés ingresar otro!";
        } catch (Exception $e) { 
            $error = "❌ Error al guardar. Es posible que el Código de Barras o el SKU ya estén registrados."; 
        }
    }

    $listaCategorias = $db->query("SELECT id, nombre FROM categorias ORDER BY nombre ASC")->fetchAll();

    require_once __DIR__ . '/../src/Application/Views/productos_alta.php';
    exit;
}
// ==========================================
// MÓDULO: EXPORTAR PRODUCTOS A CSV
// ==========================================
if (isset($_GET['action']) && $_GET['action'] === 'productos_exportar_csv' && isset($_SESSION['usuario_id'])) {
    if ($_SESSION['rol_id'] > 2) { die("Acceso denegado."); }
    $db = (new Database())->getConnection();

    // Traemos todos los productos con los nombres de su categoría 
    $productos = $db->query("
        SELECT p.codigo_barras, p.sku, p.descripcion, p.marca, 
               c.nombre as categoria 
        FROM productos p 
        LEFT JOIN categorias c ON p.categoria_id = c.id 
        ORDER BY p.descripcion ASC
    ")->fetchAll(PDO::FETCH_ASSOC);

    // Configuramos las cabeceras para forzar la descarga del archivo CSV
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=catalogo_productos_' . date('Ymd_His') . '.csv');

    $salida = fopen('php://output', 'w');
    
    // Este código (BOM) fuerza a Excel a leer correctamente las tildes y las eñes
    fprintf($salida, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // Escribimos los títulos de las columnas (separados por punto y coma)
    fputcsv($salida, ['Código de Barras', 'SKU', 'Descripción', 'Marca', 'Categoría'], ';');

    // Recorremos los productos y los metemos fila por fila
    foreach ($productos as $p) {
        fputcsv($salida, $p, ';');
    }

    fclose($salida);
    exit;
}
// ==========================================
// MÓDULO: GESTIÓN DE PRODUCTOS (BUSCADOR EN TIEMPO REAL)
// ==========================================
if (isset($_GET['action']) && $_GET['action'] === 'productos_gestion' && isset($_SESSION['usuario_id'])) {
    if ($_SESSION['rol_id'] > 2) { die("Acceso denegado."); }
    $db = (new Database())->getConnection();

    // Traemos TODOS los productos para alimentar el buscador en tiempo real de JavaScript.
    // Cruzamos los datos con categorías para obtener los nombres reales.
    $listaProductos = $db->query("
        SELECT p.*, c.nombre as categoria_nombre
        FROM productos p 
        LEFT JOIN categorias c ON p.categoria_id = c.id 
        ORDER BY p.id DESC
    ")->fetchAll();

    require_once __DIR__ . '/../src/Application/Views/productos_gestion.php';
    exit;
}
// ==========================================
// MÓDULO: EDICIÓN DE PRODUCTO
// ==========================================
if (isset($_GET['action']) && $_GET['action'] === 'productos_editar' && isset($_SESSION['usuario_id'])) {
    if ($_SESSION['rol_id'] > 2) { die("Acceso denegado."); }
    $db = (new Database())->getConnection();
    $id = $_GET['id'] ?? null;

    if (!$id) { header("Location: index.php?action=productos_gestion"); exit; }

    // --- PROCESAR LA ACTUALIZACIÓN (POST) ---
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        try {
            $sql = "UPDATE productos SET 
                    codigo_barras = :cb, sku = :sku, descripcion = :desc, 
                    marca = :marca, 
                    categoria_id = :cat
                    WHERE id = :id";
            
            $stmt = $db->prepare($sql);
            $stmt->execute([
                'cb'    => trim($_POST['codigo_barras']),
                'sku'   => trim($_POST['sku']),
                'desc'  => trim(strtoupper($_POST['descripcion'])),
                'marca' => !empty($_POST['marca']) ? trim(strtoupper($_POST['marca'])) : null,
                'cat'   => !empty($_POST['categoria_id']) ? intval($_POST['categoria_id']) : null,
                'id'    => $id
            ]);
            
            // 🔥 LA MAGIA ESTÁ ACÁ: Redirigimos al listado con un mensaje de éxito por URL
            
            // Buscá dentro de 'productos_alta', después del $stmt->execute(...), cambiá el header:
            header("Location: index.php?action=productos_gestion&msj=creado");
            exit;
        
        } catch (Exception $e) {
            $error = "❌ Error al actualizar: El código o SKU ya existen.";
        }
    }

    // --- CARGAR DATOS DEL PRODUCTO ---
    $stmt = $db->prepare("SELECT * FROM productos WHERE id = ?");
    $stmt->execute([$id]);
    $p = $stmt->fetch();

    if (!$p) { die("Producto no encontrado."); }

    $listaCategorias = $db->query("SELECT id, nombre FROM categorias ORDER BY nombre ASC")->fetchAll();
    
    require_once __DIR__ . '/../src/Application/Views/productos_editar.php';
    exit;
}
// ==========================================
// --- 2. NUEVA ACCIÓN: ELIMINAR PRODUCTO dentro de productos_gestion ---
if (isset($_GET['action']) && $_GET['action'] === 'productos_eliminar' && isset($_SESSION['usuario_id'])) {
    // Seguridad: Solo el administrador (Rol 1) puede borrar productos
    if ($_SESSION['rol_id'] != 1) { die("Acceso denegado. Solo administradores pueden eliminar productos."); }
    
    $db = (new Database())->getConnection();
    $id = $_GET['id'] ?? null;

    if ($id) {
        try {
            $stmt = $db->prepare("DELETE FROM productos WHERE id = ?");
            $stmt->execute([$id]);
            header("Location: index.php?action=productos_gestion&msj=eliminado");
        } catch (Exception $e) {
            header("Location: index.php?action=productos_gestion&msj=error_borrado");
        }
    } else {
        header("Location: index.php?action=productos_gestion");
    }
    exit;
}
// ==========================================
// MÓDULO: ABM DE USUARIOS
// ==========================================

// 1. LISTADO Y ALTA
if (isset($_GET['action']) && $_GET['action'] === 'usuarios_gestion' && isset($_SESSION['usuario_id'])) {
    if ($_SESSION['rol_id'] != 1) { die("Acceso denegado."); } // Solo el Dios del sistema (Admin)
    $db = (new Database())->getConnection();
    $error = "";

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['usuario']) && !empty($_POST['password'])) {
        try {
            $hash = password_hash(trim($_POST['password']), PASSWORD_DEFAULT);
            $stmt = $db->prepare("INSERT INTO usuarios (nombre_completo, usuario, password, rol_id, estado) VALUES (?, ?, ?, ?, 1)");
            $stmt->execute([
                trim($_POST['nombre_completo']),
                trim($_POST['usuario']),
                $hash,
                $_POST['rol_id']
            ]);
            header("Location: index.php?action=usuarios_gestion");
            exit;
        } catch (Exception $e) {
            $error = "❌ Error: Es posible que el nombre de usuario de inicio de sesión ya exista.";
        }
    }

    // Traemos los usuarios con el nombre de su rol
    $listaUsuarios = $db->query("SELECT u.*, r.nombre as rol_nombre 
                                 FROM usuarios u 
                                 LEFT JOIN roles r ON u.rol_id = r.id 
                                 ORDER BY r.id ASC, u.nombre_completo ASC")->fetchAll();
    
    // Traemos los roles para el formulario
    $listaRoles = $db->query("SELECT id, nombre FROM roles ORDER BY id ASC")->fetchAll();

    require_once __DIR__ . '/../src/Application/Views/usuarios_gestion.php';
    exit;
}

// 2. EDICIÓN
if (isset($_GET['action']) && $_GET['action'] === 'editar_usuario' && isset($_SESSION['usuario_id'])) {
    if ($_SESSION['rol_id'] != 1) { die("Acceso denegado."); }
    $db = (new Database())->getConnection();
    $error = "";

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['id']) && !empty($_POST['usuario'])) {
        try {
            if (!empty($_POST['password'])) {
                // Si escribió una clave nueva, la encriptamos y la actualizamos
                $hash = password_hash(trim($_POST['password']), PASSWORD_DEFAULT);
                $stmt = $db->prepare("UPDATE usuarios SET nombre_completo = ?, usuario = ?, password = ?, rol_id = ?, estado = ? WHERE id = ?");
                $stmt->execute([trim($_POST['nombre_completo']), trim($_POST['usuario']), $hash, $_POST['rol_id'], $_POST['estado'], $_POST['id']]);
            } else {
                // Si la dejó en blanco, actualizamos todo MENOS la contraseña
                $stmt = $db->prepare("UPDATE usuarios SET nombre_completo = ?, usuario = ?, rol_id = ?, estado = ? WHERE id = ?");
                $stmt->execute([trim($_POST['nombre_completo']), trim($_POST['usuario']), $_POST['rol_id'], $_POST['estado'], $_POST['id']]);
            }
            header("Location: index.php?action=usuarios_gestion");
            exit;
        } catch (Exception $e) {
            $error = "❌ Error: El nombre de usuario ya está en uso.";
        }
    }

    $stmt = $db->prepare("SELECT * FROM usuarios WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $usuario_editar = $stmt->fetch();
    
    $listaRoles = $db->query("SELECT id, nombre FROM roles ORDER BY id ASC")->fetchAll();

    require_once __DIR__ . '/../src/Application/Views/editar_usuario.php';
    exit;
}

// 3. ELIMINACIÓN
if (isset($_GET['action']) && $_GET['action'] === 'eliminar_usuario' && isset($_SESSION['usuario_id'])) {
    if ($_SESSION['rol_id'] != 1) { die("Acceso denegado."); }
    $db = (new Database())->getConnection();
    
    try {
        // Evitamos que el admin se borre a sí mismo por accidente
        if ($_GET['id'] == $_SESSION['usuario_id']) {
            die("<div style='padding:20px; font-family:Arial;'><h3>❌ Error</h3><p>No podés eliminar tu propio usuario mientras estás en sesión.</p><a href='index.php?action=usuarios_gestion'>Volver atrás</a></div>");
        }

        $stmt = $db->prepare("DELETE FROM usuarios WHERE id = ?");
        $stmt->execute([$_GET['id']]);
    } catch (Exception $e) {
        die("<div style='padding:20px; font-family:Arial;'><h3>❌ Error</h3><p>No se puede eliminar este usuario porque ya tiene registros de conteos asociados. Recomendación: Editalo y cambiale el Estado a 'Inactivo'.</p><a href='index.php?action=usuarios_gestion'>Volver atrás</a></div>");
    }
    
    header("Location: index.php?action=usuarios_gestion");
    exit;
}
// ==========================================
// ==========================================
// MÓDULO: SUB-MENÚ DE GRÁFICOS Y ACTAS
// ==========================================
if (isset($_GET['action']) && $_GET['action'] === 'menu_graficos') {
    if ($_SESSION['rol_id'] != 1) { die("Acceso denegado."); }
    require_once __DIR__ . '/../src/Application/Views/menu_graficos.php';
    exit;
}
// ==========================================
// MÓDULO: CIERRE DE ACTAS (EVALUACIÓN CLIENTE)
// ==========================================

// 1. EL USUARIO GENERA EL ACTA DESDE EL MODAL
if (isset($_GET['action']) && $_GET['action'] === 'generar_acta' && isset($_SESSION['usuario_id'])) {
    if ($_SESSION['rol_id'] > 2) { die("Acceso denegado."); }
    $db = (new Database())->getConnection();
    
    $local_id = intval($_GET['local_id']);
    // ⚠️ CAMBIO: Ahora el encargado viene del formulario (ventanita), no de la sesión
    $encargado_id = intval($_GET['encargado_id']); 
    $token = bin2hex(random_bytes(16)); // Llave secreta
    
    // ⚠️ CAMBIO: Insertamos en la tabla 'actas'
    $stmt = $db->prepare("INSERT INTO actas (local_id, encargado_id, token_seguridad) VALUES (?, ?, ?)");
    $stmt->execute([$local_id, $encargado_id, $token]);
    
    header("Location: index.php?action=evaluacion_cliente&token=" . $token);
    exit;
}

// 2. LA PANTALLA MODO KIOSCO Y EL PROCESAMIENTO
if (isset($_GET['action']) && $_GET['action'] === 'evaluacion_cliente') {
    $db = (new Database())->getConnection();
    $token = $_GET['token'] ?? '';
    
    // ⚠️ CAMBIO: Buscamos en 'actas'
    $stmt = $db->prepare("SELECT a.*, l.nombre as local_nombre FROM actas a JOIN locales l ON a.local_id = l.id WHERE a.token_seguridad = ?");
    $stmt->execute([$token]);
    $evaluacion = $stmt->fetch();
    
    if (!$evaluacion) { die("<h2 style='text-align:center; margin-top:50px; font-family:Arial;'>❌ Error: Acta inválida.</h2>"); }
    
    if ($evaluacion['completada'] == 1) {
        die("<div style='text-align:center; margin-top:50px; font-family:Arial;'>
                <h2>✔️ Acta Cerrada</h2>
                <p>Este documento ya fue firmado y enviado de forma confidencial.</p>
                <a href='index.php?action=dashboard' style='padding:10px 20px; background:#00897b; color:white; text-decoration:none; border-radius:5px;'>Volver al Sistema</a>
             </div>");
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['firma'])) {
        // ⚠️ CAMBIO: Actualizamos 'actas'
        $stmt = $db->prepare("UPDATE actas SET 
            nombre_evaluador = ?, 
            estrellas_puntualidad = ?, 
            estrellas_organizacion = ?, 
            estrellas_prolijidad = ?, 
            estrellas_trato = ?, 
            comentario = ?, 
            firma_base64 = ?, 
            completada = 1, 
            fecha_cierre = CURRENT_TIMESTAMP 
            WHERE id = ?");
            
        $stmt->execute([
            trim($_POST['nombre_evaluador']),
            intval($_POST['est_puntualidad']), 
            intval($_POST['est_organizacion']),
            intval($_POST['est_prolijidad']), 
            intval($_POST['est_trato']),
            trim($_POST['comentario']), 
            $_POST['firma'], 
            $evaluacion['id']
        ]);
        
        header("Location: index.php?action=evaluacion_exito");
        exit;
    }
    
    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    require_once __DIR__ . '/../src/Application/Views/evaluacion_cliente.php';
    exit;
}

// 3. PANTALLA DE ÉXITO
if (isset($_GET['action']) && $_GET['action'] === 'evaluacion_exito') {
    die("<div style='text-align:center; margin-top:50px; font-family:Arial;'>
            <h1 style='color:#2e7d32; font-size:60px; margin-bottom:10px;'>✔️</h1>
            <h2>Acta Conformada con Éxito</h2>
            <p>Muchas gracias. Los datos han sido encriptados y enviados a la administración.</p>
            <p style='color:#d32f2f; font-weight:bold;'>Por favor, devuelva el dispositivo al encargado de MACARO.</p>
            <br><br>
            <a href='index.php?action=dashboard' style='padding:15px 30px; background:#666; color:white; text-decoration:none; border-radius:8px; font-weight:bold;'>Salir al Menú Principal</a>
         </div>");
}


// 4. RANKING DE PIQUEADORES
if (isset($_GET['action']) && $_GET['action'] === 'ranking_piqueadores') {
    if ($_SESSION['rol_id'] != 1) { die("Acceso denegado."); }
    $db = (new Database())->getConnection();

    // Consultamos el total de ítems y zonas en la tabla CORRECTA: 'conteos'
    $query = "SELECT 
                u.nombre_completo, 
                SUM(c.cantidad) as total_articulos, 
                COUNT(DISTINCT c.zona_id) as total_zonas 
              FROM usuarios u 
              LEFT JOIN conteos c ON u.id = c.usuario_id 
              WHERE u.rol_id = 3 
              GROUP BY u.id 
              ORDER BY total_articulos DESC";
    
    $ranking = $db->query($query)->fetchAll();

    require_once __DIR__ . '/../src/Application/Views/ranking_piqueadores.php';
    exit;
}
// ==========================================
// ==========================================
// NUEVO: MENÚ PRINCIPAL DE ACTAS - MODAL
// ==========================================
if (isset($_GET['action']) && $_GET['action'] === 'actas_menu' && isset($_SESSION['usuario_id'])) {
    if ($_SESSION['rol_id'] > 2) { die("Acceso denegado."); }
    $db = (new Database())->getConnection();
    // Traemos los encargados activos para el Modal de "Nueva Acta"
    $listaEncargados = $db->query("SELECT id, nombre_completo FROM usuarios WHERE rol_id = 2 AND estado = 1 ORDER BY nombre_completo ASC")->fetchAll();
    // Traemos los locales activos para el Modal de "Nueva Acta"
    $listaLocales = $db->query("SELECT id, nombre FROM locales WHERE estado = 1 ORDER BY nombre ASC")->fetchAll();

    require_once __DIR__ . '/../src/Application/Views/actas_menu.php';
    exit;
}

// ==========================================
// MÓDULO: IMPORTACIÓN DE CSV (LOYVERSE Y MACARO)
// ==========================================
if (isset($_GET['action']) && $_GET['action'] === 'importar_csv' && isset($_SESSION['usuario_id'])) {
    if ($_SESSION['rol_id'] != 1) { die("Acceso denegado. Solo administradores."); }
    $db = (new Database())->getConnection();

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['archivo_csv'])) {
        $archivo = $_FILES['archivo_csv']['tmp_name'];

        if (($handle = fopen($archivo, "r")) !== FALSE) {
            // 1. Leer la primera fila y detectar el delimitador correcto
            $delimitador = ",";
            $headers = fgetcsv($handle, 1000, $delimitador); 
            
            // Si viene separado por punto y coma, cambiamos el delimitador
            if (count($headers) <= 1) {
                fclose($handle);
                $handle = fopen($archivo, "r");
                $delimitador = ";";
                $headers = fgetcsv($handle, 1000, $delimitador);
            }

            // --- EL TRUCO MÁGICO: ELIMINAR EL CARÁCTER FANTASMA (BOM) ---
            $headers[0] = str_replace(chr(0xEF).chr(0xBB).chr(0xBF), '', $headers[0]);
            // -----------------------------------------------------------

            // 2. BUSCADOR INTELIGENTE DE COLUMNAS
            $idx_nombre = false;
            $idx_codigo = false;
            $idx_sku = false;

            foreach ($headers as $index => $columna) {
                $col = strtolower(trim($columna));
                $col = str_replace(['á','é','í','ó','ú'], ['a','e','i','o','u'], $col);

                if ($col === 'nombre' || $col === 'nombre del articulo' || $col === 'item name' || $col === 'descripcion') {
                    $idx_nombre = $index;
                }
                if ($col === 'codigo de barras' || $col === 'barcode') {
                    $idx_codigo = $index;
                }
                if ($col === 'ref' || $col === 'sku') {
                    $idx_sku = $index;
                }
            }

            // 3. Verificamos si encontramos lo mínimo indispensable
            if ($idx_nombre === false || $idx_codigo === false) {
                $error = "❌ El archivo no es válido. Faltan las columnas 'Nombre/Descripción' o 'Código de barras'.";
            } else {
                $insertados = 0;
                $duplicados = 0;

                $check_stmt = $db->prepare("SELECT id FROM productos WHERE codigo_barras = ?");
                $insert_stmt = $db->prepare("INSERT INTO productos (codigo_barras, sku, descripcion) VALUES (:cb, :sku, :desc)");

                // 4. Recorrer fila por fila
                while (($datos = fgetcsv($handle, 1000, $delimitador)) !== FALSE) {
                    if (empty($datos) || count($datos) < 2) continue;

                    $codigo = trim($datos[$idx_codigo]);
                    $descripcion = trim(strtoupper($datos[$idx_nombre]));

                    if (empty($codigo)) continue;

                    $check_stmt->execute([$codigo]);
                    if (!$check_stmt->fetch()) {
                        
                        // Protección contra valores vacíos para que la base de datos no tire error
                        $sku = ($idx_sku !== false && isset($datos[$idx_sku]) && $datos[$idx_sku] !== '') ? trim($datos[$idx_sku]) : $codigo;
                       

                        $insert_stmt->execute([
                            'cb' => $codigo,
                            'sku' => $sku,
                            'desc' => $descripcion,
                        ]);
                        $insertados++;
                    } else {
                        $duplicados++;
                    }
                }
                $exito = "✅ Importación exitosa. Se agregaron <strong>$insertados</strong> productos nuevos. ($duplicados ya existían).";
            }
            fclose($handle);
        } else {
            $error = "❌ No se pudo leer el archivo subido.";
        }
    }

    require_once __DIR__ . '/../src/Application/Views/importar_csv.php';
    exit;
}
// ==========================================
// MÓDULO: CALENDARIO DE AUDITORÍAS (CON SINCRONIZACIÓN)
// ==========================================
// --- ACCIÓN A: CARGAR EL CALENDARIO ---
if (isset($_GET['action']) && $_GET['action'] === 'calendario') {
    if ($_SESSION['rol_id'] > 2) { die("Acceso denegado."); }
    $db = (new Database())->getConnection();

    $locales = $db->query("SELECT id, nombre FROM locales ORDER BY nombre")->fetchAll();
    $encargados = $db->query("SELECT id, nombre_completo FROM usuarios WHERE rol_id = 2 AND estado = 1 ORDER BY nombre_completo")->fetchAll();

    // 1. DATOS PARA PINTAR EL CALENDARIO VISUAL (Todos)
    $eventos_calendario = $db->query("
        SELECT ap.id, ap.fecha_auditoria, ap.hora_auditoria, l.nombre as local_nombre, u.nombre_completo as encargado_nombre, ap.estado
        FROM auditorias_programadas ap
        JOIN locales l ON ap.local_id = l.id
        JOIN usuarios u ON ap.encargado_id = u.id
    ")->fetchAll();

    $eventos_js = [];
    foreach($eventos_calendario as $e) {
        $color = '#2196f3'; 
        if($e['estado'] === 'Completada') $color = '#4caf50';
        if($e['estado'] === 'Cancelada') $color = '#f44336';

        $eventos_js[] = [
            'id'    => $e['id'],
            'title' => $e['local_nombre'] . " (" . $e['encargado_nombre'] . ")",
            'start' => $e['fecha_auditoria'] . 'T' . $e['hora_auditoria'],
            'backgroundColor' => $color,
            'borderColor' => $color
        ];
    }

// 2. MATEMÁTICA PARA LA TABLA (Hoy + 30 días)
    $hoyObj = new DateTime();
    $hoy_sql = $hoyObj->format('Y-m-d'); // '2026-04-07'
    
    $finPeriodo = clone $hoyObj;
    $finPeriodo->modify("+30 days"); 
    $fecha_fin = $finPeriodo->format('Y-m-d');

    // 3. DATOS PARA LA TABLA (Futuro + Pendientes del pasado)
    $stmt_tabla = $db->prepare("
        SELECT ap.id, ap.fecha_auditoria, ap.hora_auditoria, ap.local_id, ap.encargado_id, 
               l.nombre as local_nombre, u.nombre_completo as encargado_nombre, ap.estado
        FROM auditorias_programadas ap
        JOIN locales l ON ap.local_id = l.id
        JOIN usuarios u ON ap.encargado_id = u.id
        WHERE 
            (ap.fecha_auditoria BETWEEN :hoy AND :fin) 
            OR (ap.fecha_auditoria < :hoy_mismo AND ap.estado = 'Pendiente')
        ORDER BY ap.fecha_auditoria ASC, ap.hora_auditoria ASC
    ");
    
    $stmt_tabla->execute([
        'hoy'       => $hoy_sql,
        'fin'       => $fecha_fin,
        'hoy_mismo' => $hoy_sql
    ]);
    $eventos_db = $stmt_tabla->fetchAll(); 
    
    // Título dinámico para la vista
    $titulo_periodo = "Próximos 30 días y Pendientes (al " . $finPeriodo->format('d/m/Y') . ")";

    // Atrapamos el error si intentan agendar doble para pasarlo a la vista
    $error = isset($_GET['error']) ? $_GET['error'] : '';

    require_once __DIR__ . '/../src/Application/Views/calendario.php';
    exit;
}

// --- ACCIÓN B: GUARDAR NUEVA (Sincroniza Local a Activo) ---
if (isset($_GET['action']) && $_GET['action'] === 'guardar_auditoria') {
    $db = (new Database())->getConnection();
    $local_id = intval($_POST['local_id']);
    
    // BARRERA DE SEGURIDAD: Comprobamos si el local ya tiene una auditoría "Pendiente"
    $check_stmt = $db->prepare("SELECT id FROM auditorias_programadas WHERE local_id = ? AND estado = 'Pendiente'");
    $check_stmt->execute([$local_id]);
    
    if ($check_stmt->fetch()) {
        // Si ya tiene una pendiente, cancelamos todo y redirigimos con un error
        header("Location: index.php?action=calendario&error=auditoria_duplicada");
        exit;
    }

    $stmt = $db->prepare("INSERT INTO auditorias_programadas (local_id, encargado_id, fecha_auditoria, hora_auditoria, estado) VALUES (?, ?, ?, ?, 'Pendiente')");
    $stmt->execute([$local_id, intval($_POST['encargado_id']), $_POST['fecha'], $_POST['hora']]);
    
    // Al agendar, el local se pone en estado 1 (Visible)
    $db->prepare("UPDATE locales SET estado = 1 WHERE id = ?")->execute([$local_id]);
    
    header("Location: index.php?action=calendario&res=creado");
    exit;
}

// --- ACCIÓN C: ACTUALIZAR EXISTENTE (Sincroniza según el Estado) ---
if (isset($_GET['action']) && $_GET['action'] === 'actualizar_auditoria') {
    $db = (new Database())->getConnection();
    $local_id = intval($_POST['local_id']);
    $estado_cal = $_POST['estado'];

    $stmt = $db->prepare("UPDATE auditorias_programadas SET local_id = ?, encargado_id = ?, fecha_auditoria = ?, hora_auditoria = ?, estado = ? WHERE id = ?");
    $stmt->execute([$local_id, intval($_POST['encargado_id']), $_POST['fecha'], $_POST['hora'], $estado_cal, intval($_POST['auditoria_id'])]);
    
    // Si es Pendiente -> Local Visible (1). Si no -> Local Oculto (0)
    $estado_local = ($estado_cal === 'Pendiente') ? 1 : 0;
    $db->prepare("UPDATE locales SET estado = ? WHERE id = ?")->execute([$estado_local, $local_id]);
    
    header("Location: index.php?action=calendario&res=editado");
    exit;
}

// --- ACCIÓN D: CANCELAR (Sincroniza Local a Oculto) ---
if (isset($_GET['action']) && $_GET['action'] === 'cancelar_auditoria') {
    $db = (new Database())->getConnection();
    $id = intval($_GET['id']);
    
    $stmt = $db->prepare("SELECT local_id FROM auditorias_programadas WHERE id = ?");
    $stmt->execute([$id]);
    $auditoria = $stmt->fetch();
    
    if($auditoria) {
        $db->prepare("UPDATE locales SET estado = 0 WHERE id = ?")->execute([$auditoria['local_id']]);
        $db->prepare("UPDATE auditorias_programadas SET estado = 'Cancelada' WHERE id = ?")->execute([$id]);
    }
    
    header("Location: index.php?action=calendario&res=cancelado");
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