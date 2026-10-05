<?php
class ProveedorModel extends Query
{
    public function __construct()
    {
        parent::__construct();
    }
    public function getProveedor(int $estado)
    {
        $sql = "SELECT * FROM proveedor WHERE estado = ?";
        $data = $this->selectAll($sql, [$estado]);
        return $data;
    }
    public function buscarProveedor(string $valor)
    {
        $sql = "SELECT id, ruc, nombre, direccion FROM proveedor WHERE estado = 1 AND (ruc LIKE ? OR nombre LIKE ?) LIMIT 20";
        $data = $this->selectAll($sql, ["%$valor%", "%$valor%"]);
        return $data;
    }
    public function registrar(string $ruc, string $nombre, string $telefono, string $direccion)
    {
        $existeRuc = $this->select("SELECT id FROM proveedor WHERE ruc = ?", [$ruc]);
        $existeTel = $this->select("SELECT id FROM proveedor WHERE telefono = ?", [$telefono]);
        if (!empty($existeRuc)) {
            $res = "ruc";
        }else if (!empty($existeTel)) {
            $res = "telefono";
        } else {
            $sql = "INSERT INTO proveedor(ruc, nombre, telefono, direccion) VALUES (?,?,?,?)";
            $datos = array($ruc, $nombre, $telefono, $direccion);
            $data = $this->save($sql, $datos);
            if ($data == 1) {
                $res = "ok";
            } else {
                $res = "error";
            }
        }
        return $res;
    }
    public function modificar(string $ruc, string $nombre, string $telefono, string $direccion, int $id)
    {
        $existeRuc = $this->select("SELECT id FROM proveedor WHERE ruc = ? AND id != ?", [$ruc, $id]);
        $existeTel = $this->select("SELECT id FROM proveedor WHERE telefono = ? AND id != ?", [$telefono, $id]);
        if (!empty($existeRuc)) {
            $res = "ruc";
        }else if (!empty($existeTel)) {
            $res = "telefono";
        } else {
            $sql = "UPDATE proveedor SET ruc = ?, nombre = ?, telefono = ? ,direccion = ? WHERE id = ?";
            $datos = array($ruc, $nombre, $telefono, $direccion, $id);
            $data = $this->save($sql, $datos);
            if ($data == 1) {
                $res = "ok";
            } else {
                $res = "error";
            }
        }
        return $res;
    }
    public function editarpr(int $id)
    {
        $sql = "SELECT * FROM proveedor WHERE id = ?";
        $data = $this->select($sql, [$id]);
        return $data;
    }
    public function accionpr(int $estado, int $id)
    {
        $sql = "UPDATE proveedor SET estado = ? WHERE id = ?";
        $datos = array($estado, $id);
        $data = $this->save($sql, $datos);
        return $data;
    }
    public function verificarPermisos($id_user, $permiso)
    {
        $sql = "SELECT p.id, p.permiso, d.* FROM permisos p INNER JOIN detalle_permisos d ON p.id = d.id_permiso WHERE d.id_usuario = ? AND p.permiso = ?";
        $existe = $this->select($sql, [$id_user, $permiso]);
        return $existe;
    }
}
