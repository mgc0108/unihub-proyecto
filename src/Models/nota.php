<?php
class Nota {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function guardar($materia, $tipo, $calificacion, $porcentaje, $fecha) {
        $sql = "INSERT INTO notas (materia, tipo, calificacion, porcentaje, fecha) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$materia, $tipo, $calificacion, $porcentaje, $fecha]);
    }

    public function obtenerTodas() {
        $sql = "SELECT * FROM notas ORDER BY fecha DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function eliminar($id) {
    $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':id', $id);
    return $stmt->execute();
}
}