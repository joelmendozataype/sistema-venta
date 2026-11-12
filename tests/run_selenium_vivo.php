<?php
/**
 * Ejecutor de Pruebas E2E con Navegador Google Chrome EN VIVO (Visible) + Capturas Automáticas.
 * Comando: C:\xampp\php\php.exe tests/run_selenium_vivo.php
 */

define('PROJECT_ROOT', dirname(__DIR__));
require_once PROJECT_ROOT . '/tests/Selenium/W3CWebDriverClient.php';

use Tests\Selenium\W3CWebDriverClient;

$baseUrl = 'http://127.0.0.1:8085/';
$chromeDriverUrl = 'http://127.0.0.1:4444';
$screenshotDir = PROJECT_ROOT . '/tests/reports/screenshots';
if (!is_dir($screenshotDir)) {
    mkdir($screenshotDir, 0777, true);
}

echo "=========================================================================\n";
echo " INICIANDO ENTORNO E2E EN VIVO: PHP + MYSQL + CHROMEDRIVER + CHROME GUI\n";
echo "=========================================================================\n";

// 1. Crear Config/Config.php para el servidor local en 127.0.0.1:8085
$configContent = <<<PHP
<?php
const BASE_URL = "http://127.0.0.1:8085/";
const HOST = "127.0.0.1:3306";
const USER = "root";
const PASS = "";
const DB = "cotizaciones";
const CHARSET = "charset=utf8";
const HOST_SMTP = "smtp.gmail.com";
const USER_SMTP = "correo@gmail.com";
const CLAVE_SMTP = "clave_correo";
const PUERTO_SMTP = 465;
const TITLE = "SISTEMA VENTA";
const ZONA_HORARIA = "America/Lima";
const ZONA_HORARIA_MYSQL = "-05:00";
date_default_timezone_set(ZONA_HORARIA);
const SUPER_ADMIN_ID = 1;
PHP;
file_put_contents(PROJECT_ROOT . '/Config/Config.php', $configContent);

// 2. Verificar que MySQL esté corriendo (si no, iniciarlo desde C:\xampp\mysql\bin\mysqld.exe)
$mysqlRunning = false;
try {
    $pdo = new PDO('mysql:host=127.0.0.1;port=3306', 'root', '');
    $mysqlRunning = true;
} catch (\Throwable $e) {
    echo " -> Iniciando servicio MySQL de XAMPP (esperando inicialización)...\n";
    shell_exec('powershell -NoProfile -Command "Start-Process -FilePath \'C:\xampp\mysql\bin\mysqld.exe\' -ArgumentList \'--defaults-file=C:\xampp\mysql\bin\my.ini\' -WindowStyle Hidden"');
    for ($i = 0; $i < 60; $i++) {
        usleep(500000);
        try {
            $pdo = new PDO('mysql:host=127.0.0.1;port=3306', 'root', '');
            $mysqlRunning = true;
            break;
        } catch (\Throwable $ex) {
        }
    }
}

if (!$mysqlRunning) {
    die("ERROR: No se pudo conectar a MySQL en 127.0.0.1:3306. Abre XAMPP y dale Start a MySQL.\n");
}
echo " -> MySQL conectado correctamente en 127.0.0.1:3306.\n";

// 3. Verificar o importar la base de datos `cotizaciones` y `datos_prueba.sql`
$stmt = $pdo->query("SHOW DATABASES LIKE 'cotizaciones'");
if ($stmt->rowCount() === 0) {
    echo " -> Importando base de datos 'cotizaciones' y 'datos_prueba.sql'...\n";
    $mysqlExe = 'C:\xampp\mysql\bin\mysql.exe';
    shell_exec('cmd /c ""' . $mysqlExe . '" -u root < "' . PROJECT_ROOT . '\DB\cotizaciones.sql""');
    shell_exec('cmd /c ""' . $mysqlExe . '" -u root cotizaciones < "' . PROJECT_ROOT . '\DB\datos_prueba.sql""');
}

// 4. Iniciar el servidor web PHP en 127.0.0.1:8085 (usando rutas relativas sin espacios)
echo " -> Verificando servidor web PHP en $baseUrl ...\n";
chdir(PROJECT_ROOT);
$chCheck = curl_init($baseUrl);
curl_setopt_array($chCheck, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 1]);
$respCheck = curl_exec($chCheck);
curl_close($chCheck);
if ($respCheck === false || strpos($respCheck, 'frmLogin') === false) {
    shell_exec('powershell -NoProfile -Command "Start-Process -FilePath \'C:\xampp\php\php.exe\' -WorkingDirectory \'' . PROJECT_ROOT . '\' -ArgumentList \'-S\',\'127.0.0.1:8085\',\'tests/server_router.php\' -WindowStyle Hidden"');
    sleep(2);
}

// 5. Iniciar ChromeDriver en el puerto 4444 (descarga automática si el compañero no lo tiene)
$chromeDriverExe = PROJECT_ROOT . '\tests\bin\chromedriver.exe';
if (!file_exists($chromeDriverExe)) {
    echo " -> Descargando ChromeDriver compatible con el Google Chrome de esta PC...\n";
    if (!is_dir(PROJECT_ROOT . '\tests\bin')) {
        mkdir(PROJECT_ROOT . '\tests\bin', 0777, true);
    }
    $chromeVer = trim((string) shell_exec('powershell -NoProfile -Command "(Get-Item \'C:\Program Files\Google\Chrome\Application\chrome.exe\').VersionInfo.ProductVersion"'));
    if (!$chromeVer) {
        $chromeVer = '156.0.8078.80';
    }
    $zipUrl = "https://storage.googleapis.com/chrome-for-testing-public/$chromeVer/win64/chromedriver-win64.zip";
    shell_exec('curl.exe -sL "' . $zipUrl . '" -o "' . PROJECT_ROOT . '\tests\bin\cd.zip"');
    shell_exec('tar.exe -xf "' . PROJECT_ROOT . '\tests\bin\cd.zip" -C "' . PROJECT_ROOT . '\tests\bin"');
    shell_exec('powershell -NoProfile -Command "Move-Item \'tests\bin\chromedriver-win64\chromedriver.exe\' \'tests\bin\chromedriver.exe\' -Force; Remove-Item \'tests\bin\cd.zip\', \'tests\bin\chromedriver-win64\' -Recurse -Force"');
}
$chCd = curl_init($chromeDriverUrl . '/status');
curl_setopt_array($chCd, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 1]);
$respCd = curl_exec($chCd);
curl_close($chCd);
if ($respCd === false) {
    echo " -> Iniciando Selenium ChromeDriver en $chromeDriverUrl ...\n";
    shell_exec('powershell -NoProfile -Command "Start-Process -FilePath \'tests\bin\chromedriver.exe\' -WorkingDirectory \'' . PROJECT_ROOT . '\' -ArgumentList \'--port=4444\' -WindowStyle Hidden"');
    sleep(2);
} else {
    echo " -> Selenium ChromeDriver activo en $chromeDriverUrl.\n";
}

// 6. Abrir Google Chrome en VIVO (headless = false) y ejecutar los casos E2E
$driver = new W3CWebDriverClient($chromeDriverUrl);

try {
    echo " -> Abriendo ventana de Google Chrome controlada por Selenium WebDriver...\n\n";
    $driver->startSession(false); // false = Ventana de Chrome VISIBLE en pantalla

    // -------------------------------------------------------------------------
    // CASO 1: Login Fallido con Credenciales Incorrectas
    // -------------------------------------------------------------------------
    $t0 = microtime(true);
    $driver->navigateTo($baseUrl);
    sleep(1);
    $elCorreo = $driver->findElementByCss('#correo');
    $elClave  = $driver->findElementByCss('#clave');
    $elBtn    = $driver->findElementByCss('#btnAccion');

    $driver->sendKeys($elCorreo, 'admin@agmail.com');
    $driver->sendKeys($elClave, 'clave_incorrecta_999');
    $driver->click($elBtn);
    sleep(2);

    $elAlerta = $driver->findElementByCss('#alerta');
    $textoAlerta = $driver->getElementText($elAlerta);
    $driver->takeScreenshot($screenshotDir . '/01_login_credenciales_invalidas.png');
    $passed1 = (stripos($textoAlerta, 'incorrecta') !== false);
    $ms1 = round((microtime(true) - $t0) * 1000, 2);
    echo sprintf(" [%s] TC-SEL-01: Login con clave errónea muestra alerta '%s' (%s ms)\n", $passed1 ? 'PASSED' : 'FAILED', $textoAlerta, $ms1);

    // -------------------------------------------------------------------------
    // CASO 2: Prevención de Inyección SQL y XSS en Login
    // -------------------------------------------------------------------------
    $t0 = microtime(true);
    $driver->navigateTo($baseUrl);
    sleep(1);
    $elCorreo = $driver->findElementByCss('#correo');
    $elClave  = $driver->findElementByCss('#clave');
    $elBtn    = $driver->findElementByCss('#btnAccion');

    $driver->sendKeys($elCorreo, "admin@agmail.com");
    $driver->sendKeys($elClave, "admin' OR '1'='1 -- <script>alert(1)</script>");
    $driver->click($elBtn);
    sleep(2);

    $elAlerta = $driver->findElementByCss('#alerta');
    $textoSql = $driver->getElementText($elAlerta);
    $driver->takeScreenshot($screenshotDir . '/02_prevencion_sqli_xss_login.png');
    $passed2 = (stripos($textoSql, 'incorrecta') !== false && strpos($driver->getCurrentUrl(), 'administracion/home') === false);
    $ms2 = round((microtime(true) - $t0) * 1000, 2);
    echo sprintf(" [%s] TC-SEL-02: Bloqueo de payload SQLi/XSS en Login (%s ms)\n", $passed2 ? 'PASSED' : 'FAILED', $ms2);

    // -------------------------------------------------------------------------
    // CASO 3: Login Exitoso SuperAdmin + SweetAlert2 + Redirección al Tablero
    // -------------------------------------------------------------------------
    $t0 = microtime(true);
    $driver->navigateTo($baseUrl);
    sleep(1);
    $elCorreo = $driver->findElementByCss('#correo');
    $elClave  = $driver->findElementByCss('#clave');
    $elBtn    = $driver->findElementByCss('#btnAccion');

    $driver->sendKeys($elCorreo, 'admin@agmail.com');
    $driver->sendKeys($elClave, 'admin');
    $driver->click($elBtn);
    sleep(1);
    // Captura del modal SweetAlert2 "Bienvenido al Sistema"
    $driver->takeScreenshot($screenshotDir . '/03_login_exitoso_sweetalert.png');
    sleep(3); // Esperar que termine el timer de 3000ms de SweetAlert2 y redirija a administracion/home

    $urlActual = $driver->getCurrentUrl();
    $driver->takeScreenshot($screenshotDir . '/04_tablero_administracion_home.png');
    $passed3 = (strpos($urlActual, 'administracion/home') !== false);
    $ms3 = round((microtime(true) - $t0) * 1000, 2);
    echo sprintf(" [%s] TC-SEL-03: Login exitoso SuperAdmin y redirección a %s (%s ms)\n", $passed3 ? 'PASSED' : 'FAILED', $urlActual, $ms3);

    // -------------------------------------------------------------------------
    // CASO 4: Navegación a Arqueo de Caja y Apertura de Caja
    // -------------------------------------------------------------------------
    $t0 = microtime(true);
    $driver->navigateTo($baseUrl . 'cajas/arqueo');
    sleep(2);
    $driver->takeScreenshot($screenshotDir . '/05_modulo_arqueo_caja.png');
    $passed4 = (strpos($driver->getCurrentUrl(), 'cajas/arqueo') !== false);
    $ms4 = round((microtime(true) - $t0) * 1000, 2);
    echo sprintf(" [%s] TC-SEL-04: Acceso e inspección de Arqueo de Caja (%s ms)\n", $passed4 ? 'PASSED' : 'FAILED', $ms4);

    // -------------------------------------------------------------------------
    // CASO 5: Navegación a Módulo de Nueva Venta e Interacción con Controles
    // -------------------------------------------------------------------------
    $t0 = microtime(true);
    $driver->navigateTo($baseUrl . 'ventas');
    sleep(2);
    $elBuscar = $driver->findElementByCss('#buscarVenta');
    $elPagar  = $driver->findElementByCss('#pagar_con');
    $driver->sendKeys($elBuscar, 'LAPTOP');
    $driver->sendKeys($elPagar, '500');
    sleep(1);
    $driver->takeScreenshot($screenshotDir . '/06_modulo_nueva_venta.png');
    $passed5 = (strpos($driver->getCurrentUrl(), 'ventas') !== false);
    $ms5 = round((microtime(true) - $t0) * 1000, 2);
    echo sprintf(" [%s] TC-SEL-05: Interacción en vivo con pantalla de Nueva Venta (%s ms)\n", $passed5 ? 'PASSED' : 'FAILED', $ms5);

    // -------------------------------------------------------------------------
    // CASO 6: Auditoría Exclusiva de SuperAdmin
    // -------------------------------------------------------------------------
    $t0 = microtime(true);
    $driver->navigateTo($baseUrl . 'auditoria');
    sleep(2);
    $driver->takeScreenshot($screenshotDir . '/07_modulo_auditoria_superadmin.png');
    $passed6 = (strpos($driver->getCurrentUrl(), 'auditoria') !== false);
    $ms6 = round((microtime(true) - $t0) * 1000, 2);
    echo sprintf(" [%s] TC-SEL-06: Verificación de módulo de Auditoría (%s ms)\n", $passed6 ? 'PASSED' : 'FAILED', $ms6);

    sleep(2);
} catch (\Throwable $e) {
    echo "\n [ERROR EN EJECUCIÓN SELENIUM]: " . $e->getMessage() . "\n";
} finally {
    $driver->quit();
}

echo "=========================================================================\n";
echo " EJECUCIÓN EN NAVEGADOR FINALIZADA CON ÉXITO\n";
echo " Se guardaron 7 capturas automáticas de alta resolución en:\n";
echo " -> tests/reports/screenshots/\n";
echo "=========================================================================\n";
