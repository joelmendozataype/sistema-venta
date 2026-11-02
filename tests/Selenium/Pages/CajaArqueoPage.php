<?php

namespace Tests\Selenium\Pages;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\WebDriverWait;

/**
 * Page Object Model (POM) para Arqueo de Caja (Views/cajas/arqueo.php y Views/modals/apertura.php).
 */
class CajaArqueoPage
{
    private RemoteWebDriver $driver;
    private string $baseUrl;

    // Selectores reales de Views/cajas/arqueo.php y Views/modals/apertura.php
    public const BTN_ABRIR_CAJA    = '#btnAbrirCaja';
    public const BTN_CERRAR_CAJA   = '#btnCerrarCaja';
    public const MODAL_APERTURA    = '#myModal';
    public const INPUT_MONTO       = '#monto_inicial';
    public const INPUT_EFECTIVO    = '#monto_general';
    public const BTN_REGISTRAR     = '#btnAccion';
    public const TABLA_ARQUEO      = '#t_arqueo';

    public function __construct(RemoteWebDriver $driver, string $baseUrl)
    {
        $this->driver = $driver;
        $this->baseUrl = rtrim($baseUrl, '/') . '/';
    }

    public function abrir(): self
    {
        $this->driver->get($this->baseUrl . 'cajas/arqueo');
        return $this;
    }

    public function cajaEstaCerrada(): bool
    {
        $botones = $this->driver->findElements(WebDriverBy::cssSelector(self::BTN_ABRIR_CAJA));
        return count($botones) > 0 && $botones[0]->isDisplayed();
    }

    public function abrirCajaConMonto(string $montoInicial): self
    {
        $this->driver->findElement(WebDriverBy::cssSelector(self::BTN_ABRIR_CAJA))->click();

        $wait = new WebDriverWait($this->driver, 5);
        $inputMonto = $wait->until(
            WebDriverExpectedCondition::visibilityOfElementLocated(
                WebDriverBy::cssSelector(self::INPUT_MONTO)
            )
        );
        $inputMonto->clear();
        $inputMonto->sendKeys($montoInicial);

        $this->driver->findElement(WebDriverBy::cssSelector(self::BTN_REGISTRAR))->click();
        return $this;
    }

    public function cerrarCaja(): self
    {
        $this->driver->findElement(WebDriverBy::cssSelector(self::BTN_CERRAR_CAJA))->click();

        $wait = new WebDriverWait($this->driver, 5);
        $btnConfirmarModal = $wait->until(
            WebDriverExpectedCondition::elementToBeClickable(
                WebDriverBy::cssSelector(self::BTN_REGISTRAR)
            )
        );
        $btnConfirmarModal->click();
        return $this;
    }
}

