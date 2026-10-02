<?php
// View/vreporte_otros_gastos.php
require_once __DIR__ . '/header.php';

// Obtener todos los egresos registrados
$gastos_rows = [];
if (isset($egresos) && $egresos->rowCount() > 0) {
    $gastos_rows = $egresos->fetchAll(PDO::FETCH_ASSOC);
}

$meses_nombres = [
    '01' => 'ENERO', '02' => 'FEBRERO', '03' => 'MARZO', '04' => 'ABRIL',
    '05' => 'MAYO', '06' => 'JUNIO', '07' => 'JULIO', '08' => 'AGOSTO',
    '09' => 'SEPTIEMBRE', '10' => 'OCTUBRE', '11' => 'NOVIEMBRE', '12' => 'DICIEMBRE'
];
$mes_actual_num = date('m');
$mes_actual_nombre = $meses_nombres[$mes_actual_num] ?? 'AGOSTO';
$anio_actual = date('Y');
?>

<style>
/* Estilos generales de la tabla Excel */
.reporte-excel-table {
    width: 100%;
    border-collapse: collapse;
    font-family: inherit;
    background-color: #ffffff;
    border: 1.5px solid #2b5797;
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
}

.reporte-excel-table th {
    background-color: #8ea9db !important;
    color: #000000 !important;
    font-weight: 700;
    text-transform: uppercase;
    font-size: 12px;
    padding: 10px 8px;
    border: 1px solid #2b5797;
    text-align: center;
    vertical-align: middle;
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
}

.reporte-excel-table td {
    padding: 8px 10px;
    border: 1px solid #2b5797;
    font-size: 13px;
    color: #000000;
    vertical-align: middle;
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
}

.reporte-excel-table tr:nth-child(even) td {
    background-color: #f8fafc;
}

.reporte-excel-table .total-label-cell {
    background-color: #8ea9db !important;
    color: #000000 !important;
    font-weight: 800 !important;
    text-transform: uppercase;
    text-align: center !important;
    font-size: 13px;
    letter-spacing: 0.5px;
    border: 1px solid #2b5797 !important;
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
}

.reporte-excel-table .total-value-cell {
    background-color: #ffff00 !important; /* AMARILLO INTENSO SEGÚN IMAGEN 2 */
    color: #000000 !important;
    font-weight: 800 !important;
    text-align: right !important;
    font-size: 14px;
    border: 1.5px solid #2b5797 !important;
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
}

/* Tarjetas de estadísticas en pantalla */
.stats-cards-wrapper {
    display: flex;
    gap: 20px;
    flex-wrap: wrap;
    margin-bottom: 22px;
}

.stat-card-custom {
    flex: 1;
    min-width: 220px;
    background-color: #ffffff;
    border-radius: 12px;
    padding: 16px 20px;
    display: flex;
    align-items: center;
    gap: 16px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 4px 12px rgba(0,0,0,0.03);
}

.stat-icon-custom {
    width: 48px;
    height: 48px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    flex-shrink: 0;
}

.print-logo {
    max-height: 42px;
    width: auto;
}
</style>

<!-- VISTA PRINCIPAL (PANTALLA) -->
<div id="screenReportView">
    
    <div class="dashboard-header">
        <div class="header-actions" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; margin-bottom: 20px;">
            <div>
                <h1 class="page-title" style="margin: 0; font-size: 24px; font-weight: 700; color: #0f172a;">Reporte de Otros Gastos</h1>
                <p class="page-subtitle" style="margin: 4px 0 0 0; color: #64748b; font-size: 14px;">Listado oficial de egresos e imprevistos institucionales registrados en CEREMA</p>
            </div>
        </div>
    </div>

    <!-- Tarjetas de Estadísticas del Reporte -->
    <div class="stats-cards-wrapper">
        <div class="stat-card-custom">
            <div class="stat-icon-custom" style="background-color: rgba(239, 68, 68, 0.12); color: #dc2626;">
                <i class="fa-solid fa-receipt"></i>
            </div>
            <div>
                <h4 style="margin: 0 0 4px 0; font-size: 12px; color: #64748b; font-weight: 600; text-transform: uppercase;">Total Gastos Registrados</h4>
                <p id="statTotalRegistros" style="margin: 0; font-size: 20px; font-weight: 700; color: #0f172a;">0</p>
            </div>
        </div>

        <div class="stat-card-custom">
            <div class="stat-icon-custom" style="background-color: rgba(220, 38, 38, 0.12); color: #b91c1c;">
                <i class="fa-solid fa-hand-holding-dollar"></i>
            </div>
            <div>
                <h4 style="margin: 0 0 4px 0; font-size: 12px; color: #64748b; font-weight: 600; text-transform: uppercase;">Total Egresado (Bs.)</h4>
                <p id="statTotalEgresado" style="margin: 0; font-size: 20px; font-weight: 700; color: #dc2626;">Bs. 0.00</p>
            </div>
        </div>

        <div class="stat-card-custom">
            <div class="stat-icon-custom" style="background-color: rgba(142, 68, 173, 0.12); color: #8e44ad;">
                <i class="fa-solid fa-calendar-day"></i>
            </div>
            <div>
                <h4 style="margin: 0 0 4px 0; font-size: 12px; color: #64748b; font-weight: 600; text-transform: uppercase;">Fecha de Emisión</h4>
                <p style="margin: 0; font-size: 20px; font-weight: 700; color: #0f172a;"><?php echo date('d/m/Y'); ?></p>
            </div>
        </div>
    </div>

    <!-- Toolbar de Filtros Avanzados con Combobox -->
    <div class="toolbar-container" style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 15px;">
        <div class="report-filters-bar" style="display: inline-flex; gap: 12px; align-items: flex-end; flex-wrap: wrap; background-color: #ffffff; padding: 14px 18px; border-radius: 12px; border: 1.5px solid #cbd5e1; box-shadow: 0 2px 8px rgba(0,0,0,0.04);">
            
            <!-- COMBOBOX DE SELECCIÓN DE OTRO GASTO -->
            <div style="min-width: 240px; max-width: 320px;">
                <label for="filterOtroGastoConcepto" style="display: block; font-size: 12.5px; font-weight: 700; color: #1e293b; margin-bottom: 4px;">
                    <i class="fa-solid fa-list-check" style="color: #0284c7; margin-right: 4px;"></i> Seleccionar Otro Gasto:
                </label>
                <select id="filterOtroGastoConcepto" onchange="filtrarReporteOtrosGastos(true);" style="width: 100%; padding: 8px 10px; border: 1.5px solid #0284c7; border-radius: 8px; font-size: 13px; font-weight: 600; background-color: #f0f9ff; color: #0369a1; outline: none; height: 38px;">
                    <option value="all">-- Todos los Gastos --</option>
                    <?php if (!empty($conceptos_gastos)): ?>
                        <?php foreach ($conceptos_gastos as $concepto): ?>
                            <option value="<?php echo htmlspecialchars($concepto); ?>"><?php echo htmlspecialchars($concepto); ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>

            <!-- Desplegable Gestión / Año (Por defecto: Año Actual) -->
            <div style="width: 120px;">
                <label for="filterGestion" style="display: block; font-size: 12.5px; font-weight: 700; color: #334155; margin-bottom: 4px;">Gestión:</label>
                <select id="filterGestion" onchange="filtrarReporteOtrosGastos(true);" class="filter-select" style="width: 100%; padding: 8px 10px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 13px; font-weight: 500; background-color: #f8fafc; color: #0f172a; outline: none; height: 38px;">
                    <option value="all">-- Todas --</option>
                    <?php 
                    for ($y = (int)$anio_actual + 2; $y >= 2018; $y--):
                    ?>
                        <option value="<?php echo $y; ?>" <?php echo ($y == $anio_actual) ? 'selected' : ''; ?>><?php echo $y; ?></option>
                    <?php endfor; ?>
                </select>
            </div>

            <!-- Desplegable Mes (Por defecto: Mes Actual) -->
            <div style="width: 140px;">
                <label for="filterMes" style="display: block; font-size: 12.5px; font-weight: 700; color: #334155; margin-bottom: 4px;">Mes:</label>
                <select id="filterMes" onchange="filtrarReporteOtrosGastos(true);" class="filter-select" style="width: 100%; padding: 8px 10px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 13px; font-weight: 500; background-color: #f8fafc; color: #0f172a; outline: none; height: 38px;">
                    <option value="all">-- Todos los Meses --</option>
                    <?php foreach ($meses_nombres as $num => $nombre): ?>
                        <option value="<?php echo $num; ?>" <?php echo ($num == $mes_actual_num) ? 'selected' : ''; ?>><?php echo $nombre; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Desplegable Estado (Por defecto: Todos) -->
            <div style="width: 120px;">
                <label for="filterEstado" style="display: block; font-size: 12.5px; font-weight: 700; color: #334155; margin-bottom: 4px;">Estado:</label>
                <select id="filterEstado" onchange="filtrarReporteOtrosGastos(true);" class="filter-select" style="width: 100%; padding: 8px 10px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 13px; font-weight: 500; background-color: #f8fafc; color: #0f172a; outline: none; height: 38px;">
                    <option value="all" selected>-- Todos --</option>
                    <option value="Pagado">Pagado</option>
                    <option value="Anulado">Anulado</option>
                </select>
            </div>

            <!-- Botón Limpiar -->
            <div style="display: flex; gap: 8px; align-items: center;">
                <button type="button" id="btnLimpiarReporte" onclick="resetReporteOtrosGastos();" class="btn-secondary" style="padding: 8px 14px; font-size: 13.5px; font-weight: 600; border-radius: 8px; background-color: #64748b; color: #ffffff; border: none; cursor: pointer; display: flex; align-items: center; gap: 6px; height: 38px;" title="Limpiar los filtros">
                    <i class="fa-solid fa-rotate-left"></i> Limpiar
                </button>
            </div>
        </div>

        <!-- Botón Imprimir en PDF -->
        <div style="display: flex; gap: 10px; align-items: center;">
            <button type="button" id="btnImprimirFiltrado" onclick="imprimirReporteFiltrado();" class="btn-primary" style="background-color: #0284c7; border: none; cursor: pointer; padding: 10px 18px; border-radius: 8px; font-weight: 600; font-size: 13.5px; color: #ffffff; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 2px 6px rgba(0,0,0,0.06); height: 42px;" title="Imprimir el reporte con los registros seleccionados">
                <i class="fa-solid fa-print"></i> Imprimir en PDF
            </button>
        </div>
    </div>

    <!-- Título del Concepto en la Parte Superior al Centro -->
    <div id="conceptoHeaderContainer" style="text-align: center; margin-bottom: 14px; display: none;">
        <h3 id="conceptoHeaderTitle" style="margin: 0; font-size: 19px; font-weight: 800; color: #1e293b; text-transform: uppercase; letter-spacing: 0.8px;">COMPRAS</h3>
    </div>

    <!-- Tabla Única de Reporte -->
    <div class="table-container" style="overflow-x: auto; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); background-color: #ffffff;">
        <table class="reporte-excel-table" id="tablaReporteOtrosGastos">
            <thead>
                <tr>
                    <th style="width: 110px;">FECHA</th>
                    <th style="width: 140px;">Nº RECIBO O FACTURA</th>
                    <th>DETALLE</th>
                    <th style="width: 140px;">UNIDAD DE MEDIDA</th>
                    <th style="width: 100px;">CANTIDAD</th>
                    <th style="width: 110px;">PRECIO</th>
                    <th style="width: 130px;">TOTAL</th>
                </tr>
            </thead>
            <tbody id="tbodyReporteOtrosGastos">
                <?php 
                if (count($gastos_rows) > 0): 
                    foreach ($gastos_rows as $row):
                        $fechaPago = !empty($row['fecha_pago']) ? date('d/m/Y', strtotime($row['fecha_pago'])) : '-';
                        $mesNum = !empty($row['fecha_pago']) ? date('m', strtotime($row['fecha_pago'])) : '';
                        $rowFechaAnio = !empty($row['fecha_pago']) ? date('Y', strtotime($row['fecha_pago'])) : '';
                        $rowAnio = !empty($row['gestion']) ? $row['gestion'] : $rowFechaAnio;
                        $estadoStr = $row['estado'] ?? 'Pagado';
                        $monto = floatval($row['monto']);
                        $cantidad = floatval($row['cantidad'] ?? 1);
                        $precio = floatval($row['precio'] ?? ($cantidad > 0 ? $monto / $cantidad : $monto));
                        $nombreGasto = !empty($row['nombre_gasto']) ? $row['nombre_gasto'] : $row['detalle'];
                        $detalleText = !empty($row['detalle']) ? $row['detalle'] : $nombreGasto;
                        $unidadMedida = !empty($row['unidad_medida']) ? $row['unidad_medida'] : '';
                ?>
                    <tr class="gasto-row" 
                        data-concepto="<?php echo htmlspecialchars($nombreGasto); ?>"
                        data-anio="<?php echo htmlspecialchars($rowAnio); ?>"
                        data-fecha-anio="<?php echo htmlspecialchars($rowFechaAnio); ?>"
                        data-mes="<?php echo htmlspecialchars($mesNum); ?>"
                        data-estado="<?php echo htmlspecialchars($estadoStr); ?>"
                        data-monto="<?php echo $monto; ?>">
                        
                        <td style="text-align: center; white-space: nowrap;"><?php echo $fechaPago; ?></td>
                        <td style="text-align: center; font-weight: 600; font-family: monospace;"><?php echo !empty($row['comprobante']) ? htmlspecialchars($row['comprobante']) : '-'; ?></td>
                        <td style="font-weight: 500; color: #1e293b;"><?php echo htmlspecialchars($detalleText); ?></td>
                        <td style="text-align: center; font-weight: 500; text-transform: uppercase;"><?php echo htmlspecialchars($unidadMedida); ?></td>
                        <td style="text-align: right;"><?php echo number_format($cantidad, 2); ?></td>
                        <td style="text-align: right;"><?php echo number_format($precio, 2); ?></td>
                        <td style="text-align: right; font-weight: 700;"><?php echo number_format($monto, 2); ?></td>
                    </tr>
                <?php 
                    endforeach;
                endif; 
                ?>
                <!-- FILAS EN BLANCO PARA ESTADO INICIAL / VACÍO -->
                <tr class="empty-blank-row" style="height: 34px;"><td>&nbsp;</td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
                <tr class="empty-blank-row" style="height: 34px;"><td>&nbsp;</td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
                <tr class="empty-blank-row" style="height: 34px;"><td>&nbsp;</td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
                <tr class="empty-blank-row" style="height: 34px;"><td>&nbsp;</td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
                <tr class="empty-blank-row" style="height: 34px;"><td>&nbsp;</td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="6" class="total-label-cell" id="totalLabelText">
                        TOTAL EGRESOS
                    </td>
                    <td class="total-value-cell" id="totalValueBox">
                        0.00
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<!-- CONTENEDOR EXCLUSIVO PARA IMPRESIÓN (MANEJADO POR JS Y REPORTE.CSS) -->
<div id="printReportView"></div>

<script>
const mesesMap = {
    '01': 'ENERO', '02': 'FEBRERO', '03': 'MARZO', '04': 'ABRIL',
    '05': 'MAYO', '06': 'JUNIO', '07': 'JULIO', '08': 'AGOSTO',
    '09': 'SEPTIEMBRE', '10': 'OCTUBRE', '11': 'NOVIEMBRE', '12': 'DICIEMBRE'
};

let hasSearched = false;

function mostrarReporteVacio() {
    hasSearched = false;
    const rows = document.querySelectorAll('#tbodyReporteOtrosGastos tr.gasto-row');
    rows.forEach(row => { row.style.display = 'none'; });

    const blankRows = document.querySelectorAll('#tbodyReporteOtrosGastos tr.empty-blank-row');
    blankRows.forEach(row => { row.style.display = ''; });

    document.getElementById('statTotalRegistros').innerText = '0';
    document.getElementById('statTotalEgresado').innerText = 'Bs. 0.00';
    document.getElementById('totalValueBox').innerText = '0.00';
    
    const valMes = document.getElementById('filterMes').value;
    const valGestion = document.getElementById('filterGestion').value;
    let labelText = "TOTAL EGRESOS";
    if (valMes !== 'all' && mesesMap[valMes]) {
        labelText += " MES " + mesesMap[valMes];
    }
    if (valGestion !== 'all') {
        labelText += " " + valGestion;
    }
    document.getElementById('totalLabelText').innerText = labelText;

    const headerContainer = document.getElementById('conceptoHeaderContainer');
    if (headerContainer) headerContainer.style.display = 'none';
}

function filtrarReporteOtrosGastos(forceSearch = false) {
    if (forceSearch) {
        hasSearched = true;
    }
    
    if (!hasSearched) {
        mostrarReporteVacio();
        return;
    }

    const valConceptoRaw = document.getElementById('filterOtroGastoConcepto').value;
    const valConcepto = valConceptoRaw.toLowerCase().trim();
    const valGestion = document.getElementById('filterGestion').value.toLowerCase().trim();
    const valMes = document.getElementById('filterMes').value.toLowerCase().trim();
    const valEstado = document.getElementById('filterEstado').value.toLowerCase().trim();
    const searchElem = document.getElementById('searchQuery');
    const searchVal = searchElem ? searchElem.value.toLowerCase().trim() : '';

    const rows = document.querySelectorAll('#tbodyReporteOtrosGastos tr.gasto-row');
    const blankRows = document.querySelectorAll('#tbodyReporteOtrosGastos tr.empty-blank-row');
    
    let visibleCount = 0;
    let sumTotal = 0;
    let firstVisibleConcept = '';
    let allSameConcept = true;

    rows.forEach(row => {
        const rowConcepto = (row.dataset.concepto || '').toLowerCase();
        const rowConceptoOrig = row.dataset.concepto || '';
        const rowAnio = (row.dataset.anio || '').toLowerCase();
        const rowFechaAnio = (row.dataset.fechaAnio || '').toLowerCase();
        const rowMes = (row.dataset.mes || '').toLowerCase();
        const rowEstado = (row.dataset.estado || '').toLowerCase();
        const rowText = row.textContent.toLowerCase();
        const rowMonto = parseFloat(row.dataset.monto) || 0;

        let matches = true;

        if (valConcepto !== 'all' && rowConcepto !== valConcepto) {
            matches = false;
        }
        if (valGestion !== 'all' && rowAnio !== valGestion && rowFechaAnio !== valGestion) {
            matches = false;
        }
        if (valMes !== 'all' && rowMes !== valMes) {
            matches = false;
        }
        if (valEstado !== 'all' && rowEstado !== valEstado) {
            matches = false;
        }
        if (searchVal !== '' && !rowText.includes(searchVal)) {
            matches = false;
        }

        if (matches) {
            row.style.display = '';
            visibleCount++;
            sumTotal += rowMonto;

            if (!firstVisibleConcept) {
                firstVisibleConcept = rowConceptoOrig;
            } else if (firstVisibleConcept.toLowerCase() !== rowConceptoOrig.toLowerCase()) {
                allSameConcept = false;
            }
        } else {
            row.style.display = 'none';
        }
    });

    if (visibleCount > 0) {
        blankRows.forEach(row => { row.style.display = 'none'; });
    } else {
        blankRows.forEach(row => { row.style.display = ''; });
    }

    const formattedSum = sumTotal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

    // Actualizar recuadros de estadísticas en pantalla
    document.getElementById('statTotalRegistros').innerText = visibleCount.toLocaleString();
    document.getElementById('statTotalEgresado').innerText = 'Bs. ' + formattedSum;
    
    // Actualizar recuadro amarillo del footer
    document.getElementById('totalValueBox').innerText = formattedSum;

    // Actualizar etiqueta del total según filtro activo
    let labelText = "TOTAL EGRESOS";
    if (valMes !== 'all' && mesesMap[valMes]) {
        labelText += " MES " + mesesMap[valMes];
    }
    if (valGestion !== 'all') {
        labelText += " " + valGestion;
    }
    if (valConcepto !== 'all') {
        labelText += " (" + valConceptoRaw.toUpperCase() + ")";
    }
    document.getElementById('totalLabelText').innerText = labelText;

    // Actualizar Título del Concepto en la Parte Superior al Centro
    const headerContainer = document.getElementById('conceptoHeaderContainer');
    const headerTitle = document.getElementById('conceptoHeaderTitle');
    if (headerContainer && headerTitle) {
        if (valConcepto !== 'all') {
            headerTitle.innerText = valConceptoRaw.toUpperCase();
            headerContainer.style.display = 'block';
        } else if (visibleCount > 0 && allSameConcept && firstVisibleConcept) {
            headerTitle.innerText = firstVisibleConcept.toUpperCase();
            headerContainer.style.display = 'block';
        } else {
            headerContainer.style.display = 'none';
        }
    }
}

function resetReporteOtrosGastos() {
    document.getElementById('filterOtroGastoConcepto').value = 'all';
    const searchElem = document.getElementById('searchQuery');
    if (searchElem) searchElem.value = '';
    document.getElementById('filterGestion').value = '<?php echo $anio_actual; ?>';
    document.getElementById('filterMes').value = '<?php echo $mes_actual_num; ?>';
    document.getElementById('filterEstado').value = 'all';
    mostrarReporteVacio();
}

function generarHtmlImpresion() {
    const printContainer = document.getElementById('printReportView');
    if (!printContainer) return;

    const visibleRows = Array.from(document.querySelectorAll('#tbodyReporteOtrosGastos tr.gasto-row'))
        .filter(r => r.style.display !== 'none');

    let totalSum = 0;
    let rowsHtml = '';

    if (visibleRows.length > 0) {
        visibleRows.forEach(r => {
            const cells = r.querySelectorAll('td');
            const fecha = cells[0] ? cells[0].innerHTML : '-';
            const comprobante = cells[1] ? cells[1].innerHTML : '-';
            const detalle = cells[2] ? cells[2].innerHTML : '-';
            const unidad = cells[3] ? cells[3].innerHTML : '';
            const cantidad = cells[4] ? cells[4].innerHTML : '1.00';
            const precio = cells[5] ? cells[5].innerHTML : '0.00';
            const total = cells[6] ? cells[6].innerHTML : '0.00';

            const montoNum = parseFloat(r.dataset.monto) || 0;
            const estadoStr = (r.dataset.estado || 'pagado').toLowerCase();
            if (estadoStr === 'pagado') {
                totalSum += montoNum;
            }

            rowsHtml += `
                <tr>
                    <td style="text-align: center; white-space: nowrap;">${fecha}</td>
                    <td style="text-align: center; font-family: monospace;">${comprobante}</td>
                    <td>${detalle}</td>
                    <td style="text-align: center; text-transform: uppercase;">${unidad}</td>
                    <td style="text-align: right;">${cantidad}</td>
                    <td style="text-align: right;">${precio}</td>
                    <td style="text-align: right; font-weight: 700;">${total}</td>
                </tr>
            `;
        });
    } else {
        rowsHtml = `
            <tr style="height: 34px;"><td>&nbsp;</td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
            <tr style="height: 34px;"><td>&nbsp;</td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
            <tr style="height: 34px;"><td>&nbsp;</td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
        `;
    }

    const formattedTotal = totalSum.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    const labelText = document.getElementById('totalLabelText') ? document.getElementById('totalLabelText').innerText : 'TOTAL EGRESOS';
    const dateStr = new Date().toLocaleDateString('es-BO', { day: '2-digit', month: '2-digit', year: 'numeric' });
    const timeStr = new Date().toLocaleTimeString('es-BO');

    const headerTitleElem = document.getElementById('conceptoHeaderTitle');
    const headerContainerElem = document.getElementById('conceptoHeaderContainer');
    const isHeaderVisible = headerContainerElem && headerContainerElem.style.display !== 'none';
    const conceptoText = (headerTitleElem && isHeaderVisible) ? headerTitleElem.innerText : '';
    const printConceptoHtml = conceptoText 
        ? `<h4 style="font-size: 15px; margin: 4px 0 0 0; color: #1e293b; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px;">${conceptoText}</h4>`
        : '';

    printContainer.innerHTML = `
        <div class="print-page-block">
            <div class="print-header" style="display: block !important; margin-bottom: 10px; border-bottom: 2px solid #27AE60; padding-bottom: 4px;">
                <div class="print-header-top" style="display: flex; align-items: center; justify-content: space-between;">
                    <img src="../imagenes/institucinal/logo.png" alt="CEREMA Logo" class="print-logo" style="max-height: 42px; width: auto;">
                    <div class="print-title-group" style="text-align: center; flex-grow: 1;">
                        <h2 style="font-size: 14px; margin: 0; color: #1a252f; text-transform: uppercase;">CENTRO DE RESIDENTES MAIRANEÑOS - CEREMA</h2>
                        <h3 style="font-size: 11px; margin: 2px 0 0 0; color: #27AE60; font-weight: 600;">REPORTE OFICIAL DE OTROS GASTOS</h3>
                        ${printConceptoHtml}
                    </div>
                    <div style="width: 50px;"></div>
                </div>
                <div class="print-meta" style="font-size: 10px; color: #333; display: flex; justify-content: space-between; margin-top: 4px;">
                    <span><strong>Fecha de Generación:</strong> ${dateStr} ${timeStr}</span>
                    <span><strong>Total Registros:</strong> ${visibleRows.length} | <strong>Total Egresado:</strong> Bs. ${formattedTotal}</span>
                </div>
            </div>

            <table class="reporte-excel-table" style="width: 100%; border-collapse: collapse; border: 1.5px solid #2b5797; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important;">
                <thead>
                    <tr style="-webkit-print-color-adjust: exact !important; print-color-adjust: exact !important;">
                        <th style="width: 100px; background-color: #8ea9db !important; color: #000000 !important; font-weight: 700; text-transform: uppercase; border: 1px solid #2b5797; padding: 6px 4px; text-align: center; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important;">FECHA</th>
                        <th style="width: 130px; background-color: #8ea9db !important; color: #000000 !important; font-weight: 700; text-transform: uppercase; border: 1px solid #2b5797; padding: 6px 4px; text-align: center; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important;">Nº RECIBO O FACTURA</th>
                        <th style="background-color: #8ea9db !important; color: #000000 !important; font-weight: 700; text-transform: uppercase; border: 1px solid #2b5797; padding: 6px 4px; text-align: center; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important;">DETALLE</th>
                        <th style="width: 130px; background-color: #8ea9db !important; color: #000000 !important; font-weight: 700; text-transform: uppercase; border: 1px solid #2b5797; padding: 6px 4px; text-align: center; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important;">UNIDAD DE MEDIDA</th>
                        <th style="width: 90px; background-color: #8ea9db !important; color: #000000 !important; font-weight: 700; text-transform: uppercase; border: 1px solid #2b5797; padding: 6px 4px; text-align: center; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important;">CANTIDAD</th>
                        <th style="width: 100px; background-color: #8ea9db !important; color: #000000 !important; font-weight: 700; text-transform: uppercase; border: 1px solid #2b5797; padding: 6px 4px; text-align: center; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important;">PRECIO</th>
                        <th style="width: 120px; background-color: #8ea9db !important; color: #000000 !important; font-weight: 700; text-transform: uppercase; border: 1px solid #2b5797; padding: 6px 4px; text-align: center; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important;">TOTAL</th>
                    </tr>
                </thead>
                <tbody>
                    ${rowsHtml}
                </tbody>
                <tfoot>
                    <tr style="-webkit-print-color-adjust: exact !important; print-color-adjust: exact !important;">
                        <td colspan="6" class="total-label-cell" style="background-color: #8ea9db !important; color: #000000 !important; font-weight: 800 !important; text-transform: uppercase; text-align: center !important; border: 1px solid #2b5797 !important; padding: 8px 6px; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important;">
                            ${labelText}
                        </td>
                        <td class="total-value-cell" style="background-color: #ffff00 !important; color: #000000 !important; font-weight: 800 !important; text-align: right !important; border: 1.5px solid #2b5797 !important; padding: 8px 6px; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important;">
                            ${formattedTotal}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    `;
}

function imprimirReporteFiltrado() {
    if (!hasSearched) {
        filtrarReporteOtrosGastos(true);
    }
    generarHtmlImpresion();
    window.print();
}

document.addEventListener('DOMContentLoaded', function() {
    mostrarReporteVacio();
});
</script>

<link rel="stylesheet" href="../css/reporte.css">
<?php include 'footer.php'; ?>
