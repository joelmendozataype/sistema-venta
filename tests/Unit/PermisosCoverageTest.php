<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Pruebas Automatizadas de Cobertura de Rutas y Reglas de Permisos (Config/Permisos.php)
 */
class PermisosCoverageTest extends TestCase
{
    private array $mapaPermisos;

    protected function setUp(): void
    {
        $this->mapaPermisos = require PROJECT_ROOT . '/Config/Permisos.php';
    }

    public function testTodosLosControladoresCriticosTienenReglasDefinidas(): void
    {
        $controladoresEsperados = [
            'home', 'errors', 'administracion', 'usuarios', 'cajas',
            'clientes', 'proveedor', 'medidas', 'categorias', 'productos',
            'compras', 'ventas', 'cotizaciones', 'apartados', 'creditos',
            'auditoria', 'landing'
        ];

        foreach ($controladoresEsperados as $ctrl) {
            $this->assertArrayHasKey(
                $ctrl,
                $this->mapaPermisos,
                "El controlador '$ctrl' debe estar registrado en Config/Permisos.php"
            );
        }
    }

    public function testAuditoriaEsExclusivaDeSuperAdmin(): void
    {
        $this->assertSame('superadmin', $this->mapaPermisos['auditoria']['index']);
        $this->assertSame('superadmin', $this->mapaPermisos['auditoria']['listar']);
    }

    public function testRutasPublicasSonUnicamenteLoginYRecuperacion(): void
    {
        $rutasPublicas = [];
        foreach ($this->mapaPermisos as $ctrl => $acciones) {
            foreach ($acciones as $metodo => $regla) {
                if ($regla === 'publico') {
                    $rutasPublicas[] = "$ctrl/$metodo";
                }
            }
        }

        $esperadas = [
            'home/index',
            'usuarios/validar',
            'usuarios/enviarCorreo',
            'usuarios/restablecer',
            'usuarios/resetear',
            'usuarios/salir',
        ];

        $this->assertEqualsCanonicalizing($esperadas, $rutasPublicas);
    }

    public function testOperacionesGuardarSeparanPermisoCrearYModificar(): void
    {
        $modulosCrud = ['usuarios', 'cajas', 'clientes', 'proveedor', 'medidas', 'categorias', 'productos'];
        foreach ($modulosCrud as $modulo) {
            $reglaRegistrar = $this->mapaPermisos[$modulo]['registrar'] ?? null;
            $this->assertIsArray($reglaRegistrar);
            $this->assertArrayHasKey('guardar', $reglaRegistrar);
            $this->assertCount(2, $reglaRegistrar['guardar']);
        }
    }
}

