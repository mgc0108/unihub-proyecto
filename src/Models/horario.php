<?php
class Horario {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    // Obtener clases para el día de hoy o semana (la que ya tenías)
    public function obtenerSemana() {
        $query = "SELECT * FROM horarios ORDER BY FIELD(dia_semana, 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'), hora_inicio";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Función corregida para la cuadrícula completa
    public function obtenerTodos() {
        // Cambiado $this->table_name por "horarios" y $this->conn por $this->db
        $query = "SELECT * FROM horarios ORDER BY FIELD(dia_semana, 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'), hora_inicio";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}