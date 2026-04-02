<?php
// 1. Iniciamos el sistema de memoria temporal (Sesiones)
session_start();

require_once __DIR__ . '/../vendor/autoload.php';
use App\Infrastructure\Database;

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

        // Verificamos si esta zona YA SE CERRÓ para este Local Y este Sector específico.
        $check = $db->prepare("SELECT id FROM zonas_cerradas WHERE local_id = ? AND sector_id <=> ? AND zona_id = ?");
        $check->execute([$local, $sector, $zona]);
        
        if ($check->fetch()) {
            $error = "❌ Esa Zona ya fue completada y cerrada en este Sector.";
        } else {
            // ---> NUEVA TÉCNICA: CHECKPOINT POR ID <---
            // Buscamos cuál es el último número registrado en la tabla
            $max_id = $db->query("SELECT MAX(id) FROM conteos")->fetchColumn();
            
            $_SESSION['piqueo'] = [
                'local_id' => $local,
                'sector_id' => $sector,
                'zona_id' => $zona,
                'start_id' => $max_id ? $max_id : 0 // Guardamos ese número (o 0 si la tabla está vacía)
            ];
            header("Location: index.php?action=piqueo_escaner");
            exit;
        }
    }

    $locales = $db->query("SELECT id, nombre FROM locales ORDER BY nombre ASC")->fetchAll();
    $sectores = $db->query("SELECT id, nombre FROM sectores ORDER BY nombre ASC")->fetchAll();
    $zonas = $db->query("SELECT id, codigo FROM zonas ORDER BY codigo ASC")->fetchAll();
    
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
        $codigo = trim($_POST['codigo_barras']);
        $cantidad = !empty($_POST['cantidad']) ? $_POST['cantidad'] : 1;

        $stmt = $db->prepare("INSERT INTO conteos (local_id, sector_id, zona_id, usuario_id, codigo_barras, cantidad) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$_SESSION['piqueo']['local_id'], $_SESSION['piqueo']['sector_id'], $_SESSION['piqueo']['zona_id'], $_SESSION['usuario_id'], $codigo, $cantidad]);

        $check = $db->prepare("SELECT descripcion FROM productos WHERE codigo_barras = ?");
        $check->execute([$codigo]);
        $prod = $check->fetch();

        if ($prod) {
            // NUEVO: Calculamos cuánto hay EN TOTAL de ESTE producto específico en ESTA zona
            $stmt_item = $db->prepare("SELECT SUM(cantidad) FROM conteos 
                                       WHERE local_id = ? AND sector_id <=> ? AND zona_id = ? AND codigo_barras = ?");
            $stmt_item->execute([
                $_SESSION['piqueo']['local_id'], 
                $_SESSION['piqueo']['sector_id'], 
                $_SESSION['piqueo']['zona_id'], 
                $codigo
            ]);
            $total_item = $stmt_item->fetchColumn();
            $total_item = $total_item ? number_format($total_item, 2, '.', '') : "0.00"; // Formateamos a 2 decimales

            // NUEVO DISEÑO DE TARJETA: Total grande, suma pequeña al lado
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
        
        // Borramos SOLO lo que ESTE usuario escaneó en ESTA zona cuyo ID sea MAYOR al checkpoint
        $stmt = $db->prepare("DELETE FROM conteos 
                              WHERE local_id = ? 
                              AND sector_id <=> ? 
                              AND zona_id = ? 
                              AND usuario_id = ? 
                              AND id > ?");
        
        $stmt->execute([
            $_SESSION['piqueo']['local_id'],
            $_SESSION['piqueo']['sector_id'],
            $_SESSION['piqueo']['zona_id'],
            $_SESSION['usuario_id'],
            $_SESSION['piqueo']['start_id']
        ]);
        
        unset($_SESSION['piqueo']); // Apagamos la memoria temporal
    }
    header("Location: index.php?action=piqueo_config");
    exit;
}

// 4. Terminar y Bloquear Zona (AHORA CON SECTOR INCLUIDO)
if (isset($_GET['action']) && $_GET['action'] === 'piqueo_terminar_zona' && isset($_SESSION['usuario_id'])) {
    if (isset($_SESSION['piqueo']['zona_id'])) {
        $db = (new Database())->getConnection();
        
        $stmt = $db->prepare("INSERT INTO zonas_cerradas (local_id, sector_id, zona_id) VALUES (?, ?, ?)");
        $stmt->execute([$_SESSION['piqueo']['local_id'], $_SESSION['piqueo']['sector_id'], $_SESSION['piqueo']['zona_id']]);
        
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