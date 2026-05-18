<?php
class Database {
    private $host = "127.0.0.1:3307"; 
    private $db_name = "pizzeria_dte";
    private $username = "root";
    private $password = "";
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
            
            // Al pasar "127.0.0.1:3307" en $this->host, mysqli lo lee perfecto
            $this->conn = new mysqli($this->host, $this->username, $this->password, $this->db_name);
            $this->conn->set_charset("utf8mb4");
        } catch (mysqli_sql_exception $e) {
            die("Error de conexión a la base de datos: " . $e->getMessage());
        }
        return $this->conn;
    }
}
?>