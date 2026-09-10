<?php
// View/vreporte_egresos.php
require_once __DIR__ . '/header.php';

$egresos_rows = [];
if (isset($egresos) && $egresos->rowCount() > 0) {
    $egresos_rows = $egresos->fetchAll(PDO::FETCH_ASSOC);
}
$records_per_print_page = 40;
$chunks = array_chunk($egresos_rows, $records_per_print_page);

$tipo = $egreso_tipo ?? 'sueldo';

// Definir subtítulo y labels dinámicos según el tipo de egreso
$subtitulo = "Listado oficial de egresos correspondientes a ";
if ($tipo === 'sueldo') {
    $subtitulo .= "Sueldos y Salarios del personal de CEREMA";
} elseif ($tipo === 'servicios') {
    $subtitulo .= "Servicios Básicos (Agua, Luz, Teléfono, etc.)";
} elseif ($tipo === 'extraordinario') {
    $subtitulo .= "Gastos Extraordinarios institucionales";
} elseif ($tipo === 'especiales') {
    $subtitulo .= "Gastos Especiales institucionales";
} else {
    $subtitulo .= "Otros Gastos de la institución";
}
?>

<!-- VISTA EN PANTALLA (NO IMPRESIÓN) -->
<div id="screenReportView">
    <div class="dashboard-header">
        <div class="header-actions" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
            <div>
                <h1 class="page-title"><?php echo htmlspecialchars($titulo_reporte ?? 'Reporte de Egresos'); ?></h1>
                <p class="page-subtitle"><?php echo htmlspecialchars($subtitulo); ?></p>
            </div>
        </div>
    </div>

    <!-- Tarjetas de Estadísticas del Reporte -->
    <div class="report-stats-grid">
        <div class="report-stat-card">
            <div class="report-stat-icon active-users" style="background-color: rgba(239, 68, 68, 0.1); color: #dc2626;">
                <i class="fa-solid fa-receipt"></i>
            </div>
            <div class="report-stat-info">
                <h4>Total Egresos Registrados</h4>
                <p><?php echo number_format($stats['total_registros'] ?? 0); ?></p>
            </div>
        </div>

        <div class="report-stat-card">
            <div class="report-stat-icon shares" style="background-color: rgba(220, 38, 38, 0.1); color: #b91c1c;">
                <i class="fa-solid fa-money-bill-transfer"></i>
            </div>
            <div class="report-stat-info">
                <h4>Total Egresado (Bs.)</h4>
                <p>Bs. <?php echo number_format($stats['total_egresado'] ?? 0, 2); ?></p>
            </div>
        </div>

        <div class="report-stat-card">
            <div class="report-stat-icon date">
                <i class="fa-solid fa-calendar-day"></i>
            </div>
            <div class="report-stat-info">
                <h4>Fecha de Emisión</h4>
                <p><?php echo date('d/m/Y'); ?></p>
            </div>
        </div>
    </div>

    <!-- Toolbar de Filtros Avanzados -->
    <div class="toolbar-container" style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 15px;">
        <div class="report-filters-bar" style="display: inline-flex; gap: 12px; align-items: flex-end; flex-wrap: wrap; background-color: #ffffff; padding: 14px 18px; border-radius: 12px; border: 1.5px solid #cbd5e1; box-shadow: 0 2px 8px rgba(0,0,0,0.04);">
            
            <!-- Búsqueda por Texto -->
            <div style="width: 260px;">
                <label for="searchQuery" style="display: block; font-size: 12.5px; font-weight: 700; color: #334155; margin-bottom: 4px;">Buscar Concepto / Recibo:</label>
                <input type="text" id="searchQuery" placeholder="Buscar por detalle, comprobante..." style="width: 100%; padding: 8px 10px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 13px; font-weight: 500; background-color: #f8fafc; color: #0f172a; outline: none; box-sizing: border-box;">
            </div>

            <!-- Desplegable Gestión / Año -->
            <div style="width: 130px;">
                <label for="filterGestion" style="display: block; font-size: 12.5px; font-weight: 700; color: #334155; margin-bottom: 4px;">Gestión:</label>
                <select id="filterGestion" class="filter-select" style="width: 100%; padding: 8px 10px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 13px; font-weight: 500; background-color: #f8fafc; color: #0f172a; outline: none;">
                    <option value="all">-- Todas --</option>
                    <?php 
                    $currentAnio = date('Y');
                    $maxAnio = (int)$currentAnio + 3;
                    for ($y = $maxAnio; $y >= 2018; $y--):
                    ?>
                        <option value="<?php echo $y; ?>" <?php echo ($y == $currentAnio) ? 'selected' : ''; ?>><?php echo $y; ?></option>
                    <?php endfor; ?>
                </select>
            </div>

            <!-- Desplegable Estado -->
            <div style="width: 140px;">
                <label for="filterEstado" style="display: block; font-size: 12.5px; font-weight: 700; color: #334155; margin-bottom: 4px;">Estado:</label>
                <select id="filterEstado" class="filter-select" style="width: 100%; padding: 8px 10px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 13px; font-weight: 500; background-color: #f8fafc; color: #0f172a; outline: none;">
                    <option value="all">-- Todos --</option>
                    <option value="Pagado">Pagado</option>
                    <option value="Pendiente">Pendiente</option>
                </select>
            </div>

            <!-- Botones Buscar y Limpiar -->
            <div style="display: flex; gap: 8px; align-items: center;">
                <button type="button" id="btnBuscarReporte" onclick="triggerReportSearch();" class="btn-primary" style="padding: 8px 16px; font-size: 13.5px; font-weight: 600; border-radius: 8px; background-color: #27AE60; color: #ffffff; border: none; cursor: pointer; display: flex; align-items: center; gap: 6px; height: 38px; transition: background-color 0.2s;" title="Buscar con los filtros seleccionados">
                    <i class="fa-solid fa-magnifying-glass"></i> Buscar
                </button>
                <button type="button" id="btnLimpiarReporte" onclick="resetReportFilters();" class="btn-secondary" disabled style="padding: 8px 14px; font-size: 13.5px; font-weight: 600; border-radius: 8px; background-color: #64748b; color: #ffffff; border: none; cursor: not-allowed; opacity: 0.4; display: flex; align-items: center; gap: 6px; height: 38px; transition: all 0.2s ease;" title="Limpiar los filtros y borrar la lista">
                    <i class="fa-solid fa-rotate-left"></i> Limpiar
                </button>
            </div>
        </div>

        <!-- Botón Imprimir en PDF -->
        <div style="display: flex; gap: 10px; align-items: center;">
            <button type="button" id="btnImprimirFiltrado" onclick="window.print();" class="btn-primary" style="background-color: #0284c7; border: none; cursor: pointer; padding: 10px 18px; border-radius: 8px; font-weight: 600; font-size: 13.5px; color: #ffffff; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 2px 6px rgba(0,0,0,0.06); height: 42px;" title="Imprimir el reporte en PDF">
                <i class="fa-solid fa-print"></i> Imprimir en PDF
            </button>
        </div>
    </div>

    <!-- Tabla de Resultados Interactivas (Pantalla) -->
    <div class="table-container">
        <table class="data-table" id="reporteSociosTable">
            <thead>
                <tr>
                    <th style="width: 45px; text-align: center;">N°</th>
                    <th style="width: 120px;">N° Comprobante</th>
                    <th style="width: 80px; text-align: center;">Gestión</th>
                    <?php if ($tipo === 'sueldo'): ?>
                        <th>Cargo</th>
                        <th>Empleado</th>
                        <th style="width: 100px; text-align: center;">Mes</th>
                    <?php elseif ($tipo === 'servicios'): ?>
                        <th>Tipo de Servicio</th>
                        <th style="width: 110px; text-align: center;">Mes Pago</th>
                    <?php elseif ($tipo === 'extraordinario' || $tipo === 'especiales'): ?>
                        <th>Motivo</th>
                        <th>Detalle / Descripción</th>
                    <?php else: ?>
                        <th>Detalle / Concepto del Gasto</th>
                    <?php endif; ?>
                    <th style="width: 120px; text-align: right;">Monto (Bs.)</th>
                    <th style="width: 110px; text-align: center;">Fecha Pago</th>
                    <th style="width: 100px; text-align: center;">Estado</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                if (count($egresos_rows) > 0): 
                    $count = 1;
                    foreach ($egresos_rows as $row):
                        $fechaPago = !empty($row['fecha_pago']) ? date('d/m/Y', strtotime($row['fecha_pago'])) : '-';
                        $rowAnio = !empty($row['gestion']) ? $row['gestion'] : (!empty($row['fecha_pago']) ? date('Y', strtotime($row['fecha_pago'])) : '');
                        $estadoStr = $row['estado'] ?? 'Pagado';
                        $estadoClass = (strtolower($estadoStr) === 'pagado' || strtolower($estadoStr) === 'cobrado') ? 'activo' : 'inactivo';
                ?>
                    <tr data-anio="<?php echo htmlspecialchars($rowAnio); ?>" data-estado="<?php echo htmlspecialchars($estadoStr); ?>">
                        <td style="text-align: center; font-weight: 500;"><?php echo $count++; ?></td>
                        <td style="font-weight: 600; font-family: monospace; font-size: 13px; color: var(--primary);"><?php echo !empty($row['comprobante']) ? htmlspecialchars($row['comprobante']) : '-'; ?></td>
                        <td style="text-align: center; font-weight: 700; color: var(--text-main);"><?php echo !empty($rowAnio) ? htmlspecialchars($rowAnio) : '-'; ?></td>
                        
                        <?php if ($tipo === 'sueldo'): ?>
                            <td style="font-weight: 600; color: var(--text-main);"><?php echo htmlspecialchars($row['cargo'] ?? '-'); ?></td>
                            <td style="font-weight: 600; color: var(--text-main);"><?php echo htmlspecialchars($row['empleado'] ?? '-'); ?></td>
                            <td style="text-align: center; font-weight: 500;"><?php echo htmlspecialchars($row['mes'] ?? '-'); ?></td>
                        <?php elseif ($tipo === 'servicios'): ?>
                            <td style="font-weight: 600; color: var(--text-main);"><?php echo htmlspecialchars($row['tipo_servicio'] ?? '-'); ?></td>
                            <td style="text-align: center; font-weight: 500;"><?php echo htmlspecialchars($row['mes_pago'] ?? '-'); ?></td>
                        <?php elseif ($tipo === 'extraordinario' || $tipo === 'especiales'): ?>
                            <td style="font-weight: 600; color: var(--text-main);"><?php echo htmlspecialchars($row['motivo'] ?? '-'); ?></td>
                            <td style="font-weight: 500; color: #475569;"><?php echo htmlspecialchars($row['detalle'] ?? '-'); ?></td>
                        <?php else: ?>
                            <td style="font-weight: 600; color: var(--text-main);"><?php echo htmlspecialchars($row['detalle'] ?? '-'); ?></td>
                        <?php endif; ?>

                        <td style="text-align: right; font-weight: 700; color: #dc2626; font-size: 13.5px;"><?php echo number_format($row['monto'], 2); ?></td>
                        <td style="text-align: center; font-size: 13px; white-space: nowrap;"><?php echo $fechaPago; ?></td>
                        <td style="text-align: center;">
                            <span class="badge-status <?php echo $estadoClass; ?>"><?php echo htmlspecialchars($estadoStr); ?></span>
                        </td>
                    </tr>
                <?php 
                    endforeach;
                else:
                ?>
                    <tr>
                        <td colspan="9" style="text-align: center; padding: 50px 20px; color: var(--text-muted);">
                            <i class="fa-solid fa-money-bill-transfer" style="font-size: 44px; margin-bottom: 12px; display: block; opacity: 0.25;"></i>
                            <span style="font-size: 14px; font-weight: 500;">No hay registros de egresos registrados en esta categoría.</span>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ESTRUCTURA EXCLUSIVA PARA IMPRESIÓN -->
<div id="printReportView">
    <?php 
    if (count($chunks) > 0):
        foreach ($chunks as $chunkIdx => $chunk):
    ?>
        <div class="print-page-block <?php echo ($chunkIdx < count($chunks) - 1) ? 'page-break' : ''; ?>">
            <!-- ENCABEZADO INSTITUCIONAL -->
            <div class="print-header">
                <div class="print-header-top">
                    <img src="../imagenes/institucinal/logo.png" alt="CEREMA Logo" class="print-logo">
                    <div class="print-title-group" style="text-align: center; flex-grow: 1;">
                        <h2>CENTRO DE RESIDENTES MAIRANEÑOS - CEREMA</h2>
                        <h3><?php echo htmlspecialchars($titulo_reporte ?? 'REPORTE OFICIAL DE EGRESOS'); ?></h3>
                    </div>
                    <div style="width: 60px;"></div>
                </div>
                <div class="print-meta">
                    <span><strong>Fecha de Generación:</strong> <?php echo date('d/m/Y H:i:s'); ?></span>
                    <span><strong>Total Registros:</strong> <?php echo number_format($stats['total_registros'] ?? 0); ?> | <strong>Total Egresado:</strong> Bs. <?php echo number_format($stats['total_egresado'] ?? 0, 2); ?></span>
                </div>
            </div>

            <!-- TABLA DE IMPRESIÓN -->
            <table class="data-table print-table">
                <thead>
                    <tr>
                        <th style="width: 38px; text-align: center;">N°</th>
                        <th style="width: 95px;">N° Comprobante</th>
                        <th style="width: 60px; text-align: center;">Gestión</th>
                        <?php if ($tipo === 'sueldo'): ?>
                            <th>Cargo</th>
                            <th>Empleado</th>
                            <th style="width: 80px; text-align: center;">Mes</th>
                        <?php elseif ($tipo === 'servicios'): ?>
                            <th>Tipo de Servicio</th>
                            <th style="width: 85px; text-align: center;">Mes Pago</th>
                        <?php elseif ($tipo === 'extraordinario' || $tipo === 'especiales'): ?>
                            <th>Motivo</th>
                            <th>Detalle / Descripción</th>
                        <?php else: ?>
                            <th>Detalle / Concepto del Gasto</th>
                        <?php endif; ?>
                        <th style="width: 95px; text-align: right;">Monto (Bs.)</th>
                        <th style="width: 90px; text-align: center;">Fecha Pago</th>
                        <th style="width: 70px; text-align: center;">Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    foreach ($chunk as $rowIdx => $row):
                        $globalCount = ($chunkIdx * $records_per_print_page) + $rowIdx + 1;
                        $fechaPago = !empty($row['fecha_pago']) ? date('d/m/Y', strtotime($row['fecha_pago'])) : '-';
                        $rowAnio = !empty($row['gestion']) ? $row['gestion'] : (!empty($row['fecha_pago']) ? date('Y', strtotime($row['fecha_pago'])) : '-');
                        $estadoStr = $row['estado'] ?? 'Pagado';
                    ?>
                        <tr>
                            <td style="text-align: center; font-weight: 500;"><?php echo $globalCount; ?></td>
                            <td style="font-weight: 600; font-family: monospace; font-size: 11px;"><?php echo !empty($row['comprobante']) ? htmlspecialchars($row['comprobante']) : '-'; ?></td>
                            <td style="text-align: center; font-weight: 600;"><?php echo htmlspecialchars($rowAnio); ?></td>
                            
                            <?php if ($tipo === 'sueldo'): ?>
                                <td style="font-size: 11px; font-weight: 600; color: #000;"><?php echo htmlspecialchars($row['cargo'] ?? '-'); ?></td>
                                <td style="font-size: 11px; font-weight: 600; color: #000;"><?php echo htmlspecialchars($row['empleado'] ?? '-'); ?></td>
                                <td style="text-align: center; font-size: 10.5px;"><?php echo htmlspecialchars($row['mes'] ?? '-'); ?></td>
                            <?php elseif ($tipo === 'servicios'): ?>
                                <td style="font-size: 11px; font-weight: 600; color: #000;"><?php echo htmlspecialchars($row['tipo_servicio'] ?? '-'); ?></td>
                                <td style="text-align: center; font-size: 10.5px;"><?php echo htmlspecialchars($row['mes_pago'] ?? '-'); ?></td>
                            <?php elseif ($tipo === 'extraordinario' || $tipo === 'especiales'): ?>
                                <td style="font-size: 11px; font-weight: 600; color: #000;"><?php echo htmlspecialchars($row['motivo'] ?? '-'); ?></td>
                                <td style="font-size: 10.5px; color: #333;"><?php echo htmlspecialchars($row['detalle'] ?? '-'); ?></td>
                            <?php else: ?>
                                <td style="font-size: 11px; font-weight: 600; color: #000;"><?php echo htmlspecialchars($row['detalle'] ?? '-'); ?></td>
                            <?php endif; ?>

                            <td style="text-align: right; font-weight: 700; color: #000; font-size: 11px;"><?php echo number_format($row['monto'], 2); ?></td>
                            <td style="text-align: center; font-size: 10.5px; white-space: nowrap;"><?php echo $fechaPago; ?></td>
                            <td style="text-align: center; font-size: 10.5px;"><?php echo htmlspecialchars($estadoStr); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php 
        endforeach;
    endif;
    ?>
</div>

<link rel="stylesheet" href="../css/reporte.css">
<script>
function triggerReportSearch() {
    const q = document.getElementById("searchQuery").value.trim().toLowerCase();
    const g = document.getElementById("filterGestion").value;
    const e = document.getElementById("filterEstado").value.toLowerCase();

    const rows = document.querySelectorAll("#reporteSociosTable tbody tr");
    let hasFilter = (q !== "" || g !== "all" || e !== "all");

    document.getElementById("btnLimpiarReporte").disabled = !hasFilter;
    document.getElementById("btnLimpiarReporte").style.opacity = hasFilter ? "1" : "0.4";
    document.getElementById("btnLimpiarReporte").style.cursor = hasFilter ? "pointer" : "not-allowed";

    rows.forEach(r => {
        if (r.id === "emptyReportRow") return;
        const text = r.textContent.toLowerCase();
        const rowAnio = r.getAttribute("data-anio");
        const rowEstado = (r.getAttribute("data-estado") || "").toLowerCase();

        const matchQ = (q === "" || text.includes(q));
        const matchG = (g === "all" || rowAnio === g);
        const matchE = (e === "all" || rowEstado.includes(e));

        if (matchQ && matchG && matchE) {
            r.style.display = "";
        } else {
            r.style.display = "none";
        }
    });
}

function resetReportFilters() {
    document.getElementById("searchQuery").value = "";
    document.getElementById("filterGestion").value = "all";
    document.getElementById("filterEstado").value = "all";
    triggerReportSearch();
}
</script>

<?php include 'footer.php'; ?>
