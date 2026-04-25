<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Pizzería DTE - Módulo de Pruebas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Simulador de Facturación Electrónica</h4>
                </div>
                <div class="card-body">
                    <form action="procesar_test.php" method="POST">
                        <h5>Datos del Cliente (Receptor)</h5>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Nombre Completo</label>
                                <input type="text" name="nombre_cliente" class="form-control" placeholder="Ej: Juan Pérez" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">DUI / NIT</label>
                                <input type="text" name="documento_cliente" class="form-control" placeholder="00000000-0">
                            </div>
                        </div>

                        <hr>
                        <h5>Detalle del Pedido</h5>
                        <div class="row g-2 align-items-end mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Producto</label>
                                <select name="id_producto" class="form-select">
                                    <option value="1">Pizza de Pepperoni ($5.00)</option>
                                    <option value="2">Pizza de Carne ($6.00)</option>
                                    <option value="3">Coca Cola ($0.75)</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Cantidad</label>
                                <input type="number" name="cantidad" class="form-control" value="1" min="1">
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-success w-100">Generar JSON</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>