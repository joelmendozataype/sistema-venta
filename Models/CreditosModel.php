<?php
/*
 * Estados de creditos: 1 = pendiente, 0 = finalizado, 2 = anulado (venta anulada)
 */
class CreditosModel extends Query{
    public function __construct()
    {
        parent::__construct();
    }
    public function getHistorial(int $estado)
    {
        $sql = "SELECT cr.*, cl.nombre FROM creditos cr INNER JOIN ventas v ON cr.id_venta = v.id INNER JOIN clientes cl ON v.id_cliente = cl.id WHERE cr.estado = ?";
        $data = $this->selectAll($sql, [$estado]);
        return $data;
    }
    public function getAbono(int $id_credito)
    {
        $sql = "SELECT COALESCE(SUM(abono), 0) AS total FROM abonos WHERE id_credito = ?";
        $data = $this->select($sql, [$id_credito]);
        return $data;
    }
    public function actualizarEstado(int $id_credito)
    {
        $sql = "UPDATE creditos SET estado = ? WHERE id = ? AND estado = 1";
        $datos = array(0, $id_credito);
        $data = $this->save($sql, $datos);
        return $data;
    }
    public function getCredito(int $id_credito)
    {
        $sql = "SELECT * FROM creditos WHERE id = ?";
        $data = $this->select($sql, [$id_credito]);
        return $data;
    }
    public function registrar($monto, int $id_credito, int $id_usuario)
    {
        $sql = "INSERT INTO abonos(abono, id_credito, id_usuario) VALUES (?,?,?)";
        $datos = array($monto, $id_credito, $id_usuario);
        $data = $this->insertar($sql, $datos);
        return $data;
    }
    // El abono es dinero que entra a caja: el usuario debe tener su caja abierta
    public function verificarCaja(int $id_usuario)
    {
        $sql = "SELECT id FROM cierre_caja WHERE id_usuario = ? AND estado = 1";
        $data = $this->select($sql, [$id_usuario]);
        return $data;
    }
    public function getListarAbonos()
    {
        $sql = "SELECT a.*, u.nombre FROM abonos a INNER JOIN usuarios u ON a.id_usuario = u.id";
        $data = $this->selectAll($sql);
        return $data;
    }
}

?>
