<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}
$user_id = $_SESSION['usuario_id'];
class Menu {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function obtenerMenuHoy() {
        $hoy = date('Y-m-d');
        $query = "SELECT * FROM menu_bar WHERE fecha = ? LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$hoy]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}