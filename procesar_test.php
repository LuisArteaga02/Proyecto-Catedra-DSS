<?php
require_once 'class/GeneradorDTE.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Aquí normalmente insertarías en 'factura' y 'factura_detalle' primero.
    // Para la prueba, simularemos que la factura creada tiene el ID 1.
    $idFacturaPrueba = 1; 

    $generador = new GeneradorDTE();
    $jsonResultado = $generador->generarJSONConsumidorFinal($idFacturaPrueba);

    echo "<h3>JSON Generado para Hacienda:</h3>";
    echo "<pre style='background: #272822; color: #f8f8f2; padding: 20px; border-radius: 8px;'>";
    echo $jsonResultado;
    echo "</pre>";
    echo "<a href='formulario_test.php' class='btn btn-primary'>Volver</a>";
}