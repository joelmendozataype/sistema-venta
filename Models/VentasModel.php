<?php
class VentasModel extends Query
{
    public function __construct()
    {
        parent::__construct();
    }
    public function getClientes()
    {
        $sql = "SELECT * FROM clientes WHERE estado = 1";
        $data = $this->selectAll($sql);
        return $data;
    }
    public function getProductos(int $id)
    {
        $sql = "SELECT * FROM productos WHERE id = ?";
        $data = $this->select($sql, [$id]);
        return $data;
    }
    public function registrarDetalle(string $table, int $id_producto, int $id_usuario, string $precio, $cantidad)
    {
        $sql = "INSERT INTO $table (id_producto, id_usuario, precio, cantidad) VALUES (?,?,?,?)";
        $datos = array($id_producto, $id_usuario, $precio, $cantidad);
        $data = $this->save($sql, $datos);
        if ($data == 1) {
            $res = "ok";
        } else {
            $res = "error";
        }
        return $res;
    }
    public function getDetalle(string $table, int $id)
    {
        $sql = "SELECT d.*, p.descripcion FROM $table d INNER JOIN productos p ON d.id_producto = p.id WHERE d.id_usuario = ?";
        $data = $this->selectAll($sql, [$id]);
        return $data;
    }
    public function deleteDetalle(string $table, int $id)
    {
        $sql = "DELETE FROM $table WHERE id = ?";
        $datos = array($id);
        $data = $this->save($sql, $datos);
        if ($data == 1) {
            $res = "ok";
        } else {
            $res = "error";
        }
        return $res;
    }
    public function consultarDetalle(string $table, int $id_producto, int $id_usuario)
    {
        $sql = "SELECT * FROM $table WHERE id_producto = ? AND id_usuario = ?";
        $data = $this->select($sql, [$id_producto, $id_usuario]);
        return $data;
    }
    public function actualizarDetalle(string $table, string $precio, $cantidad, int $id_producto, int $id_usuario)
    {
        $sql = "UPDATE $table SET precio = ?, cantidad = ? WHERE id_producto = ? AND id_usuario = ?";
        $datos = array($precio, $cantidad, $id_producto, $id_usuario);
        $data = $this->save($sql, $datos);
        if ($data == 1) {
            $res = "modificado";
        } else {
            $res = "error";
        }
        return $res;
    }
    public function registrarDetalleVenta(int $id_venta, int $id_pro, $cantidad, string $precio, string $fecha)
    {
        $sql = "INSERT INTO detalle_ventas (id_venta, id_producto, cantidad, precio, fecha) VALUES (?,?,?,?,?)";
        $datos = array($id_venta, $id_pro, $cantidad, $precio, $fecha);
        $data = $this->save($sql, $datos);
        if ($data == 1) {
            $res = "ok";
        } else {
            $res = "error";
        }
        return $res;
    }
    public function getEmpresa()
    {
        $sql = "SELECT m.simbolo, c.* FROM moneda m INNER JOIN configuracion c ON m.id = c.moneda";
        $data = $this->select($sql);
        return $data;
    }
    public function vaciarDetalle(string $table, int $id_usuario)
    {
        $sql = "DELETE FROM $table WHERE id_usuario = ?";
        $datos = array($id_usuario);
        $data = $this->save($sql, $datos);
        if ($data == 1) {
            $res = "ok";
        } else {
            $res = "error";
        }
        return $res;
    }
    public function getProVenta(int $id_venta)
    {
        $sql = "SELECT v.*, d.*, p.descripcion FROM ventas v INNER JOIN detalle_ventas d ON v.id = d.id_venta INNER JOIN productos p ON p.id = d.id_producto WHERE v.id = ?";
        $data = $this->selectAll($sql, [$id_venta]);
        return $data;
    }
    public function getFecha(string $table, int $id)
    {
        $sql = "SELECT * FROM $table WHERE id = ?";
        $data = $this->select($sql, [$id]);
        return $data;
    }
    public function getHistorialVentas(int $estado)
    {
        // en las anuladas se incluye quién anuló y el motivo (registro de auditoría)
        $sql = "SELECT v.*, c.nombre,
                (SELECT CONCAT(u.nombre, ' · ', DATE_FORMAT(a.fecha, '%Y-%m-%d %H:%i')) FROM auditoria a INNER JOIN usuarios u ON u.id = a.id_usuario WHERE a.accion = 'anular_venta' AND a.id_registro = v.id ORDER BY a.id DESC LIMIT 1) AS anulado_por,
                (SELECT a.motivo FROM auditoria a WHERE a.accion = 'anular_venta' AND a.id_registro = v.id ORDER BY a.id DESC LIMIT 1) AS motivo_anulacion
                FROM ventas v INNER JOIN clientes c ON c.id = v.id_cliente WHERE v.estado = ?";
        $data = $this->selectAll($sql, [$estado]);
        return $data;
    }
    public function actualizarStock($cantidad, int $id_pro)
    {
        $sql = "UPDATE productos SET cantidad = ? WHERE id = ?";
        $datos = array($cantidad, $id_pro);
        $data = $this->save($sql, $datos);
        return $data;
    }
    public function registraVenta(int $id_user, int $id_cliente, string $total, string $fecha, string $hora, int $serie, $metodo)
    {
        $sql = "INSERT INTO ventas (id_usuario, id_cliente, total, fecha, hora, serie, metodo) VALUES (?,?,?,?,?,?,?)";
        $datos = array($id_user, $id_cliente, $total, $fecha, $hora, $serie, $metodo);
        $data = $this->insertar($sql, $datos);
        if ($data > 0) {
            $res = $data;
        } else {
            $res = 0;
        }
        return $res;
    }
    public function registraCredito($total, $id_venta)
    {
        $sql = "INSERT INTO creditos (monto, id_venta) VALUES (?,?)";
        $datos = array($total, $id_venta);
        $data = $this->insertar($sql, $datos);
        if ($data > 0) {
            $res = $data;
        } else {
            $res = 0;
        }
        return $res;
    }
    public function clientesVenta(int $id)
    {
        $sql = "SELECT c.* FROM ventas v INNER JOIN clientes c ON c.id = v.id_cliente WHERE v.id = ?";
        $data = $this->select($sql, [$id]);
        return $data;
    }
    public function getDetalleTemp(int $id)
    {
        $sql = "SELECT * FROM detalle_temp WHERE id = ?";
        $data = $this->select($sql, [$id]);
        return $data;
    }
    public function anular(string $table, int $id)
    {
        $sql = "UPDATE $table SET estado = ? WHERE id = ?";
        $datos = array(0, $id);
        $data = $this->save($sql, $datos);
        if ($data == 1) {
            $res = "ok";
        } else {
            $res = "error";
        }
        return $res;
    }
    public function getAnularVentas(int $id)
    {
        $sql = "SELECT * FROM detalle_ventas WHERE id_venta = ?";
        $data = $this->selectAll($sql, [$id]);
        return $data;
    }
    public function getVenta(int $id)
    {
        $sql = "SELECT v.id, v.estado, v.metodo, v.total, v.fecha, v.hora, c.nombre AS cliente FROM ventas v INNER JOIN clientes c ON c.id = v.id_cliente WHERE v.id = ?";
        return $this->select($sql, [$id]);
    }
    // Crédito activo de una venta, con lo abonado hasta ahora
    public function getCreditoVenta(int $id_venta)
    {
        $sql = "SELECT cr.id, (SELECT COALESCE(SUM(a.abono), 0) FROM abonos a WHERE a.id_credito = cr.id) AS abonado
                FROM creditos cr WHERE cr.id_venta = ? AND cr.estado != 2";
        return $this->select($sql, [$id_venta]);
    }
    // estado 2 = anulado: deja de aparecer en pendientes y finalizados
    public function anularCredito(int $id_credito)
    {
        $sql = "UPDATE creditos SET estado = ? WHERE id = ?";
        return $this->save($sql, array(2, $id_credito));
    }
    public function verificarCaja(int $id)
    {
        $sql = "SELECT * FROM cierre_caja WHERE id_usuario = ? AND estado = 1";
        $data = $this->select($sql, [$id]);
        return $data;
    }
    // Devolución al anular una venta: queda registrada como entrada en el inventario
    public function ingresarEntrada(int $id, int $id_user, $cantidad, string $fecha)
    {
        $sql = "INSERT INTO inventario(id_producto, id_usuario, entradas, fecha) VALUES (?,?,?,?)";
        return $this->save($sql, array($id, $id_user, $cantidad, $fecha));
    }
    public function ingresarSalida(int $id, int $id_user, $cantidad, string $fecha)
    {
        $sql = "INSERT INTO inventario(id_producto, id_usuario, salidas, fecha) VALUES (?,?,?,?)";
        $datos = array($id, $id_user, $cantidad, $fecha);
        $data = $this->save($sql, $datos);
        return $data;
    }
    public function verificarPermisos($id_user, $permiso)
    {
        $sql = "SELECT p.permiso, d.* FROM permisos p INNER JOIN detalle_permisos d ON p.id = d.id_permiso WHERE d.id_usuario = ? AND p.permiso = ?";
        $existe = $this->select($sql, [$id_user, $permiso]);
        return $existe;
    }
    public function detalle(int $id, string $table)
    {
        $sql = "SELECT * FROM $table WHERE id = ?";
        $data = $this->select($sql, [$id]);
        return $data;
    }
    //agregarCantidad
    public function actualizarCantidad($table, $cantidad, $id)
    {
        $sql = "UPDATE $table SET cantidad = ? WHERE id = ?";
        $datos = array($cantidad, $id);
        $data = $this->save($sql, $datos);
        if ($data == 1) {
            $res = 'ok';
        } else {
            $res = 'error';
        }
        return $res;
    }
    public function registrarCliente($dni, string $nombre, string $telefono, string $direccion)
    {
        $sql = "INSERT INTO clientes(dni, nombre, telefono, direccion) VALUES (?,?,?,?)";
        $datos = array($dni, $nombre, $telefono, $direccion);
        $data = $this->insertar($sql, $datos);
        if ($data > 0) {
            $res = $data;
        } else {
            $res = 0;
        }
        return $res;
    }
}