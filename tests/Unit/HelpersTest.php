<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Pruebas Automatizadas de Unidad y Seguridad sobre Config/Helpers.php
 */
class HelpersTest extends TestCase
{
    public function testStrCleanEliminaVectoresXssYSqlInjection(): void
    {
        $entradaMaliciosa = "  <script>alert('xss')</script> SELECT * FROM usuarios OR '1'='1 --  ";
        $limpio = strClean($entradaMaliciosa);

        $this->assertStringNotContainsString('<script>', $limpio);
        $this->assertStringNotContainsString('</script>', $limpio);
        $this->assertStringNotContainsString('SELECT * FROM', $limpio);
        $this->assertStringNotContainsString("OR '1'='1", $limpio);
        $this->assertStringNotContainsString('--', $limpio);
    }

    public function testMotivoAuditoriaValidaLongitudMinimaYMaxima(): void
    {
        // Caso 1: Motivo vacío o menor a 10 caracteres -> null
        $_POST['motivo'] = 'Corto';
        $this->assertNull(motivoAuditoria());

        // Caso 2: Motivo válido (entre 10 y 255 caracteres)
        $_POST['motivo'] = 'Error en digitación de cantidad de producto en factura';
        $this->assertSame('Error en digitación de cantidad de producto en factura', motivoAuditoria());

        // Caso 3: Motivo excede 255 caracteres -> null
        $_POST['motivo'] = str_repeat('A', 260);
        $this->assertNull(motivoAuditoria());
    }

    public function testClaveHashYVerificacionBcryptYLegacySha256(): void
    {
        $clavePlana = 'SecretoVentas2026!';
        $hashBcrypt = claveHash($clavePlana);

        $this->assertTrue(claveVerificar($clavePlana, $hashBcrypt));
        $this->assertFalse(claveVerificar('ClaveIncorrecta', $hashBcrypt));
        $this->assertFalse(claveRehash($hashBcrypt));

        // Compatibilidad con hash antiguo SHA-256 (64 caracteres hex)
        $hashSha256 = hash('SHA256', $clavePlana);
        $this->assertTrue(claveVerificar($clavePlana, $hashSha256));
        $this->assertTrue(claveRehash($hashSha256));
    }

    public function testAssetGeneraUrlConVersion(): void
    {
        $url = asset('assets/js/login.js');
        $this->assertStringStartsWith(BASE_URL . 'assets/js/login.js?v=', $url);
    }
}

