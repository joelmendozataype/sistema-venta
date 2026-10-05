<?php
class CajasModel extends Query
{
    public function __construct()
    {
        parent::__construct();
    }
    public function getCajas(int $estado)
    {
        $sql = "SELECT * FROM caja WHERE estado = ?";
        $data = $this->selectAll($sql, [$estado]);
        return $data;
    }
    public function getCierre_caja(int $id)
    {
        $sql = "SELECT * FROM cierre_caja WHERE id_usuario = ?";
        $data = $this->selectAll($sql, [$id]);
        return $data;
    }
    public function registrarCaja(string $caja)
    {
        $verficar = "SELECT * FROM caja WHERE caja = ?";
        $existe = $this->select($verficar, [$caja]);
        if (empty($existe)) {
            # code...
            $sql = "INSERT INTO caja (caja) VALUES (?)";
            $datos = array($caja);
            $data = $this->save($sql, $datos);
            if ($data == 1) {
                $res = "ok";
            } else {
                $res = "error";
            }
        } else {
            $res = "existe";
        }
        return $res;
    }
    public function modificarCaja(string $caja, int $id)
    {
        $verficar = "SELECT * FROM caja WHERE caja = ? AND id != ?";
        $existe = $this->select($verficar, [$caja, $id]);
        if (empty($existe)) {
            $sql = "UPDATE caja SET caja = ? WHERE id = ?";
            $datos = array($caja, $id);
            $data = $this->save($sql, $datos);
            if ($data == 1) {
                $res = "modificado";
            } else {
                $res = "error";
            }
        }else {
            $res = "existe";
        }
        return $res;
    }
    public function editarCaja(int $id)
    {
        $sql = "SELECT * FROM caja WHERE id = ?";
        $data = $this->select($sql, [$id]);
        return $data;
    }
    public function accionCaja(int $estado, int $id)
    {
        $sql = "UPDATE caja SET estado = ? WHERE id = ?";
        $datos = array($estado, $id);
        $data = $this->save($sql, $datos);
        return $data;
    }
    public function registrarArqueo(int $id_usuario, string $monto_inical, string $fecha_apertura)
    {
        $verificar = "SELECT * FROM cierre_caja WHERE id_usuario = ? AND estado = 1";
        $existe = $this->select($verificar, [$id_usuario]);
        if (empty($existe)) {
            $sql = "INSERT INTO cierre_caja (id_usuario, monto_inicial, fecha_apertura) VALUES (?,?,?)";
            $datos = array($id_usuario, $monto_inical, $fecha_apertura);
            $data = $this->save($sql, $datos);
            if ($data == 1) {
                $res = "ok";
            } else {
                $res = "error";
            }
        } else {
            $res = "existe";
        }
        return $res;
    }
    // Ventas al contado del turno (metodo 1): es el dinero que entra a caja
    public function getVentas(int $id_user)
    {
        $sql = "SELECT COALESCE(SUM(total), 0) AS total FROM ventas WHERE id_usuario = ? AND estado = 1 AND apertura = 1 AND metodo = 1";
        $data = $this->select($sql, [$id_user]);
        return $data;
    }
    // Ventas a crédito del turno (metodo 2): no entran a caja hasta que se abonan
    public function getVentasCredito(int $id_user)
    {
        $sql = "SELECT COALESCE(SUM(total), 0) AS total FROM ventas WHERE id_usuario = ? AND estado = 1 AND apertura = 1 AND metodo = 2";
        $data = $this->select($sql, [$id_user]);
        return $data;
    }
    // Cobros del turno: abonos de créditos + pagos de apartados (anticipos y saldos)
    public function getAbonos(int $id_user)
    {
        $sql = "SELECT COALESCE(SUM(monto), 0) AS total, COUNT(*) AS cantidad FROM (
                    SELECT abono AS monto FROM abonos WHERE id_usuario = ? AND apertura = 1
                    UNION ALL
                    SELECT monto FROM pagos_apartados WHERE id_usuario = ? AND apertura = 1
                ) cobros";
        $data = $this->select($sql, [$id_user, $id_user]);
        return $data;
    }
    public function getTotalVentas(int $id_user)
    {
        $sql = "SELECT COUNT(total) AS total FROM ventas WHERE id_usuario = ? AND estado = 1 AND apertura = 1";
        $data = $this->select($sql, [$id_user]);
        return $data;
    }
    public function getMontoInicial(int $id_user)
    {
        $sql = "SELECT id, monto_inicial FROM cierre_caja WHERE id_usuario = ? AND estado = 1";
        $data = $this->select($sql, [$id_user]);
        return $data;
    }
    public function actualizarArqueo(string $final, string $cierre, string $ventas, string $credito, string $abonos, string $general, int $id)
    {
        $sql = "UPDATE cierre_caja SET monto_final=?, fecha_cierre=?,total_ventas=?, ventas_credito=?, total_abonos=?, monto_total=?, estado=? WHERE id = ?";
        $datos = array($final, $cierre, $ventas, $credito, $abonos, $general, 0, $id);
        $data = $this->save($sql, $datos);
        if ($data == 1) {
            $res = "ok";
        } else {
            $res = "error";
        }
        return $res;
    }
    public function actualizarApertura(int $id)
    {
        $sql = "UPDATE ventas SET apertura=? WHERE id_usuario = ?";
        $datos = array(0, $id);
        $this->save($sql, $datos);
        $sql = "UPDATE abonos SET apertura=? WHERE id_usuario = ?";
        $this->save($sql, $datos);
        $sql = "UPDATE pagos_apartados SET apertura=? WHERE id_usuario = ?";
        $this->save($sql, $datos);
    }
    // Cierres de caja de todos los usuarios, filtrados por fecha de apertura y usuario (0 = todos)
    public function getReporteCierres(string $desde, string $hasta, int $id_usuario)
    {
        $sql = "SELECT cc.*, u.nombre AS usuario, c.caja
                FROM cierre_caja cc
                INNER JOIN usuarios u ON u.id = cc.id_usuario
                INNER JOIN caja c ON c.id = u.id_caja
                WHERE cc.fecha_apertura BETWEEN ? AND ? AND (? = 0 OR cc.id_usuario = ?)
                ORDER BY cc.id DESC";
        return $this->selectAll($sql, [$desde, $hasta, $id_usuario, $id_usuario]);
    }
    // Usuarios que alguna vez abrieron caja (para el filtro del reporte)
    public function getUsuariosConCierres()
    {
        $sql = "SELECT DISTINCT u.id, u.nombre FROM usuarios u INNER JOIN cierre_caja cc ON cc.id_usuario = u.id ORDER BY u.nombre";
        return $this->selectAll($sql);
    }
    public function verificarPermisos($id_user, $permiso)
    {
        $sql = "SELECT p.id, p.permiso, d.* FROM permisos p INNER JOIN detalle_permisos d ON p.id = d.id_permiso WHERE d.id_usuario = ? AND p.permiso = ?";
        $existe = $this->select($sql, [$id_user, $permiso]);
        return $existe;
    }
}
