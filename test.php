<?php
session_start();
require_once 'class/Database.php';
if (!isset($_SESSION['usuario_nombre'])) {
    header("Location: login.php");
    exit();
}
$pagina_activa = 'FE';

$db = new Database();
$conn = $db->getConnection();

// Productos activos
$sql_prod = "SELECT id_producto, product_name, codigo_mh, precio FROM producto WHERE activo = 1";
$result_prod = $conn->query($sql_prod);
$productos_db = [];
if ($result_prod) {
    while ($row = $result_prod->fetch_assoc()) {
        $productos_db[] = $row;
    }
}

// Departamentos
$sql_deptos = "SELECT codigo, nombre FROM cat_departamento ORDER BY nombre";
$result_deptos = $conn->query($sql_deptos);
$departamentos_db = [];
if ($result_deptos) {
    while ($row = $result_deptos->fetch_assoc()) {
        $departamentos_db[] = $row;
    }
}

// Municipios
$sql_muni = "SELECT codigo, codigo_depto, nombre FROM cat_municipio";
$result_muni = $conn->query($sql_muni);
$municipios_db = [];
if ($result_muni) {
    while ($row = $result_muni->fetch_assoc()) {
        $municipios_db[] = $row;
    }
}

// Medios de pago
$sql_pago = "SELECT codigo, nombre FROM cat_medio_pago ORDER BY codigo";
$result_pago = $conn->query($sql_pago);
$medios_pago_db = [];
if ($result_pago) {
    while ($row = $result_pago->fetch_assoc()) {
        $medios_pago_db[] = $row;
    }
}
// Fallback si la tabla está vacía
if (empty($medios_pago_db)) {
    $medios_pago_db = [
        ['codigo' => '01', 'nombre' => 'Billetes y Monedas'],
        ['codigo' => '02', 'nombre' => 'Tarjeta Débito'],
        ['codigo' => '03', 'nombre' => 'Tarjeta Crédito'],
        ['codigo' => '04', 'nombre' => 'Cheque'],
        ['codigo' => '05', 'nombre' => 'Transferencia'],
        ['codigo' => '99', 'nombre' => 'Otros'],
    ];
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
    <link rel="stylesheet" href="css/formulario.css">
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
                <div style="background:#fde8e7;border:1px solid #d32f2f;color:#d32f2f;padding:12px 20px;margin-bottom:20px;border-radius:6px;font-weight:600;">
                    ❌ <?= htmlspecialchars($_GET['error']) ?>
                </div>
            <?php endif; ?>

            <div class="fe-wrapper">

                <div class="fe-topbar">
                    <div class="fe-topbar-left">
                        <span class="fe-badge">FE</span>
                        <span class="fe-topbar-title">Nueva factura de consumidor final</span>
                    </div>
                    <div class="fe-topbar-right">
                        <span class="fe-empresa"><?= htmlspecialchars($_SESSION['empresa_nombre'] ?? 'PIZZERIA EL SALVADOR S.A. DE C.V.') ?></span>
                        <span class="fe-num-badge">FE - 01</span>
                    </div>
                </div>

                <form id="formFactura" action="procesar_factura.php" method="POST" novalidate>

                    <input type="hidden" name="codigo_generacion"   id="codigo_generacion">
                    <input type="hidden" name="numero_control"      id="numero_control">
                    <input type="hidden" name="fecha_generacion"    id="fecha_generacion">
                    <input type="hidden" name="modelo_facturacion"  value="1">
                    <input type="hidden" name="tipo_transmision"    value="1">
                    <input type="hidden" name="tipo_item_default"   value="1">
                    <input type="hidden" name="unidad_default"      value="59">
                    <input type="hidden" name="h_nosujeta"   id="h_nosujeta">
                    <input type="hidden" name="h_exenta"     id="h_exenta">
                    <input type="hidden" name="h_gravada"    id="h_gravada">
                    <input type="hidden" name="h_suma"       id="h_suma">
                    <input type="hidden" name="h_desc_global" id="h_desc_global">
                    <input type="hidden" name="h_subtotal"   id="h_subtotal">
                    <input type="hidden" name="h_iva_retenido" id="h_iva_retenido">
                    <input type="hidden" name="h_total"      id="h_total">

                    <div class="fe-body">

                        <section class="fe-section">
                            <div class="fe-section-title">
                                <span class="num">1</span>
                                Datos del receptor
                            </div>

                            <div class="fe-grid fe-grid-1" style="margin-bottom:10px;">
                                <div class="fe-field col-full">
                                    <label>Nombre completo del cliente <span style="color:#a33">*</span></label>
                                    <input type="text" name="cliente_nombre" id="cliente_nombre"
                                           maxlength="250" placeholder="Ej: Carlos Rivera Martínez" required>
                                    <span class="err" id="err_nombre">Ingrese el nombre del cliente.</span>
                                </div>
                            </div>

                            <div class="fe-grid fe-grid-2" style="margin-bottom:10px;">
                                <div class="fe-field">
                                    <label>Tipo de identificación <span style="color:#a33">*</span></label>
                                    <select name="tipo_doc" id="tipo_doc">
                                        <option value="13">13 — DUI</option>
                                        <option value="02">02 — NIT</option>
                                        <option value="03">03 — Pasaporte</option>
                                        <option value="04">04 — Carné de Residente</option>
                                        <option value="36">36 — Sin documento</option>
                                    </select>
                                </div>
                                <div class="fe-field">
                                    <label>DUI / NIT del cliente <span style="color:#a33">*</span></label>
                                    <input type="text" name="cliente_doc" id="doc_identidad"
                                           maxlength="20" placeholder="00000000-0" required>
                                    <span class="err" id="err_doc">Formato inválido para el tipo de documento.</span>
                                </div>
                            </div>

                            <div class="fe-grid fe-grid-2" style="margin-bottom:10px;">
                                <div class="fe-field">
                                    <label>Correo electrónico <span style="color:#a33">*</span></label>
                                    <input type="email" name="cliente_email" id="cliente_email"
                                           maxlength="100" placeholder="ejemplo@correo.com" required>
                                    <span class="err" id="err_email">Correo electrónico inválido.</span>
                                </div>
                                <div class="fe-field">
                                    <label>Teléfono</label>
                                    <input type="text" name="cliente_tel" id="telefono"
                                           maxlength="9" placeholder="7000-0000">
                                    <span class="err" id="err_tel">Formato: 2222-3333 o 7777-8888.</span>
                                </div>
                            </div>

                            <div class="fe-grid fe-grid-2" style="margin-bottom:10px;">
                                <div class="fe-field">
                                    <label>Departamento <span style="color:#a33">*</span></label>
                                    <select name="dir_departamento" id="departamento" required onchange="cargarMunicipios()">
                                        <option value="">-- Seleccione --</option>
                                        <?php foreach ($departamentos_db as $d): ?>
                                            <option value="<?= htmlspecialchars($d['codigo']) ?>">
                                                <?= htmlspecialchars($d['codigo']) ?> - <?= htmlspecialchars($d['nombre']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <span class="err" id="err_depto">Seleccione un departamento.</span>
                                </div>
                                <div class="fe-field">
                                    <label>Municipio <span style="color:#a33">*</span></label>
                                    <select name="dir_municipio" id="municipio" required>
                                        <option value="">-- Seleccione departamento --</option>
                                    </select>
                                    <span class="err" id="err_muni">Seleccione un municipio.</span>
                                </div>
                            </div>

                            <div class="fe-grid fe-grid-1">
                                <div class="fe-field">
                                    <label>Dirección completa</label>
                                    <input type="text" name="cliente_direccion"
                                           maxlength="200" placeholder="Col. Escalón, Calle Los Bambúes #24">
                                </div>
                            </div>
                        </section>

                        <section class="fe-section">
                            <div class="fe-section-title">
                                <span class="num">2</span>
                                Detalles de productos
                            </div>

                            <div class="items-wrap">
                                <table class="items-table" id="tablaItems">
                                    <thead>
                                        <tr>
                                            <th style="width:28px;">#</th>
                                            <th style="width:80px;">Código</th>
                                            <th>Descripción</th>
                                            <th style="width:70px;">Cant.</th>
                                            <th style="width:100px;">Precio Unit.</th>
                                            <th style="width:100px;">Descuento</th>
                                            <th style="width:100px;">Subtotal</th>
                                            <th style="width:30px;"></th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                            <button type="button" class="btn-add-row" id="addItem">+ Agregar producto</button>
                        </section>

                        <section class="fe-section">
                            <div class="fe-section-title">
                                <span class="num">3</span>
                                Resumen y forma de pago
                            </div>

                            <div class="fe-footer">
                                <div class="pago-left">
                                    <div class="fe-grid fe-grid-2" style="margin-bottom:10px;">
                                        <div class="fe-field">
                                            <label>Método de pago <span style="color:#a33">*</span></label>
                                            <select name="forma_pago_codigo[]" id="metodo_pago">
                                                <?php foreach ($medios_pago_db as $mp): ?>
                                                    <option value="<?= htmlspecialchars($mp['codigo']) ?>">
                                                        <?= htmlspecialchars($mp['codigo']) ?> — <?= htmlspecialchars($mp['nombre']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="fe-field">
                                            <label>Referencia</label>
                                            <input type="text" name="pago_referencia" id="pago_referencia"
                                                   maxlength="50" placeholder="Visa ****1234">
                                        </div>
                                    </div>

                                    <div class="fe-grid fe-grid-2">
                                        <div class="fe-field">
                                            <label>Monto recibido</label>
                                            <input type="number" name="forma_pago_monto[]" id="monto_recibido"
                                                   step="0.01" min="0" placeholder="0.00"
                                                   onkeypress="return isNum(event)">
                                        </div>
                                        <div class="fe-field">
                                            <label>Condición de pago <span style="color:#a33">*</span></label>
                                            <select name="condicion_pago" id="condicion_pago">
                                                <option value="01">01 — Contado</option>
                                                <option value="02">02 — A Crédito</option>
                                                <option value="03">03 — Otro</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="totales-box">
                                    <div class="total-row">
                                        <span>Total ventas gravadas</span>
                                        <span id="t_gravada">$ 0.00</span>
                                    </div>
                                    <div class="total-row">
                                        <span>Descuento total</span>
                                        <span id="t_desc_global">$ 0.00</span>
                                    </div>
                                    <div class="total-row">
                                        <span>Subtotal</span>
                                        <span id="t_subtotal">$ 0.00</span>
                                    </div>
                                    <div class="total-row highlight">
                                        <span>TOTAL A PAGAR</span>
                                        <span class="val-total" id="t_total">$ 0.00</span>
                                    </div>
                                    <p class="total-letras" id="t_letras">Total en letras: CERO 00/100 DÓLARES</p>
                                </div>
                            </div>
                        </section>

                        <div class="fe-actions">
                            <button type="button" class="btn-cancel" onclick="confirmarCancelar()">Cancelar</button>
                            <button type="submit" class="btn-save" onclick="return validarFormulario()">Guardar</button>
                        </div>

                    </div></form>
            </div></main>
    </div>
</div>

<div id="toast" style="position:fixed;bottom:24px;right:24px;background:#333;color:#fff;padding:12px 20px;border-radius:8px;font-size:13px;display:none;z-index:9999;box-shadow:0 4px 16px rgba(0,0,0,.25);"></div>

<script>
/* ================================================================
   DATOS DESDE PHP
================================================================ */
const PRODUCTOS_DB  = <?= json_encode($productos_db) ?>;
const MUNICIPIOS_DB = <?= json_encode($municipios_db) ?>;

/* ================================================================
   UTILIDADES
================================================================ */
function toast(msg, tipo = 'error') {
    const t = document.getElementById('toast');
    t.textContent = msg;
    t.style.background = tipo === 'success' ? '#2e7d32' : '#b71c1c';
    t.style.display = 'block';
    clearTimeout(t._t);
    t._t = setTimeout(() => t.style.display = 'none', 4000);
}
function isNum(evt) {
    const c = evt.which || evt.keyCode;
    return !(c === 45 || c === 101 || c === 69);
}
function fmt(n) { return parseFloat(n || 0).toFixed(2); }
function fmtMoney(n) { return '$ ' + fmt(n); }

/* ================================================================
   INIT METADATOS OCULTOS
================================================================ */
(function () {
    const ahora = new Date();
    const p = n => String(n).padStart(2, '0');
    document.getElementById('fecha_generacion').value =
        `${ahora.getFullYear()}-${p(ahora.getMonth()+1)}-${p(ahora.getDate())} ` +
        `${p(ahora.getHours())}:${p(ahora.getMinutes())}:${p(ahora.getSeconds())}`;
    document.getElementById('codigo_generacion').value =
        'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, c => {
            const r = Math.random() * 16 | 0, v = c === 'x' ? r : (r & 0x3 | 0x8);
            return v.toString(16).toUpperCase();
        });
    document.getElementById('numero_control').value = '';
})();

/* ================================================================
   CASCADA DEPARTAMENTO → MUNICIPIO
================================================================ */
function cargarMunicipios() {
    const codDepto = document.getElementById('departamento').value;
    const sel = document.getElementById('municipio');
    sel.innerHTML = '<option value="">-- Seleccione --</option>';
    if (codDepto) {
        MUNICIPIOS_DB.filter(m => m.codigo_depto === codDepto).forEach(m => {
            const opt = document.createElement('option');
            opt.value = m.codigo;
            opt.textContent = m.nombre;
            sel.appendChild(opt);
        });
    }
}

/* ================================================================
   TABLA DE ÍTEMS
================================================================ */
function optsProductos() {
    let html = '<option value="">-- Seleccione producto --</option>';
    PRODUCTOS_DB.forEach(p => {
        html += `<option value="${p.id_producto}" data-precio="${p.precio}" data-codigo="${p.codigo_mh}">
                    ${p.codigo_mh} - ${p.product_name}
                 </option>`;
    });
    return html;
}

function filaItem(n) {
    return `
    <tr>
        <td class="td-num">${n}</td>
        <td><input type="text" name="codigo_mh[]" class="item-codigo" readonly placeholder="—"></td>
        <td>
            <select name="id_producto[]" class="prod-select" required onchange="seleccionarProducto(this)">
                ${optsProductos()}
            </select>
            <input type="hidden" name="tipo_item[]"   value="1">
            <input type="hidden" name="unidad[]"      value="59">
            <input type="hidden" name="v_nosujeta[]"  class="v_ns" value="0.00">
            <input type="hidden" name="v_exenta[]"    class="v_ex" value="0.00">
            <input type="hidden" name="v_gravada[]"   class="v_grav" value="0.00">
            <input type="hidden" name="iva_item[]"    class="iva_item" value="0.00">
        </td>
        <td><input type="number" name="cant[]"   class="qty"   min="1" value="1" step="1" onkeypress="return isNum(event)" oninput="calcTotales()"></td>
        <td><input type="number" name="precio[]" class="price" step="0.01" min="0" value="0.00" readonly></td>
        <td><input type="number" name="descuento_item[]" class="desc_item" step="0.01" min="0" value="0.00" onkeypress="return isNum(event)" oninput="calcTotales()"></td>
        <td><input type="number" name="subtotal_item[]" class="subtotal" step="0.01" value="0.00" readonly></td>
        <td class="td-del"><button type="button" class="btn-del-row" onclick="delItem(this)" title="Eliminar">×</button></td>
    </tr>`;
}

function seleccionarProducto(sel) {
    const tr  = sel.closest('tr');
    const opt = sel.options[sel.selectedIndex];
    const priceInput  = tr.querySelector('.price');
    const codigoInput = tr.querySelector('.item-codigo');
    if (opt.value) {
        priceInput.value  = parseFloat(opt.dataset.precio).toFixed(2);
        codigoInput.value = opt.dataset.codigo || '—';
    } else {
        priceInput.value  = '0.00';
        codigoInput.value = '';
    }
    calcTotales();
}

function addItem() {
    const tbody = document.querySelector('#tablaItems tbody');
    tbody.insertAdjacentHTML('beforeend', filaItem(tbody.rows.length + 1));
}

function delItem(btn) {
    const tbody = document.querySelector('#tablaItems tbody');
    if (tbody.rows.length <= 1) { toast('Debe haber al menos un producto.'); return; }
    btn.closest('tr').remove();
    [...tbody.rows].forEach((tr, i) => tr.querySelector('.td-num').textContent = i + 1);
    calcTotales();
}

document.getElementById('addItem').addEventListener('click', addItem);
addItem(); // Primera fila por defecto

/* ================================================================
   CÁLCULO DE TOTALES
================================================================ */
function calcTotales() {
    let grav = 0, desc = 0;

    document.querySelectorAll('#tablaItems tbody tr').forEach(tr => {
        const q = Math.max(0, parseFloat(tr.querySelector('.qty')?.value)       || 0);
        const p = Math.max(0, parseFloat(tr.querySelector('.price')?.value)     || 0);
        const d = Math.max(0, parseFloat(tr.querySelector('.desc_item')?.value) || 0);

        const subtotal = Math.max(0, q * p - d);
        if (tr.querySelector('.subtotal')) tr.querySelector('.subtotal').value = subtotal.toFixed(2);
        // Para consumidor final todo es gravado (IVA incluido)
        const g = subtotal;
        if (tr.querySelector('.v_grav'))    tr.querySelector('.v_grav').value    = g.toFixed(2);
        if (tr.querySelector('.iva_item'))  tr.querySelector('.iva_item').value  = (g - g / 1.13).toFixed(2);
        grav += g;
        desc += d;
    });

    const total = grav;

    // Mostrar en UI
    document.getElementById('t_gravada').textContent    = fmtMoney(grav);
    document.getElementById('t_desc_global').textContent= fmtMoney(desc);
    document.getElementById('t_subtotal').textContent   = fmtMoney(total);
    document.getElementById('t_total').textContent      = fmtMoney(total);
    document.getElementById('t_letras').textContent     = 'Total en letras: ' + numLetras(total);

    // Llenar campos hidden
    document.getElementById('h_nosujeta').value    = '0.00';
    document.getElementById('h_exenta').value      = '0.00';
    document.getElementById('h_gravada').value     = fmt(grav);
    document.getElementById('h_suma').value        = fmt(grav);
    document.getElementById('h_desc_global').value = fmt(desc);
    document.getElementById('h_subtotal').value    = fmt(grav);
    document.getElementById('h_iva_retenido').value= '0.00';
    document.getElementById('h_total').value       = fmt(total);

    // Auto-rellenar monto recibido si está vacío
    const montoInput = document.getElementById('monto_recibido');
    if (!montoInput.dataset.modified) montoInput.value = fmt(total);
}

document.getElementById('monto_recibido').addEventListener('input', function () {
    this.dataset.modified = 'true';
});

document.getElementById('formFactura').addEventListener('input', function(e) {
    if (!e.target.closest('#tablaItems') && e.target.id !== 'monto_recibido') return;
    calcTotales();
});

calcTotales();

/* ================================================================
   NÚMERO A LETRAS
================================================================ */
function numLetras(num) {
    const U = ['','UNO','DOS','TRES','CUATRO','CINCO','SEIS','SIETE','OCHO','NUEVE',
                'DIEZ','ONCE','DOCE','TRECE','CATORCE','QUINCE','DIECISÉIS',
                'DIECISIETE','DIECIOCHO','DIECINUEVE'];
    const D = ['','','VEINTE','TREINTA','CUARENTA','CINCUENTA','SESENTA','SETENTA','OCHENTA','NOVENTA'];
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
    if (ent >= 1000) l += g(Math.floor(ent/1000)) + ' MIL ';
    l += g(ent % 1000);
    return l.trim() + ' ' + String(cts).padStart(2,'0') + '/100 DÓLARES';
}

/* ================================================================
   MÁSCARAS
================================================================ */
document.getElementById('tipo_doc').addEventListener('change', function () {
    const c = document.getElementById('doc_identidad');
    c.value = '';
    c.placeholder = this.value === '13' ? '00000000-0' : this.value === '02' ? '0000-000000-000-0' : '';
    c.maxLength = this.value === '13' ? 10 : 17;
});
function mascDUI(v) { v = v.replace(/\D/g,'').slice(0,9); return v.length>8 ? v.slice(0,8)+'-'+v.slice(8) : v; }
function mascNIT(v) {
    v = v.replace(/\D/g,'').slice(0,14);
    if (v.length>13) v=v.slice(0,13)+'-'+v.slice(13);
    else if(v.length>10) v=v.slice(0,10)+'-'+v.slice(10);
    else if(v.length>4)  v=v.slice(0,4)+'-'+v.slice(4);
    return v;
}
function mascTel(v) { v = v.replace(/\D/g,'').slice(0,8); return v.length>4 ? v.slice(0,4)+'-'+v.slice(4) : v; }
document.getElementById('doc_identidad').addEventListener('input', function () {
    const t = document.getElementById('tipo_doc').value;
    this.value = t==='13' ? mascDUI(this.value) : t==='02' ? mascNIT(this.value) : this.value;
});
document.getElementById('telefono').addEventListener('input', function () { this.value = mascTel(this.value); });

/* ================================================================
   VALIDACIONES
================================================================ */
function markErr(fieldId, errId, show) {
    document.getElementById(fieldId)?.classList.toggle('is-invalid', show);
    document.getElementById(errId)?.classList.toggle('visible', show);
}
const reEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
const reDUI   = /^\d{8}-\d$/;
const reNIT   = /^\d{4}-\d{6}-\d{3}-\d$/;
const reTel   = /^\d{4}-\d{4}$/;

function validarFormulario() {
    let ok = true;

    const nombre = document.getElementById('cliente_nombre').value.trim();
    markErr('cliente_nombre','err_nombre', nombre.length < 2);
    if (nombre.length < 2) ok = false;

    const tipo = document.getElementById('tipo_doc').value;
    const doc  = document.getElementById('doc_identidad').value.trim();
    const docOk = !!doc && !(tipo==='13' && !reDUI.test(doc)) && !(tipo==='02' && !reNIT.test(doc));
    markErr('doc_identidad','err_doc', !docOk);
    if (!docOk) ok = false;

    const email = document.getElementById('cliente_email').value.trim();
    markErr('cliente_email','err_email', !reEmail.test(email));
    if (!reEmail.test(email)) ok = false;

    const tel = document.getElementById('telefono').value.trim();
    if (tel && !reTel.test(tel)) { markErr('telefono','err_tel', true); ok = false; }

    const depto = document.getElementById('departamento').value;
    markErr('departamento','err_depto', !depto);
    if (!depto) ok = false;

    const muni = document.getElementById('municipio').value;
    markErr('municipio','err_muni', !muni);
    if (!muni) ok = false;

    let itemsOk = true;
    document.querySelectorAll('#tablaItems tbody tr').forEach(tr => {
        const s = tr.querySelector('.prod-select');
        const v = s && s.value !== '';
        if (s) s.classList.toggle('is-invalid', !v);
        if (!v) itemsOk = false;
    });
    if (!itemsOk) { toast('Seleccione un producto en todas las filas.'); ok = false; }

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
</script>
</body>
</html>