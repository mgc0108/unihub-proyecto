<?php
// 1. PARCHE PARA CLEVER CLOUD (HTTPS PROXY)
if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
    $_SERVER['HTTPS'] = 'on';
}

// 2. CONFIGURACIÓN DE SESIÓN SEGURA
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 1); 

// Iniciamos sesión solo si no ha empezado ya
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class Database {
    private $host;
    private $db_name;
    private $username;
    private $password;
    public $conn;

    public function __construct() {
        // Configuración Clever Cloud vs Localhost
        if (getenv("MYSQL_ADDON_HOST")) {
            $this->host = getenv("MYSQL_ADDON_HOST");
            $this->db_name = getenv("MYSQL_ADDON_DB");
            $this->username = getenv("MYSQL_ADDON_USER");
            $this->password = getenv("MYSQL_ADDON_PASSWORD");
        } else {
            $this->host = "localhost";
            $this->db_name = "uni_hub";
            $this->username = "root";
            $this->password = "";
        }
    }

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->exec("set names utf8");
        } catch(PDOException $e) {
            // En producción es mejor no mostrar el error detallado, pero para debug lo dejamos
            error_log("Error de conexión: " . $e->getMessage());
        }
        return $this->conn;
    }
}