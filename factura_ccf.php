<?php
/* ============================================================
   factura_ccf.php — Comprobante de Crédito Fiscal (DTE Tipo 03)
   Pizzeria El Salvador — Sistema de Facturación Electrónica
   ============================================================ */

session_start();
require_once 'class/Database.php';

if (!isset($_SESSION['usuario_nombre'])) {
    header("Location: login.php");
    exit();
}

$pagina_activa = 'CCF';

$db   = new Database();
$conn = $db->getConnection();

/* ---------- Catálogos ---------- */

$sql_prod = "SELECT id_producto, product_name, codigo_mh, precio FROM producto WHERE activo = 1";
$result_prod = $conn->query($sql_prod);
$productos_db = [];
if ($result_prod) {
    while ($row = $result_prod->fetch_assoc()) {
        $productos_db[] = $row;
    }
}

$sql_act = "SELECT codigo, descripcion FROM cat_actividad_economica";
$result_act = $conn->query($sql_act);
$actividades_db = [];
if ($result_act) {
    while ($row = $result_act->fetch_assoc()) {
        $actividades_db[] = $row;
    }
}

$sql_deptos = "SELECT codigo, nombre FROM cat_departamento";
$result_deptos = $conn->query($sql_deptos);
$departamentos_db = [];
if ($result_deptos) {
    while ($row = $result_deptos->fetch_assoc()) {
        $departamentos_db[] = $row;
    }
}

$sql_muni = "SELECT codigo, codigo_depto, nombre FROM cat_municipio";
$result_muni = $conn->query($sql_muni);
$municipios_db = [];
if ($result_muni) {
    while ($row = $result_muni->fetch_assoc()) {
        $municipios_db[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>DTE — Comprobante de Crédito Fiscal</title>
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="css/formularios.css">
</head>

<body>
<div class="layout">
  <?php include 'navegacion.php'; ?>

  <div class="main-content">
    <header class="topbar">
      <h1>Emisión de Documento Tributario Electrónico</h1>
      <span class="status-badge"></span>
    </header>

    <main class="page-content">

      <?php if (isset($_GET['error'])): ?>
        <div style="background:#fde8e7;border:1px solid #d32f2f;color:#d32f2f;
             padding:12px 20px;margin-bottom:20px;border-radius:6px;font-weight:600;">
          ❌ <?= htmlspecialchars($_GET['error']) ?>
        </div>
      <?php endif; ?>

      <div class="ccf-container">

        <!-- ===== BANNER SUPERIOR ===== -->
        <div class="fe-header-banner">
          <div class="fe-header-left">
            <span class="badge-ccf">CCF</span>
            <div>
              <div class="fe-title">COMPROBANTE DE CRÉDITO FISCAL (DTE TIPO 03)</div>
              <div style="font-size:10px;color:#f0a0a0;margin-top:2px;">Ministerio de Hacienda — El Salvador</div>
            </div>
          </div>
          <div class="fe-header-right">
            <div style="font-weight:700;"><?= htmlspecialchars($_SESSION['empresa_nombre'] ?? 'EMPRESA S.A. DE C.V.') ?></div>
            <div>NRC: <strong style="color:#e8b800;"><?= htmlspecialchars($_SESSION['empresa_nrc'] ?? '') ?></strong></div>
            <span class="fe-code">MODELO: FACTURACIÓN PREVIA</span>
          </div>
        </div>

        <!-- ===== AVISO INFORMATIVO CCF ===== -->
        <div class="ccf-aviso">
          El CCF aplica únicamente a <strong>receptores inscritos en IVA (contribuyentes)</strong>.
          A diferencia de la FE, el precio unitario se ingresa <strong>SIN IVA</strong> y el IVA se calcula
          sobre la base neta: <strong>IVA = ventaGravada × 0.13</strong>.
          Son obligatorios el NIT, NRC, actividad económica y nombre de la empresa receptora.
        </div>

        <form id="formCCF" action="procesar_ccf.php" method="POST" novalidate>

          <!-- ===== METADATOS DTE ===== -->
          <div class="metadata-strip-ccf">
            <div class="fe-group">
              <label>CÓDIGO DE GENERACIÓN <span class="req">*</span></label>
              <input type="text" name="codigo_generacion" id="codigo_generacion"
                     readonly placeholder="Se generará automáticamente" style="font-size:10px;">
            </div>
            <div class="fe-group">
              <label>NÚMERO DE CONTROL <span class="req">*</span></label>
              <input type="text" name="numero_control" id="numero_control"
                     readonly placeholder="Generado al guardar (Ej: DTE-03...)">
            </div>
            <div class="fe-group">
              <label>SELLO DE RECEPCIÓN</label>
              <input type="text" name="sello_recepcion" readonly
                     placeholder="Asignado por MH tras transmisión">
            </div>
            <div class="fe-group">
              <label>MODELO DE FACTURACIÓN</label>
              <input type="text" value="1 — PREVIO" readonly>
            </div>
            <div class="fe-group">
              <label>TIPO DE TRANSMISIÓN</label>
              <input type="text" value="1 — NORMAL" readonly>
            </div>
            <div class="fe-group">
              <label>FECHA Y HORA DE GENERACIÓN</label>
              <input type="text" name="fecha_generacion" id="fecha_generacion" readonly>
            </div>
          </div>


          <!-- ===== SECCIÓN 1: RECEPTOR — EMPRESA CONTRIBUYENTE ===== -->
          <section class="fe-section">
            <h3 class="section-title"><span class="num">1</span> Información del Receptor — Empresa Contribuyente</h3>

            <!-- Sub-bloque: Identificación tributaria -->
            <div class="box-info" style="margin-bottom:14px;">
              <h4>🏢 Identificación Tributaria</h4>
              <div class="grid-3">

                <div class="fe-group">
                  <label>NIT DE LA EMPRESA <span class="req">*</span></label>
                  <div class="nit-wrapper">
                    <input type="text" name="receptor_nit" id="receptor_nit"
                           required maxlength="17" placeholder="0000-000000-000-0">
                    <button type="button" class="btn-consulta-nit"
                            onclick="consultarNIT()">Consultar en Hacienda</button>
                  </div>
                  <span class="error-msg" id="err_nit">Formato inválido. Use: 0000-000000-000-0</span>
                </div>

                <div class="fe-group">
                  <label>NRC DE LA EMPRESA <span class="req">*</span></label>
                  <input type="text" name="receptor_nrc" id="receptor_nrc"
                         required maxlength="8" placeholder="00000000 (máx. 8 dígitos)">
                  <span class="error-msg" id="err_nrc">El NRC debe tener hasta 8 dígitos.</span>
                </div>

                <div class="fe-group">
                  <label>CÓDIGO DE ACTIVIDAD ECONÓMICA <span class="req">*</span></label>
                  <select name="receptor_cod_actividad" id="receptor_cod_actividad" required>
                    <option value="">-- Seleccione --</option>
                    <?php foreach ($actividades_db as $act): ?>
                      <option value="<?= htmlspecialchars($act['codigo']) ?>">
                        <?= htmlspecialchars($act['codigo']) ?> — <?= htmlspecialchars($act['descripcion']) ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                  <span class="error-msg" id="err_actividad">Seleccione la actividad económica.</span>
                </div>

                <div class="fe-group span3">
                  <label>DESCRIPCIÓN DE LA ACTIVIDAD <span class="req">*</span></label>
                  <input type="text" name="receptor_desc_actividad" id="receptor_desc_actividad"
                         required maxlength="150"
                         placeholder="Se rellena automáticamente al seleccionar la actividad">
                  <span class="error-msg" id="err_desc_actividad">Ingrese la descripción de la actividad.</span>
                </div>

              </div>
            </div>

            <!-- Sub-bloque: Datos generales del receptor -->
            <div class="grid-3">

              <div class="fe-group full">
                <label>NOMBRE O RAZÓN SOCIAL <span class="req">*</span></label>
                <input type="text" name="receptor_nombre" id="receptor_nombre"
                       required maxlength="250"
                       placeholder="Nombre completo o razón social de la empresa receptora">
                <span class="error-msg" id="err_nombre">Ingrese el nombre o razón social del receptor.</span>
              </div>

              <div class="fe-group">
                <label>NOMBRE COMERCIAL</label>
                <input type="text" name="receptor_nombre_comercial"
                       maxlength="150" placeholder="Nombre comercial (si aplica)">
              </div>

              <div class="fe-group">
                <label>CORREO ELECTRÓNICO <span class="req">*</span></label>
                <input type="email" name="receptor_email" id="receptor_email"
                       required maxlength="100" placeholder="contabilidad@empresa.com">
                <span class="error-msg" id="err_email">Ingrese un correo electrónico válido.</span>
              </div>

              <div class="fe-group">
                <label>TELÉFONO</label>
                <input type="text" name="receptor_tel" id="receptor_tel"
                       maxlength="9" placeholder="2222-3333">
                <span class="error-msg" id="err_tel">Formato: 2222-3333 o 7777-8888.</span>
              </div>

              <div class="fe-group">
                <label>DEPARTAMENTO <span class="req">*</span></label>
                <select name="dir_departamento" id="departamento" required onchange="cargarMunicipios()">
                  <option value="">-- Seleccione --</option>
                  <?php foreach ($departamentos_db as $depto): ?>
                    <option value="<?= htmlspecialchars($depto['codigo']) ?>">
                      <?= htmlspecialchars($depto['nombre']) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
                <span class="error-msg" id="err_depto">Seleccione un departamento.</span>
              </div>

              <div class="fe-group">
                <label>MUNICIPIO <span class="req">*</span></label>
                <select name="dir_municipio" id="municipio" required>
                  <option value="">-- Seleccione departamento --</option>
                </select>
                <span class="error-msg" id="err_muni">Seleccione un municipio.</span>
              </div>

              <div class="fe-group full">
                <label>DIRECCIÓN <span class="req">*</span></label>
                <input type="text" name="receptor_direccion" id="receptor_direccion"
                       required maxlength="200"
                       placeholder="Dirección completa de la empresa receptora">
                <span class="error-msg" id="err_direccion">Ingrese la dirección del receptor.</span>
              </div>

            </div>
          </section>


          <!-- ===== SECCIÓN 2: DOCUMENTOS RELACIONADOS ===== -->
          <section class="fe-section">
            <h3 class="section-title"><span class="num">2</span> Documentos Relacionados (Si aplica)</h3>
            <div class="table-wrap">
              <table class="docs-rel-table" id="tablaDocRel">
                <thead>
                  <tr>
                    <th>Tipo de Documento</th>
                    <th>N° de Documento</th>
                    <th>Fecha del Documento</th>
                    <th></th>
                  </tr>
                </thead>
                <tbody></tbody>
              </table>
              <button type="button" class="btn-add" id="addDocRel">+ Agregar Documento Relacionado</button>
            </div>
            <p style="font-size:10px;color:#888;margin-top:6px;font-style:italic;">
              * Complete esta sección solo si este DTE está relacionado con otro documento tributario previo.
            </p>
          </section>


          <!-- ===== SECCIÓN 3: DETALLE DE PRODUCTOS / SERVICIOS ===== -->
          <section class="fe-section">
            <h3 class="section-title">
              <span class="num">3</span> Detalle de Productos / Servicios
              <span style="font-size:9px;font-weight:500;color:var(--text-muted);
                           text-transform:none;margin-left:8px;">Precios SIN IVA — distinto a FE</span>
            </h3>
            <p style="font-size:10px;color:var(--navy);background:var(--red-light);
                      padding:7px 12px;border-radius:4px;margin-bottom:10px;
                      font-weight:600;border-left:3px solid var(--red);">
              ℹ IVA por ítem = ventaGravada × 0.13 &nbsp;|&nbsp; Tributos por ítem = ["20"] — código IVA CAT-015
            </p>
            <div class="table-wrap">
              <table class="items-table" id="tablaItems">
                <thead>
                  <tr>
                    <th style="width:30px;">N°</th>
                    <th style="width:80px;">TIPO ÍTEM</th>
                    <th style="width:55px;">CANT.</th>
                    <th style="width:75px;">UNIDAD</th>
                    <th style="min-width:160px;">DESCRIPCIÓN</th>
                    <th style="width:90px;">P.UNIT. NETO<br><span style="font-weight:400;font-size:9px;">(Sin IVA)</span></th>
                    <th style="width:80px;">DESCUENTO</th>
                    <th style="width:85px;">V.GRAVADA</th>
                    <th style="width:80px;">IVA (13%)</th>
                    <th style="width:90px;">TOTAL C/IVA</th>
                    <th style="width:28px;"></th>
                  </tr>
                </thead>
                <tbody></tbody>
              </table>
              <button type="button" class="btn-add" id="addItem">+ Agregar Producto o Servicio</button>
            </div>
            <p style="font-size:10px;color:#888;margin-top:6px;font-style:italic;">
              * Precio unitario SIN IVA. El sistema calcula IVA (13%) y Total con IVA automáticamente.
            </p>
          </section>


          <!-- ===== SECCIÓN 4: RESUMEN Y TOTALES ===== -->
          <section class="fe-section no-border">
            <h3 class="section-title"><span class="num">4</span> Resumen del Comprobante</h3>
            <div class="footer-grid">

              <!-- Columna izquierda -->
              <div>

                <div class="fe-group" style="margin-bottom:12px;">
                  <label>OBSERVACIONES</label>
                  <textarea name="observaciones" rows="4" maxlength="3000"
                            placeholder="Notas adicionales..."></textarea>
                </div>

                <div class="condicion-row">
                  <label>CONDICIÓN DE LA OPERACIÓN: <span class="req">*</span></label>
                  <select name="condicion_pago" id="condicion_pago">
                    <option value="01">01 — Contado</option>
                    <option value="02">02 — A Crédito</option>
                    <option value="03">03 — Otro</option>
                  </select>
                </div>

                <div id="bloque_plazo" style="display:none;margin-top:10px;">
                  <div class="grid-2" style="gap:10px;">
                    <div class="fe-group">
                      <label>PLAZO (Meses)</label>
                      <input type="number" name="plazo_meses" min="1" max="120"
                             placeholder="Número de meses">
                    </div>
                    <div class="fe-group">
                      <label>FECHA VENCIMIENTO</label>
                      <input type="date" name="fecha_vencimiento">
                    </div>
                  </div>
                </div>

                <!-- Retenciones — exclusivo CCF -->
                <div style="margin-top:14px;">
                  <div class="section-divider">Retenciones</div>
                  <div class="retencion-grid">
                    <div class="fe-group">
                      <label>IVA RETENIDO (1%)</label>
                      <input type="number" name="iva_retenido" id="iva_retenido"
                             step="0.01" min="0" value="0.00" placeholder="0.00"
                             onkeypress="return isNum(event)">
                    </div>
                    <div class="fe-group">
                      <label>RETENCIÓN DE RENTA</label>
                      <input type="number" name="retencion_renta" id="retencion_renta"
                             step="0.01" min="0" value="0.00" placeholder="0.00"
                             onkeypress="return isNum(event)">
                    </div>
                  </div>
                </div>

                <!-- Formas de pago -->
                <div style="margin-top:14px;">
                  <div class="section-divider">Forma de Pago</div>
                  <div id="formasPago"></div>
                  <button type="button" class="btn-add" id="addFormaPago"
                          style="margin:6px 0;">+ Forma de Pago</button>
                </div>

              </div><!-- /col izquierda -->

              <!-- Columna derecha: totales -->
              <div>
                <div class="totales-box">
                  <div class="totales-title">📊 RESUMEN DE TOTALES</div>
                  <div class="total-row">
                    <span>Ventas No Sujetas:</span><span id="t_nosujeta">0.00</span>
                  </div>
                  <div class="total-row">
                    <span>Ventas Exentas:</span><span id="t_exenta">0.00</span>
                  </div>
                  <div class="total-row">
                    <span>Total Ventas Gravadas (base):</span><span id="t_gravada">0.00</span>
                  </div>
                  <div class="total-row separator">
                    <span>Descuento Total:</span><span id="t_desc_global">0.00</span>
                  </div>
                  <div class="total-row">
                    <span>Subtotal sin IVA:</span><span id="t_subtotal">0.00</span>
                  </div>
                  <div class="total-row separator">
                    <span>+ IVA 13% (tributo código 20):</span><span id="t_iva">0.00</span>
                  </div>
                  <div class="total-row">
                    <span>IVA Retenido / Retención Renta:</span>
                    <span id="t_retenciones">$0.00 / $0.00</span>
                  </div>
                  <div class="total-row highlight">
                    <strong>TOTAL A PAGAR:</strong>
                    <strong id="t_total" class="val-total">$ 0.00</strong>
                  </div>
                  <p class="total-letras" id="t_letras">SON: CERO 00/100 DÓLARES</p>
                  <div class="total-row credito-fiscal">
                    <span>Crédito fiscal deducible para el receptor:</span>
                    <span id="t_credito_fiscal">$0.00</span>
                  </div>
                </div>

                <!-- Campos ocultos POST -->
                <input type="hidden" name="h_nosujeta"              id="h_nosujeta">
                <input type="hidden" name="h_exenta"                id="h_exenta">
                <input type="hidden" name="h_gravada"               id="h_gravada">
                <input type="hidden" name="h_desc_global"           id="h_desc_global">
                <input type="hidden" name="h_subtotal"              id="h_subtotal">
                <input type="hidden" name="h_iva"                   id="h_iva">
                <input type="hidden" name="h_iva_retenido"          id="h_iva_retenido_h">
                <input type="hidden" name="h_retencion_renta"       id="h_retencion_renta_h">
                <input type="hidden" name="h_total"                 id="h_total">

              </div><!-- /col derecha -->
            </div><!-- /footer-grid -->
          </section>


          <!-- ===== SECCIÓN 5: RESPONSABLES ===== -->
          <section class="fe-section">
            <h3 class="section-title"><span class="num">5</span> Responsables</h3>
            <div class="responsables-grid">
              <div class="responsable-box">
                <label>RESPONSABLE — EMISOR</label>
                <input type="text" name="responsable_emisor_nombre"
                       value="<?= htmlspecialchars($_SESSION['usuario_nombre']) ?>"
                       maxlength="100" placeholder="Nombre del responsable">
                <div style="margin-top:8px;">
                  <label>N° DE DOCUMENTO</label>
                  <input type="text" name="responsable_emisor_doc"
                         maxlength="20" placeholder="DUI o NIT">
                </div>
              </div>
              <div class="responsable-box">
                <label>RESPONSABLE — RECEPTOR (Opcional)</label>
                <input type="text" name="responsable_receptor_nombre"
                       maxlength="100" placeholder="Nombre del responsable">
                <div style="margin-top:8px;">
                  <label>N° DE DOCUMENTO</label>
                  <input type="text" name="responsable_receptor_doc"
                         maxlength="20" placeholder="DUI o NIT">
                </div>
              </div>
            </div>
          </section>


          <!-- ===== ACCIONES ===== -->
          <div class="form-actions">
            <button type="button" class="btn-cancel"
                    onclick="confirmarCancelar()">Cancelar Operación</button>
            <button type="button" class="btn-preview"
                    onclick="previsualizarDTE()">👁 Vista Previa</button>
            <button type="submit" class="btn-save"
                    onclick="return validarFormulario()">📤 GENERAR CCF Y PDF</button>
          </div>

        </form>
      </div><!-- /ccf-container -->
    </main>
  </div>
</div>

<div id="toast-ccf"></div>

<script>
/* ================================================================
   CATÁLOGOS
   ================================================================ */
const TIPOS_ITEM = [
  { v: '1', l: '1 — Bien' },
  { v: '2', l: '2 — Servicio' },
  { v: '3', l: '3 — Ambos' },
  { v: '4', l: '4 — Otro cargo' }
];

const UNIDADES = [
  { v: '59', l: 'Unidad' },
  { v: '39', l: 'Servicio' },
  { v: '26', l: 'Libra' },
  { v: '27', l: 'Kilogramo' },
  { v: '10', l: 'Litro' },
  { v: '01', l: 'Metro' },
  { v: '99', l: 'Otro' }
];

const FORMAS_PAGO = [
  { v: '01', l: '01 — Billetes y Monedas' },
  { v: '02', l: '02 — Tarjeta Débito' },
  { v: '03', l: '03 — Tarjeta Crédito' },
  { v: '04', l: '04 — Cheque' },
  { v: '05', l: '05 — Transferencia Crédito' },
  { v: '06', l: '06 — Transferencia Débito' },
  { v: '07', l: '07 — Vales' },
  { v: '08', l: '08 — Dinero Electrónico' },
  { v: '09', l: '09 — Tarjeta Prepago' },
  { v: '10', l: '10 — Pago Móvil' },
  { v: '11', l: '11 — Bitcoin' },
  { v: '99', l: '99 — Otros' }
];

const TIPOS_DOC_REL = [
  { v: '01', l: '01 — Factura' },
  { v: '02', l: '02 — CCF' },
  { v: '03', l: '03 — Nota de Remisión' },
  { v: '04', l: '04 — Nota de Crédito' },
  { v: '05', l: '05 — Nota de Débito' },
  { v: '06', l: '06 — Comp. Liquidación' },
  { v: '07', l: '07 — Doc. Contable Liq.' },
  { v: '08', l: '08 — Factura Sujeto Excluido' },
  { v: '09', l: '09 — DTE' }
];

const PRODUCTOS_DB  = <?= json_encode($productos_db) ?>;
const MUNICIPIOS_DB = <?= json_encode($municipios_db) ?>;

/* ================================================================
   HELPERS
   ================================================================ */
function opts(arr, sel = '') {
  return arr.map(o =>
    `<option value="${o.v}"${o.v === sel ? ' selected' : ''}>${o.l}</option>`
  ).join('');
}
function mkSel(name, arr, cls = 'in-table') {
  return `<select name="${name}" class="${cls}">${opts(arr)}</select>`;
}
function fmt(n) { return parseFloat(n || 0).toFixed(2); }
function isNum(evt) {
  const c = evt.which || evt.keyCode;
  return !(c === 45 || c === 101 || c === 69);
}

/* ================================================================
   TOAST
   ================================================================ */
function toast(msg, tipo = 'error') {
  const t = document.getElementById('toast-ccf');
  t.textContent = msg;
  t.className   = 'show' + (tipo === 'success' ? ' success' : '');
  clearTimeout(t._t);
  t._t = setTimeout(() => t.className = '', 4500);
}

/* ================================================================
   INIT METADATOS
   ================================================================ */
(function () {
  const ahora = new Date();
  const p = n => String(n).padStart(2, '0');

  document.getElementById('fecha_generacion').value =
    `${ahora.getFullYear()}-${p(ahora.getMonth()+1)}-${p(ahora.getDate())} ` +
    `${p(ahora.getHours())}:${p(ahora.getMinutes())}:${p(ahora.getSeconds())}`;

  document.getElementById('codigo_generacion').value =
    'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, c => {
      const r = Math.random() * 16 | 0,
            v = c === 'x' ? r : (r & 0x3 | 0x8);
      return v.toString(16).toUpperCase();
    });

  document.getElementById('numero_control').value       = '';
  document.getElementById('numero_control').placeholder = 'Generado al guardar (Ej: DTE-03-M001-P001-000000000000001)';
})();

/* ================================================================
   CASCADA DEPARTAMENTO → MUNICIPIO
   ================================================================ */
function cargarMunicipios() {
  const codDepto   = document.getElementById('departamento').value;
  const muniSelect = document.getElementById('municipio');
  muniSelect.innerHTML = '<option value="">-- Seleccione --</option>';
  if (codDepto) {
    MUNICIPIOS_DB
      .filter(m => m.codigo_depto === codDepto)
      .forEach(m => {
        const opt       = document.createElement('option');
        opt.value       = m.codigo;
        opt.textContent = m.nombre;
        muniSelect.appendChild(opt);
      });
  }
}

/* ================================================================
   AUTOCOMPLETAR DESCRIPCIÓN DE ACTIVIDAD ECONÓMICA
   ================================================================ */
document.getElementById('receptor_cod_actividad').addEventListener('change', function () {
  const texto = this.options[this.selectedIndex].text;
  const desc  = texto.includes(' — ') ? texto.split(' — ')[1] : '';
  document.getElementById('receptor_desc_actividad').value = desc;
  markErr('receptor_cod_actividad',  'err_actividad',      !this.value);
  markErr('receptor_desc_actividad', 'err_desc_actividad', desc.length < 2);
});

/* ================================================================
   CONSULTA NIT (placeholder — conectar a Hacienda cuando disponible)
   ================================================================ */
function consultarNIT() {
  const nit = document.getElementById('receptor_nit').value.trim();
  if (!reNIT.test(nit)) {
    toast('Ingrese un NIT válido antes de consultar (0000-000000-000-0).');
    markErr('receptor_nit', 'err_nit', true);
    return;
  }
  // TODO: integrar endpoint de consulta al MH
  toast('Consulta a Hacienda en desarrollo. Ingrese los datos manualmente.', 'success');
}

/* ================================================================
   TABLA DE ÍTEMS  —  Precios SIN IVA (diferencia clave vs FE)
   ================================================================ */
function optsProductos() {
  let html = '<option value="">-- Seleccione un Producto --</option>';
  PRODUCTOS_DB.forEach(p => {
    // El precio en BD incluye IVA; el CCF trabaja con el precio neto
    const precioNeto = (parseFloat(p.precio) / 1.13).toFixed(8);
    html += `<option value="${p.id_producto}" data-precio="${precioNeto}">`
          + `${p.codigo_mh} - ${p.product_name}</option>`;
  });
  return html;
}

function filaItem(n) {
  return `
<tr>
  <td class="td-num">${n}</td>
  <td class="td-tipo">${mkSel('tipo_item[]', TIPOS_ITEM)}</td>
  <td><input type="number" name="cant[]"
             class="in-table qty" min="1" value="1" step="1"
             onkeypress="return isNum(event)"></td>
  <td>${mkSel('unidad[]', UNIDADES)}</td>
  <td>
    <select name="id_producto[]" class="in-table desc-col prod-select"
            required onchange="seleccionarProducto(this)">
      ${optsProductos()}
    </select>
  </td>
  <td>
    <input type="text" class="in-table price-display" value="0.00" readonly>
    <input type="hidden" name="precio_neto[]" class="in-table price" value="0.00000000">
  </td>
  <td><input type="number" name="descuento_item[]"
             class="in-table desc_item" step="0.01" value="0.00"
             onkeypress="return isNum(event)"></td>
  <td><input type="number" name="v_gravada[]"
             class="in-table v_grav" step="0.01" value="0.00" readonly></td>
  <td><input type="number" name="iva_item[]"
             class="in-table iva_item" step="0.01" value="0.00" readonly></td>
  <td><input type="number" name="total_civa[]"
             class="in-table total_civa" step="0.01" value="0.00" readonly></td>
  <td><button type="button" class="btn-del"
              onclick="delItem(this)">×</button></td>
</tr>`;
}

function seleccionarProducto(selectEl) {
  const tr           = selectEl.closest('tr');
  const option       = selectEl.options[selectEl.selectedIndex];
  
  // Capturamos ambos inputs
  const priceHidden  = tr.querySelector('.price');         // El oculto
  const priceDisplay = tr.querySelector('.price-display'); // El visible

  if (option.value) {
    const precioExacto = parseFloat(option.dataset.precio);
    
    // Al input oculto (y al POST) le mandamos los 8 decimales
    priceHidden.value  = precioExacto.toFixed(8);
    
    // Al input visual le mostramos solo 2 decimales redondeados
    priceDisplay.value = precioExacto.toFixed(2);
  } else {
    priceHidden.value  = '0.00000000';
    priceDisplay.value = '0.00';
  }

  calcTotales();
}

function addItem() {
  const tbody = document.querySelector('#tablaItems tbody');
  tbody.insertAdjacentHTML('beforeend', filaItem(tbody.rows.length + 1));
}

function delItem(btn) {
  const tbody = document.querySelector('#tablaItems tbody');
  if (tbody.rows.length <= 1) { toast('Debe haber al menos un ítem.'); return; }
  btn.closest('tr').remove();
  [...tbody.rows].forEach((tr, i) =>
    tr.querySelector('.td-num').textContent = i + 1
  );
  calcTotales();
}

document.getElementById('addItem').addEventListener('click', addItem);
addItem();

/* ================================================================
   TABLA DOCUMENTOS RELACIONADOS
   ================================================================ */
function filaDocRel() {
  return `
<tr>
  <td>${mkSel('doc_rel_tipo[]', TIPOS_DOC_REL)}</td>
  <td><input type="text" name="doc_rel_numero[]" class="in-table"
             maxlength="36" placeholder="N° o código"></td>
  <td><input type="date" name="doc_rel_fecha[]" class="in-table"></td>
  <td><button type="button" class="btn-del"
              onclick="this.closest('tr').remove()">×</button></td>
</tr>`;
}

document.getElementById('addDocRel').addEventListener('click', function () {
  document.querySelector('#tablaDocRel tbody')
    .insertAdjacentHTML('beforeend', filaDocRel());
});

/* ================================================================
   FORMAS DE PAGO
   ================================================================ */
function filaFormaPago(esEliminar = true) {
  const del = esEliminar
    ? `<div class="fe-group" style="align-self:flex-end;">
         <label>&nbsp;</label>
         <button type="button" class="btn-del"
                 onclick="this.closest('.forma-pago-row').remove()">×</button>
       </div>`
    : '';
  return `
<div class="forma-pago-row">
  <div class="fe-group">
    <label>CÓDIGO FORMA PAGO <span class="req">*</span></label>
    <select name="forma_pago_codigo[]">${opts(FORMAS_PAGO)}</select>
  </div>
  <div class="fe-group">
    <label>MONTO <span class="req">*</span></label>
    <input type="number" name="forma_pago_monto[]"
           step="0.01" min="0" placeholder="0.00"
           onkeypress="return isNum(event)">
  </div>
  <div class="fe-group">
    <label>PERIODO (Días)</label>
    <input type="number" name="forma_pago_periodo[]" min="0" placeholder="0">
  </div>
  ${del}
</div>`;
}

document.getElementById('addFormaPago').addEventListener('click', function () {
  document.getElementById('formasPago')
    .insertAdjacentHTML('beforeend', filaFormaPago(true));
});
document.getElementById('formasPago')
  .insertAdjacentHTML('beforeend', filaFormaPago(false));

/* ================================================================
   CÁLCULO DE TOTALES — CCF
   ventaGravada = (qty × precioNeto) − descuento
   IVA          = ventaGravada × 0.13
   Total C/IVA  = ventaGravada + IVA
   Total a pagar = subtotal + IVA total − retenciones
   ================================================================ */
function calcTotales() {
  let grav = 0, exen = 0, nosuj = 0, desc = 0, ivaTotal = 0;

  document.querySelectorAll('#tablaItems tbody tr').forEach(tr => {
    const q = Math.max(0, parseFloat(tr.querySelector('.qty')?.value)       || 0);
    const p = Math.max(0, parseFloat(tr.querySelector('.price')?.value)     || 0);
    const d = Math.max(0, parseFloat(tr.querySelector('.desc_item')?.value) || 0);

    const vGrav     = Math.max(0, (q * p) - d);
    const ivaItem   = vGrav * 0.13;
    const totalCIva = vGrav + ivaItem;

    tr.querySelector('.v_grav').value     = vGrav.toFixed(2);
    tr.querySelector('.iva_item').value   = ivaItem.toFixed(2);
    tr.querySelector('.total_civa').value = totalCIva.toFixed(2);

    grav     += vGrav;
    desc     += d;
    ivaTotal += ivaItem;
  });

  const ivaRet   = Math.max(0, parseFloat(document.getElementById('iva_retenido').value)   || 0);
  const retRenta = Math.max(0, parseFloat(document.getElementById('retencion_renta').value) || 0);

  const subtotal = grav + exen + nosuj;
  const total    = subtotal + ivaTotal - ivaRet - retRenta;

  const set = (id, v) => { const el = document.getElementById(id); if (el) el.textContent = fmt(v); };
  set('t_nosujeta',   nosuj);
  set('t_exenta',     exen);
  set('t_gravada',    grav);
  set('t_desc_global', desc);
  set('t_subtotal',   subtotal);
  set('t_iva',        ivaTotal);

  document.getElementById('t_retenciones').textContent   = `$${fmt(ivaRet)} / $${fmt(retRenta)}`;
  document.getElementById('t_total').textContent          = '$ ' + fmt(total);
  document.getElementById('t_letras').textContent         = 'SON: ' + numLetras(total);
  document.getElementById('t_credito_fiscal').textContent = '$' + fmt(ivaTotal);

  const hset = (id, v) => { const el = document.getElementById(id); if (el) el.value = fmt(v); };
  hset('h_nosujeta',          nosuj);
  hset('h_exenta',            exen);
  hset('h_gravada',           grav);
  hset('h_desc_global',       desc);
  hset('h_subtotal',          subtotal);
  hset('h_iva',               ivaTotal);
  hset('h_iva_retenido_h',    ivaRet);
  hset('h_retencion_renta_h', retRenta);
  hset('h_total',             total);
}

document.getElementById('formCCF').addEventListener('input', calcTotales);
calcTotales();

/* ================================================================
   NÚMERO A LETRAS
   ================================================================ */
function numLetras(num) {
  const U = ['','UNO','DOS','TRES','CUATRO','CINCO','SEIS','SIETE','OCHO','NUEVE',
             'DIEZ','ONCE','DOCE','TRECE','CATORCE','QUINCE','DIECISÉIS',
             'DIECISIETE','DIECIOCHO','DIECINUEVE'];
  const D = ['','','VEINTE','TREINTA','CUARENTA','CINCUENTA',
             'SESENTA','SETENTA','OCHENTA','NOVENTA'];
  const C = ['','CIENTO','DOSCIENTOS','TRESCIENTOS','CUATROCIENTOS','QUINIENTOS',
             'SEISCIENTOS','SETECIENTOS','OCHOCIENTOS','NOVECIENTOS'];

  function g(n) {
    let s = '';
    if (n >= 100) { s += (n === 100 ? 'CIEN' : C[Math.floor(n/100)]) + ' '; n %= 100; }
    if (n >= 20)  { s += D[Math.floor(n/10)]; if (n%10) s += ' Y ' + U[n%10]; }
    else if (n > 0) s += U[n];
    return s.trim();
  }

  if (num === 0) return 'CERO 00/100 DÓLARES';
  const ent = Math.floor(num), cts = Math.round((num - ent) * 100);
  let l = '';
  if (ent >= 1000) l += g(Math.floor(ent / 1000)) + ' MIL ';
  l += g(ent % 1000);
  return l.trim() + ' ' + String(cts).padStart(2, '0') + '/100 DÓLARES';
}

/* ================================================================
   MÁSCARAS
   ================================================================ */
const reNIT   = /^\d{4}-\d{6}-\d{3}-\d$/;
const reNRC   = /^\d{1,8}$/;
const reEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
const reTel   = /^\d{4}-\d{4}$/;

function mascNIT(v) {
  v = v.replace(/\D/g, '').slice(0, 14);
  if      (v.length > 13) v = v.slice(0,4)+'-'+v.slice(4,10)+'-'+v.slice(10,13)+'-'+v.slice(13);
  else if (v.length > 10) v = v.slice(0,4)+'-'+v.slice(4,10)+'-'+v.slice(10);
  else if (v.length > 4)  v = v.slice(0,4)+'-'+v.slice(4);
  return v;
}
function mascTel(v) {
  v = v.replace(/\D/g, '').slice(0, 8);
  return v.length > 4 ? v.slice(0,4)+'-'+v.slice(4) : v;
}

document.getElementById('receptor_nit').addEventListener('input', function () {
  this.value = mascNIT(this.value);
});
document.getElementById('receptor_nrc').addEventListener('input', function () {
  this.value = this.value.replace(/\D/g, '').slice(0, 8);
});
document.getElementById('receptor_tel').addEventListener('input', function () {
  this.value = mascTel(this.value);
});
document.getElementById('condicion_pago').addEventListener('change', function () {
  document.getElementById('bloque_plazo').style.display =
    this.value === '02' ? 'block' : 'none';
});

/* ================================================================
   VALIDACIONES EN TIEMPO REAL (blur)
   ================================================================ */
function markErr(id, errId, show) {
  document.getElementById(id)?.classList.toggle('is-invalid', show);
  document.getElementById(errId)?.classList.toggle('visible', show);
}

document.getElementById('receptor_nombre').addEventListener('blur', function () {
  markErr('receptor_nombre', 'err_nombre', this.value.trim().length < 2);
});
document.getElementById('receptor_nit').addEventListener('blur', function () {
  markErr('receptor_nit', 'err_nit', !reNIT.test(this.value.trim()));
});
document.getElementById('receptor_nrc').addEventListener('blur', function () {
  markErr('receptor_nrc', 'err_nrc', !reNRC.test(this.value.trim()));
});
document.getElementById('receptor_email').addEventListener('blur', function () {
  markErr('receptor_email', 'err_email', !reEmail.test(this.value.trim()));
});
document.getElementById('receptor_tel').addEventListener('blur', function () {
  const v = this.value.trim();
  if (v) markErr('receptor_tel', 'err_tel', !reTel.test(v));
});
document.getElementById('departamento').addEventListener('change', function () {
  markErr('departamento', 'err_depto', !this.value);
});
document.getElementById('municipio').addEventListener('change', function () {
  markErr('municipio', 'err_muni', !this.value);
});
document.getElementById('receptor_desc_actividad').addEventListener('blur', function () {
  markErr('receptor_desc_actividad', 'err_desc_actividad', this.value.trim().length < 2);
});
document.getElementById('receptor_direccion').addEventListener('blur', function () {
  markErr('receptor_direccion', 'err_direccion', this.value.trim().length < 5);
});

/* ================================================================
   VALIDACIÓN COMPLETA AL ENVIAR
   ================================================================ */
function validarFormulario() {
  let ok = true;

  const nombre = document.getElementById('receptor_nombre').value.trim();
  markErr('receptor_nombre', 'err_nombre', nombre.length < 2);
  if (nombre.length < 2) ok = false;

  const nit = document.getElementById('receptor_nit').value.trim();
  markErr('receptor_nit', 'err_nit', !reNIT.test(nit));
  if (!reNIT.test(nit)) ok = false;

  const nrc = document.getElementById('receptor_nrc').value.trim();
  markErr('receptor_nrc', 'err_nrc', !reNRC.test(nrc));
  if (!reNRC.test(nrc)) ok = false;

  const email = document.getElementById('receptor_email').value.trim();
  markErr('receptor_email', 'err_email', !reEmail.test(email));
  if (!reEmail.test(email)) ok = false;

  const tel = document.getElementById('receptor_tel').value.trim();
  if (tel && !reTel.test(tel)) { markErr('receptor_tel', 'err_tel', true); ok = false; }

  const depto = document.getElementById('departamento').value;
  markErr('departamento', 'err_depto', !depto);
  if (!depto) ok = false;

  const muni = document.getElementById('municipio').value;
  markErr('municipio', 'err_muni', !muni);
  if (!muni) ok = false;

  const dir = document.getElementById('receptor_direccion').value.trim();
  markErr('receptor_direccion', 'err_direccion', dir.length < 5);
  if (dir.length < 5) ok = false;

  const act = document.getElementById('receptor_cod_actividad').value;
  markErr('receptor_cod_actividad', 'err_actividad', !act);
  if (!act) ok = false;

  const descAct = document.getElementById('receptor_desc_actividad').value.trim();
  markErr('receptor_desc_actividad', 'err_desc_actividad', descAct.length < 2);
  if (descAct.length < 2) ok = false;

  let itemsOk = true;
  document.querySelectorAll('#tablaItems tbody tr').forEach(tr => {
    const d     = tr.querySelector('.prod-select');
    const valid = d && d.value !== '';
    if (d) d.classList.toggle('is-invalid', !valid);
    if (!valid) itemsOk = false;
  });
  if (!itemsOk) { toast('Debe seleccionar un producto válido para todos los ítems.'); ok = false; }

  const total = parseFloat(document.getElementById('h_total').value) || 0;
  if (total <= 0) { toast('El total a pagar debe ser mayor a $0.00'); ok = false; }

  if (!ok) toast('Corrija los errores marcados antes de continuar.');
  return ok;
}

/* ================================================================
   ACCIONES
   ================================================================ */
function confirmarCancelar() {
  if (confirm('¿Cancelar operación? Se perderán los datos ingresados.'))
    location.href = 'index.php';
}

function previsualizarDTE() {
  if (!validarFormulario()) return;
  toast('Vista previa en desarrollo. Use "GENERAR CCF Y PDF" para continuar.', 'success');
}
</script>
</body>
</html>