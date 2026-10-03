<?php
// View/vreporte_pagos.php
require_once __DIR__ . '/header.php';

// Obtener todos los registros en array para el chunking de impresión (40 registros por hoja llenando la página hasta abajo)
$pagos_rows = [];
if (isset($pagos) && $pagos->rowCount() > 0) {
    $pagos_rows = $pagos->fetchAll(PDO::FETCH_ASSOC);
}
$records_per_print_page = 40;
$chunks = array_chunk($pagos_rows, $records_per_print_page);
$categoria_nombre = $pago_categoria ?? 'Pagos';

$btn_planilla_label = "Imprimir Planilla Anual Mensualidades";
$tipo_cat = "mensualidades";
if (stripos($categoria_nombre, 'Extraordinario') !== false) {
    $btn_planilla_label = "Imprimir Planilla Anual Aporte Extraordinario";
    $tipo_cat = "extraordinarios";
} else if (stripos($categoria_nombre, 'Especial') !== false) {
    $btn_planilla_label = "Imprimir Planilla Anual Aporte Especial";
    $tipo_cat = "especiales";
}
?>

<!-- VISTA EN PANTALLA (NO IMPRESIÓN) -->
<div id="screenReportView">
    <div class="dashboard-header">
        <div class="header-actions" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
            <div>
                <h1 class="page-title">Reporte de Pagos - <?php echo htmlspecialchars($categoria_nombre); ?></h1>
                <p class="page-subtitle">Listado oficial de pagos registrados en la fraternidad CEREMA</p>
            </div>
        </div>
    </div>

    <!-- Tarjetas de Estadísticas del Reporte -->
    <div class="report-stats-grid">
        <div class="report-stat-card">
            <div class="report-stat-icon active-users">
                <i class="fa-solid fa-receipt"></i>
            </div>
            <div class="report-stat-info">
                <h4>Total Pagos Registrados</h4>
                <p><?php echo number_format($stats['total_registros'] ?? 0); ?></p>
            </div>
        </div>

        <div class="report-stat-card">
            <div class="report-stat-icon shares" style="background-color: rgba(39, 174, 96, 0.1); color: #27ae60;">
                <i class="fa-solid fa-sack-dollar"></i>
            </div>
            <div class="report-stat-info">
                <h4>Total Recaudado (Bs.)</h4>
                <p>Bs. <?php echo number_format($stats['total_recaudado'] ?? 0, 2); ?></p>
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

    <!-- Toolbar de Filtros Avanzados (Desplegables a la izquierda + Botón Planilla Anual a la derecha) -->
    <div class="toolbar-container" style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 15px;">
        <div class="report-filters-bar" style="display: inline-flex; gap: 12px; align-items: flex-end; flex-wrap: wrap; background-color: #ffffff; padding: 14px 18px; border-radius: 12px; border: 1.5px solid #cbd5e1; box-shadow: 0 2px 8px rgba(0,0,0,0.04);">
            
            <!-- Desplegable Asociados (Compacto) -->
            <div style="width: 280px;">
                <label for="filterSocio" style="display: block; font-size: 12.5px; font-weight: 700; color: #334155; margin-bottom: 4px;">Asociado:</label>
                <select id="filterSocio" class="filter-select" style="width: 100%; padding: 8px 10px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 13px; font-weight: 500; background-color: #f8fafc; color: #0f172a; outline: none;">
                    <option value="all">-- Todos los Asociados --</option>
                    <?php if (!empty($todos_socios)): ?>
                        <?php foreach ($todos_socios as $s): ?>
                            <?php $ci_full = $s['ci'] . (!empty($s['complemento']) ? '-' . $s['complemento'] : ''); ?>
                            <option value="<?php echo $s['id_socio']; ?>">
                                <?php echo htmlspecialchars($s['ap_paterno'] . ' ' . $s['ap_materno'] . ' ' . $s['nombre'] . ' (' . $ci_full . ')'); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>

            <!-- Desplegable Meses (Compacto) -->
            <div style="width: 140px;">
                <label for="filterMes" style="display: block; font-size: 12.5px; font-weight: 700; color: #334155; margin-bottom: 4px;">Mes:</label>
                <select id="filterMes" class="filter-select" style="width: 100%; padding: 8px 10px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 13px; font-weight: 500; background-color: #f8fafc; color: #0f172a; outline: none;">
                    <option value="all">-- Todos --</option>
                    <option value="Enero">Enero</option>
                    <option value="Febrero">Febrero</option>
                    <option value="Marzo">Marzo</option>
                    <option value="Abril">Abril</option>
                    <option value="Mayo">Mayo</option>
                    <option value="Junio">Junio</option>
                    <option value="Julio">Julio</option>
                    <option value="Agosto">Agosto</option>
                    <option value="Septiembre">Septiembre</option>
                    <option value="Octubre">Octubre</option>
                    <option value="Noviembre">Noviembre</option>
                    <option value="Diciembre">Diciembre</option>
                </select>
            </div>

            <!-- Desplegable Gestión / Año (Compacto) -->
            <div style="width: 110px;">
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

        <!-- Botones de Acción a la Derecha (Imprimir en PDF & Imprimir Planilla Anual) -->
        <div style="display: flex; gap: 10px; align-items: center;">
            <!-- Botón 1: Imprimir en PDF (Deshabilitado inicialmente, se habilita al elegir algún filtro) -->
            <button type="button" id="btnImprimirFiltrado" onclick="printReport();" class="btn-primary" disabled style="background-color: #0284c7; border: none; cursor: not-allowed; opacity: 0.4; padding: 10px 18px; border-radius: 8px; font-weight: 600; font-size: 13.5px; color: #ffffff; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 2px 6px rgba(0,0,0,0.06); height: 42px; transition: all 0.2s ease;" title="Imprimir los resultados filtrados en PDF">
                <i class="fa-solid fa-print"></i> Imprimir en PDF
            </button>

            <!-- Botón 2: Imprimir Planilla Anual (Habilitado inicialmente, se deshabilita al elegir algún filtro) -->
            <button type="button" id="btnPlanillaAnual" onclick="imprimirPlanillaAnualDirecto('<?php echo $tipo_cat; ?>');" class="btn-export" style="background-color: #0d9488; border: none; cursor: pointer; opacity: 1; padding: 10px 18px; border-radius: 8px; font-weight: 600; font-size: 13.5px; color: #ffffff; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 2px 6px rgba(0,0,0,0.06); height: 42px; transition: all 0.2s ease;" title="<?php echo htmlspecialchars($btn_planilla_label); ?>">
                <i class="fa-solid fa-table-cells"></i> <?php echo htmlspecialchars($btn_planilla_label); ?>
            </button>
        </div>
    </div>

    <!-- Tabla de Resultados Interactivas (Pantalla) -->
    <div class="table-container">
        <table class="data-table" id="reporteSociosTable">
            <thead>
                <tr>
                    <th style="width: 45px; text-align: center;">N°</th>
                    <th style="width: 100px;">N° Recibo</th>
                    <th style="width: 100px;">C.I.</th>
                    <th style="white-space: nowrap;">Nombre Completo</th>
                    <th style="width: 70px; text-align: center;">Acción</th>
                    <th>Detalle / Concepto</th>
                    <th style="width: 110px; text-align: right;">Monto (Bs.)</th>
                    <th style="width: 120px; text-align: center;">Fecha Pago</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                if (count($pagos_rows) > 0): 
                    $count = 1;
                    foreach ($pagos_rows as $row):
                        $nombreCompleto = htmlspecialchars($row['ap_paterno'] . ' ' . $row['ap_materno'] . ', ' . $row['nombre']);
                        $fechaPago = !empty($row['fecha_pago']) ? date('d/m/Y H:i', strtotime($row['fecha_pago'])) : '-';
                        $detalle = isset($row['mes']) ? htmlspecialchars($row['mes'] . ' ' . $row['anio']) : htmlspecialchars($row['motivo'] ?? '-');
                        $rowAnio = !empty($row['anio']) ? $row['anio'] : (!empty($row['fecha_pago']) ? date('Y', strtotime($row['fecha_pago'])) : '');
                        $rowMes = !empty($row['mes']) ? $row['mes'] : '';
                ?>
                    <tr data-id-socio="<?php echo $row['id_socio']; ?>" data-mes="<?php echo htmlspecialchars($rowMes); ?>" data-anio="<?php echo htmlspecialchars($rowAnio); ?>">
                        <td style="text-align: center; font-weight: 500;"><?php echo $count++; ?></td>
                        <td style="font-weight: 600; font-family: monospace; font-size: 13px; color: var(--primary);"></td>
                        <td style="font-weight: 600; font-family: monospace; font-size: 13px; white-space: nowrap;"></td>
                        <td style="font-weight: 600; color: var(--text-main); white-space: nowrap;"><?php echo $nombreCompleto; ?></td>
                        <td style="text-align: center; font-weight: 700; color: var(--text-main);"><?php echo htmlspecialchars($row['numero_accion'] ?? 1); ?></td>
                        <td><?php echo $detalle; ?></td>
                        <td style="text-align: right; font-weight: 700; color: #27ae60; font-size: 13.5px;"><?php echo number_format($row['monto'], 2); ?></td>
                        <td style="text-align: center; font-size: 13px; white-space: nowrap;"></td>
                    </tr>
                <?php 
                    endforeach;
                endif;
                ?>
                <tr id="emptyReportRow">
                    <td colspan="8" style="text-align: center; padding: 50px 20px; color: var(--text-muted);">
                        <i class="fa-solid fa-receipt" style="font-size: 44px; margin-bottom: 12px; display: block; opacity: 0.25;"></i>
                        <span id="emptyReportText" style="font-size: 14px; font-weight: 500;">Seleccione los filtros deseados y presione "Buscar" para consultar los registros.</span>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Controles de Paginación Eliminados -->
</div>

<!-- ESTRUCTURA EXCLUSIVA PARA IMPRESIÓN (EXACTAMENTE 40 REGISTROS POR HOJA EXTENDIDOS HASTA ABAJO) -->
<div id="printReportView">
    <?php 
    if (count($chunks) > 0):
        foreach ($chunks as $chunkIdx => $chunk):
    ?>
        <div class="print-page-block <?php echo ($chunkIdx < count($chunks) - 1) ? 'page-break' : ''; ?>">
            <!-- ENCABEZADO INSTITUCIONAL EN CADA HOJA -->
            <div class="print-header">
                <div class="print-header-top">
                    <img src="../imagenes/institucinal/logo.png" alt="CEREMA Logo" class="print-logo">
                    <div class="print-title-group" style="text-align: center; flex-grow: 1;">
                        <h2>CENTRO DE RESIDENTES MAIRANEÑOS - CEREMA</h2>
                        <h3><?php echo htmlspecialchars($titulo_reporte ?? 'REPORTE OFICIAL DE PAGOS'); ?></h3>
                    </div>
                    <div style="width: 60px;"></div>
                </div>
                <div class="print-meta">
                    <span><strong>Fecha de Generación:</strong> <?php echo date('d/m/Y H:i:s'); ?></span>
                    <span><strong>Total Registros:</strong> <?php echo number_format($stats['total_registros'] ?? 0); ?> | <strong>Total Recaudado:</strong> Bs. <?php echo number_format($stats['total_recaudado'] ?? 0, 2); ?></span>
                </div>
            </div>

            <!-- TABLA DE IMPRESIÓN AJUSTADA PARA 40 REGISTROS EXTENDIDOS -->
            <table class="data-table print-table">
                <thead>
                    <tr>
                        <th style="width: 38px; text-align: center;">N°</th>
                        <th style="width: 85px;">N° Recibo</th>
                        <th style="width: 90px;">C.I.</th>
                        <th style="white-space: nowrap;">Nombre Completo</th>
                        <th style="width: 55px; text-align: center;">Acción</th>
                        <th>Detalle / Concepto</th>
                        <th style="width: 90px; text-align: right;">Monto (Bs.)</th>
                        <th style="width: 95px; text-align: center;">Fecha Pago</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    foreach ($chunk as $rowIdx => $row):
                        $globalCount = ($chunkIdx * $records_per_print_page) + $rowIdx + 1;
                        $nombreCompleto = htmlspecialchars($row['ap_paterno'] . ' ' . $row['ap_materno'] . ', ' . $row['nombre']);
                        $fechaPago = !empty($row['fecha_pago']) ? date('d/m/Y', strtotime($row['fecha_pago'])) : '-';
                        $detalle = isset($row['mes']) ? htmlspecialchars($row['mes'] . ' ' . $row['anio']) : htmlspecialchars($row['motivo'] ?? '-');
                    ?>
                        <tr>
                            <td style="text-align: center; font-weight: 500;"><?php echo $globalCount; ?></td>
                            <td style="font-weight: 600; font-family: monospace; font-size: 11px;"></td>
                            <td style="font-weight: 600; font-family: monospace; font-size: 11px; white-space: nowrap;"></td>
                            <td style="font-weight: 600; color: #000; white-space: nowrap;"><?php echo $nombreCompleto; ?></td>
                            <td style="text-align: center; font-weight: 700; color: #000;"><?php echo htmlspecialchars($row['numero_accion'] ?? 1); ?></td>
                            <td style="font-size: 10.5px;"><?php echo $detalle; ?></td>
                            <td style="text-align: right; font-weight: 700; color: #000; font-size: 11px;"><?php echo number_format($row['monto'], 2); ?></td>
                            <td style="text-align: center; font-size: 10.5px; white-space: nowrap;"></td>
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
<script src="../js/reporte.js"></script>
