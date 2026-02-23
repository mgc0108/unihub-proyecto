<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}
$user_id = $_SESSION['usuario_id'];
class Horario {
    private $conn;
    private $table_name = "horarios";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function obtenerHoy() {
        $dias = ['Sunday'=>'Domingo','Monday'=>'Lunes','Tuesday'=>'Martes','Wednesday'=>'Miércoles','Thursday'=>'Jueves','Friday'=>'Viernes','Saturday'=>'Sábado'];
        $hoy = $dias[date('l')];
        // Usamos $this->conn y $this->table_name correctamente
        $query = "SELECT * FROM " . $this->table_name . " WHERE dia_semana = :hoy ORDER BY hora_inicio ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':hoy', $hoy);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}