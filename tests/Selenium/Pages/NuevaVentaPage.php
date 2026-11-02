<?php

namespace Tests\Selenium\Pages;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverKeys;
use Facebook\WebDriver\WebDriverSelect;

/**
 * Page Object Model (POM) para Nueva Venta (Views/ventas/index.php).
 */
class NuevaVentaPage
{
    private RemoteWebDriver $driver;
    private string $baseUrl;

    // Selectores reales de Views/ventas/index.php
    public const INPUT_BUSCAR_PRODUCTO = '#buscarVenta';
    public const SPAN_ERROR_BUSQUEDA   = '#errorBusquedaVenta';
    public const TBODY_DETALLE_VENTA   = '#tblDetalleVenta';
    public const SPAN_TOTAL_VENTA      = '#alert_total';
    public const INPUT_PAGAR_CON       = '#pagar_con';
    public const INPUT_CAMBIO          = '#cambio';
    public const INPUT_BUSCAR_CLIENTE  = '#buscarCliente';
    public const SELECT_METODO         = '#metodo';
    public const BTN_GENERAR_VENTA     = 'button[onclick="procesarVenta()"]';
    public const BTN_ANULAR_VENTA      = 'button[name="anularVenta"]';

    public function __construct(RemoteWebDriver $driver, string $baseUrl)
    {
        $this->driver = $driver;
        $this->baseUrl = rtrim($baseUrl, '/') . '/';
    }

    public function abrir(): self
    {
        $this->driver->get($this->baseUrl . 'ventas');
        return $this;
    }

    public function buscarProductoPorCodigo(string $codigo): self
    {
        $input = $this->driver->findElement(WebDriverBy::cssSelector(self::INPUT_BUSCAR_PRODUCTO));
        $input->clear();
        $input->sendKeys($codigo . WebDriverKeys::ENTER);
        return $this;
    }

    public function ingresarPagoEfectivo(string $monto): self
    {
        $input = $this->driver->findElement(WebDriverBy::cssSelector(self::INPUT_PAGAR_CON));
        $input->clear();
        $input->sendKeys($monto . WebDriverKeys::ENTER);
        return $this;
    }

    public function seleccionarMetodoPago(string $valorMetodo = '1'): self
    {
        $selectElement = $this->driver->findElement(WebDriverBy::cssSelector(self::SELECT_METODO));
        $select = new WebDriverSelect($selectElement);
        $select->selectByValue($valorMetodo); // '1' = Contado, '2' = Credito
        return $this;
    }

    public function obtenerTotalMostrado(): float
    {
        $texto = $this->driver->findElement(WebDriverBy::cssSelector(self::SPAN_TOTAL_VENTA))->getText();
        return (float) str_replace(',', '', trim($texto));
    }

    public function obtenerCambioCalculado(): float
    {
        $valor = $this->driver->findElement(WebDriverBy::cssSelector(self::INPUT_CAMBIO))->getAttribute('value');
        return (float) str_replace(',', '', trim((string) $valor));
    }

    public function procesarVenta(): self
    {
        $this->driver->findElement(WebDriverBy::cssSelector(self::BTN_GENERAR_VENTA))->click();
        return $this;
    }
}

