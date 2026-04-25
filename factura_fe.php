<?php
session_start();
if (!isset($_SESSION['usuario_nombre'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DTE — Factura Consumidor Final</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/formularios.css">
</head>
<body>

<div class="layout">
    <?php include 'navegacion.php'; ?>

    <div class="main-content">
        <header class="topbar">
            <h1> Emisión de Documento Tributario Electrónico</h1>
            <span class="status-badge"> </span>
        </header>

        <main class="page-content">
            <div class="fe-container">

                <!-- BANNER SUPERIOR -->
                <div class="fe-header-banner">
                    <div class="fe-header-left">
                        <span class="badge-fe">FE</span>
                        <div>
                            <div class="fe-title">FACTURA DE CONSUMIDOR FINAL (DTE TIPO 01)</div>
                            <div style="font-size:10px;color:#aaa;margin-top:2px;">Ministerio de Hacienda — El Salvador</div>
                        </div>
                    </div>
                    <div class="fe-header-right">
                        <div style="font-weight:700;"><?= htmlspecialchars($_SESSION['empresa_nombre'] ?? 'EMPRESA S.A. DE C.V.') ?></div>
                        <div>NRC: <strong style="color:#e8b800;"><?= htmlspecialchars($_SESSION['empresa_nrc'] ?? '') ?></strong></div>
                        <span class="fe-code">MODELO: FACTURACIÓN PREVIA</span>
                    </div>
                </div>

                <form id="formFactura" action="procesar_factura.php" method="POST" novalidate>

                    <!-- ===== METADATOS DTE ===== -->
                    <div class="metadata-strip">
                        <div class="fe-group">
                            <label>CÓDIGO DE GENERACIÓN <span class="req">*</span></label>
                            <input type="text" name="codigo_generacion" id="codigo_generacion" readonly placeholder="Se generará automáticamente" style="font-size:10px;">
                        </div>
                        <div class="fe-group">
                            <label>NÚMERO DE CONTROL <span class="req">*</span></label>
                            <input type="text" name="numero_control" id="numero_control" readonly placeholder="DTE-01-00000001-000000000000001" style="font-size:10px;">
                        </div>
                        <div class="fe-group">
                            <label>SELLO DE RECEPCIÓN</label>
                            <input type="text" name="sello_recepcion" readonly placeholder="Asignado por MH tras transmisión">
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

                    <!-- ===== SECCIÓN 1: RECEPTOR ===== -->
                    <section class="fe-section">
                        <h3 class="section-title"><span class="num">1</span> Información del Receptor</h3>
                        <div class="grid-3">
                            <div class="fe-group full">
                                <label>NOMBRE O RAZÓN SOCIAL <span class="req">*</span></label>
                                <input type="text" name="cliente_nombre" id="cliente_nombre" required maxlength="250" placeholder="Nombre completo o razón social del receptor">
                                <span class="error-msg" id="err_nombre">Ingrese el nombre del receptor.</span>
                            </div>
                            <div class="fe-group">
                                <label>TIPO DOCUMENTO IDENTIFICACIÓN <span class="req">*</span></label>
                                <select name="tipo_doc" id="tipo_doc">
                                    <option value="13">13 — DUI</option>
                                    <option value="02">02 — NIT</option>
                                    <option value="03">03 — Pasaporte</option>
                                    <option value="04">04 — Carné de Residente</option>
                                    <option value="36">36 — Sin documento</option>
                                </select>
                            </div>
                            <div class="fe-group">
                                <label>NÚMERO DE DOCUMENTO <span class="req">*</span></label>
                                <input type="text" name="cliente_doc" id="doc_identidad" required maxlength="20" placeholder="00000000-0">
                                <span class="error-msg" id="err_doc">Formato inválido para el tipo de documento.</span>
                            </div>
                            <div class="fe-group">
                                <label>NRC (Si aplica)</label>
                                <input type="text" name="cliente_nrc" maxlength="14" placeholder="Opcional">
                            </div>
                            <div class="fe-group">
                                <label>CORREO ELECTRÓNICO <span class="req">*</span></label>
                                <input type="email" name="cliente_email" id="cliente_email" required maxlength="100" placeholder="ejemplo@correo.com">
                                <span class="error-msg" id="err_email">Ingrese un correo electrónico válido.</span>
                            </div>
                            <div class="fe-group">
                                <label>TELÉFONO</label>
                                <input type="text" name="cliente_tel" id="telefono" maxlength="9" placeholder="2222-3333">
                                <span class="error-msg" id="err_tel">Formato: 2222-3333 o 7777-8888.</span>
                            </div>
                            <div class="fe-group">
                                <label>DEPARTAMENTO / MUNICIPIO <span class="req">*</span></label>
                                <select name="ubicacion" id="ubicacion" required>

                                <option value="0101">Ahuachapán, Ahuachapán</option>
                                    <?php
                                    /* Cargar municipios desde BD.*/
                                    ?>
                                    <option value="">-- Seleccione --</option>
                                </select>
                                <span class="error-msg" id="err_ubicacion">Seleccione departamento/municipio.</span>
                            </div>
                            <div class="fe-group span2">
                                <label>DIRECCIÓN</label>
                                <input type="text" name="cliente_direccion" maxlength="200" placeholder="Dirección completa (opcional para consumidor final)">
                            </div>
                            <div class="fe-group">
                                <label>ACTIVIDAD ECONÓMICA</label>
                                <input type="text" name="actividad_receptor" maxlength="150" placeholder="Consumo Final">
                            </div>
                            <div class="fe-group">
                                <label>NOMBRE COMERCIAL (Opcional)</label>
                                <input type="text" name="cliente_nombre_comercial" maxlength="150" placeholder="Nombre comercial si aplica">
                            </div>
                        </div>
                    </section>

                    <!-- ===== SECCIÓN 2: VENTA A CUENTA DE TERCEROS ===== -->
                    <section class="fe-section">
                        <h3 class="section-title"><span class="num">2</span> Venta a Cuenta de Terceros (Si aplica)</h3>
                        <div class="box-info">
                            <h4>⚠️ Complete solo si la venta se realiza en nombre de un tercero</h4>
                            <div class="grid-2">
                                <div class="fe-group">
                                    <label>NIT DEL TERCERO</label>
                                    <input type="text" name="tercero_nit" id="tercero_nit" maxlength="17" placeholder="0000-000000-000-0">
                                    <span class="error-msg" id="err_tercero_nit">Formato NIT inválido.</span>
                                </div>
                                <div class="fe-group">
                                    <label>NOMBRE / RAZÓN SOCIAL DEL TERCERO</label>
                                    <input type="text" name="tercero_nombre" maxlength="250" placeholder="Razón social del tercero">
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- ===== SECCIÓN 3: DOCUMENTOS RELACIONADOS ===== -->
                    <section class="fe-section">
                        <h3 class="section-title"><span class="num">3</span> Documentos Relacionados (Si aplica)</h3>
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

                    <!-- ===== SECCIÓN 4: DETALLE DE ÍTEMS ===== -->
                    <section class="fe-section">
                        <h3 class="section-title"><span class="num">4</span> Detalle de Ítems</h3>
                        <div class="table-wrap">
                            <table class="items-table" id="tablaItems">
                                <thead>
                                    <tr>
                                        <th style="width:30px;">N°</th>
                                        <th style="width:80px;">TIPO ÍTEM</th>
                                        <th style="width:55px;">CANT.</th>
                                        <th style="width:75px;">UNIDAD</th>
                                        <th style="min-width:160px;">DESCRIPCIÓN</th>
                                        <th style="width:85px;">PRECIO UNIT.</th>
                                        <th style="width:80px;">OTROS MONTOS NO AFECTOS</th>
                                        <th style="width:75px;">IVA POR ÍTEM</th>
                                        <th style="width:75px;">DESC. ÍTEM</th>
                                        <th style="width:85px;">VENTA NO SUJETA</th>
                                        <th style="width:85px;">VENTA EXENTA</th>
                                        <th style="width:85px;">VENTA GRAVADA</th>
                                        <th style="width:28px;"></th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                            <button type="button" class="btn-add" id="addItem">+ Agregar Ítem</button>
                        </div>
                        <p style="font-size:10px;color:#888;margin-top:6px;font-style:italic;">
                            * IVA (13%) incluido en precio. La Venta Gravada se calcula automáticamente.
                        </p>
                    </section>

                    <!-- ===== SECCIÓN 5: RESUMEN Y TOTALES ===== -->
                    <section class="fe-section no-border">
                        <h3 class="section-title"><span class="num">5</span> Resumen y Totales</h3>
                        <div class="footer-grid">

                            <!-- Columna izquierda: observaciones, condición, formas de pago -->
                            <div>
                                <div class="fe-group" style="margin-bottom:12px;">
                                    <label>OBSERVACIONES</label>
                                    <textarea name="observaciones" rows="4" maxlength="3000" placeholder="Notas adicionales..."></textarea>
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
                                            <input type="number" name="plazo_meses" min="1" max="120" placeholder="Número de meses">
                                        </div>
                                        <div class="fe-group">
                                            <label>FECHA VENCIMIENTO</label>
                                            <input type="date" name="fecha_vencimiento">
                                        </div>
                                    </div>
                                </div>
                                <div style="margin-top:14px;">
                                    <div class="section-divider">Forma de Pago</div>
                                    <div id="formasPago"></div>
                                    <button type="button" class="btn-add" id="addFormaPago" style="margin:6px 0;">+ Forma de Pago</button>
                                </div>
                            </div>

                            <!-- Columna derecha: totales -->
                            <div>
                                <div class="totales-box">
                                    <div class="totales-title">📊 RESUMEN DE TOTALES</div>
                                    <div class="total-row"><span>Total Ventas No Sujetas:</span><span id="t_nosujeta">0.00</span></div>
                                    <div class="total-row"><span>Total Ventas Exentas:</span><span id="t_exenta">0.00</span></div>
                                    <div class="total-row"><span>Total Ventas Gravadas:</span><span id="t_gravada">0.00</span></div>
                                    <div class="total-row separator"><span>Suma de Ventas ($):</span><span id="t_suma">0.00</span></div>
                                    <div class="total-row"><span>Monto Global Descuento:</span><span id="t_desc_global">0.00</span></div>
                                    <div class="total-row"><span>Desc. Rebaja No Sujeta:</span><span id="t_desc_nosujeta">0.00</span></div>
                                    <div class="total-row"><span>Desc. Rebaja Exenta:</span><span id="t_desc_exenta">0.00</span></div>
                                    <div class="total-row"><span>Desc. Rebaja Gravada:</span><span id="t_desc_gravada">0.00</span></div>
                                    <div class="total-row separator"><span>Sub-Total:</span><span id="t_subtotal">0.00</span></div>
                                    <div class="total-row"><span>IVA Retenido (1%):</span><span id="t_iva_retenido">0.00</span></div>
                                    <div class="total-row"><span>Nombre del Tributo:</span><span>IVA 13%</span></div>
                                    <div class="total-row"><span>Valor del Tributo:</span><span id="t_tributo">0.00</span></div>
                                    <div class="total-row"><span>Total Otros Montos No Afectos:</span><span id="t_otros">0.00</span></div>
                                    <div class="total-row highlight">
                                        <strong>TOTAL A PAGAR:</strong>
                                        <strong id="t_total" class="val-total">$ 0.00</strong>
                                    </div>
                                    <p class="total-letras" id="t_letras">SON: CERO 00/100 DÓLARES</p>
                                </div>
                                <!-- Hidden totales -->
                                <input type="hidden" name="h_nosujeta"    id="h_nosujeta">
                                <input type="hidden" name="h_exenta"      id="h_exenta">
                                <input type="hidden" name="h_gravada"     id="h_gravada">
                                <input type="hidden" name="h_suma"        id="h_suma">
                                <input type="hidden" name="h_desc_global" id="h_desc_global">
                                <input type="hidden" name="h_subtotal"    id="h_subtotal">
                                <input type="hidden" name="h_iva_retenido"id="h_iva_retenido">
                                <input type="hidden" name="h_total"       id="h_total">
                            </div>
                        </div>
                    </section>

                    <!-- ===== SECCIÓN 6: RESPONSABLES ===== -->
                    <section class="fe-section">
                        <h3 class="section-title"><span class="num">6</span> Responsables</h3>
                        <div class="responsables-grid">
                            <div class="responsable-box">
                                <label>RESPONSABLE — EMISOR</label>
                                <input type="text" name="responsable_emisor_nombre"
                                       value="<?= htmlspecialchars($_SESSION['usuario_nombre']) ?>"
                                       maxlength="100" placeholder="Nombre del responsable">
                                <div style="margin-top:8px;">
                                    <label>N° DE DOCUMENTO</label>
                                    <input type="text" name="responsable_emisor_doc" maxlength="20" placeholder="DUI o NIT">
                                </div>
                            </div>
                            <div class="responsable-box">
                                <label>RESPONSABLE — RECEPTOR (Opcional)</label>
                                <input type="text" name="responsable_receptor_nombre" maxlength="100" placeholder="Nombre del responsable">
                                <div style="margin-top:8px;">
                                    <label>N° DE DOCUMENTO</label>
                                    <input type="text" name="responsable_receptor_doc" maxlength="20" placeholder="DUI o NIT">
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- ===== ACCIONES ===== -->
                    <div class="form-actions">
                        <button type="button" class="btn-cancel" onclick="confirmarCancelar()">Cancelar Operación</button>
                        <button type="button" class="btn-preview" onclick="previsualizarDTE()">👁 Vista Previa</button>
                        <button type="submit" class="btn-save" onclick="return validarFormulario()">📤 GENERAR DTE Y PDF</button>
                    </div>

                </form>
            </div>
        </main>
    </div>
</div>

<div id="toast"></div>

<script>
/* ================================================================
   CONFIGURACIÓN
   ================================================================ */
const TIPOS_ITEM = [
    {v:'1',l:'1 — Bien'}, {v:'2',l:'2 — Servicio'},
    {v:'3',l:'3 — Ambos'}, {v:'4',l:'4 — Otro cargo'}
];
const UNIDADES = [
    {v:'59',l:'Unidad'}, {v:'39',l:'Servicio'}, {v:'26',l:'Libra'},
    {v:'27',l:'Kilogramo'}, {v:'10',l:'Litro'}, {v:'01',l:'Metro'}, {v:'99',l:'Otro'}
];
const FORMAS_PAGO = [
    {v:'01',l:'01 — Billetes y Monedas'}, {v:'02',l:'02 — Tarjeta Débito'},
    {v:'03',l:'03 — Tarjeta Crédito'},    {v:'04',l:'04 — Cheque'},
    {v:'05',l:'05 — Transferencia Crédito'},{v:'06',l:'06 — Transferencia Débito'},
    {v:'07',l:'07 — Vales'},              {v:'08',l:'08 — Dinero Electrónico'},
    {v:'09',l:'09 — Tarjeta Prepago'},    {v:'10',l:'10 — Pago Móvil'},
    {v:'11',l:'11 — Bitcoin'},            {v:'99',l:'99 — Otros'}
];
const TIPOS_DOC_REL = [
    {v:'01',l:'01 — Factura'}, {v:'02',l:'02 — CCF'},
    {v:'03',l:'03 — Nota de Remisión'}, {v:'04',l:'04 — Nota de Crédito'},
    {v:'05',l:'05 — Nota de Débito'},   {v:'06',l:'06 — Comp. Liquidación'},
    {v:'07',l:'07 — Doc. Contable Liq.'},{v:'08',l:'08 — Factura Sujeto Excluido'},
    {v:'09',l:'09 — DTE'}
];

/* Genera <option> desde array {v, l} */
function opts(arr, sel='') {
    return arr.map(o => `<option value="${o.v}"${o.v===sel?' selected':''}>${o.l}</option>`).join('');
}
/* Genera <select> */
function sel(name, arr, cls='in-table') {
    return `<select name="${name}" class="${cls}">${opts(arr)}</select>`;
}

/* ================================================================
   UTILIDADES
   ================================================================ */
function toast(msg, tipo='error') {
    const t = document.getElementById('toast');
    t.textContent = msg;
    t.className = 'show' + (tipo==='success' ? ' success' : '');
    clearTimeout(t._t);
    t._t = setTimeout(() => t.className = '', 4000);
}

function isNum(evt) {
    const c = evt.which || evt.keyCode;
    return !(c===45 || c===101 || c===69);
}

function fmt(n) { return parseFloat(n||0).toFixed(2); }

/* ================================================================
   INIT METADATOS
   ================================================================ */
(function() {
    const ahora = new Date();
    const p = n => String(n).padStart(2,'0');
    document.getElementById('fecha_generacion').value =
        `${ahora.getFullYear()}-${p(ahora.getMonth()+1)}-${p(ahora.getDate())} ` +
        `${p(ahora.getHours())}:${p(ahora.getMinutes())}:${p(ahora.getSeconds())}`;

    document.getElementById('codigo_generacion').value =
        'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, c => {
            const r = Math.random()*16|0, v = c==='x' ? r : (r&0x3|0x8);
            return v.toString(16).toUpperCase();
        });

    document.getElementById('numero_control').value = 'DTE-01-00000001-000000000000001';
})();

/* ================================================================
   TABLA DE ÍTEMS
   ================================================================ */
function filaItem(n) {
    return `
    <tr>
        <td class="td-num">${n}</td>
        <td class="td-tipo">${sel('tipo_item[]', TIPOS_ITEM)}</td>
        <td><input type="number" name="cant[]"          class="in-table qty"       min="1"   value="1"    step="1"    onkeypress="return isNum(event)"></td>
        <td>${sel('unidad[]', UNIDADES)}</td>
        <td><input type="text"   name="desc[]"          class="in-table desc-col"  required  maxlength="1000"></td>
        <td><input type="number" name="precio[]"        class="in-table price"     step="0.01" min="0" value="0.00" onkeypress="return isNum(event)"></td>
        <td><input type="number" name="otros_montos[]"  class="in-table otros"     step="0.01" value="0.00"         onkeypress="return isNum(event)"></td>
        <td><input type="number" name="iva_item[]"      class="in-table iva_item"  step="0.01" value="0.00" readonly></td>
        <td><input type="number" name="descuento_item[]"class="in-table desc_item" step="0.01" value="0.00"         onkeypress="return isNum(event)"></td>
        <td><input type="number" name="v_nosujeta[]"    class="in-table v_ns"      step="0.01" value="0.00"         onkeypress="return isNum(event)"></td>
        <td><input type="number" name="v_exenta[]"      class="in-table v_ex"      step="0.01" value="0.00"         onkeypress="return isNum(event)"></td>
        <td><input type="number" name="v_gravada[]"     class="in-table v_grav"    step="0.01" value="0.00" readonly></td>
        <td><button type="button" class="btn-del" onclick="delItem(this)">×</button></td>
    </tr>`;
}

function addItem() {
    const tbody = document.querySelector('#tablaItems tbody');
    tbody.insertAdjacentHTML('beforeend', filaItem(tbody.rows.length + 1));
}

function delItem(btn) {
    const tbody = document.querySelector('#tablaItems tbody');
    if (tbody.rows.length <= 1) { toast('Debe haber al menos un ítem.'); return; }
    btn.closest('tr').remove();
    [...tbody.rows].forEach((tr, i) => tr.querySelector('.td-num').textContent = i+1);
    calcTotales();
}

document.getElementById('addItem').addEventListener('click', addItem);
addItem(); // Primera fila por defecto

/* ================================================================
   TABLA DOCUMENTOS RELACIONADOS
   ================================================================ */
function filaDocRel() {
    return `
    <tr>
        <td>${sel('doc_rel_tipo[]', TIPOS_DOC_REL)}</td>
        <td><input type="text" name="doc_rel_numero[]" class="in-table" maxlength="36" placeholder="N° o código"></td>
        <td><input type="date" name="doc_rel_fecha[]"  class="in-table"></td>
        <td><button type="button" class="btn-del" onclick="this.closest('tr').remove()">×</button></td>
    </tr>`;
}

document.getElementById('addDocRel').addEventListener('click', function() {
    document.querySelector('#tablaDocRel tbody').insertAdjacentHTML('beforeend', filaDocRel());
});

/* ================================================================
   FORMAS DE PAGO
   ================================================================ */
function filaFormaPago(esEliminar=true) {
    const del = esEliminar
        ? `<div class="fe-group" style="align-self:flex-end;">
               <label>&nbsp;</label>
               <button type="button" class="btn-del" onclick="this.closest('.forma-pago-row').remove()">×</button>
           </div>`
        : '';
    return `
    <div class="forma-pago-row" style="${esEliminar?'':''}">
        <div class="fe-group">
            <label>CÓDIGO FORMA PAGO <span class="req">*</span></label>
            <select name="forma_pago_codigo[]">${opts(FORMAS_PAGO)}</select>
        </div>
        <div class="fe-group">
            <label>MONTO <span class="req">*</span></label>
            <input type="number" name="forma_pago_monto[]" step="0.01" min="0" placeholder="0.00" onkeypress="return isNum(event)">
        </div>
        <div class="fe-group">
            <label>PERIODO (Días)</label>
            <input type="number" name="forma_pago_periodo[]" min="0" placeholder="0">
        </div>
        ${del}
    </div>`;
}

document.getElementById('addFormaPago').addEventListener('click', function() {
    document.getElementById('formasPago').insertAdjacentHTML('beforeend', filaFormaPago(true));
});
document.getElementById('formasPago').insertAdjacentHTML('beforeend', filaFormaPago(false));

/* ================================================================
   CÁLCULO DE TOTALES
   ================================================================ */
function calcTotales() {
    let grav=0, exen=0, nosuj=0, desc=0, otros=0;

    document.querySelectorAll('#tablaItems tbody tr').forEach(tr => {
        const q    = Math.max(0, parseFloat(tr.querySelector('.qty')?.value)||0);
        const p    = Math.max(0, parseFloat(tr.querySelector('.price')?.value)||0);
        const d    = Math.max(0, parseFloat(tr.querySelector('.desc_item')?.value)||0);
        const ns   = Math.max(0, parseFloat(tr.querySelector('.v_ns')?.value)||0);
        const ex   = Math.max(0, parseFloat(tr.querySelector('.v_ex')?.value)||0);
        const ot   = Math.max(0, parseFloat(tr.querySelector('.otros')?.value)||0);

        let g = Math.max(0, (q*p) - d - ns - ex);
        tr.querySelector('.v_grav').value   = g.toFixed(2);
        tr.querySelector('.iva_item').value = (g - g/1.13).toFixed(2);

        grav  += g; exen += ex; nosuj += ns; desc += d; otros += ot;
    });

    const suma    = grav + exen + nosuj;
    const tributo = grav - grav/1.13;
    const total   = suma - desc;

    const set = (id, v) => { const el=document.getElementById(id); if(el) el.textContent=fmt(v); };
    set('t_nosujeta', nosuj); set('t_exenta', exen); set('t_gravada', grav);
    set('t_suma',     suma);  set('t_desc_global', desc);
    set('t_desc_nosujeta', 0); set('t_desc_exenta', 0); set('t_desc_gravada', desc);
    set('t_subtotal', suma);  set('t_iva_retenido', 0);
    set('t_tributo',  tributo); set('t_otros', otros);
    document.getElementById('t_total').textContent  = '$ ' + fmt(total);
    document.getElementById('t_letras').textContent = 'SON: ' + numLetras(total);

    const hset = (id, v) => { const el=document.getElementById(id); if(el) el.value=fmt(v); };
    hset('h_nosujeta',grav); hset('h_exenta',exen); hset('h_gravada',grav);
    hset('h_suma',suma); hset('h_desc_global',desc);
    hset('h_subtotal',suma); hset('h_iva_retenido',0); hset('h_total',total);
}

document.getElementById('formFactura').addEventListener('input', calcTotales);
calcTotales();

/* ================================================================
   NÚMERO A LETRAS
   ================================================================ */
function numLetras(num) {
    const U=['','UNO','DOS','TRES','CUATRO','CINCO','SEIS','SIETE','OCHO','NUEVE',
             'DIEZ','ONCE','DOCE','TRECE','CATORCE','QUINCE','DIECISÉIS','DIECISIETE','DIECIOCHO','DIECINUEVE'];
    const D=['','','VEINTE','TREINTA','CUARENTA','CINCUENTA','SESENTA','SETENTA','OCHENTA','NOVENTA'];
    const C=['','CIENTO','DOSCIENTOS','TRESCIENTOS','CUATROCIENTOS','QUINIENTOS',
             'SEISCIENTOS','SETECIENTOS','OCHOCIENTOS','NOVECIENTOS'];
    function g(n) {
        let s='';
        if(n>=100){ s+=(n===100?'CIEN':C[Math.floor(n/100)])+' '; n%=100; }
        if(n>=20) { s+=D[Math.floor(n/10)]; if(n%10) s+=' Y '+U[n%10]; }
        else if(n>0) s+=U[n];
        return s.trim();
    }
    if(num===0) return 'CERO 00/100 DÓLARES';
    const ent=Math.floor(num), cts=Math.round((num-ent)*100);
    let l='';
    if(ent>=1000) l+=g(Math.floor(ent/1000))+' MIL ';
    l+=g(ent%1000);
    return l.trim()+' '+String(cts).padStart(2,'0')+'/100 DÓLARES';
}

/* ================================================================
   MÁSCARAS
   ================================================================ */
document.getElementById('tipo_doc').addEventListener('change', function() {
    const c = document.getElementById('doc_identidad');
    c.value = '';
    c.placeholder = this.value==='13' ? '00000000-0' : this.value==='02' ? '0000-000000-000-0' : '';
    c.maxLength    = this.value==='13' ? 10 : 17;
});

function mascDUI(v)  { v=v.replace(/\D/g,'').slice(0,9); return v.length>8?v.slice(0,8)+'-'+v.slice(8):v; }
function mascNIT(v)  {
    v=v.replace(/\D/g,'').slice(0,14);
    if(v.length>13) v=v.slice(0,13)+'-'+v.slice(13);
    else if(v.length>10) v=v.slice(0,10)+'-'+v.slice(10);
    else if(v.length>4)  v=v.slice(0,4)+'-'+v.slice(4);
    return v;
}
function mascTel(v)  { v=v.replace(/\D/g,'').slice(0,8); return v.length>4?v.slice(0,4)+'-'+v.slice(4):v; }

document.getElementById('doc_identidad').addEventListener('input', function() {
    const t = document.getElementById('tipo_doc').value;
    this.value = t==='13' ? mascDUI(this.value) : t==='02' ? mascNIT(this.value) : this.value;
});
document.getElementById('telefono').addEventListener('input', function() { this.value = mascTel(this.value); });
document.getElementById('tercero_nit').addEventListener('input', function() { this.value = mascNIT(this.value); });
document.getElementById('condicion_pago').addEventListener('change', function() {
    document.getElementById('bloque_plazo').style.display = this.value==='02' ? 'block' : 'none';
});

/* ================================================================
   VALIDACIONES
   ================================================================ */
function markErr(id, errId, show) {
    document.getElementById(id)?.classList.toggle('is-invalid', show);
    document.getElementById(errId)?.classList.toggle('visible', show);
}

const reEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
const reDUI   = /^\d{8}-\d$/;
const reNIT   = /^\d{4}-\d{6}-\d{3}-\d$/;
const reTel   = /^\d{4}-\d{4}$/;

// Validación en tiempo real (blur)
document.getElementById('cliente_nombre').addEventListener('blur', function() {
    markErr('cliente_nombre','err_nombre', this.value.trim().length < 2);
});
document.getElementById('cliente_email').addEventListener('blur', function() {
    markErr('cliente_email','err_email', !reEmail.test(this.value.trim()));
});
document.getElementById('doc_identidad').addEventListener('blur', function() {
    const t=document.getElementById('tipo_doc').value, v=this.value.trim();
    let inv = !v || (t==='13' && !reDUI.test(v)) || (t==='02' && !reNIT.test(v));
    markErr('doc_identidad','err_doc', inv);
});
document.getElementById('telefono').addEventListener('blur', function() {
    const v=this.value.trim();
    if(v) markErr('telefono','err_tel', !reTel.test(v));
});
document.getElementById('ubicacion').addEventListener('change', function() {
    markErr('ubicacion','err_ubicacion', !this.value);
});

function validarFormulario() {
    let ok = true;

    const nombre = document.getElementById('cliente_nombre').value.trim();
    markErr('cliente_nombre','err_nombre', nombre.length<2); if(nombre.length<2) ok=false;

    const tipo=document.getElementById('tipo_doc').value, doc=document.getElementById('doc_identidad').value.trim();
    let docOk = !!doc && !(tipo==='13' && !reDUI.test(doc)) && !(tipo==='02' && !reNIT.test(doc));
    markErr('doc_identidad','err_doc',!docOk); if(!docOk) ok=false;

    const email=document.getElementById('cliente_email').value.trim();
    markErr('cliente_email','err_email',!reEmail.test(email)); if(!reEmail.test(email)) ok=false;

    const tel=document.getElementById('telefono').value.trim();
    if(tel && !reTel.test(tel)) { markErr('telefono','err_tel',true); ok=false; }

    const ubi=document.getElementById('ubicacion').value;
    markErr('ubicacion','err_ubicacion',!ubi); if(!ubi) ok=false;

    const tnit=document.getElementById('tercero_nit').value.trim();
    if(tnit && !reNIT.test(tnit)) { markErr('tercero_nit','err_tercero_nit',true); ok=false; }

    let itemsOk=true;
    document.querySelectorAll('#tablaItems tbody tr').forEach(tr=>{
        const d=tr.querySelector('[name="desc[]"]');
        const valid=d.value.trim().length>0;
        d.classList.toggle('is-invalid',!valid);
        if(!valid) itemsOk=false;
    });
    if(!itemsOk){ toast('Todos los ítems deben tener descripción.'); ok=false; }

    const total=parseFloat(document.getElementById('h_total').value)||0;
    if(total<=0){ toast('El total a pagar debe ser mayor a $0.00'); ok=false; }

    if(!ok) toast('Corrija los errores marcados antes de continuar.');
    return ok;
}

/* ================================================================
   ACCIONES
   ================================================================ */
function confirmarCancelar() {
    if(confirm('¿Cancelar operación? Se perderán los datos ingresados.'))
        location.href='index.php';
}

function previsualizarDTE() {
    if(!validarFormulario()) return;
    toast('Vista previa en desarrollo. Use "GENERAR DTE Y PDF" para continuar.','success');
}
</script>
</body>
</html>