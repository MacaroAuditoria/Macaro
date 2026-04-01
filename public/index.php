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
// MÓDULO DE MARCAS (ABM COMPLETO)
// ==========================================

// 1. ALTA Y LECTURA
if (isset($_GET['action']) && $_GET['action'] === 'marcas' && isset($_SESSION['usuario_id'])) {
    if ($_SESSION['rol_id'] > 2) { die("Acceso denegado."); }
    $db = (new Database())->getConnection();

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['nueva_marca'])) {
        try {
            $stmt = $db->prepare("INSERT INTO marcas (nombre) VALUES (:nombre)");
            $stmt->execute(['nombre' => trim($_POST['nueva_marca'])]);
            header("Location: index.php?action=marcas");
            exit;
        } catch (Exception $e) {
            $error = "La marca ya existe o hubo un error.";
        }
    }
    $stmt = $db->query("SELECT id, nombre FROM marcas ORDER BY nombre ASC");
    $listaMarcas = $stmt->fetchAll();
    require_once __DIR__ . '/../src/Application/Views/marcas.php';
    exit;
}

// 2. MODIFICACIÓN (EDICIÓN)
if (isset($_GET['action']) && $_GET['action'] === 'editar_marca' && isset($_SESSION['usuario_id'])) {
    if ($_SESSION['rol_id'] > 2) { die("Acceso denegado."); }
    $db = (new Database())->getConnection();

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['nombre']) && !empty($_POST['id'])) {
        try {
            $stmt = $db->prepare("UPDATE marcas SET nombre = :nombre WHERE id = :id");
            $stmt->execute(['nombre' => trim($_POST['nombre']), 'id' => $_POST['id']]);
            header("Location: index.php?action=marcas"); 
            exit;
        } catch (Exception $e) {
            $error = "La marca ya existe o hubo un error.";
        }
    }
    if (isset($_GET['id'])) {
        $stmt = $db->prepare("SELECT id, nombre FROM marcas WHERE id = :id");
        $stmt->execute(['id' => $_GET['id']]);
        $marcaActual = $stmt->fetch();
        if ($marcaActual) {
            require_once __DIR__ . '/../src/Application/Views/editar_marca.php';
            exit;
        }
    }
    header("Location: index.php?action=marcas");
    exit;
}

// 3. BAJA (ELIMINACIÓN)
if (isset($_GET['action']) && $_GET['action'] === 'eliminar_marca' && isset($_SESSION['usuario_id'])) {
    if ($_SESSION['rol_id'] > 2) { die("Acceso denegado."); }
    if (isset($_GET['id'])) {
        $db = (new Database())->getConnection();
        try {
            $stmt = $db->prepare("DELETE FROM marcas WHERE id = :id");
            $stmt->execute(['id' => $_GET['id']]);
        } catch (Exception $e) {
            die("<div style='padding:20px; font-family:Arial;'><h3>❌ Error</h3><p>No se puede eliminar esta marca porque ya está vinculada a un producto.</p><a href='index.php?action=marcas'>Volver atrás</a></div>");
        }
    }
    header("Location: index.php?action=marcas");
    exit;
}
// ==========================================

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
// MÓDULO DE PRODUCTOS (CATÁLOGO CENTRAL)
// ==========================================

if (isset($_GET['action']) && $_GET['action'] === 'productos' && isset($_SESSION['usuario_id'])) {
    if ($_SESSION['rol_id'] > 2) { die("Acceso denegado."); }
    $db = (new Database())->getConnection();

    // 1. Si enviaron el formulario para guardar un nuevo producto
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['codigo_barras'])) {
        try {
            $sql = "INSERT INTO productos (codigo_barras, sku, descripcion, precio_compra, precio_venta, marca_id, categoria_id, distribuidor_id) 
                    VALUES (:codigo_barras, :sku, :descripcion, :precio_compra, :precio_venta, :marca_id, :categoria_id, :distribuidor_id)";
            
            $stmt = $db->prepare($sql);
            $stmt->execute([
                'codigo_barras' => trim($_POST['codigo_barras']),
                'sku' => !empty(trim($_POST['sku'])) ? trim($_POST['sku']) : null, // El SKU es opcional
                'descripcion' => trim($_POST['descripcion']),
                'precio_compra' => $_POST['precio_compra'],
                'precio_venta' => $_POST['precio_venta'],
                'marca_id' => $_POST['marca_id'],
                'categoria_id' => $_POST['categoria_id'],
                'distribuidor_id' => $_POST['distribuidor_id']
            ]);
            
            $exito = "¡Producto guardado correctamente!";
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $error = "Error: Ese código de barras ya existe en el catálogo.";
            } else {
                $error = "Hubo un error al guardar el producto: " . $e->getMessage();
            }
        }
    }

    // 2. Traemos los datos para rellenar los combobox del formulario
    $listaMarcas = $db->query("SELECT id, nombre FROM marcas ORDER BY nombre ASC")->fetchAll();
    $listaCategorias = $db->query("SELECT id, nombre FROM categorias ORDER BY nombre ASC")->fetchAll();
    $listaDistribuidores = $db->query("SELECT id, nombre FROM distribuidores ORDER BY nombre ASC")->fetchAll();

    // 3. Traemos la lista de productos ya creados (Unimos las tablas para traer los nombres reales en lugar de los IDs)
    $sqlLista = "SELECT p.id, p.codigo_barras, p.descripcion, p.precio_venta, 
                        m.nombre AS marca_nombre, c.nombre AS categoria_nombre 
                 FROM productos p 
                 INNER JOIN marcas m ON p.marca_id = m.id 
                 INNER JOIN categorias c ON p.categoria_id = c.id 
                 ORDER BY p.id DESC";
    $listaProductos = $db->query($sqlLista)->fetchAll();

    // 4. Mostramos la vista
    require_once __DIR__ . '/../src/Application/Views/productos.php';
    exit;
}
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