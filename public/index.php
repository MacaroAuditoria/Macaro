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

// 1. LISTADO Y ALTA (Actualizado con Teléfono, Email y Fecha Nacimiento)
if (isset($_GET['action']) && $_GET['action'] === 'usuarios_gestion' && isset($_SESSION['usuario_id'])) {
    if ($_SESSION['rol_id'] != 1) { die("Acceso denegado."); }
    $db = (new Database())->getConnection();
    $error = "";

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['usuario']) && !empty($_POST['password'])) {
        try {
            $hash = password_hash(trim($_POST['password']), PASSWORD_DEFAULT);
            $stmt = $db->prepare("INSERT INTO usuarios (nombre_completo, usuario, password, rol_id, estado, telefono, email, fecha_nacimiento) VALUES (?, ?, ?, ?, 1, ?, ?, ?)");
            $stmt->execute([
                trim($_POST['nombre_completo']),
                trim($_POST['usuario']),
                $hash,
                $_POST['rol_id'],
                !empty($_POST['telefono']) ? trim($_POST['telefono']) : null,
                !empty($_POST['email']) ? trim($_POST['email']) : null,
                !empty($_POST['fecha_nacimiento']) ? $_POST['fecha_nacimiento'] : null
            ]);
            header("Location: index.php?action=usuarios_gestion");
            exit;
        } catch (Exception $e) {
            $error = "❌ Error: El nombre de usuario ya existe.";
        }
    }

    $listaUsuarios = $db->query("SELECT u.*, r.nombre as rol_nombre FROM usuarios u LEFT JOIN roles r ON u.rol_id = r.id ORDER BY r.id ASC, u.nombre_completo ASC")->fetchAll();
    $listaRoles = $db->query("SELECT id, nombre FROM roles ORDER BY id ASC")->fetchAll();

    require_once __DIR__ . '/../src/Application/Views/usuarios_gestion.php';
    exit;
}

// 2. EDICIÓN (Actualizado)
if (isset($_GET['action']) && $_GET['action'] === 'editar_usuario' && isset($_SESSION['usuario_id'])) {
    if ($_SESSION['rol_id'] != 1) { die("Acceso denegado."); }
    $db = (new Database())->getConnection();

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['id'])) {
        $tel = !empty($_POST['telefono']) ? trim($_POST['telefono']) : null;
        $mail = !empty($_POST['email']) ? trim($_POST['email']) : null;
        $f_nac = !empty($_POST['fecha_nacimiento']) ? $_POST['fecha_nacimiento'] : null;

        if (!empty($_POST['password'])) {
            $hash = password_hash(trim($_POST['password']), PASSWORD_DEFAULT);
            $stmt = $db->prepare("UPDATE usuarios SET nombre_completo = ?, usuario = ?, password = ?, rol_id = ?, estado = ?, telefono = ?, email = ?, fecha_nacimiento = ? WHERE id = ?");
            $stmt->execute([trim($_POST['nombre_completo']), trim($_POST['usuario']), $hash, $_POST['rol_id'], $_POST['estado'], $tel, $mail, $f_nac, $_POST['id']]);
        } else {
            $stmt = $db->prepare("UPDATE usuarios SET nombre_completo = ?, usuario = ?, rol_id = ?, estado = ?, telefono = ?, email = ?, fecha_nacimiento = ? WHERE id = ?");
            $stmt->execute([trim($_POST['nombre_completo']), trim($_POST['usuario']), $_POST['rol_id'], $_POST['estado'], $tel, $mail, $f_nac, $_POST['id']]);
        }
        header("Location: index.php?action=usuarios_gestion");
        exit;
    }

    $stmt = $db->prepare("SELECT * FROM usuarios WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $usuario_editar = $stmt->fetch();
    $listaRoles = $db->query("SELECT id, nombre FROM roles ORDER BY id ASC")->fetchAll();

    require_once __DIR__ . '/../src/Application/Views/editar_usuario.php';
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
                  FROM evaluaciones e 
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
        $encargados = $db->query("SELECT id, nombre_completo FROM usuarios ORDER BY nombre_completo")->fetchAll();

        require_once __DIR__ . '/../src/Application/Views/actas_buscar.php';
        exit;
    }

    // 2. VER UN ACTA ESPECÍFICA (SOLO LECTURA)
    if ($_GET['action'] === 'acta_ver') {
        $stmt = $db->prepare("SELECT e.*, l.nombre as local_nombre, u.nombre_completo as encargado_nombre 
                              FROM evaluaciones e 
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
                FROM evaluaciones 
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
// MÓDULO: ABM DE SECTORES
// ==========================================

// 1. LISTADO Y ALTA
if (isset($_GET['action']) && $_GET['action'] === 'ajustes_sectores' && isset($_SESSION['usuario_id'])) {
    // Permite acceso a Admin (1) y Encargados (2)
    if ($_SESSION['rol_id'] > 2) { die("Acceso denegado."); }
    $db = (new Database())->getConnection();

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['nombre']) && !empty($_POST['local_id'])) {
        $stmt = $db->prepare("INSERT INTO sectores (nombre, local_id) VALUES (?, ?)");
        $stmt->execute([
            trim($_POST['nombre']),
            $_POST['local_id']
        ]);
        header("Location: index.php?action=ajustes_sectores");
        exit;
    }

    // Traemos los sectores y le pegamos el nombre del local al que pertenecen
    $listaSectores = $db->query("SELECT s.*, l.nombre as local_nombre 
                                 FROM sectores s 
                                 LEFT JOIN locales l ON s.local_id = l.id 
                                 ORDER BY l.nombre ASC, s.nombre ASC")->fetchAll();
    
    // Traemos los locales para armar el menú desplegable (ComboBox)
    $listaLocales = $db->query("SELECT id, nombre FROM locales ORDER BY nombre ASC")->fetchAll();

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
            trim($_POST['nombre']),
            $_POST['local_id'],
            $_POST['id']
        ]);
        header("Location: index.php?action=ajustes_sectores");
        exit;
    }

    $stmt = $db->prepare("SELECT * FROM sectores WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $sector = $stmt->fetch();
    
    $listaLocales = $db->query("SELECT id, nombre FROM locales ORDER BY nombre ASC")->fetchAll();

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
// MÓDULO: ABM DE LOCALES (INVENTARIOS)
// ==========================================

// 1. LISTADO Y ALTA
if (isset($_GET['action']) && $_GET['action'] === 'ajustes_locales' && isset($_SESSION['usuario_id'])) {
    if ($_SESSION['rol_id'] != 1) { die("Acceso denegado."); }
    $db = (new Database())->getConnection();

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['nombre'])) {
        $stmt = $db->prepare("INSERT INTO locales (nombre, direccion, encargado_id) VALUES (?, ?, ?)");
        $stmt->execute([
            trim($_POST['nombre']),
            trim($_POST['direccion']),
            !empty($_POST['encargado_id']) ? $_POST['encargado_id'] : null
        ]);
        header("Location: index.php?action=ajustes_locales");
        exit;
    }

    // Traemos los locales con el nombre del encargado
    $listaLocales = $db->query("SELECT l.*, u.nombre_completo as encargado_nombre 
                                FROM locales l 
                                LEFT JOIN usuarios u ON l.encargado_id = u.id 
                                ORDER BY l.nombre ASC")->fetchAll();
    
    // Traemos los usuarios que pueden ser encargados (Admin y Encargados activos)
    $listaUsuarios = $db->query("SELECT id, nombre_completo FROM usuarios WHERE rol_id <= 2 AND estado = 1 ORDER BY nombre_completo ASC")->fetchAll();

    require_once __DIR__ . '/../src/Application/Views/ajustes_locales.php';
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

// 1. EL ENCARGADO GENERA EL ACTA (Botón en el Monitor)
if (isset($_GET['action']) && $_GET['action'] === 'generar_acta' && isset($_SESSION['usuario_id'])) {
    if ($_SESSION['rol_id'] > 2) { die("Acceso denegado."); }
    $db = (new Database())->getConnection();
    
    $local_id = intval($_GET['local_id']);
    $encargado_id = $_SESSION['usuario_id'];
    $token = bin2hex(random_bytes(16)); // Llave secreta
    
    $stmt = $db->prepare("INSERT INTO evaluaciones (local_id, encargado_id, token_seguridad) VALUES (?, ?, ?)");
    $stmt->execute([$local_id, $encargado_id, $token]);
    
    header("Location: index.php?action=evaluacion_cliente&token=" . $token);
    exit;
}

// 2. LA PANTALLA MODO KIOSCO Y EL PROCESAMIENTO
if (isset($_GET['action']) && $_GET['action'] === 'evaluacion_cliente') {
    $db = (new Database())->getConnection();
    $token = $_GET['token'] ?? '';
    
    $stmt = $db->prepare("SELECT e.*, l.nombre as local_nombre FROM evaluaciones e JOIN locales l ON e.local_id = l.id WHERE e.token_seguridad = ?");
    $stmt->execute([$token]);
    $evaluacion = $stmt->fetch();
    
    if (!$evaluacion) { die("<h2 style='text-align:center; margin-top:50px; font-family:Arial;'>❌ Error: Acta inválida.</h2>"); }
    
    // Si el ticket ya fue quemado
    if ($evaluacion['completada'] == 1) {
        die("<div style='text-align:center; margin-top:50px; font-family:Arial;'>
                <h2>✔️ Acta Cerrada</h2>
                <p>Este documento ya fue firmado y enviado de forma confidencial.</p>
                <a href='index.php?action=dashboard' style='padding:10px 20px; background:#00897b; color:white; text-decoration:none; border-radius:5px;'>Volver al Sistema</a>
             </div>");
    }

    // Si viene el POST del formulario con la firma
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['firma'])) {
        $stmt = $db->prepare("UPDATE evaluaciones SET 
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
    
    // Prohibir al navegador guardar la pantalla para que no puedan volver atrás
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
// MÓDULO: CALENDARIO DE AUDITORÍAS (CON SINCRONIZACIÓN)
// ==========================================

// --- ACCIÓN A: CARGAR EL CALENDARIO ---
if (isset($_GET['action']) && $_GET['action'] === 'calendario') {
    if ($_SESSION['rol_id'] > 2) { die("Acceso denegado."); }
    $db = (new Database())->getConnection();

    // En el calendario mostramos TODOS los locales para poder reactivarlos si hace falta
    $locales = $db->query("SELECT id, nombre FROM locales ORDER BY nombre")->fetchAll();
    $encargados = $db->query("SELECT id, nombre_completo FROM usuarios WHERE rol_id = 2 AND estado = 1 ORDER BY nombre_completo")->fetchAll();

    $eventos_db = $db->query("
        SELECT ap.id, ap.fecha_auditoria, ap.hora_auditoria, ap.local_id, ap.encargado_id, 
               l.nombre as local_nombre, u.nombre_completo as encargado_nombre, ap.estado
        FROM auditorias_programadas ap
        JOIN locales l ON ap.local_id = l.id
        JOIN usuarios u ON ap.encargado_id = u.id
        ORDER BY ap.fecha_auditoria DESC
    ")->fetchAll();

    $eventos_js = [];
    foreach($eventos_db as $e) {
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
    require_once __DIR__ . '/../src/Application/Views/calendario.php';
    exit;
}

// --- ACCIÓN B: GUARDAR NUEVA (Sincroniza Local a Activo) ---
if (isset($_GET['action']) && $_GET['action'] === 'guardar_auditoria') {
    $db = (new Database())->getConnection();
    $local_id = intval($_POST['local_id']);
    
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