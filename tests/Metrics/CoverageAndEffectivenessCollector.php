<?php

namespace Tests\Metrics;

use ReflectionClass;
use ReflectionMethod;

/**
 * Analizador y Calculador de Métricas de Cobertura y Efectividad en Pruebas Automatizadas.
 *
 * Implementa las métricas estándar de Ingeniería de Software (ISTQB / IEEE 829 / ISO 29119):
 * 1. Cobertura Funcional / de Módulos (Functional Coverage)
 * 2. Cobertura de Rutas y Reglas ACL (Endpoint / Route Coverage)
 * 3. Cobertura de Funciones y Métodos Críticos (Method / Function Coverage)
 * 4. Cobertura de Selectores DOM en Page Objects Selenium (UI Element Coverage)
 * 5. Efectividad de Ejecución de Pruebas (Test Pass Rate)
 * 6. Efectividad en Detección de Defectos (DDE - Defect Detection Effectiveness / Mutation Score)
 * 7. Eficiencia y Densidad de Aserciones (Assertion Density & Defect Yield)
 */
class CoverageAndEffectivenessCollector
{
    private array $testResults = [];
    private array $coveredRoutes = [];
    private array $coveredFunctions = [];
    private array $coveredModules = [];
    private array $coveredDomSelectors = [];
    private int $totalAssertions = 0;
    private int $defectsSeeded = 0;
    private int $defectsDetected = 0;

    public function recordTest(
        string $id,
        string $nombre,
        string $categoria,
        string $modulo,
        bool $passed,
        int $assertions,
        float $durationMs,
        array $routesTested = [],
        array $functionsTested = [],
        array $selectorsTested = [],
        bool $detectedSeededDefect = false,
        string $detalle = ''
    ): void {
        $this->testResults[] = [
            'id' => $id,
            'nombre' => $nombre,
            'categoria' => $categoria,
            'modulo' => $modulo,
            'estado' => $passed ? 'PASSED' : 'FAILED',
            'passed' => $passed,
            'assertions' => $assertions,
            'duration_ms' => round($durationMs, 3),
            'detected_defect' => $detectedSeededDefect,
            'detalle' => $detalle,
        ];

        $this->totalAssertions += $assertions;
        $this->coveredModules[$modulo] = true;

        foreach ($routesTested as $route) {
            $this->coveredRoutes[strtolower($route)] = true;
        }
        foreach ($functionsTested as $fn) {
            $this->coveredFunctions[$fn] = true;
        }
        foreach ($selectorsTested as $sel) {
            $this->coveredDomSelectors[$sel] = true;
        }
        if ($detectedSeededDefect) {
            $this->defectsSeeded++;
            if ($passed) {
                $this->defectsDetected++;
            }
        }
    }

    /**
     * Audita el inventario real del proyecto (Controladores, Rutas ACL, Funciones Helper y Vistas DOM).
     */
    public function auditSystemInventory(): array
    {
        $permisosMap = require PROJECT_ROOT . '/Config/Permisos.php';
        $allRoutes = [];
        foreach ($permisosMap as $ctrl => $actions) {
            foreach ($actions as $action => $rule) {
                $allRoutes[] = strtolower("$ctrl/$action");
            }
        }

        // Funciones definidas en Config/Helpers.php
        $helperFunctions = ['strClean', 'motivoAuditoria', 'asset', 'claveHash', 'claveVerificar', 'claveRehash'];

        // Métodos de seguridad en Config/App/Auth.php
        $authMethods = [
            'Auth::iniciarSesion',
            'Auth::idUsuario',
            'Auth::esSuperAdmin',
            'Auth::logueado',
            'Auth::permisos',
            'Auth::puede',
            'Auth::puedeRuta',
            'Auth::autorizar',
            'Auth::cerrarSesion',
        ];

        // Selectores críticos de las vistas automatizadas con Selenium POM
        $criticalDomSelectors = [
            // Login (Views/index.php)
            '#correo', '#clave', '#btnAccion', '#alerta', '#frmLogin', '#myModal', '#correo_reset', '#accion', '.swal2-title',
            // Arqueo de Caja (Views/cajas/arqueo.php y Views/modals/apertura.php)
            '#btnAbrirCaja', '#btnCerrarCaja', '#monto_inicial', '#monto_general', '#t_arqueo',
            // Nueva Venta (Views/ventas/index.php)
            '#buscarVenta', '#errorBusquedaVenta', '#tblDetalleVenta', '#alert_total', '#pagar_con', '#cambio', '#buscarCliente', '#metodo', 'button[onclick="procesarVenta()"]', 'button[name="anularVenta"]',
        ];

        return [
            'modulos_totales' => array_keys($permisosMap),
            'rutas_totales' => $allRoutes,
            'funciones_criticas' => array_merge($helperFunctions, $authMethods),
            'selectores_dom_criticos' => $criticalDomSelectors,
        ];
    }

    /**
     * Calcula todas las métricas de cobertura y efectividad con sus fórmulas.
     */
    public function calculateMetrics(): array
    {
        $inventory = $this->auditSystemInventory();

        $totalModulos = count($inventory['modulos_totales']);
        $modulosCubiertos = count(array_intersect($inventory['modulos_totales'], array_keys($this->coveredModules)));

        $totalRutas = count($inventory['rutas_totales']);
        $rutasCubiertas = count(array_intersect($inventory['rutas_totales'], array_keys($this->coveredRoutes)));

        $totalFunciones = count($inventory['funciones_criticas']);
        $funcionesCubiertas = count(array_intersect($inventory['funciones_criticas'], array_keys($this->coveredFunctions)));

        $totalSelectores = count($inventory['selectores_dom_criticos']);
        $selectoresCubiertos = count(array_intersect($inventory['selectores_dom_criticos'], array_keys($this->coveredDomSelectors)));

        $totalTests = count($this->testResults);
        $passedTests = count(array_filter($this->testResults, fn($t) => $t['passed']));
        $failedTests = $totalTests - $passedTests;
        $totalTimeMs = array_sum(array_column($this->testResults, 'duration_ms'));

        // Fórmulas de Cobertura (%)
        $coberturaModulos = $totalModulos > 0 ? round(($modulosCubiertos / $totalModulos) * 100, 2) : 0.0;
        $coberturaRutas = $totalRutas > 0 ? round(($rutasCubiertas / $totalRutas) * 100, 2) : 0.0;
        $coberturaFunciones = $totalFunciones > 0 ? round(($funcionesCubiertas / $totalFunciones) * 100, 2) : 0.0;
        $coberturaSelectoresDom = $totalSelectores > 0 ? round(($selectoresCubiertos / $totalSelectores) * 100, 2) : 0.0;
        $coberturaGlobalPonderada = round(
            ($coberturaModulos * 0.25) +
            ($coberturaRutas * 0.25) +
            ($coberturaFunciones * 0.25) +
            ($coberturaSelectoresDom * 0.25),
            2
        );

        // Fórmulas de Efectividad (%)
        $tasaExito = $totalTests > 0 ? round(($passedTests / $totalTests) * 100, 2) : 0.0;
        $dde = $this->defectsSeeded > 0 ? round(($this->defectsDetected / $this->defectsSeeded) * 100, 2) : 0.0;
        $densidadAserciones = $totalTests > 0 ? round($this->totalAssertions / $totalTests, 2) : 0.0;
        $rendimientoDefectos = $totalTests > 0 ? round(($this->defectsDetected / $totalTests) * 100, 2) : 0.0;

        return [
            'fecha_ejecucion' => date('Y-m-d H:i:s'),
            'rama_git' => 'featuring/jorge_sistema_ventas',
            'cobertura' => [
                'modulos' => [
                    'cubiertos' => $modulosCubiertos,
                    'totales' => $totalModulos,
                    'porcentaje' => $coberturaModulos,
                ],
                'rutas_acl' => [
                    'cubiertas' => $rutasCubiertas,
                    'totales' => $totalRutas,
                    'porcentaje' => $coberturaRutas,
                ],
                'funciones_nucleo' => [
                    'cubiertas' => $funcionesCubiertas,
                    'totales' => $totalFunciones,
                    'porcentaje' => $coberturaFunciones,
                ],
                'selectores_selenium_dom' => [
                    'cubiertos' => $selectoresCubiertos,
                    'totales' => $totalSelectores,
                    'porcentaje' => $coberturaSelectoresDom,
                ],
                'promedio_global_porcentaje' => $coberturaGlobalPonderada,
            ],
            'efectividad' => [
                'total_casos_ejecutados' => $totalTests,
                'casos_exitosos' => $passedTests,
                'casos_fallidos' => $failedTests,
                'tasa_exito_porcentaje' => $tasaExito,
                'defectos_sembrados_evaluados' => $this->defectsSeeded,
                'defectos_detectados' => $this->defectsDetected,
                'efectividad_deteccion_defectos_dde_porcentaje' => $dde,
                'total_aserciones' => $this->totalAssertions,
                'densidad_aserciones_por_caso' => $densidadAserciones,
                'rendimiento_deteccion_por_caso_porcentaje' => $rendimientoDefectos,
                'tiempo_total_ms' => round($totalTimeMs, 3),
            ],
            'resultados_pruebas' => $this->testResults,
        ];
    }
}

