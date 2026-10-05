<?php
class AdministracionModel extends Query
{
    public function __construct()
    {
        parent::__construct();
    }
    public function getEmpresa()
    {
        $sql = "SELECT * FROM configuracion";
        $data = $this->select($sql);
        return $data;
    }
    public function getDatos(string $table)
    {
        $sql = "SELECT COUNT(*) AS total FROM $table WHERE estado = 1";
        $data = $this->select($sql);
        return $data;
    }
    public function getVentas(string $table, int $id_user)
    {
        $sql = "SELECT COUNT(*) AS total FROM $table WHERE fecha = CURDATE() AND estado = 1 AND id_usuario = ?";
        $data = $this->select($sql, [$id_user]);
        return $data;
    }
    public function modificar(string $ruc, string $nombre, string $tel, string $correo, string $dir, string $mensaje, string $img, int $moneda, int $impuesto, int $cant_factura, $site, int $id)
    {
        $sql = "UPDATE configuracion SET ruc=?, nombre = ?, telefono =?, correo=?, direccion=?, mensaje=?, logo = ?, moneda=?, impuesto=?, cant_factura=?, site=? WHERE id=?";
        $datos = array($ruc, $nombre, $tel, $correo, $dir, $mensaje, $img, $moneda, $impuesto, $cant_factura, $site, $id);
        $data = $this->save($sql, $datos);
        if ($data == 1) {
            $res = "ok";
        } else {
            $res = "error";
        }
        return $res;
    }
    public function getproductosVendidos(string $table, string $desde, string $hasta, int $id_user)
    {
        $sql = "SELECT
    SUM(IF(MONTH(fecha) = 1,  total, 0)) AS ene,
    SUM(IF(MONTH(fecha) = 2,  total, 0)) AS feb,
    SUM(IF(MONTH(fecha) = 3,  total, 0)) AS mar,
    SUM(IF(MONTH(fecha) = 4,  total, 0)) AS abr,
    SUM(IF(MONTH(fecha) = 5,  total, 0)) AS may,
    SUM(IF(MONTH(fecha) = 6,  total, 0)) AS jun,
    SUM(IF(MONTH(fecha) = 7,  total, 0)) AS jul,
    SUM(IF(MONTH(fecha) = 8,  total, 0)) AS ago,
    SUM(IF(MONTH(fecha) = 9,  total, 0)) AS sep,
    SUM(IF(MONTH(fecha) = 10, total, 0)) AS oct,
    SUM(IF(MONTH(fecha) = 11, total, 0)) AS nov,
    SUM(IF(MONTH(fecha) = 12, total, 0)) AS dic
FROM $table
WHERE fecha BETWEEN '$desde' AND '$hasta' AND id_usuario = $id_user AND metodo = 1";
        $data = $this->select($sql);
        return $data;
    }
    public function getMontoCaja(int $id_user)
    {
        // efectivo del turno: ventas al contado + abonos cobrados (las ventas a crédito no son efectivo)
        $sql = "SELECT (SELECT COALESCE(SUM(total), 0) FROM ventas WHERE id_usuario = ? AND estado = 1 AND apertura = 1 AND metodo = 1)
                     + (SELECT COALESCE(SUM(abono), 0) FROM abonos WHERE id_usuario = ? AND apertura = 1)
                     + (SELECT COALESCE(SUM(monto), 0) FROM pagos_apartados WHERE id_usuario = ? AND apertura = 1) AS total";
        $data = $this->select($sql, [$id_user, $id_user, $id_user]);
        return $data;
    }
    public function getMontoInicial(int $id_user)
    {
        $sql = "SELECT id, monto_inicial FROM cierre_caja WHERE id_usuario = ? AND estado = 1";
        $data = $this->select($sql, [$id_user]);
        return $data;
    }
    public function getStockMinimo()
    {
        $sql = "SELECT * FROM productos WHERE cantidad < 15 AND estado = 1 ORDER BY cantidad DESC LIMIT 10";
        $data = $this->selectAll($sql);
        return $data;
    }
    //Monedas
    public function registrarMoneda(string $simbolo, string $nom)
    {
        $verficar = "SELECT * FROM moneda WHERE simbolo = ?";
        $existe = $this->select($verficar, [$simbolo]);
        if (empty($existe)) {
            $sql = "INSERT INTO moneda (simbolo, nombre) VALUES (?,?)";
            $datos = array($simbolo, $nom);
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
    public function getMonedas(int $estado)
    {
        $sql = "SELECT * FROM moneda WHERE estado = ?";
        $data = $this->selectAll($sql, [$estado]);
        return $data;
    }
    public function modificarMoneda(string $simbolo, string $nombre, int $id)
    {
        $verficar = "SELECT * FROM moneda WHERE simbolo = ? AND id != ?";
        $existe = $this->select($verficar, [$simbolo, $id]);
        if (empty($existe)) {
            $sql = "UPDATE moneda SET simbolo = ?, nombre = ? WHERE id = ?";
            $datos = array($simbolo, $nombre, $id);
            $data = $this->save($sql, $datos);
            if ($data == 1) {
                $res = "modificado";
            } else {
                $res = "error";
            }
        } else {
            $res = "existe";
        }
        return $res;
    }
    public function editarMoneda(int $id)
    {
        $sql = "SELECT * FROM moneda WHERE id = ?";
        $data = $this->select($sql, [$id]);
        return $data;
    }
    public function accionMoneda(int $estado, int $id)
    {
        $sql = "UPDATE moneda SET estado = ? WHERE id = ?";
        $datos = array($estado, $id);
        $data = $this->save($sql, $datos);
        return $data;
    }
    public function getEmpresaMoneda()
    {
        $sql = "SELECT m.id, m.simbolo, c.moneda FROM moneda m INNER JOIN configuracion c ON m.id = c.moneda";
        $data = $this->select($sql);
        return $data;
    }
    public function verificarPermisos($id_user, $permiso)
    {
        $sql = "SELECT p.id, p.permiso, d.* FROM permisos p INNER JOIN detalle_permisos d ON p.id = d.id_permiso WHERE d.id_usuario = ? AND p.permiso = ?";
        $existe = $this->select($sql, [$id_user, $permiso]);
        return $existe;
    }
    public function topProductos()
    {
        $sql = "SELECT p.descripcion, SUM(d.cantidad) AS total FROM detalle_ventas d INNER JOIN productos p ON p.id = d.id_producto GROUP BY d.id_producto ORDER BY total, p.descripcion DESC lIMIT 10";
        $data = $this->selectAll($sql);
        return $data;
    }
}
