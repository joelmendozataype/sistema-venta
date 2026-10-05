<?php
class Query extends Conexion{
    private $pdo, $con, $sql, $datos;
    public function __construct() {
        $this->pdo = new Conexion();
        $this->con = $this->pdo->conect();
    }
    public function select(string $sql, array $params = [])
    {
        $this->sql = $sql;
        $resul = $this->con->prepare($this->sql);
        $resul->execute($params);
        $data = $resul->fetch(PDO::FETCH_ASSOC);
        return $data;
    }
    public function selectAll(string $sql, array $params = [])
    {
        $this->sql = $sql;
        $resul = $this->con->prepare($this->sql);
        $resul->execute($params);
        $data = $resul->fetchAll(PDO::FETCH_ASSOC);
        return $data;
    }
    public function save(string $sql, array $datos)
    {
        $this->sql = $sql;
        $this->datos = $datos;
        $insert = $this->con->prepare($this->sql);
        $data = $insert->execute($this->datos);
        if ($data) {
            $res = 1;
        }else{
            $res = 0;
        }
        return $res;
    }
    // Ejecuta y devuelve las filas afectadas (para UPDATE condicionales como descontar stock)
    public function ejecutar(string $sql, array $datos)
    {
        $stmt = $this->con->prepare($sql);
        $stmt->execute($datos);
        return $stmt->rowCount();
    }
    // Suma (o resta, con $delta negativo) stock en una sola consulta atómica.
    // Devuelve false si el producto no tiene stock suficiente, evitando ventas simultáneas de lo mismo.
    public function moverStock(int $id_producto, $delta)
    {
        $sql = "UPDATE productos SET cantidad = cantidad + ? WHERE id = ? AND cantidad + ? >= 0";
        return $this->ejecutar($sql, array($delta, $id_producto, $delta)) > 0;
    }
    // Registro de auditoría con la misma conexión del modelo: dentro de una transacción,
    // la acción y su registro se guardan juntos o no se guarda ninguno.
    // $detalle es una "foto" de los datos en ese momento (no cambia si luego se editan).
    public function registrarAuditoria(string $accion, $id_registro = null, $motivo = null, array $detalle = array())
    {
        $sql = "INSERT INTO auditoria (id_usuario, accion, id_registro, motivo, detalle, ip) VALUES (?,?,?,?,?,?)";
        $datos = array(
            Auth::idUsuario(),
            $accion,
            $id_registro,
            $motivo,
            empty($detalle) ? null : json_encode($detalle, JSON_UNESCAPED_UNICODE),
            $_SERVER['REMOTE_ADDR'] ?? null,
        );
        if ($this->insertar($sql, $datos) <= 0) {
            throw new Exception('No se pudo registrar la auditoría');
        }
    }
    // Transacciones: una operación de varios pasos se guarda completa o no se guarda
    public function iniciarTransaccion()
    {
        $this->con->beginTransaction();
    }
    public function confirmar()
    {
        $this->con->commit();
    }
    public function revertir()
    {
        if ($this->con->inTransaction()) {
            $this->con->rollBack();
        }
    }
    public function insertar(string $sql, array $datos)
    {
        $this->sql = $sql;
        $this->datos = $datos;
        $insert = $this->con->prepare($this->sql);
        $data = $insert->execute($this->datos);
        if ($data) {
            $res = $this->con->lastInsertId();
        } else {
            $res = 0;
        }
        return $res;
    }
}


?>