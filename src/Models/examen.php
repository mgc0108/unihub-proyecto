<?php
class Examen {
    private $db;
    private $table_name = "examenes";

    public function __construct($db) {
        $this->db = $db;
    }

    public function obtenerProximos() {
        $query = "SELECT *, DATEDIFF(fecha, CURDATE()) as dias_restantes FROM " . $this->table_name . " WHERE fecha >= CURDATE() ORDER BY fecha ASC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function actualizarProgreso($id, $progreso, $anotaciones, $nota = null) {
        $query = "UPDATE " . $this->table_name . " SET progreso = :prog, anotaciones = :anot, nota_sacada = :nota WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':prog', $progreso);
        $stmt->bindParam(':anot', $anotaciones);
        $stmt->bindParam(':nota', $nota);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public function guardar($materia, $fecha, $hora, $tipo) {
        $query = "INSERT INTO " . $this->table_name . " (materia, fecha, hora, tipo) VALUES (:mat, :fec, :hor, :tip)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':mat', $materia);
        $stmt->bindParam(':fec', $fecha);
        $stmt->bindParam(':hor', $hora);
        $stmt->bindParam(':tip', $tipo);
        return $stmt->execute();
    }

    public function eliminar($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
}