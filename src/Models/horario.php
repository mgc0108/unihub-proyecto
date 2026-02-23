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
    public function obtenerHoy() {
    $dias = ['Sunday'=>'Domingo','Monday'=>'Lunes','Tuesday'=>'Martes','Wednesday'=>'Miércoles','Thursday'=>'Jueves','Friday'=>'Viernes','Saturday'=>'Sábado'];
    $hoy = $dias[date('l')];
    $query = "SELECT * FROM " . $this->table_name . " WHERE dia_semana = :hoy ORDER BY hora_inicio ASC";
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':hoy', $hoy);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
}