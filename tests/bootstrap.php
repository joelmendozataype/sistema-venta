<?php
/**
 * Bootstrap de Pruebas Automatizadas (Unitarias + Selenium WebDriver + Métricas)
 */

define('PROJECT_ROOT', dirname(__DIR__));

if (file_exists(PROJECT_ROOT . '/vendor/autoload.php')) {
    require_once PROJECT_ROOT . '/vendor/autoload.php';
}

// Autoloader PSR-4 para el namespace Tests\ (funciona incluso sin composer dump-autoload)
spl_autoload_register(function (string $class): void {
    $prefix = 'Tests\\';
    if (strncmp($prefix, $class, strlen($prefix)) === 0) {
        $relativeClass = substr($class, strlen($prefix));
        $file = PROJECT_ROOT . '/tests/' . str_replace('\\', '/', $relativeClass) . '.php';
        if (file_exists($file)) {
            require_once $file;
        }
    }
});

// Cargar configuración del sistema (o plantilla por defecto en entorno de pruebas)
if (!defined('BASE_URL')) {
    if (file_exists(PROJECT_ROOT . '/Config/Config.php')) {
        require_once PROJECT_ROOT . '/Config/Config.php';
    } else {
        require_once PROJECT_ROOT . '/Config/Config.example.php';
    }
}

// Cargar funciones auxiliares del sistema
require_once PROJECT_ROOT . '/Config/Helpers.php';

