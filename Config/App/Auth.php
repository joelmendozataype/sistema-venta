<?php
/*
 * Control de acceso centralizado.
 * - autorizar(): lo llama index.php antes de crear el controlador.
 * - puede() / puedeRuta(): para mostrar u ocultar menús y botones en las vistas.
 * Los permisos se leen de la base de datos en cada petición, así un cambio de
 * permisos o una baja del usuario se aplica de inmediato.
 */
class Auth
{
    private static $acl = null;
    private static $usuario = null;
    private static $permisos = null;

    public static function iniciarSesion()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
            session_start();
        }
    }

    public static function idUsuario(): int
    {
        return (int) ($_SESSION['id_usuario'] ?? 0);
    }

    public static function esSuperAdmin(): bool
    {
        return self::logueado() && self::idUsuario() === SUPER_ADMIN_ID;
    }

    // Sesión iniciada y usuario todavía activo en la base de datos
    public static function logueado(): bool
    {
        if (self::$usuario === null) {
            self::$usuario = false;
            if (!empty($_SESSION['activo']) && self::idUsuario() > 0) {
                $query = new Query();
                $usuario = $query->select("SELECT id FROM usuarios WHERE id = ? AND estado = 1", [self::idUsuario()]);
                self::$usuario = !empty($usuario);
            }
        }
        return self::$usuario;
    }

    // Nombres de los permisos asignados al usuario actual
    public static function permisos(): array
    {
        if (self::$permisos === null) {
            self::$permisos = [];
            if (self::logueado()) {
                $query = new Query();
                $data = $query->selectAll("SELECT p.permiso FROM permisos p INNER JOIN detalle_permisos d ON p.id = d.id_permiso WHERE d.id_usuario = ?", [self::idUsuario()]);
                foreach ($data as $row) {
                    self::$permisos[strtolower($row['permiso'])] = true;
                }
            }
        }
        return self::$permisos;
    }

    // true si el usuario tiene al menos uno de los permisos indicados
    public static function puede($permisos): bool
    {
        if (self::esSuperAdmin()) {
            return true;
        }
        $asignados = self::permisos();
        foreach ((array) $permisos as $permiso) {
            if (isset($asignados[strtolower($permiso)])) {
                return true;
            }
        }
        return false;
    }

    // true si el usuario puede entrar a la ruta (para menús, enlaces y botones)
    public static function puedeRuta(string $controlador, string $metodo = 'index', string $parametro = ''): bool
    {
        $regla = self::regla($controlador, $metodo, $parametro);
        if ($regla === null) {
            return false;
        }
        if ($regla === 'publico') {
            return true;
        }
        if (!self::logueado()) {
            return false;
        }
        if ($regla === 'sesion') {
            return true;
        }
        if ($regla === 'superadmin') {
            return self::esSuperAdmin();
        }
        return self::puede(isset($regla['guardar']) ? $regla['guardar'] : $regla);
    }

    // Valida la petición actual; si no tiene acceso responde y termina la ejecución
    public static function autorizar(string $controlador, string $metodo, string $parametro)
    {
        $regla = self::regla($controlador, $metodo, $parametro);
        if ($regla === 'publico') {
            return;
        }
        if (!self::logueado()) {
            if (!empty($_SESSION['activo'])) {
                // el usuario fue dado de baja con la sesión abierta
                self::cerrarSesion();
            }
            self::denegar('Tu sesión ha expirado, ingresa nuevamente', BASE_URL);
        }
        if ($regla === null) {
            self::denegar('La ruta solicitada no existe', BASE_URL . 'errors');
        }
        if ($regla === 'sesion' || self::esSuperAdmin()) {
            return;
        }
        if ($regla === 'superadmin') {
            // exclusivo del Super Administrador: ningún permiso lo habilita
            self::denegar('Solo el Super Administrador puede acceder', BASE_URL . 'administracion/permisos');
        }
        if (isset($regla['guardar'])) {
            // id vacío => crear, id con valor => modificar
            $requerido = empty($_POST['id']) ? $regla['guardar'][0] : $regla['guardar'][1];
        } else {
            $requerido = $regla;
        }
        if (!self::puede($requerido)) {
            self::denegar('No tienes permiso para realizar esta acción', BASE_URL . 'administracion/permisos');
        }
    }

    public static function cerrarSesion()
    {
        $_SESSION = [];
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
        self::$usuario = null;
        self::$permisos = null;
    }

    private static function regla(string $controlador, string $metodo, string $parametro)
    {
        if (self::$acl === null) {
            // claves en minúscula: PHP no distingue mayúsculas en los nombres de métodos
            self::$acl = [];
            foreach (require 'Config/Permisos.php' as $ctrl => $rutas) {
                foreach ($rutas as $ruta => $regla) {
                    self::$acl[strtolower($ctrl)][strtolower($ruta)] = $regla;
                }
            }
        }
        $controlador = strtolower($controlador);
        $metodo = strtolower($metodo);
        $primerParametro = strtolower(explode(',', $parametro)[0]);
        if ($primerParametro !== '' && isset(self::$acl[$controlador]["$metodo/$primerParametro"])) {
            return self::$acl[$controlador]["$metodo/$primerParametro"];
        }
        return self::$acl[$controlador][$metodo] ?? null;
    }

    // Navegación normal => redirige; petición AJAX => responde JSON con el aviso
    private static function denegar(string $mensaje, string $redireccion)
    {
        $modo = $_SERVER['HTTP_SEC_FETCH_MODE'] ?? '';
        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        $esNavegacion = $modo === 'navigate' || ($modo === '' && strpos($accept, 'text/html') !== false);
        if ($esNavegacion) {
            header('Location: ' . $redireccion);
        } else {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['msg' => $mensaje, 'icono' => 'warning'], JSON_UNESCAPED_UNICODE);
        }
        exit;
    }
}
