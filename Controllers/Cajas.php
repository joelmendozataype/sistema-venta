<?php
class Cajas extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }
    public function index()
    {
        $id_user = $_SESSION['id_usuario'];
        $data['permisos'] = $this->model->verificarPermisos($id_user, "crear_caja");
        if (!empty($data['permisos']) || $id_user == 1) {
            $data['existe'] = true;
        } else {
            $data['existe'] = false;
        }
        $data['modal'] = 'caja';
        $this->views->getView('cajas',  "index", $data);
    }
    public function arqueo()
    {
        $id_user = $_SESSION['id_usuario'];
        $cerrar_caja = $this->model->verificarPermisos($id_user, "cerrar_caja");
        $abrir_caja = $this->model->verificarPermisos($id_user, "abrir_caja");
        if (!empty($abrir_caja) || $id_user == 1) {
            $data['abrir_caja'] = true;
        } else {
            $data['abrir_caja'] = false;
        }
        if (!empty($cerrar_caja) || $id_user == 1) {
            $data['cerrar_caja'] = true;
        } else {
            $data['cerrar_caja'] = false;
        }
        $data['datos'] = $this->model->getMontoInicial($id_user);
        $data['modal'] = 'apertura';
        $this->views->getView('cajas',  "arqueo", $data);
    }
    public function listar()
    {
        $id_user = $_SESSION['id_usuario'];
        $modificar = $this->model->verificarPermisos($id_user, "modificar_caja");
        $eliminar = $this->model->verificarPermisos($id_user, "eliminar_caja");
        $data = $this->model->getCajas(1);
        for ($i = 0; $i < count($data); $i++) {
            $data[$i]['editar'] = '';
            $data[$i]['eliminar'] = '';
            $data[$i]['estado'] = '<span class="badge bg-success">Activo</span>';
            if (!empty($modificar) || $id_user == 1) {
                $data[$i]['editar'] = '<button class="btn btn-outline-primary" type="button" onclick="btnEditarCaja(' . $data[$i]['id'] . ');"><i class="fas fa-edit"></i></button>';
            }
            if (!empty($eliminar) || $id_user == 1) {
                $data[$i]['eliminar'] = '<button class="btn btn-outline-danger" type="button" onclick="btnEliminarCaja(' . $data[$i]['id'] . ');"><i class="fas fa-trash-alt"></i></button>';
            }
        }
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        die();
    }
    public function listar_arqueo()
    {
        $data = $this->model->getCierre_caja($_SESSION['id_usuario']);
        for ($i = 0; $i < count($data); $i++) {
            $data[$i]['status'] = $data[$i]['estado'];
            if ($data[$i]['estado'] == 1) {
                $data[$i]['estado'] = '<span class="badge bg-success">Abierta</span>';
            } else {
                $data[$i]['estado'] = '<span class="badge bg-danger">Cerrada</span>';
            }
        }
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        die();
    }
    public function registrar()
    {
        if (isset($_POST['nombre'])) {
            $caja = strClean($_POST['nombre']);
            $id = strClean($_POST['id']);
            if (empty($caja)) {
                $msg = array('msg' => 'Todo los campos son obligatorios', 'icono' => 'warning');
            } else {
                if (strlen($caja) > 2) {
                    if ($id == "") {
                        $data = $this->model->registrarCaja($caja);
                        if ($data == "ok") {
                            $msg = array('msg' => 'Caja registrado', 'icono' => 'success');
                        } else if ($data == "existe") {
                            $msg = array('msg' => 'La caja ya existe', 'icono' => 'warning');
                        } else {
                            $msg = array('msg' => 'Error al registrar la caja', 'icono' => 'error');
                        }
                    } else {
                        $data = $this->model->modificarCaja($caja, $id);
                        if ($data == "modificado") {
                            $msg = array('msg' => 'Caja Modificado', 'icono' => 'success');
                        } else if ($data == "existe") {
                            $msg = array('msg' => 'La caja ya existe', 'icono' => 'warning');
                        } else {
                            $msg = array('msg' => 'Error al modificar la caja', 'icono' => 'error');
                        }
                    }
                } else {
                    $msg = array('msg' => 'el nombre debe contener un minímo 3 caracteres', 'icono' => 'warning');
                }
            }
        } else {
            $msg = array('msg' => 'error fatal', 'icono' => 'error');
        }

        echo json_encode($msg, JSON_UNESCAPED_UNICODE);
        die();
    }
    public function abrirArqueo()
    {
        if (isset($_POST['monto_inicial'])) {
            $monto_inicial = strClean($_POST['monto_inicial']);
            $fecha_apertura = date('Y-m-d');
            $id_usuario = $_SESSION['id_usuario'];
            $id = $_POST['id'];
            if (empty($monto_inicial)) {
                $msg = array('msg' => 'Todo los campos son obligatorios', 'icono' => 'warning');
            } else {
                if ($id == '') {
                    $data = $this->model->registrarArqueo($id_usuario, $monto_inicial, $fecha_apertura);
                    if ($data == "ok") {
                        $msg = array('msg' => 'Caja abierta', 'icono' => 'success');
                    } else if ($data == "existe") {
                        $msg = array('msg' => 'La caja ya esta abierta', 'icono' => 'warning');
                    } else {
                        $msg = array('msg' => 'Error al abrir la caja', 'icono' => 'error');
                    }
                } else {
                    $arqueo = $this->calcularArqueo($id_usuario);
                    if (empty($arqueo['inicial'])) {
                        $msg = array('msg' => 'La caja ya esta cerrada', 'icono' => 'warning');
                    } else if ($arqueo['total_ventas'] == 0 && $arqueo['cantidad_abonos'] == 0) {
                        $msg = array('msg' => 'No puedes cerrar la caja sin ventas ni abonos', 'icono' => 'warning');
                    } else {
                        // el cierre y el marcado de ventas/cobros como cerrados van juntos
                        try {
                            $this->model->iniciarTransaccion();
                            $data = $this->model->actualizarArqueo($arqueo['monto_final'], $fecha_apertura, $arqueo['total_ventas'], $arqueo['ventas_credito'], $arqueo['abonos'], $arqueo['monto_general'], $arqueo['inicial']['id']);
                            if ($data != "ok") {
                                throw new Exception('Error al cerrar la caja');
                            }
                            $this->model->actualizarApertura($id_usuario);
                            $this->model->confirmar();
                            $msg = array('msg' => 'Caja cerrada', 'icono' => 'success');
                        } catch (Throwable $e) {
                            $this->model->revertir();
                            $msg = array('msg' => 'Error al cerrar la caja', 'icono' => 'error');
                        }
                    }
                }
            }
        } else {
            $msg = array('msg' => 'error fatal', 'icono' => 'error');
        }
        echo json_encode($msg, JSON_UNESCAPED_UNICODE);
        die();
    }
    public function editar(int $id)
    {
        $data = $this->model->editarCaja($id);
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        die();
    }
    public function eliminar(int $id)
    {
        $data = $this->model->accionCaja(0, $id);
        if ($data == 1) {
            $msg = array('msg' => 'Caja dado de baja', 'icono' => 'success');
        } else {
            $msg = array('msg' => 'Error al aliminar la caja', 'icono' => 'error');
        }
        echo json_encode($msg, JSON_UNESCAPED_UNICODE);
        die();
    }
    public function reingresar(int $id)
    {
        $data = $this->model->accionCaja(1, $id);
        if ($data == 1) {
            $msg = array('msg' => 'Caja reingresado', 'icono' => 'success');
        } else {
            $msg = array('msg' => 'Error al reingresar la caja', 'icono' => 'error');
        }
        echo json_encode($msg, JSON_UNESCAPED_UNICODE);
        die();
    }
    // Reporte de cierres de caja de todos los usuarios (permiso reporte_cajas)
    public function reporte()
    {
        $data['usuarios'] = $this->model->getUsuariosConCierres();
        $this->views->getView('cajas', "reporte", $data);
    }
    public function listarReporte()
    {
        $fecha = '/^\d{4}-\d{2}-\d{2}$/';
        $desde = preg_match($fecha, $_GET['desde'] ?? '') ? $_GET['desde'] : date('Y-m-01');
        $hasta = preg_match($fecha, $_GET['hasta'] ?? '') ? $_GET['hasta'] : date('Y-m-d');
        $data = $this->model->getReporteCierres($desde, $hasta, intval($_GET['usuario'] ?? 0));
        $totales = array('contado' => 0, 'credito' => 0, 'cobros' => 0, 'efectivo' => 0);
        foreach ($data as $i => $row) {
            if ($row['estado'] == 1) {
                // caja aún abierta: montos calculados en vivo
                $vivo = $this->calcularArqueo($row['id_usuario']);
                $row['monto_final'] = $vivo['monto_final'];
                $row['total_ventas'] = $vivo['total_ventas'];
                $row['ventas_credito'] = $vivo['ventas_credito'];
                $row['total_abonos'] = $vivo['abonos'];
                $row['monto_total'] = $vivo['monto_general'];
                $row['estado'] = '<span class="badge bg-success">Abierta (en vivo)</span>';
            } else {
                $row['estado'] = '<span class="badge bg-danger">Cerrada</span>';
            }
            $row['ventas_contado'] = number_format($row['monto_final'] - $row['total_abonos'], 2, '.', '');
            $totales['contado'] += $row['ventas_contado'];
            $totales['credito'] += $row['ventas_credito'];
            $totales['cobros'] += $row['total_abonos'];
            $totales['efectivo'] += $row['monto_total'];
            $data[$i] = $row;
        }
        foreach ($totales as $k => $v) {
            $totales[$k] = number_format($v, 2, '.', '');
        }
        echo json_encode(array('cierres' => $data, 'totales' => $totales), JSON_UNESCAPED_UNICODE);
        die();
    }
    public function getVentas()
    {
        $data = $this->calcularArqueo($_SESSION['id_usuario']);
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        die();
    }
    /*
     * Efectivo en caja = monto inicial + ventas al contado + abonos cobrados.
     * Las ventas a crédito se informan aparte: no son dinero recibido.
     */
    private function calcularArqueo(int $id_usuario)
    {
        $contado = (float) $this->model->getVentas($id_usuario)['total'];
        $credito = (float) $this->model->getVentasCredito($id_usuario)['total'];
        $abonos = $this->model->getAbonos($id_usuario);
        $inicial = $this->model->getMontoInicial($id_usuario);
        $monto_inicial = empty($inicial) ? 0 : (float) $inicial['monto_inicial'];
        $total_abonos = (float) $abonos['total'];
        $data['inicial'] = $inicial;
        $data['ventas_contado'] = number_format($contado, 2, '.', '');
        $data['ventas_credito'] = number_format($credito, 2, '.', '');
        $data['abonos'] = number_format($total_abonos, 2, '.', '');
        $data['cantidad_abonos'] = (int) $abonos['cantidad'];
        $data['total_ventas'] = (int) $this->model->getTotalVentas($id_usuario)['total'];
        $data['monto_final'] = number_format($contado + $total_abonos, 2, '.', '');
        $data['monto_general'] = number_format($monto_inicial + $contado + $total_abonos, 2, '.', '');
        return $data;
    }
    public function inactivos()
    {
        //$this->model->eliminarbasura();
        $id_user = $_SESSION['id_usuario'];
        $data['permisos'] = $this->model->verificarPermisos($id_user, "restaurar_caja");
        if (!empty($data['permisos']) || $id_user == 1) {
            $data['existe'] = true;
        } else {
            $data['existe'] = false;
        }
        $data['cajas'] = $this->model->getCajas(0);
        $this->views->getView('cajas',  "inactivos", $data);
    }
}
