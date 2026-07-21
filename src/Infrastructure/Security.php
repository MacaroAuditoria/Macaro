<?php

namespace App\Infrastructure;

use PDO;

/**
 * Funciones de seguridad transversales al sistema:
 * - Tokens CSRF (para que no se pueda forzar una acción desde otro sitio)
 * - Control de intentos de login (para frenar fuerza bruta)
 *
 * Se usa como clase estática para no tener que instanciarla en cada action.
 */
class Security
{
    // ---------------------------------------------------------
    // CSRF
    // ---------------------------------------------------------

    /** Genera (o reutiliza) el token CSRF de la sesión actual. */
    public static function tokenCSRF(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /** Devuelve el <input type="hidden"> listo para pegar dentro de un <form>. */
    public static function campoCSRF(): string
    {
        $token = htmlspecialchars(self::tokenCSRF(), ENT_QUOTES);
        return "<input type=\"hidden\" name=\"csrf_token\" value=\"{$token}\">";
    }

    /**
     * Valida el token CSRF recibido en un POST. Si no coincide, corta la
     * ejecución con un 403 (esto evita que un formulario/enlace armado desde
     * otro sitio pueda ejecutar acciones usando la sesión de un usuario logueado).
     */
    public static function validarCSRF(): void
    {
        $enviado = $_POST['csrf_token'] ?? '';
        $esperado = $_SESSION['csrf_token'] ?? '';

        if (!$enviado || !$esperado || !hash_equals($esperado, $enviado)) {
            http_response_code(403);
            die('Solicitud inválida o expirada (token de seguridad incorrecto). Volvé atrás, refrescá la página e intentá de nuevo.');
        }
    }

    /**
     * Igual que validarCSRF() pero para las acciones destructivas que hoy
     * se disparan con un simple link (GET) en vez de un formulario —
     * por ejemplo "Borrar producto". Sin esto, cualquier página externa
     * podría poner <img src="index.php?action=productos_eliminar&id=1">
     * y borrar datos con la sesión de un admin logueado, sin que se dé cuenta.
     */
    public static function validarCSRF_GET(): void
    {
        $enviado = $_GET['csrf'] ?? '';
        $esperado = $_SESSION['csrf_token'] ?? '';

        if (!$enviado || !$esperado || !hash_equals($esperado, $enviado)) {
            http_response_code(403);
            die('Enlace inválido o expirado. Volvé a la pantalla anterior y probá de nuevo.');
        }
    }

    // ---------------------------------------------------------
    // CONTROL DE INTENTOS DE LOGIN (fuerza bruta)
    // ---------------------------------------------------------

    private const MAX_INTENTOS = 5;
    private const MINUTOS_BLOQUEO = 15;

    /** ¿Este usuario/IP está bloqueado por demasiados intentos fallidos recientes? */
    public static function estaBloqueado(PDO $db, string $usuario): bool
    {
        $ip = self::ip();
        $stmt = $db->prepare(
            "SELECT COUNT(*) FROM intentos_login
             WHERE (usuario = :usuario OR ip = :ip)
               AND exitoso = 0
               AND fecha > (NOW() - INTERVAL " . self::MINUTOS_BLOQUEO . " MINUTE)"
        );
        $stmt->execute(['usuario' => $usuario, 'ip' => $ip]);
        return (int) $stmt->fetchColumn() >= self::MAX_INTENTOS;
    }

    /** Minutos que le quedan de bloqueo (aprox), para mostrarle un mensaje claro. */
    public static function minutosDeBloqueoRestantes(PDO $db, string $usuario): int
    {
        $ip = self::ip();
        $stmt = $db->prepare(
            "SELECT MIN(fecha) FROM (
                SELECT fecha FROM intentos_login
                WHERE (usuario = :usuario OR ip = :ip) AND exitoso = 0
                ORDER BY fecha DESC LIMIT " . self::MAX_INTENTOS . "
             ) sub"
        );
        $stmt->execute(['usuario' => $usuario, 'ip' => $ip]);
        $primerIntento = $stmt->fetchColumn();
        if (!$primerIntento) return self::MINUTOS_BLOQUEO;

        $desbloqueaEn = strtotime($primerIntento) + self::MINUTOS_BLOQUEO * 60;
        $restante = ceil(($desbloqueaEn - time()) / 60);
        return max($restante, 1);
    }

    /** Registra un intento (exitoso o no) para el historial/control de fuerza bruta. */
    public static function registrarIntento(PDO $db, string $usuario, bool $exitoso): void
    {
        $stmt = $db->prepare(
            "INSERT INTO intentos_login (usuario, ip, exitoso, fecha) VALUES (?, ?, ?, NOW())"
        );
        $stmt->execute([$usuario, self::ip(), $exitoso ? 1 : 0]);
    }

    /** Limpia los intentos fallidos de un usuario tras un login exitoso. */
    public static function limpiarIntentos(PDO $db, string $usuario): void
    {
        $stmt = $db->prepare("DELETE FROM intentos_login WHERE usuario = ? AND exitoso = 0");
        $stmt->execute([$usuario]);
    }

    private static function ip(): string
    {
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
}
