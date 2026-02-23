<?php
class Examen {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function obtenerProximos() {
        // Obtenemos los exámenes de hoy en adelante, ordenados por fecha
        $sql = "SELECT *, DATEDIFF(fecha, CURDATE()) as dias_restantes 
                FROM examenes 
                WHERE fecha >= CURDATE() 
                ORDER BY fecha ASC LIMIT 5";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function guardar($materia, $fecha, $hora, $tipo) {
        $sql = "INSERT INTO examenes (materia, fecha, hora, tipo) VALUES (?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$materia, $fecha, $hora, $tipo]);
    }
    public function eliminar($id) {
    $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':id', $id);
    return $stmt->execute();
}
}