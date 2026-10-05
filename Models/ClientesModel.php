<?php
class ClientesModel extends Query
{
    public function __construct()
    {
        parent::__construct();
    }
    public function getClientes(int $estado)
    {
        $sql = "SELECT * FROM clientes WHERE estado = ?";
        $data = $this->selectAll($sql, [$estado]);
        return $data;
    }
    public function buscarCliente(string $valor)
    {
        $sql = "SELECT id, dni, nombre, direccion FROM clientes WHERE estado = 1 AND (dni LIKE ? OR nombre LIKE ?) LIMIT 20";
        $data = $this->selectAll($sql, ["%$valor%", "%$valor%"]);
        return $data;
    }
    public function registrarCliente($dni, string $nombre, string $telefono, string $direccion)
    {
        $existeDni = $this->select("SELECT id FROM clientes WHERE dni = ?", [$dni]);
        $existeTel = $this->select("SELECT id FROM clientes WHERE telefono = ?", [$telefono]);
        if (!empty($existeDni)) {
            $res = 'dni';
        } else if (!empty($existeTel)) {
            $res = 'telefono';
        }else {
            $sql = "INSERT INTO clientes(dni, nombre, telefono, direccion) VALUES (?,?,?,?)";
            $datos = array($dni, $nombre, $telefono, $direccion);
            $data = $this->insertar($sql, $datos);
            if ($data > 0) {
                $res = $data;
            } else {
                $res = 0;
            }
        }
        return $res;
    }
    public function modificarCliente($dni, string $nombre, string $telefono, string $direccion, int $id)
    {
        $existeDni = $this->select("SELECT id FROM clientes WHERE dni = ? AND id != ?", [$dni, $id]);
        $existeTel = $this->select("SELECT id FROM clientes WHERE telefono = ? AND id != ?", [$telefono, $id]);
        if (!empty($existeDni)) {
            $res = 'dni';
        } else if (!empty($existeTel)) {
            $res = 'telefono';
        }else {
            $sql = "UPDATE clientes SET dni = ?, nombre = ?, telefono = ?,direccion = ? WHERE id = ?";
            $datos = array($dni, $nombre, $telefono, $direccion, $id);
            $data = $this->save($sql, $datos);
            if ($data == 1) {
                $res = "ok";
            } else {
                $res = "error";
            }
        }
        return $res;
    }
    public function editarCli(int $id)
    {
        $sql = "SELECT * FROM clientes WHERE id = ?";
        $data = $this->select($sql, [$id]);
        return $data;
    }
    public function accionCli(int $estado, int $id)
    {
        $sql = "UPDATE clientes SET estado = ? WHERE id = ?";
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
