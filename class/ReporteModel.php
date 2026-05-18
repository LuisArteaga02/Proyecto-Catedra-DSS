<?php
class ReporteModel {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getEmisor() {
        $query = "SELECT nombre, cod_estable_mh FROM emisor LIMIT 1";
        $result = $this->conn->query($query);
        return $result->fetch_assoc();
    }

    public function getKpis($fecha_desde, $fecha_hasta) {
        $query = "SELECT 
                    COALESCE(SUM(CASE WHEN estado_mh != 'ANULADO' THEN monto_total ELSE 0 END), 0) as total_ventas,
                    SUM(CASE WHEN estado_mh != 'ANULADO' THEN 1 ELSE 0 END) as cant_dtes_emitidos,
                    SUM(CASE WHEN estado_mh = 'ANULADO' THEN 1 ELSE 0 END) as cant_dtes_anulados,
                    COALESCE(SUM(CASE WHEN estado_mh != 'ANULADO' THEN iva_retenido ELSE 0 END), 0) as total_iva_retenido
                  FROM factura
                  WHERE fecha_emision BETWEEN ? AND ?";
                  
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ss", $fecha_desde, $fecha_hasta);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function getResumenDtes($fecha_desde, $fecha_hasta) {
        $query = "SELECT 
                    f.tipo_dte as tipo, 
                    c.nombre, 
                    COUNT(f.id_factura) as cantidad, 
                    COALESCE(SUM(f.monto_total), 0) as total
                  FROM factura f
                  JOIN cat_tipo_dte c ON f.tipo_dte = c.codigo
                  WHERE f.fecha_emision BETWEEN ? AND ?
                    AND f.estado_mh != 'ANULADO'
                  GROUP BY f.tipo_dte, c.nombre
                  ORDER BY f.tipo_dte ASC";
                  
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ss", $fecha_desde, $fecha_hasta);
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        $detalles = [];
        while ($row = $resultado->fetch_assoc()) {
            $detalles[] = $row;
        }
        return $detalles;
    }

    public function getProductosTop($fecha_desde, $fecha_hasta) {
        $query = "SELECT 
                    p.product_name as nombre, 
                    SUM(fd.cantidad) as cantidad, 
                    SUM(fd.precio_venta) as total
                  FROM factura_detalle fd
                  JOIN factura f ON fd.id_factura = f.id_factura
                  JOIN producto p ON fd.id_producto = p.id_producto
                  WHERE f.fecha_emision BETWEEN ? AND ?
                    AND f.estado_mh != 'ANULADO'
                    AND f.tipo_dte IN ('01', '03') 
                  GROUP BY p.id_producto, p.product_name
                  ORDER BY cantidad DESC
                  LIMIT 4";
                  
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ss", $fecha_desde, $fecha_hasta);
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        $productos = [];
        while ($row = $resultado->fetch_assoc()) {
            $productos[] = $row;
        }
        return $productos;
    }
}
?>