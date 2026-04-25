<?php
/**
 * index.php
 * Dashboard principal del sistema DTE
 * Pizzeria El Salvador
 */

// Esta linea es para resaltar la pagina actual en el sidebar
$pagina_activa = 'BOARD';

// Este es un array de ejemplo para que se muestre en los DTEs recientes
$dtes_recientes = [
    [
        'tipo'          => 'FE',
        'tipo_label'    => 'FE - 01',
        'tipo_class'    => 'fe',
        'receptor'      => 'Carlos Rivera Martínez',
        'n_control'     => '...P001-00000047',
        'tiempo'        => 'hace 5 min',
        'monto'         => '$12.25',
        'monto_neg'     => false,
        'estado'        => 'Contingencia',
        'estado_class'  => 'contingencia',
        'accion_label'  => 'Reenviar',
        'accion_icon'   => 'reenviar',
    ],
    [
        'tipo'          => 'CCF',
        'tipo_label'    => 'CCF - 03',
        'tipo_class'    => 'ccf',
        'receptor'      => 'Distribuidora El Buen Gusto S.A.',
        'n_control'     => '...P001-00000046',
        'tiempo'        => 'hace 22 min',
        'monto'         => '$72.50',
        'monto_neg'     => false,
        'estado'        => 'Aceptado',
        'estado_class'  => 'aceptado',
        'accion_label'  => 'Ver PDF',
        'accion_icon'   => 'pdf',
    ],
    [
        'tipo'          => 'NCE',
        'tipo_label'    => 'NCE - 05',
        'tipo_class'    => 'nce',
        'receptor'      => 'Pedro Martínez Sánchez',
        'n_control'     => '...P001-00000045',
        'tiempo'        => 'hace 1 hora',
        'monto'         => '-$10.00',
        'monto_neg'     => true,
        'estado'        => 'Aceptado',
        'estado_class'  => 'aceptado',
        'accion_label'  => 'Ver PDF',
        'accion_icon'   => 'pdf',
    ],
];
?>

<!-- Estructura del dashboard -->
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio — Sistema DTE | Pizzeria El Salvador</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="layout">
    <!-- se invoca el sidebar -->
    <?php include 'navegacion.php'; ?>

    <!-- Aqui va el contenido principal -->
    <div class="main-content">

        <!-- Topbar -->
        <header class="topbar">
            <h1>Inicio</h1>

             <!-- Propuesta para agregar tiempo-->
            <div class="topbar-right">
                <span class="topbar-date">Vie, 20 Mar 2026</span>

                <!-- Propuesta para agregar notificaciones -->
                <span class="topbar-bell" title="Notificaciones">
                    <img src="https://placehold.co/20x20/6b6560/ffffff?text=N" alt="Notificaciones" class="topbar-img">
                </span>

            </div>
        </header>

        <!-- Contenido principal -->
        <main class="page-content">

            <!-- Acciones rápidas || Aqui hay que colocar las paginas correspondientes para la navegacion -->
            <h2 class="section-title">Acciones rápidas</h2>

            <div class="quick-actions-grid">

                <!-- Nueva factura consumidor final || FE -->
                <a href="test.php" class="action-card">
                    <div class="action-icon fe"><img src="https://placehold.co/24x24/c0392b/fde8e7?text=FE" alt="Factura" class="action-img"></div>
                    <h3>Factura consumidor final</h3>
                    <p>Para ventas a clientes regulares</p>
                    <span class="dte-badge fe">FE - TipoDTE 01</span>
                </a>
 
                <!-- Nuevo crédito fiscal || CCF -->
                <a href="test.php" class="action-card">
                    <div class="action-icon ccf"><img src="https://placehold.co/24x24/2e7d32/e6f4ea?text=CF" alt="Credito Fiscal" class="action-img"></div>
                    <h3>Crédito fiscal</h3>
                    <p>Para empresas y contribuyentes inscritos en IVA</p>
                    <span class="dte-badge ccf">CCF - TipoDTE 03</span>
                </a>
 
                <!-- Nota de crédito || NCE -->
                <a href="test.php" class="action-card">
                    <div class="action-icon nce"><img src="https://placehold.co/24x24/e65100/fff3e0?text=NC" alt="Nota Credito" class="action-img"></div>
                    <h3>Nota de crédito</h3>
                    <p>Para ajustar o devolver una FE o CCF ya aceptada</p>
                    <span class="dte-badge nce">NCE - TipoDTE 05</span>
                </a>
 
                <!-- Invalidar DTE || Invalidacion -->
                <a href="test.php" class="action-card">
                    <div class="action-icon inv"><img src="https://placehold.co/24x24/424242/eeeeee?text=IN" alt="Invalidar" class="action-img"></div>
                    <h3>Invalidar un DTE</h3>
                    <p>Anular un DTE aceptado dentro del plazo</p>
                    <span class="dte-badge inv">Invalidacion - 06</span>
                </a>
 
            </div>            

            <!--  DTEs recientes || Aqui se invocan los DTEs recientes establecidos arriba. La logica de esta funcion se tiene que modificar -->
            <div class="dtes-recientes-card">
                <h2 class="section-title">DTEs recientes</h2>

                <table class="dtes-table">
                    <thead>
                        <tr>
                            <th>Tipo</th>
                            <th>Receptor</th>
                            <th>N° Control</th>
                            <th>Monto</th>
                            <th>Estado</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($dtes_recientes as $dte): ?>
                        <tr>
                            <!-- Tipo -->
                            <td class="td-tipo">
                                <span class="dte-badge <?= htmlspecialchars($dte['tipo_class']) ?>">
                                    <?= htmlspecialchars($dte['tipo_label']) ?>
                                </span>
                            </td>

                            <!-- Receptor -->
                            <td class="td-receptor">
                                <div class="receptor-name"><?= htmlspecialchars($dte['receptor']) ?></div>
                                <div class="receptor-control"><?= htmlspecialchars($dte['n_control']) ?></div>
                            </td>

                            <!-- N° Control / Tiempo -->
                            <td class="td-control">
                                <?= htmlspecialchars($dte['tiempo']) ?>
                            </td>

                            <!-- Monto -->
                            <td class="td-monto <?= $dte['monto_neg'] ? 'negative' : '' ?>">
                                <?= htmlspecialchars($dte['monto']) ?>
                            </td>

                            <!-- Estado -->
                            <td>
                                <span class="estado-badge <?= htmlspecialchars($dte['estado_class']) ?>">
                                    <span class="estado-dot"></span>
                                    <?= htmlspecialchars($dte['estado']) ?>
                                </span>
                            </td>

                            <!-- Acción -->
                            <td class="td-acciones">
                                <button class="btn-accion" type="button">
                                    <img src="https://placehold.co/14x14/6b6560/ffffff?text=<?= urlencode(strtoupper(substr($dte['accion_icon'], 0, 1))) ?>" alt="<?= htmlspecialchars($dte['accion_label']) ?>" class="btn-img">
                                    <?= htmlspecialchars($dte['accion_label']) ?>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        </main>
    </div>

</div>

</body>
</html>