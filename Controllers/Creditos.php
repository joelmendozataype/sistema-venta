<?php
class Creditos extends Controller
{
    private $id_usuario;
    public function __construct()
    {
        parent::__construct();
        $this->id_usuario = $_SESSION['id_usuario'];
    }
    public function index()
    {
        $this->views->getView('creditos',  "index");
    }
    // listar/1 = pendientes, listar = finalizados
    public function listar($valor)
    {
        $estado = (empty($valor)) ? 0 : 1;
        $data = $this->model->getHistorial($estado);
        $abonar = Auth::puede("registrar_abono");
        $resultado = array();
        foreach ($data as $row) {
            $restante = $this->restante($row['monto'], $row['id']);
            $row['restante'] = number_format($restante, 2, '.', '');
            $row['abonado'] = number_format($row['monto'] - $restante, 2, '.', '');
            $row['accion'] = $abonar ? '<button class="btn btn-outline-success" type="button" title="Registrar abono" onclick="btnAddAbono(' . $row['id'] . ');"><i class="fas fa-money-bill-wave"></i></button>' : '';
            if ($estado == 1 && $restante <= 0) {
                // crédito ya pagado que quedó pendiente (datos anteriores): se finaliza
                $this->model->actualizarEstado($row['id']);
                continue;
            }
            $resultado[] = $row;
        }
        echo json_encode($resultado, JSON_UNESCAPED_UNICODE);
        die();
    }
    public function verificarMonto(int $id_credito)
    {
        $credito = $this->model->getCredito($id_credito);
        $data['restante'] = empty($credito) ? 0 : number_format($this->restante($credito['monto'], $id_credito), 2, '.', '');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        die();
    }
    public function registrarAbono()
    {
        $id_credito = intval($_POST['id_credito'] ?? 0);
        $monto_texto = str_replace(',', '.', trim($_POST['monto'] ?? ''));
        $credito = $id_credito > 0 ? $this->model->getCredito($id_credito) : array();
        if (empty($credito)) {
            $msg = array('msg' => 'El crédito no existe', 'icono' => 'error');
        } else if ($credito['estado'] != 1) {
            $msg = array('msg' => 'El crédito ya fue finalizado o anulado', 'icono' => 'warning');
        } else if (!is_numeric($monto_texto) || round((float) $monto_texto, 2) <= 0) {
            $msg = array('msg' => 'Ingresa un monto válido mayor a 0', 'icono' => 'warning');
        } else if (empty($this->model->verificarCaja($this->id_usuario))) {
            $msg = array('msg' => 'La caja esta cerrada, ábrela para registrar abonos', 'icono' => 'warning');
        } else {
            $monto = round((float) $monto_texto, 2);
            $restante = $this->restante($credito['monto'], $id_credito);
            if ($monto > $restante) {
                $msg = array('msg' => 'El monto supera lo que falta pagar (' . number_format($restante, 2) . ')', 'icono' => 'warning');
            } else {
                try {
                    $this->model->iniciarTransaccion();
                    if (!($this->model->registrar($monto, $id_credito, $this->id_usuario) > 0)) {
                        throw new Exception('error al registrar');
                    }
                    if ($this->restante($credito['monto'], $id_credito) <= 0) {
                        $this->model->actualizarEstado($id_credito);
                        $msg = array('msg' => 'Abono registrado, crédito cancelado por completo', 'icono' => 'success');
                    } else {
                        $msg = array('msg' => 'abono registrado', 'icono' => 'success');
                    }
                    $this->model->confirmar();
                } catch (Throwable $e) {
                    $this->model->revertir();
                    $msg = array('msg' => 'error al registrar', 'icono' => 'error');
                }
            }
        }
        echo json_encode($msg, JSON_UNESCAPED_UNICODE);
        die();
    }
    //finalizados
    public function finalizados()
    {
        $this->views->getView('creditos',  "finalizados");
    }
    //abonos
    public function abonos()
    {
        $this->views->getView('creditos',  "abonos");
    }
    public function listarAbonos()
    {
        $data = $this->model->getListarAbonos();
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        die();
    }
    // Saldo pendiente redondeado a céntimos (evita errores de coma flotante)
    private function restante($monto, int $id_credito)
    {
        $abonado = (float) $this->model->getAbono($id_credito)['total'];
        return max(0, round((float) $monto - $abonado, 2));
    }
}
