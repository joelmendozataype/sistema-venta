<?php
class MedidasModel extends Query
{
    public function __construct()
    {
        parent::__construct();
    }
    public function getMedidas(int $estado)
    {
        $sql = "SELECT * FROM medidas WHERE estado = ?";
        $data = $this->selectAll($sql, [$estado]);
        return $data;
    }
    public function registrarMedida(string $nombre, string $nombre_corto)
    {
        $verficar = "SELECT * FROM medidas WHERE nombre = ?";
        $existe = $this->select($verficar, [$nombre]);
        if (empty($existe)) {
            $sql = "INSERT INTO medidas(nombre, nombre_corto) VALUES (?,?)";
            $datos = array($nombre, $nombre_corto);
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
    public function modificarMedida(string $nombre, string $nombre_corto, int $id)
    {
        $verficar = "SELECT * FROM medidas WHERE nombre = ? AND id != ?";
        $existe = $this->select($verficar, [$nombre, $id]);
        if (empty($existe)) {
            $sql = "UPDATE medidas SET nombre = ?, nombre_corto = ? WHERE id = ?";
            $datos = array($nombre, $nombre_corto, $id);
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
    public function editarMedida(int $id)
    {
        $sql = "SELECT * FROM medidas WHERE id = ?";
        $data = $this->select($sql, [$id]);
        return $data;
    }
    public function accionMedida(int $estado, int $id)
    {
        $sql = "UPDATE medidas SET estado = ? WHERE id = ?";
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
