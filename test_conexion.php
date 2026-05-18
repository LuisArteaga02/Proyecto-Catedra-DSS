<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h3>Intentando conectar a MySQL...</h3>";

try {
    // Prueba con IP directo y puerto
    $conn = new mysqli("127.0.0.1", "root", "", "pizzeria_dte", 3307);
    echo "✅ ¡CONEXIÓN EXITOSA CON IP Y PUERTO 3307!";
    $conn->close();
} catch (Exception $e) {
    echo "❌ Falló con IP y puerto: " . $e->getMessage() . "<br><br>";
    
    echo "Intentando alternativa con localhost...<br>";
    try {
        $conn2 = new mysqli("localhost:3307", "root", "", "pizzeria_dte");
        echo "✅ ¡CONEXIÓN EXITOSA CON LOCALHOST:3307!";
        $conn2->close();
    } catch (Exception $ex) {
        echo "❌ También falló con localhost: " . $ex->getMessage();
    }
}
?>