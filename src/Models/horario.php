<?php
class Horario {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    // Obtener clases ordenadas por día y hora
    public function obtenerSemana() {
        $query = "SELECT * FROM horarios ORDER BY FIELD(dia_semana, 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'), hora_inicio";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}