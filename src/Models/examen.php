<?php
class Examen {
    private $conn;
    private $table_name = "examenes";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function obtenerProximos() {
        $query = "SELECT *, DATEDIFF(fecha, CURDATE()) as dias_restantes 
                  FROM " . $this->table_name . " 
                  WHERE fecha >= CURDATE() 
                  ORDER BY fecha ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}