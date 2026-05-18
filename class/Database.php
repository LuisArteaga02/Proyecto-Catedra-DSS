<?php
class Database {
<<<<<<< HEAD
    private $host = "localhost";
=======
    private $host = "127.0.0.1:3307"; 
>>>>>>> 53e73e9 (generacion de pdf de los dte funcionando)
    private $db_name = "pizzeria_dte";
    private $username = "root";
    private $password = "";
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
<<<<<<< HEAD
            // Activamos el reporte de errores estricto de mysqli
            mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
            $this->conn = new mysqli($this->host, $this->username, $this->password, $this->db_name);
            $this->conn->set_charset("utf8mb4");
        } catch (mysqli_sql_exception $e) {
            // En producción, guardar esto en un log, no imprimirlo
=======
            mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
            
            // Al pasar "127.0.0.1:3307" en $this->host, mysqli lo lee perfecto
            $this->conn = new mysqli($this->host, $this->username, $this->password, $this->db_name);
            $this->conn->set_charset("utf8mb4");
        } catch (mysqli_sql_exception $e) {
>>>>>>> 53e73e9 (generacion de pdf de los dte funcionando)
            die("Error de conexión a la base de datos: " . $e->getMessage());
        }
        return $this->conn;
    }
}
?>