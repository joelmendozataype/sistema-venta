<?php
// Registro de auditoría: quién hizo qué acción sensible, cuándo y por qué (permiso "auditoria")
class Auditoria extends Controller
{
    // nombre legible de cada acción registrada
    const ACCIONES = array(
        'anular_venta' => 'Anular venta',
        'anular_compra' => 'Anular compra',
        'anular_apartado' => 'Anular apartado',
        'ajuste_inventario' => 'Ajuste de inventario',
        'cambiar_permisos' => 'Cambio de permisos',
        'crear_usuario' => 'Alta de usuario',
        'modificar_usuario' => 'Modificación de usuario',
        'baja_usuario' => 'Baja de usuario',
        'reactivar_usuario' => 'Reactivación de usuario',
    );

    public function __construct()
    {
        parent::__construct();
    }
    public function index()
    {
        $data['usuarios'] = $this->model->getUsuarios();
        $data['acciones'] = self::ACCIONES;
        $this->views->getView('auditoria', "index", $data);
    }
    public function listar()
    {
        $fecha = '/^\d{4}-\d{2}-\d{2}$/';
        $desde = preg_match($fecha, $_GET['desde'] ?? '') ? $_GET['desde'] : date('Y-m-01');
        $hasta = preg_match($fecha, $_GET['hasta'] ?? '') ? $_GET['hasta'] : date('Y-m-d');
        $accion = isset(self::ACCIONES[$_GET['accion'] ?? '']) ? $_GET['accion'] : '';
        $data = $this->model->getRegistros($desde, $hasta, intval($_GET['usuario'] ?? 0), $accion);
        foreach ($data as $i => $row) {
            $data[$i]['accion'] = self::ACCIONES[$row['accion']] ?? $row['accion'];
            $data[$i]['motivo'] = htmlspecialchars($row['motivo'] ?? '');
            $data[$i]['detalle'] = $this->detalleLegible($row['detalle']);
        }
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        die();
    }
    // Convierte la "foto" JSON en texto legible: "clave: valor" por línea
    private function detalleLegible($json)
    {
        $detalle = json_decode($json ?? '', true);
        if (!is_array($detalle)) {
            return '';
        }
        $lineas = array();
        foreach ($detalle as $clave => $valor) {
            if (is_bool($valor)) {
                $valor = $valor ? 'sí' : 'no';
            } else if (is_array($valor)) {
                $valor = empty($valor) ? '—' : implode(', ', array_map(function ($k, $v) {
                    return is_int($k) ? $v : "$k: $v";
                }, array_keys($valor), $valor));
            }
            $lineas[] = '<b>' . htmlspecialchars(str_replace('_', ' ', $clave)) . ':</b> ' . htmlspecialchars((string) $valor);
        }
        return implode('<br>', $lineas);
    }
}
