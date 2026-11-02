<?php

namespace Tests\Selenium\Pages;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\WebDriverWait;

/**
 * Page Object Model (POM) para la pantalla de Login (Views/index.php).
 */
class LoginPage
{
    private RemoteWebDriver $driver;
    private string $baseUrl;

    // Selectores reales de Views/index.php
    public const INPUT_CORREO      = '#correo';
    public const INPUT_CLAVE       = '#clave';
    public const BTN_LOGIN         = '#btnAccion';
    public const ALERTA_ERROR      = '#alerta';
    public const FORM_LOGIN        = '#frmLogin';
    public const MODAL_RESET       = '#myModal';
    public const INPUT_CORREO_RESET = '#correo_reset';
    public const BTN_RESTABLECER   = '#accion';
    public const SWAL_TITLE        = '.swal2-title';

    public function __construct(RemoteWebDriver $driver, string $baseUrl)
    {
        $this->driver = $driver;
        $this->baseUrl = rtrim($baseUrl, '/') . '/';
    }

    public function abrir(): self
    {
        $this->driver->get($this->baseUrl);
        return $this;
    }

    public function ingresarCredenciales(string $correo, string $clave): self
    {
        $inputCorreo = $this->driver->findElement(WebDriverBy::cssSelector(self::INPUT_CORREO));
        $inputCorreo->clear();
        $inputCorreo->sendKeys($correo);

        $inputClave = $this->driver->findElement(WebDriverBy::cssSelector(self::INPUT_CLAVE));
        $inputClave->clear();
        $inputClave->sendKeys($clave);

        return $this;
    }

    public function enviarFormulario(): self
    {
        $this->driver->findElement(WebDriverBy::cssSelector(self::BTN_LOGIN))->click();
        return $this;
    }

    public function iniciarSesion(string $correo, string $clave): self
    {
        return $this->abrir()
            ->ingresarCredenciales($correo, $clave)
            ->enviarFormulario();
    }

    public function esperarRedireccionTablero(int $timeoutSegundos = 8): bool
    {
        $wait = new WebDriverWait($this->driver, $timeoutSegundos);
        return (bool) $wait->until(
            WebDriverExpectedCondition::urlContains('administracion/home')
        );
    }

    public function obtenerMensajeAlerta(int $timeoutSegundos = 5): string
    {
        $wait = new WebDriverWait($this->driver, $timeoutSegundos);
        $alerta = $wait->until(
            WebDriverExpectedCondition::visibilityOfElementLocated(
                WebDriverBy::cssSelector(self::ALERTA_ERROR)
            )
        );
        return trim($alerta->getText());
    }

    public function obtenerTituloSweetAlert(int $timeoutSegundos = 5): string
    {
        $wait = new WebDriverWait($this->driver, $timeoutSegundos);
        $titulo = $wait->until(
            WebDriverExpectedCondition::visibilityOfElementLocated(
                WebDriverBy::cssSelector(self::SWAL_TITLE)
            )
        );
        return trim($titulo->getText());
    }
}

