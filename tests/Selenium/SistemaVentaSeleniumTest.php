<?php

namespace Tests\Selenium;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use PHPUnit\Framework\TestCase;
use Tests\Selenium\Pages\CajaArqueoPage;
use Tests\Selenium\Pages\LoginPage;
use Tests\Selenium\Pages\NuevaVentaPage;

/**
 * Suite de Pruebas Automatizadas E2E con PHP + Selenium WebDriver
 * para el Sistema de Ventas (Autenticación, RBAC, Arqueo de Caja y Ventas).
 */
class SistemaVentaSeleniumTest extends TestCase
{
    private ?RemoteWebDriver $driver = null;
    private string $baseUrl;

    protected function setUp(): void
    {
        $this->baseUrl = WebDriverFactory::getBaseUrl();

        if (!class_exists(RemoteWebDriver::class)) {
            $this->markTestSkipped('Instala php-webdriver/webdriver ejecutando: composer update');
        }

        if (!WebDriverFactory::isServerAvailable()) {
            $this->markTestSkipped(
                'Servidor Selenium WebDriver no detectado en ' . WebDriverFactory::getHubUrl()
            );
        }

        $this->driver = WebDriverFactory::createDriver();
    }

    protected function tearDown(): void
    {
        if ($this->driver !== null) {
            $this->driver->quit();
            $this->driver = null;
        }
    }

    /**
     * TC-SEL-01: Verifica que el Super Administrador puede iniciar sesión y llegar al Tablero.
     */
    public function testLoginExitosoSuperAdmin(): void
    {
        $loginPage = new LoginPage($this->driver, $this->baseUrl);
        $loginPage->iniciarSesion('admin@agmail.com', 'admin');

        $this->assertEquals('Bienvenido al Sistema', $loginPage->obtenerTituloSweetAlert());
        $this->assertTrue($loginPage->esperarRedireccionTablero());
        $this->assertStringContainsString('administracion/home', $this->driver->getCurrentURL());
    }

    /**
     * TC-SEL-02: Verifica el rechazo ante contraseña inválida y visualización de alerta en DOM.
     */
    public function testLoginCredencialesIncorrectasMuestraAlerta(): void
    {
        $loginPage = new LoginPage($this->driver, $this->baseUrl);
        $loginPage->iniciarSesion('admin@agmail.com', 'clave_erronea_123');

        $mensaje = $loginPage->obtenerMensajeAlerta();
        $this->assertEquals('Usuario o contraseña incorrecta', $mensaje);
    }

    /**
     * TC-SEL-03: Verifica que un payload de SQL Injection / XSS en Login sea neutralizado por strClean().
     */
    public function testPrevencionInyeccionSqlEnLogin(): void
    {
        $loginPage = new LoginPage($this->driver, $this->baseUrl);
        $loginPage->iniciarSesion("admin@agmail.com' OR '1'='1", "<script>alert(1)</script>");

        $mensaje = $loginPage->obtenerMensajeAlerta();
        $this->assertEquals('Usuario o contraseña incorrecta', $mensaje);
        $this->assertStringNotContainsString('administracion/home', $this->driver->getCurrentURL());
    }

    /**
     * TC-SEL-04: Verifica control de acceso por roles (RBAC): Asesor no puede entrar a Auditoría.
     */
    public function testControlAccesoRbacRolAsesorDeniegaAuditoria(): void
    {
        $loginPage = new LoginPage($this->driver, $this->baseUrl);
        $loginPage->iniciarSesion('ventas@tuempresa.com', '123456');
        $loginPage->esperarRedireccionTablero();

        // Intentar acceder directamente a un módulo exclusivo del SuperAdmin
        $this->driver->get($this->baseUrl . 'auditoria');

        $this->assertStringContainsString(
            'administracion/permisos',
            $this->driver->getCurrentURL(),
            'Un usuario sin rol SuperAdmin debe ser redirigido a administracion/permisos'
        );
    }

    /**
     * TC-SEL-05: Verifica la apertura de caja en el módulo de Arqueo.
     */
    public function testAperturaDeCajaEnArqueo(): void
    {
        $loginPage = new LoginPage($this->driver, $this->baseUrl);
        $loginPage->iniciarSesion('admin@agmail.com', 'admin');
        $loginPage->esperarRedireccionTablero();

        $arqueoPage = new CajaArqueoPage($this->driver, $this->baseUrl);
        $arqueoPage->abrir();

        if ($arqueoPage->cajaEstaCerrada()) {
            $arqueoPage->abrirCajaConMonto('150.00');
        }

        $this->assertStringContainsString('cajas/arqueo', $this->driver->getCurrentURL());
    }

    /**
     * TC-SEL-06: Verifica la pantalla de Nueva Venta y selección de método de pago.
     */
    public function testPantallaNuevaVentaYSeleccionMetodo(): void
    {
        $loginPage = new LoginPage($this->driver, $this->baseUrl);
        $loginPage->iniciarSesion('admin@agmail.com', 'admin');
        $loginPage->esperarRedireccionTablero();

        $ventaPage = new NuevaVentaPage($this->driver, $this->baseUrl);
        $ventaPage->abrir()
            ->seleccionarMetodoPago('1');

        $this->assertStringContainsString('ventas', $this->driver->getCurrentURL());
    }
}

