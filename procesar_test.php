<?php
require_once 'class/Database.php';
require_once 'class/GeneradorDTE.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = (new Database())->getConnection();
    
    // 1. Recoger datos del formulario
    $nombreCliente = $_POST['nombre_cliente'];
    $docCliente = $_POST['documento_cliente'];
    $idProducto = $_POST['id_producto'];
    $cantidad = $_POST['cantidad'];

    // 2. Obtener precio del producto desde la BD
    $stmtProd = $db->prepare("SELECT product_name, precio FROM producto WHERE id_producto = ?");
    $stmtProd->bind_param("i", $idProducto);
    $stmtProd->execute();
    $producto = $stmtProd->get_result()->fetch_assoc();
    
    $precioUnitario = $producto['precio'];
    $ventaGravada = $precioUnitario * $cantidad;
    $totalIVA = $ventaGravada - ($ventaGravada / 1.13); // IVA incluido

    // 3. Preparar datos de la Factura (DTE)
    $codigoGeneracion = strtoupper(bin2hex(random_bytes(16))); // Simulando UUID
    $numeroControl = "DTE-01-A001-" . str_pad(rand(1, 9999), 15, "0", STR_PAD_LEFT);
    $fecha = date('Y-m-d');
    $hora = date('H:i:s');

    // 4. INSERTAR EN TABLA 'factura'
    $sqlFactura = "INSERT INTO factura (codigo_generacion, numero_control, fecha_emision, hora_emision, total_gravado, sub_total, monto_total, total_iva, total_letras, estado_mh) 
                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'CANTIDAD EN LETRAS', 'PENDIENTE')";
    
    $stmtFact = $db->prepare($sqlFactura);
    $stmtFact->bind_param("ssssdddd", $codigoGeneracion, $numeroControl, $fecha, $hora, $ventaGravada, $ventaGravada, $ventaGravada, $totalIVA);
    $stmtFact->execute();
    $idFacturaGenerada = $db->insert_id;

    // 5. INSERTAR EN TABLA 'factura_detalle'
    $sqlDetalle = "INSERT INTO factura_detalle (id_factura, id_producto, num_item, cantidad, precio_unitario, venta_gravada) 
                    VALUES (?, ?, 1, ?, ?, ?)";
    $stmtDet = $db->prepare($sqlDetalle);
    $stmtDet->bind_param("iiddd", $idFacturaGenerada, $idProducto, $cantidad, $precioUnitario, $ventaGravada);
    $stmtDet->execute();

    // 6. AHORA SÍ, GENERAR EL JSON
    $generador = new GeneradorDTE();
    $jsonResultado = $generador->generarJSONConsumidorFinal($idFacturaGenerada);

    echo "<h2>¡Venta Registrada Exitosamente!</h2>";
    echo "<p>ID Factura en BD: <b>$idFacturaGenerada</b></p>";
    echo "<h3>Estructura JSON para Ministerio de Hacienda:</h3>";
    echo "<pre style='background: #1e1e1e; color: #d4d4d4; padding: 20px; border-radius: 10px; overflow-x: auto;'>";
    echo $jsonResultado;
    echo "</pre>";
    echo "<br><a href='formulario_test.php' class='btn btn-primary'>Realizar otra prueba</a>";
}