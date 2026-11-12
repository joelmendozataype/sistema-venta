<?php
/**
 * Router para el servidor embebido de PHP (php -S 127.0.0.1:8085 tests/server_router.php)
 * Emula la regla RewriteRule ^(.+)$ index.php?url=$1 [QSA,L] del .htaccess
 */

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
$file = dirname(__DIR__) . $uri;

if ($uri !== '/' && file_exists($file) && !is_dir($file)) {
    return false; // Servir recurso estático (CSS, JS, imágenes, fuentes)
}

$_GET['url'] = ltrim($uri, '/');
chdir(dirname(__DIR__));
require dirname(__DIR__) . '/index.php';

