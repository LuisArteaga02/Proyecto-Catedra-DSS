<?php
class FacturaModel {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }
// Obtener datos del emisor (Pizzería)
    public function getEmisor() {
        $query = "SELECT 
                    e.*, 
                    ae.descripcion as actividad_desc
                  FROM emisor e
                  JOIN cat_actividad_economica ae ON e.codigo_actividad_economica = ae.codigo
                  WHERE e.activo = 1 LIMIT 1";
        $result = $this->conn->query($query);
        return $result->fetch_assoc();
    }

    // Obtener datos del receptor vinculado a la factura
    public function getReceptorPorFactura($idFactura) {
        $query = "SELECT 
                    r.*, 
                    ae.descripcion as actividad_desc
                  FROM receptor r
                  LEFT JOIN cat_actividad_economica ae ON r.cod_actividad = ae.codigo
                  WHERE r.id_receptor = (SELECT id_receptor FROM factura_vinculo WHERE id_factura = ?)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $idFactura);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    // Obtiene los datos generales de la factura (Encabezado y Resumen)
    public function getEncabezadoFactura($idFactura) {
        $query = "SELECT 
                    codigo_generacion, numero_control, tipo_dte, ambiente, 
                    fecha_emision, hora_emision, condicion_pago, total_no_sujeto, 
                    total_exento, total_gravado, sub_total, iva_retenido, 
                    retencion_renta, monto_total, total_iva, total_letras
                  FROM factura 
                  WHERE id_factura = ?";
                  
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $idFactura);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    // Obtiene el detalle de las pizzas/bebidas cruzando con la tabla producto
    public function getDetallesFactura($idFactura) {
        $query = "SELECT 
                    fd.num_item, 
                    fd.cantidad, 
                    p.codigo_mh, 
                    p.product_name, 
                    p.item_tipo, 
                    p.unidad_medida, 
                    fd.precio_unitario, 
                    fd.descuento, 
                    fd.venta_gravada, 
                    fd.iva_item 
                  FROM factura_detalle fd
                  JOIN producto p ON fd.id_producto = p.id_producto
                  WHERE fd.id_factura = ?
                  ORDER BY fd.num_item ASC";
                  
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $idFactura);
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        $detalles = [];
        while ($row = $resultado->fetch_assoc()) {
            $detalles[] = $row;
        }
        return $detalles;
    }
}

?>