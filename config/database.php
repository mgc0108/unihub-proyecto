<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}
$user_id = $_SESSION['usuario_id'];
class Database {
    private $host;
    private $db_name;
    private $username;
    private $password;
    public $conn;

    public function __construct() {
        // Si existe la variable de Clever Cloud, la usa. Si no, usa XAMPP.
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
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->exec("set names utf8");
        } catch(PDOException $e) {
            echo "Error de conexión: " . $e->getMessage();
        }
        return $this->conn;
    }
}