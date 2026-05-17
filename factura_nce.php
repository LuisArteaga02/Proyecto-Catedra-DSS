<?php
/* ============================================================
   factura_nce.php — Nota de Crédito Electrónica (DTE Tipo 05)
   Pizzeria El Salvador — Sistema de Facturación Electrónica

   La NCE siempre referencia un DTE original (FE o CCF) ya
   aceptado por Hacienda. Los ítems representan ajustes
   (valores negativos en el JSON final).
   ============================================================ */

session_start();
require_once 'class/Database.php';

if (!isset($_SESSION['usuario_nombre'])) {
    header("Location: login.php");
    exit();
}

$pagina_activa = 'NCE';

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
  <title>DTE — Nota de Crédito Electrónica</title>
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

      <div class="nce-container">

        <!-- ===== BANNER SUPERIOR ===== -->
        <div class="fe-header-banner">
          <div class="fe-header-left">
            <span class="badge-nce">NCE</span>
            <div>
              <div class="fe-title">NOTA DE CRÉDITO ELECTRÓNICA (DTE TIPO 05)</div>
              <div style="font-size:10px;color:#f0a0a0;margin-top:2px;">Ministerio de Hacienda — El Salvador</div>
            </div>
          </div>
          <div class="fe-header-right">
            <div style="font-weight:700;"><?= htmlspecialchars($_SESSION['empresa_nombre'] ?? 'EMPRESA S.A. DE C.V.') ?></div>
            <div>NRC: <strong style="color:#e8b800;"><?= htmlspecialchars($_SESSION['empresa_nrc'] ?? '') ?></strong></div>
            <span class="fe-code">MODELO: FACTURACIÓN PREVIA</span>
          </div>
        </div>

        <!-- ===== AVISO INFORMATIVO NCE ===== -->
        <div class="nce-aviso">
          La NCE siempre referencia un <strong>DTE original (FE o CCF) ya aceptado por Hacienda</strong>.
          No puede existir sin un documento relacionado. El plazo máximo para emitirla es
          <strong>90 días</strong> desde la fecha de autorización.
          Los ítems representan lo que se está <strong>devolviendo o ajustando</strong>,
          no los ítems originales completos.
        </div>

        <form id="formNCE" action="procesar_nce.php" method="POST" novalidate>

          <!-- ===== METADATOS DTE ===== -->
          <div class="metadata-strip-nce">
            <div class="fe-group">
              <label>CÓDIGO DE GENERACIÓN <span class="req">*</span></label>
              <input type="text" name="codigo_generacion" id="codigo_generacion"
                     readonly placeholder="Se generará automáticamente" style="font-size:10px;">
            </div>
            <div class="fe-group">
              <label>NÚMERO DE CONTROL <span class="req">*</span></label>
              <input type="text" name="numero_control" id="numero_control"
                     readonly placeholder="Generado al guardar (Ej: DTE-05...)">
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


          <!-- ===== SECCIÓN 1: DOCUMENTO RELACIONADO ===== -->
          <section class="fe-section">
            <h3 class="section-title">
              <span class="num">1</span> Documento Relacionado — DTE que se está corrigiendo
              <span style="font-size:9px;font-weight:500;color:var(--text-muted);
                           text-transform:none;margin-left:8px;">Sección 2 ANEXO II — campos #13 al #16</span>
            </h3>

            <div class="grid-3" style="margin-bottom:12px;">
              <div class="fe-group span2">
                <label>BUSCAR DTE POR NÚMERO DE CONTROL O UUID <span class="req">*</span></label>
                <div class="dte-buscar-row">
                  <input type="text" id="dte_buscar_input" name="dte_buscar_input"
                         placeholder="DTE-01-00000001-000000000000001 o UUID completo"
                         maxlength="40">
                  <button type="button" class="btn-buscar-dte" onclick="buscarDTE()">Buscar DTE</button>
                  <button type="button" class="btn-confirmar-dte" id="btnConfirmar"
                          onclick="confirmarDTE()" disabled>Confirmar documento</button>
                </div>
                <span class="error-msg" id="err_dte_rel">Debe buscar y confirmar el DTE original antes de continuar.</span>
              </div>
              
            </div>

            <!-- Card del DTE encontrado (oculta hasta buscar) -->
            <div class="dte-card" id="dteCard">
              <div class="dte-card-header">
                <div style="display:flex;align-items:center;gap:10px;">
                  <span class="dte-card-badge fe" id="dteCardBadge">FE-01</span>
                  <span class="dte-card-receptor" id="dteCardReceptor">—</span>
                </div>
                <div style="display:flex;align-items:center;gap:8px;">
                  <span class="dte-card-plazo ok" id="dteCardPlazo">—</span>
                  <button type="button" class="btn-cambiar-dte" onclick="resetDTE()">Cambiar DTE</button>
                </div>
              </div>
              <div class="dte-card-body">
                <div class="dte-card-field">
                  <label>N° CONTROL</label>
                  <span id="dteCardControl">—</span>
                </div>
                <div class="dte-card-field">
                  <label>UUID</label>
                  <span id="dteCardUUID" title="">—</span>
                </div>
                <div class="dte-card-field">
                  <label>FECHA EMISIÓN</label>
                  <span id="dteCardFecha">—</span>
                </div>
                <div class="dte-card-field">
                  <label>TOTAL ORIGINAL</label>
                  <span id="dteCardTotal">—</span>
                </div>
                <div class="dte-card-field">
                  <label>SELLO MH</label>
                  <span class="sello-link" id="dteCardSello">—</span>
                </div>
                <div class="dte-card-field">
                  <label>TIPO GENERACIÓN</label>
                  <span id="dteCardTipoGen">1 — Electrónico</span>
                </div>
                <div class="dte-card-field">
                  <label>RECEPTOR</label>
                  <span id="dteCardDocReceptor">—</span>
                </div>
                <div class="dte-card-field">
                  <label>ESTADO MH</label>
                  <span class="estado-ok" id="dteCardEstado">—</span>
                </div>
              </div>
            </div>

            <!-- Campos ocultos que se llenan al confirmar el DTE -->
            <input type="hidden" name="id_factura_original"    id="id_factura_original">
            <input type="hidden" name="tipo_dte_original"      id="tipo_dte_original">
            <input type="hidden" name="codigo_gen_original"    id="codigo_gen_original">
            <input type="hidden" name="numero_control_original" id="numero_control_original">
            <input type="hidden" name="fecha_emision_original" id="fecha_emision_original">
            <input type="hidden" name="id_receptor_original"   id="id_receptor_original">
          </section>


          <!-- ===== SECCIÓN 2: TIPO Y MOTIVO DEL AJUSTE ===== -->
          <section class="fe-section">
            <h3 class="section-title">
              <span class="num">2</span> Tipo y Motivo del Ajuste
              <span style="font-size:9px;font-weight:500;color:var(--text-muted);
                           text-transform:none;margin-left:8px;">Sección 8 ANEXO II — resumen.observaciones</span>
            </h3>

            <div class="tipo-ajuste-pills">
              <button type="button" class="pill-ajuste active"
                      onclick="setTipoAjuste('devolucion_parcial', this)">Devolución parcial</button>
              <button type="button" class="pill-ajuste"
                      onclick="setTipoAjuste('devolucion_total', this)">Devolución total</button>
              <button type="button" class="pill-ajuste"
                      onclick="setTipoAjuste('correccion_precio', this)">Corrección de precio</button>
              <button type="button" class="pill-ajuste"
                      onclick="setTipoAjuste('descuento_posterior', this)">Descuento posterior</button>
            </div>
            <input type="hidden" name="tipo_ajuste" id="tipo_ajuste" value="devolucion_parcial">

            <div class="fe-group">
              <label>DESCRIPCIÓN DEL MOTIVO <span class="req">*</span></label>
              <textarea name="descripcion_motivo" id="descripcion_motivo"
                        rows="3" maxlength="500" required
                        placeholder="Sea específico. Hacienda puede auditar este campo."></textarea>
              <span style="font-size:9px;color:var(--text-muted);">Máx. 500 caracteres — sea específico.</span>
              <span class="error-msg" id="err_motivo">Ingrese la descripción del motivo del ajuste.</span>
            </div>
          </section>


          <!-- ===== SECCIÓN 3: ÍTEMS A DEVOLVER / AJUSTAR ===== -->
          <section class="ncc-section">
            <h3 class="section-title">
              <span class="num">3</span> Ítems a Devolver / Ajustar
            </h3>
            <p class="nce-tabla-nota">
              Los valores son negativos — representan el ajuste, no el total original.
              Ingrese el precio unitario del DTE original y la cantidad a devolver.
            </p>
            <div class="table-wrap">
              <table class="items-table" id="tablaItems">
                <thead>
                  <tr>
                    <th style="width:30px;">N°</th>
                    <th style="width:75px;">CÓDIGO</th>
                    <th style="min-width:160px;">DESCRIPCIÓN DEL AJUSTE</th>
                    <th style="width:55px;">CANT.</th>
                    <th style="width:90px;">PRECIO UNIT.</th>
                    <th style="width:80px;">DESCUENTO</th>
                    <th style="width:90px;">V.AJUSTE</th>
                    <th style="width:80px;">IVA</th>
                    <th style="width:28px;"></th>
                  </tr>
                </thead>
                <tbody></tbody>
              </table>
              <button type="button" class="btn-add" id="addItem">+ Agregar ítem al ajuste</button>
            </div>
          </section>


          <!-- ===== SECCIÓN 4: RESUMEN DE LA NOTA DE CRÉDITO ===== -->
          <section class="fe-section no-border">
            <h3 class="section-title">
              <span class="num">4</span> Resumen de la Nota de Crédito
              <span style="font-size:9px;font-weight:500;color:var(--text-muted);
                           text-transform:none;margin-left:8px;">Los valores van en negativo al JSON de la NCE</span>
            </h3>

            <div class="footer-grid">

              <!-- Col izquierda: forma de devolución y observaciones -->
              <div>
                <div class="forma-devolucion-box">
                  <label>FORMA DE DEVOLUCIÓN <span class="req">*</span>
                    <span style="font-size:9px;font-weight:400;color:var(--text-muted);
                                 text-transform:none;">— Cómo se devuelve el dinero</span>
                  </label>
                  <select name="forma_devolucion" id="forma_devolucion">
                    <option value="01">01 — Efectivo</option>
                    <option value="02">02 — Tarjeta Débito</option>
                    <option value="03">03 — Tarjeta Crédito</option>
                    <option value="04">04 — Cheque</option>
                    <option value="05">05 — Transferencia Crédito</option>
                    <option value="06">06 — Transferencia Débito</option>
                    <option value="07">07 — Nota de Crédito</option>
                    <option value="08">08 — Dinero Electrónico</option>
                    <option value="99">99 — Otro</option>
                  </select>
                </div>

                <div class="fe-group" style="margin-bottom:12px;">
                  <label>OBSERVACIONES ADICIONALES</label>
                  <textarea name="observaciones" rows="3" maxlength="3000"
                            placeholder="Notas adicionales para el receptor o Hacienda..."></textarea>
                </div>

                <p class="nce-nota-legal">
                  ⚖ Los valores de la NCE reducen el débito fiscal del emisor y el crédito
                  fiscal del receptor en sus declaraciones de IVA.
                </p>
              </div>

              <!-- Col derecha: cuadro de totales -->
              <div>
                <div class="totales-box">
                  <div class="totales-title nce">📊 RESUMEN DE LA NOTA DE CRÉDITO</div>

                  <div class="total-row nce-negativo">
                    <span>Ajuste en ventas gravadas:</span>
                    <span id="t_gravada">-0.00</span>
                  </div>
                  <div class="total-row nce-negativo separator">
                    <span>Subtotal del ajuste:</span>
                    <span id="t_subtotal">-0.00</span>
                  </div>
                  <div class="total-row nce-negativo">
                    <span>IVA ajustado (13%):</span>
                    <span id="t_iva">-0.00</span>
                  </div>

                  <div class="total-row highlight nce-total-devolver">
                    <strong>TOTAL A DEVOLVER:</strong>
                    <strong id="t_total" class="val-total">-$0.00</strong>
                  </div>
                  <p class="total-letras" id="t_letras">SON: CERO 00/100 DÓLARES</p>
                </div>

                <!-- Campos ocultos POST -->
                <input type="hidden" name="h_gravada"   id="h_gravada">
                <input type="hidden" name="h_subtotal"  id="h_subtotal">
                <input type="hidden" name="h_iva"       id="h_iva">
                <input type="hidden" name="h_total"     id="h_total">
              </div>

            </div><!-- /footer-grid -->
          </section>


          <!-- ===== ACCIONES ===== -->
          <div class="form-actions">
            <button type="button" class="btn-cancel"
                    onclick="confirmarCancelar()">Cancelar Operación</button>
            <button type="button" class="btn-preview"
                    onclick="previsualizarDTE()">👁 Vista Previa</button>
            <button type="submit" class="btn-save"
                    onclick="return validarFormulario()">📤 EMITIR NOTA DE CRÉDITO</button>
          </div>

        </form>
      </div><!-- /nce-container -->
    </main>
  </div>
</div>

<div id="toast-nce"></div>

<script>
  /* ================================================================
   CATÁLOGOS
   ================================================================ */
const PRODUCTOS_DB  = <?= json_encode($productos_db) ?>;
const MUNICIPIOS_DB = <?= json_encode($municipios_db) ?>;

/* ================================================================
   HELPERS
   ================================================================ */
function fmt(n)    { return Math.abs(parseFloat(n || 0)).toFixed(2); }
function fmtNeg(n) { return '-' + fmt(n); }
function isNum(evt) {
  const c = evt.which || evt.keyCode;
  return !(c === 45 || c === 101 || c === 69);
}

/* ================================================================
   TOAST
   ================================================================ */
function toast(msg, tipo = 'error') {
  const t = document.getElementById('toast-nce');
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

  document.getElementById('numero_control').placeholder =
    'Generado al guardar (Ej: DTE-05-00000001-000000000000001)';
})();

/* ================================================================
   TIPO DE AJUSTE — PILLS
   ================================================================ */
function setTipoAjuste(tipo, btn) {
  document.querySelectorAll('.pill-ajuste').forEach(p => p.classList.remove('active'));
  btn.classList.add('active');
  document.getElementById('tipo_ajuste').value = tipo;
}

/* ================================================================
   BÚSQUEDA DEL DTE ORIGINAL (AJAX)
   ================================================================ */
let dteConfirmado = false;

function buscarDTE() {
  const q = document.getElementById('dte_buscar_input').value.trim();
  if (!q) { toast('Ingrese el número de control o UUID del DTE original.'); return; }

  fetch(`buscar_dte.php?q=${encodeURIComponent(q)}`)
    .then(r => r.json())
    .then(data => {
      if (!data.ok) { toast(data.msg || 'DTE no encontrado.'); return; }

      const fechaEmision = new Date(data.fecha_emision);
      const hoy          = new Date();
      const dias         = Math.floor((hoy - fechaEmision) / 86400000);
      const dentroPlazo  = dias <= 90;

      const badge = document.getElementById('dteCardBadge');
      badge.textContent = data.tipo_dte === '01' ? 'FE-01' : 'CCF-03';
      badge.className   = 'dte-card-badge ' + (data.tipo_dte === '01' ? 'fe' : 'ccf');

      document.getElementById('dteCardReceptor').textContent  = data.receptor_nombre || '—';
      document.getElementById('dteCardControl').textContent   = data.numero_control;
      document.getElementById('dteCardUUID').textContent      = data.codigo_generacion.substring(0,18) + '…';
      document.getElementById('dteCardUUID').title            = data.codigo_generacion;
      document.getElementById('dteCardFecha').textContent     = data.fecha_emision;
      document.getElementById('dteCardTotal').textContent     = '$' + parseFloat(data.monto_total).toFixed(2);
      document.getElementById('dteCardSello').textContent     = data.sello_recibido ? data.sello_recibido.substring(0,14) + '…' : '(pendiente)';
      document.getElementById('dteCardDocReceptor').textContent = (data.tipo_documento || 'DUI') + ': ' + (data.num_documento || '—');
      
      document.getElementById('dteCardEstado').textContent    = data.estado_mh;
      document.getElementById('dteCardEstado').className      = data.estado_mh === 'PROCESADO' || data.estado_mh === 'ACEPTADO' ? 'estado-ok' : 'estado-pending';

      const plazoEl = document.getElementById('dteCardPlazo');
      plazoEl.textContent = dentroPlazo ? `Dentro del plazo (${dias} días de 90)` : `⚠ Fuera de plazo (${dias} días)`;
      plazoEl.className = 'dte-card-plazo ' + (dentroPlazo ? 'ok' : 'vencido');

      document.getElementById('dteCard').classList.add('visible');
      document.getElementById('btnConfirmar').disabled = false;

      window._dteEncontrado = data;
    })
    .catch(() => toast('Error al consultar el DTE. Verifique la conexión.'));
}

function confirmarDTE() {
  const d = window._dteEncontrado;
  if (!d) return;

  document.getElementById('id_factura_original').value    = d.id_factura;
  document.getElementById('tipo_dte_original').value      = d.tipo_dte;
  document.getElementById('codigo_gen_original').value    = d.codigo_generacion;
  document.getElementById('numero_control_original').value = d.numero_control;
  document.getElementById('fecha_emision_original').value = d.fecha_emision;
  document.getElementById('id_receptor_original').value   = d.id_receptor || '';

  dteConfirmado = true;
  document.getElementById('btnConfirmar').textContent  = '✓ Documento confirmado';
  document.getElementById('btnConfirmar').disabled     = true;
  document.getElementById('dte_buscar_input').disabled = true;
  document.querySelector('.btn-buscar-dte').disabled   = true;

  markErr('dte_buscar_input', 'err_dte_rel', false);
  
  // Limpiamos la tabla y generamos una fila vacía nueva.
  // Como el DTE ya está confirmado, el ComboBox se llenará con los ítems de ese DTE.
  const tbody = document.querySelector('#tablaItems tbody');
  tbody.innerHTML = '';
  addItem();
  
  toast('DTE confirmado. Seleccione los ítems a ajustar en la tabla.', 'success');
}

function resetDTE() {
  dteConfirmado = false;
  window._dteEncontrado = null;

  document.getElementById('dteCard').classList.remove('visible');
  document.getElementById('btnConfirmar').textContent  = 'Confirmar documento';
  document.getElementById('btnConfirmar').disabled     = true;
  document.getElementById('dte_buscar_input').disabled = false;
  document.getElementById('dte_buscar_input').value   = '';
  document.querySelector('.btn-buscar-dte').disabled   = false;

  ['id_factura_original','tipo_dte_original','codigo_gen_original',
   'numero_control_original','fecha_emision_original','id_receptor_original']
    .forEach(id => document.getElementById(id).value = '');

  const tbody = document.querySelector('#tablaItems tbody');
  tbody.innerHTML = '';
  addItem();
  calcTotales();
}

document.getElementById('dte_buscar_input').addEventListener('keydown', function(e) {
  if (e.key === 'Enter') { e.preventDefault(); buscarDTE(); }
});

/* ================================================================
   TABLA DE ÍTEMS NCE — CON COMBOBOX
   ================================================================ */

// Genera los <options> extrayéndolos directamente del DTE encontrado
function getItemsDTEOptions() {
  if (!window._dteEncontrado || !window._dteEncontrado.items) {
    return '<option value="">-- Confirme el DTE primero --</option>';
  }
  let html = '<option value="">-- Seleccione un ítem del DTE original --</option>';
  window._dteEncontrado.items.forEach((item) => {
    html += `<option value="${item.id_producto}"
             data-codigo="${item.codigo_mh}"
             data-nombre="${item.product_name}"
             data-precio="${item.precio_unitario}"
             data-cant="${item.cantidad}"
             data-desc="${item.descuento}">
             ${item.codigo_mh} — ${item.product_name} (Máx: ${item.cantidad})
             </option>`;
  });
  return html;
}

function filaItem(n) {
  return `
<tr>
  <td class="td-num">${n}</td>
  <td>
    <input type="text" name="codigo_item[]" class="in-table cod-item" readonly placeholder="---">
  </td>
  <td>
    <select name="id_producto[]" class="in-table desc-ajuste-select" onchange="seleccionarItemDTE(this)" required>
      ${getItemsDTEOptions()}
    </select>
    <input type="hidden" name="desc_ajuste[]" class="desc-ajuste-hidden">
  </td>
  <td>
    <input type="number" name="cant[]" class="in-table qty"
           min="0.01" step="0.01" value="0.00"
           onkeypress="return isNum(event)" oninput="calcTotales()">
  </td>
  <td>
    <input type="number" name="precio_unit[]" class="in-table price"
           step="0.000001" min="0" value="0.00" readonly>
  </td>
  <td>
    <input type="number" name="descuento_item[]" class="in-table desc_item"
           step="0.01" value="0.00" onkeypress="return isNum(event)" oninput="calcTotales()">
  </td>
  <td class="val-negativo">
    <input type="number" name="v_ajuste[]" class="in-table v_ajuste"
           step="0.01" value="0.00" readonly>
  </td>
  <td class="val-negativo">
    <input type="number" name="iva_ajuste[]" class="in-table iva_ajuste"
           step="0.01" value="0.00" readonly>
  </td>
  <td>
    <button type="button" class="btn-del" onclick="delItem(this)">×</button>
  </td>
</tr>`;
}

function seleccionarItemDTE(selectEl) {
  const tr = selectEl.closest('tr');
  const option = selectEl.options[selectEl.selectedIndex];

  const codEl      = tr.querySelector('.cod-item');
  const descHidden = tr.querySelector('.desc-ajuste-hidden');
  const qtyEl      = tr.querySelector('.qty');
  const priceEl    = tr.querySelector('.price');
  const descItemEl = tr.querySelector('.desc_item');

  // Si selecciona un producto válido, rellenamos las cajas automáticamente
  if (option.value) {
    codEl.value      = option.dataset.codigo;
    descHidden.value = 'Devolución/Ajuste: ' + option.dataset.nombre;
    priceEl.value    = parseFloat(option.dataset.precio).toFixed(2);
    qtyEl.value      = option.dataset.cant; // Sugerimos devolver toda la cantidad
    qtyEl.max        = option.dataset.cant; // Límite para que no devuelva más de lo original
    descItemEl.value = option.dataset.desc;
  } else {
    codEl.value      = '';
    descHidden.value = '';
    priceEl.value    = '0.00';
    qtyEl.value      = '0.00';
    descItemEl.value = '0.00';
  }
  calcTotales();
}

function addItem() {
  const tbody = document.querySelector('#tablaItems tbody');
  tbody.insertAdjacentHTML('beforeend', filaItem(tbody.rows.length + 1));
}

function delItem(btn) {
  const tbody = document.querySelector('#tablaItems tbody');
  if (tbody.rows.length <= 1) { toast('Debe haber al menos un ítem de ajuste.'); return; }
  btn.closest('tr').remove();
  [...tbody.rows].forEach((tr, i) =>
    tr.querySelector('.td-num').textContent = i + 1
  );
  calcTotales();
}

document.getElementById('addItem').addEventListener('click', addItem);
addItem(); 

/* ================================================================
   CÁLCULO DE TOTALES
   ================================================================ */
function calcTotales() {
  let grav = 0, iva = 0;

  document.querySelectorAll('#tablaItems tbody tr').forEach(tr => {
    const q = Math.max(0, parseFloat(tr.querySelector('.qty')?.value)       || 0);
    const p = Math.max(0, parseFloat(tr.querySelector('.price')?.value)     || 0);
    const d = Math.max(0, parseFloat(tr.querySelector('.desc_item')?.value) || 0);

    const vAjuste   = Math.max(0, (q * p) - d);
    const ivaAjuste = (vAjuste / 1.13) * 0.13;

    tr.querySelector('.v_ajuste').value   = vAjuste.toFixed(2);
    tr.querySelector('.iva_ajuste').value = ivaAjuste.toFixed(2);

    grav += vAjuste;
    iva  += ivaAjuste;
  });

  const subtotal = grav;
  const total    = grav; 

  document.getElementById('t_gravada').textContent  = fmtNeg(grav);
  document.getElementById('t_subtotal').textContent = fmtNeg(subtotal);
  document.getElementById('t_iva').textContent      = fmtNeg(iva);
  document.getElementById('t_total').textContent    = '-$' + fmt(total);
  document.getElementById('t_letras').textContent   = 'SON: ' + numLetras(total);

  document.getElementById('h_gravada').value  = fmt(grav);
  document.getElementById('h_subtotal').value = fmt(subtotal);
  document.getElementById('h_iva').value      = fmt(iva);
  document.getElementById('h_total').value    = fmt(total);
}

document.getElementById('formNCE').addEventListener('input', calcTotales);
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
   VALIDACIONES
   ================================================================ */
function markErr(id, errId, show) {
  document.getElementById(id)?.classList.toggle('is-invalid', show);
  document.getElementById(errId)?.classList.toggle('visible', show);
}

document.getElementById('descripcion_motivo').addEventListener('blur', function () {
  markErr('descripcion_motivo', 'err_motivo', this.value.trim().length < 5);
});

function validarFormulario() {
  let ok = true;

  if (!dteConfirmado || !document.getElementById('id_factura_original').value) {
    markErr('dte_buscar_input', 'err_dte_rel', true);
    toast('Debe buscar y confirmar el DTE original antes de emitir la NCE.');
    ok = false;
  }

  const motivo = document.getElementById('descripcion_motivo').value.trim();
  markErr('descripcion_motivo', 'err_motivo', motivo.length < 5);
  if (motivo.length < 5) ok = false;

  let itemsOk = true;
  document.querySelectorAll('#tablaItems tbody tr').forEach(tr => {
    const select = tr.querySelector('.desc-ajuste-select');
    const price  = tr.querySelector('.price');
    const qty    = tr.querySelector('.qty');
    
    const validSelect = select && select.value !== '';
    const validPrice  = price && parseFloat(price.value) >= 0;
    const validQty    = qty   && parseFloat(qty.value)   > 0;
    
    if (select) select.classList.toggle('is-invalid', !validSelect);
    
    if (!validSelect || !validPrice || !validQty) itemsOk = false;
  });
  if (!itemsOk) {
    toast('Verifique que todos los ítems tengan un producto seleccionado y cantidad válida.');
    ok = false;
  }

  const total = parseFloat(document.getElementById('h_total').value) || 0;
  if (total <= 0) { toast('El total a devolver debe ser mayor a $0.00'); ok = false; }

  if (!ok) toast('Corrija los errores marcados antes de continuar.');
  return ok;
}

function confirmarCancelar() {
  if (confirm('¿Cancelar operación? Se perderán los datos ingresados.'))
    location.href = 'index.php';
}

function previsualizarDTE() {
  if (!validarFormulario()) return;
  toast('Vista previa en desarrollo. Use "EMITIR NOTA DE CRÉDITO" para continuar.', 'success');
}
</script>
</body>
</html>