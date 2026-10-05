<?php
// Consulta del registro de auditoría (solo lectura: el sistema no edita ni borra registros)
class AuditoriaModel extends Query
{
    public function __construct()
    {
        parent::__construct();
    }
    public function getRegistros(string $desde, string $hasta, int $id_usuario, string $accion)
    {
        $sql = "SELECT a.id, a.fecha, a.accion, a.id_registro, a.motivo, a.detalle, a.ip, u.nombre AS usuario
                FROM auditoria a
                INNER JOIN usuarios u ON u.id = a.id_usuario
                WHERE DATE(a.fecha) BETWEEN ? AND ?
                  AND (? = 0 OR a.id_usuario = ?)
                  AND (? = '' OR a.accion = ?)
                ORDER BY a.id DESC";
        return $this->selectAll($sql, [$desde, $hasta, $id_usuario, $id_usuario, $accion, $accion]);
    }
    public function getUsuarios()
    {
        return $this->selectAll("SELECT DISTINCT u.id, u.nombre FROM usuarios u INNER JOIN auditoria a ON a.id_usuario = u.id ORDER BY u.nombre");
    }
}
