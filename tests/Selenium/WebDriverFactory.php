<?php

namespace Tests\Selenium;

use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;

/**
 * Fábrica centralizada para crear sesiones de Selenium WebDriver en PHP.
 * Soporta tanto la librería oficial php-webdriver/webdriver como el cliente W3C HTTP directo.
 */
class WebDriverFactory
{
    public static function getHubUrl(): string
    {
        return getenv('SELENIUM_HUB_URL') ?: 'http://localhost:4444';
    }

    public static function getBaseUrl(): string
    {
        return rtrim(getenv('APP_BASE_URL') ?: (defined('BASE_URL') ? BASE_URL : 'http://localhost/venta/'), '/') . '/';
    }

    public static function isHeadless(): bool
    {
        $env = getenv('SELENIUM_HEADLESS');
        return $env === false ? true : filter_var($env, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Verifica si el servidor Selenium WebDriver o ChromeDriver está activo.
     */
    public static function isServerAvailable(?string $hubUrl = null): bool
    {
        $url = rtrim($hubUrl ?? self::getHubUrl(), '/') . '/status';
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 2,
            CURLOPT_CONNECTTIMEOUT => 1,
        ]);
        $response = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $response !== false && $httpCode >= 200 && $httpCode < 400;
    }

    /**
     * Crea una instancia de RemoteWebDriver configurada para Google Chrome.
     */
    public static function createDriver(): RemoteWebDriver
    {
        $options = new ChromeOptions();
        $args = [
            '--window-size=1920,1080',
            '--disable-gpu',
            '--no-sandbox',
            '--disable-dev-shm-usage',
            '--lang=es-PE',
        ];

        if (self::isHeadless()) {
            $args[] = '--headless=new';
        }

        $options->addArguments($args);

        $capabilities = DesiredCapabilities::chrome();
        $capabilities->setCapability(ChromeOptions::CAPABILITY, $options);

        $driver = RemoteWebDriver::create(
            self::getHubUrl(),
            $capabilities,
            10000, // connection timeout ms
            30000  // request timeout ms
        );

        $driver->manage()->timeouts()->implicitlyWait(5);
        return $driver;
    }
}

