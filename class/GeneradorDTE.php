<?php
require_once 'class/Database.php'; // Asumiendo que usas la clase Database del mensaje anterior
require_once 'class/FacturaModel.php';

class GeneradorDTE {
    private $db;
    private $facturaModel;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->facturaModel = new FacturaModel($this->db);
    }
    
    public function construirNodosPrincipales($idFactura) {
    $datosEmisor = $this->facturaModel->getEmisor();
    $datosReceptor = $this->facturaModel->getReceptorPorFactura($idFactura);

    // Nodo EMISOR
    $emisor = [
        "nit" => $datosEmisor['nit'],
        "nrc" => $datosEmisor['nrc'],
        "nombre" => $datosEmisor['nombre'],
        "codigoActividad" => $datosEmisor['codigo_actividad_economica'],
        "descActividad" => $datosEmisor['actividad_desc'],
        "nombreComercial" => $datosEmisor['nombre_comercial'],
        "tipoEstablecimiento" => $datosEmisor['tipo_establecimiento'],
        "direccion" => [
            "departamento" => $datosEmisor['departamento'],
            "municipio" => $datosEmisor['municipio'],
            "complemento" => "Dirección física de la pizzería" 
        ],
        "telefono" => $datosEmisor['telefono'],
        "correo" => $datosEmisor['correo'],
        "codEstableMH" => $datosEmisor['cod_estable_mh'],
        "codEstable" => $datosEmisor['cod_estable_interno'],
        "codPuntoVentaMH" => $datosEmisor['cod_punto_venta_mh'],
        "codPuntoVenta" => $datosEmisor['cod_punto_venta_int']
    ];

    // Nodo RECEPTOR
    $receptor = [
        "tipoDocumento" => $datosReceptor['tipo_documento'], // 13=DUI, 36=NIT, etc.
        "numDocumento" => $datosReceptor['num_documento'],
        "nrc" => $datosReceptor['nrc'], // Opcional para Consumidor Final
        "nombre" => $datosReceptor['nombre'],
        "codigoActividad" => $datosReceptor['cod_actividad'] ?? "60541", // Código genérico si es CF
        "descActividad" => $datosReceptor['actividad_desc'] ?? "Otros",
        "direccion" => [
            "departamento" => $datosReceptor['dir_departamento'],
            "municipio" => $datosReceptor['dir_municipio'],
            "complemento" => $datosReceptor['dir_complemento']
        ],
        "telefono" => $datosReceptor['telefono'],
        "correo" => $datosReceptor['correo']
    ];

    return ["emisor" => $emisor, "receptor" => $receptor];
}
    
    public function generarJSONConsumidorFinal($idFactura) {
        // 1. Obtener datos de la BD
        $encabezado = $this->facturaModel->getEncabezadoFactura($idFactura);
        $detallesBD = $this->facturaModel->getDetallesFactura($idFactura);
        
        if (!$encabezado || empty($detallesBD)) {
            return json_encode(["error" => "Factura no encontrada o sin detalles."]);
        }

        // 2. Construir el arreglo del Cuerpo del Documento
        $cuerpoDocumento = [];
        foreach ($detallesBD as $fila) {
            $cuerpoDocumento[] = [
                "numItem" => (int)$fila['num_item'],
                "tipoItem" => (int)$fila['item_tipo'], // Viene de la tabla producto (ej. 1 para bienes)
                "numeroDocumento" => null,
                "cantidad" => (float)$fila['cantidad'],
                "codigo" => $fila['codigo_mh'], // Ej: PZ-PEP
                "codTributo" => null,
                "uniMedida" => (int)$fila['unidad_medida'], // Ej: 59 para "Unidad"
                "descripcion" => $fila['product_name'],
                "precioUni" => (float)$fila['precio_unitario'],
                "montoDescu" => (float)$fila['descuento'],
                "ventaNoSuj" => 0.0,
                "ventaExenta" => 0.0,
                "ventaGravada" => (float)$fila['venta_gravada'],
                "tributos" => ["20"], // Código 20 corresponde al IVA en SV
                "psv" => 0.0,
                "noGravado" => 0.0,
                "ivaItem" => (float)$fila['iva_item'] // Columna autogenerada en tu SQL
            ];
        }

        // 3. Estructurar el DTE Final
        $dte = [
            "identificacion" => [
                "version" => 1,
                "ambiente" => $encabezado['ambiente'], // "00" o "01"
                "tipoDte" => $encabezado['tipo_dte'],  // "01" Factura
                "numeroControl" => $encabezado['numero_control'],
                "codigoGeneracion" => strtoupper($encabezado['codigo_generacion']),
                "tipoModelo" => 1, // Modelo de facturación previo
                "tipoOperacion" => 1,
                "fecEmi" => $encabezado['fecha_emision'],
                "horEmi" => $encabezado['hora_emision'],
                "tipoMoneda" => "USD"
            ],
         
            
            "cuerpoDocumento" => $cuerpoDocumento,
            
            "resumen" => [
                "totalNoSuj" => (float)$encabezado['total_no_sujeto'],
                "totalExenta" => (float)$encabezado['total_exento'],
                "totalGravada" => (float)$encabezado['total_gravado'],
                "subTotalVentas" => (float)$encabezado['sub_total'],
                "descuNoSuj" => 0.0,
                "descuExenta" => 0.0,
                "descuGravada" => 0.0,
                "porcentajeDescuento" => 0.0,
                "totalDescu" => 0.0,
                "tributos" => [
                    [
                        "codigo" => "20", // IVA
                        "descripcion" => "IMPUESTO AL VALOR AGREGADO 13%",
                        "valor" => (float)$encabezado['total_iva']
                    ]
                ],
                "subTotal" => (float)$encabezado['sub_total'],
                "ivaRete1" => (float)$encabezado['iva_retenido'],
                "reteRenta" => (float)$encabezado['retencion_renta'],
                "montoTotalOperacion" => (float)$encabezado['monto_total'],
                "totalPagar" => (float)$encabezado['monto_total'],
                "totalLetras" => $encabezado['total_letras'],
                "saldoFavor" => 0.0,
                "condicionOperacion" => (int)$encabezado['condicion_pago'], // 1 Contado, etc.
                "pagos" => null // Aquí iría la consulta a `factura_pago`
            ]
        ];

        return json_encode($dte, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
}
?>