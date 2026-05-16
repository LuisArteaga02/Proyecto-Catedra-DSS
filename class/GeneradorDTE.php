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
        $nodosPrincipales = $this->construirNodosPrincipales($idFactura);

        $dte = [
            "identificacion" => [
                "version" => 1,
                "ambiente" => $encabezado['ambiente'], 
                "tipoDte" => $encabezado['tipo_dte'],  
                "numeroControl" => $encabezado['numero_control'],
                "codigoGeneracion" => strtoupper($encabezado['codigo_generacion']),
                "tipoModelo" => 1, 
                "tipoOperacion" => 1,
                "fecEmi" => $encabezado['fecha_emision'],
                "horEmi" => $encabezado['hora_emision'],
                "tipoMoneda" => "USD"
            ],
            
            "emisor" => $nodosPrincipales['emisor'],     // <-- INYECTAMOS EMISOR
            "receptor" => $nodosPrincipales['receptor'], // <-- INYECTAMOS RECEPTOR
         
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
                        "codigo" => "20", 
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
                "condicionOperacion" => (int)$encabezado['condicion_pago'], 
                "pagos" => null
            ]
        ];

    

        return json_encode($dte, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
    
    public function generarJSONNotaCredito($idFacturaNce) {
        // 1. Obtener datos de la BD para la Nota de Crédito
        $encabezado = $this->facturaModel->getEncabezadoFactura($idFacturaNce);
        $detallesBD = $this->facturaModel->getDetallesFactura($idFacturaNce);
        
        if (!$encabezado || empty($detallesBD)) {
            return json_encode(["error" => "NCE no encontrada o sin detalles."]);
        }

        // 2. Buscar el documento original y el motivo en la tabla factura_evento
        // Recuerda que en procesar_nce.php guardamos el UUID de la NCE en 'codigo_generacion_r'
        $sql_evento = "
            SELECT 
                f.tipo_dte AS original_tipo, 
                f.codigo_generacion AS original_uuid, 
                f.fecha_emision AS original_fecha, 
                ev.descripcion AS motivo_ajuste
            FROM factura_evento ev
            INNER JOIN factura f ON ev.id_factura = f.id_factura
            WHERE ev.codigo_generacion_r = ? 
            LIMIT 1
        ";
        $stmt = $this->db->prepare($sql_evento);
        $stmt->bind_param("s", $encabezado['codigo_generacion']);
        $stmt->execute();
        $resultado_evento = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        // 3. Construir el arreglo del Cuerpo del Documento (Ítems de la NCE)
        $cuerpoDocumento = [];
        foreach ($detallesBD as $fila) {
            $cuerpoDocumento[] = [
                "numItem" => (int)$fila['num_item'],
                "tipoItem" => (int)$fila['item_tipo'],
                "numeroDocumento" => null,
                "cantidad" => abs((float)$fila['cantidad']), // Se mandan en positivo al JSON
                "codigo" => $fila['codigo_mh'],
                "codTributo" => null,
                "uniMedida" => (int)$fila['unidad_medida'],
                "descripcion" => $fila['product_name'],
                "precioUni" => (float)$fila['precio_unitario'],
                "montoDescu" => (float)$fila['descuento'],
                "ventaNoSuj" => 0.0,
                "ventaExenta" => 0.0,
                "ventaGravada" => abs((float)$fila['venta_gravada']), // Absoluto para JSON
                "tributos" => ["20"], // IVA
                "psv" => 0.0,
                "noGravado" => 0.0,
                "ivaItem" => abs((float)$fila['iva_item'])
            ];
        }

        // 4. Nodos Principales
        $nodosPrincipales = $this->construirNodosPrincipales($idFacturaNce);

        // 5. Estructurar el DTE Final (Tipo 05)
        $dte = [
            "identificacion" => [
                "version" => 3, // La versión para NCE suele ser distinta según catálogos MH, usualmente 1 o 3
                "ambiente" => $encabezado['ambiente'], 
                "tipoDte" => "05",  // 05 = Nota de Crédito
                "numeroControl" => $encabezado['numero_control'],
                "codigoGeneracion" => strtoupper($encabezado['codigo_generacion']),
                "tipoModelo" => 1, 
                "tipoOperacion" => 1,
                "fecEmi" => $encabezado['fecha_emision'],
                "horEmi" => $encabezado['hora_emision'],
                "tipoMoneda" => "USD"
            ],
            
            // NODO CLAVE NCE: Documento Relacionado
            "documentoRelacionado" => [
                [
                    "tipoDocumento" => $resultado_evento['original_tipo'] ?? "01",
                    "tipoGeneracion" => 1, // 1 = DTE Electrónico previo
                    "numeroDocumento" => strtoupper($resultado_evento['original_uuid'] ?? ""),
                    "fechaEmision" => $resultado_evento['original_fecha'] ?? ""
                ]
            ],

            "emisor" => $nodosPrincipales['emisor'],     
            "receptor" => $nodosPrincipales['receptor'], 
            "cuerpoDocumento" => $cuerpoDocumento,
            
            "resumen" => [
                "totalNoSuj" => abs((float)$encabezado['total_no_sujeto']),
                "totalExenta" => abs((float)$encabezado['total_exento']),
                "totalGravada" => abs((float)$encabezado['total_gravado']),
                "subTotalVentas" => abs((float)$encabezado['sub_total']),
                "descuNoSuj" => 0.0,
                "descuExenta" => 0.0,
                "descuGravada" => 0.0,
                "porcentajeDescuento" => 0.0,
                "totalDescu" => 0.0,
                "tributos" => [
                    [
                        "codigo" => "20", 
                        "descripcion" => "IMPUESTO AL VALOR AGREGADO 13%",
                        "valor" => abs((float)$encabezado['total_iva'])
                    ]
                ],
                "subTotal" => abs((float)$encabezado['sub_total']),
                "ivaRete1" => abs((float)$encabezado['iva_retenido']),
                "reteRenta" => abs((float)$encabezado['retencion_renta']),
                "montoTotalOperacion" => abs((float)$encabezado['monto_total']),
                "totalPagar" => abs((float)$encabezado['monto_total']),
                "totalLetras" => $encabezado['total_letras'],
                "saldoFavor" => 0.0,
                "condicionOperacion" => (int)$encabezado['condicion_pago'], 
                "pagos" => null
            ],
            
            // EL MOTIVO DE DEVOLUCIÓN: Hacienda exige colocar estas descripciones en "extension" o en observaciones.
            "extension" => [
                "nombEntrega" => null,
                "docuEntrega" => null,
                "nombRecibe" => null,
                "docuRecibe" => null,
                "observaciones" => "MOTIVO NCE: " . ($resultado_evento['motivo_ajuste'] ?? "Ajuste a documento previo"),
                "placaVehiculo" => null
            ]
        ];

        return json_encode($dte, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
    
}
?>