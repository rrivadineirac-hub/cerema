<?php
// View/vreporte_activos.php
require_once __DIR__ . '/header.php';

// Obtener todos los registros en array para el chunking de impresión (40 activos por hoja llenando la página hasta la parte inferior)
$activos_rows = [];
if (isset($activos) && $activos->rowCount() > 0) {
    $activos_rows = $activos->fetchAll(PDO::FETCH_ASSOC);
}
$records_per_print_page = 40;
$chunks = array_chunk($activos_rows, $records_per_print_page);
?>

<!-- VISTA EN PANTALLA (NO IMPRESIÓN) -->
<div id="screenReportView">
    <div class="dashboard-header">
        <div class="header-actions" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
            <div>
                <h1 class="page-title">Reporte de Activos y Patrimonio</h1>
                <p class="page-subtitle">Listado oficial de los bienes patrimoniales e institucionales de CEREMA</p>
            </div>
        </div>
    </div>

    <!-- Tarjetas de Estadísticas del Reporte -->
    <div class="report-stats-grid">
        <div class="report-stat-card">
            <div class="report-stat-icon active-users" style="background-color: rgba(14, 165, 233, 0.1); color: #0284c7;">
                <i class="fa-solid fa-boxes-stacked"></i>
            </div>
            <div class="report-stat-info">
                <h4>Total Activos Registrados</h4>
                <p><?php echo number_format($stats['total_activos'] ?? 0); ?></p>
            </div>
        </div>

        <div class="report-stat-card">
            <div class="report-stat-icon shares" style="background-color: rgba(39, 174, 96, 0.1); color: #27ae60;">
                <i class="fa-solid fa-vault"></i>
            </div>
            <div class="report-stat-info">
                <h4>Valor Estimado Total</h4>
                <p>Bs. <?php echo number_format($stats['valor_total'] ?? 0, 2); ?></p>
            </div>
        </div>

        <div class="report-stat-card">
            <div class="report-stat-icon active-users" style="background-color: rgba(40, 167, 69, 0.1); color: #28a745;">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <div class="report-stat-info">
                <h4>En Buen Estado</h4>
                <p><?php echo number_format($stats['operativos'] ?? 0); ?></p>
            </div>
        </div>

        <div class="report-stat-card">
            <div class="report-stat-icon shares" style="background-color: rgba(234, 179, 8, 0.1); color: #ca8a04;">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <div class="report-stat-info">
                <h4>Mantenimiento / Riesgo</h4>
                <p><?php echo number_format($stats['observados'] ?? 0); ?></p>
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

    <!-- Toolbar de Filtros Avanzados (Desplegables + Búsqueda + Botones) -->
    <div class="toolbar-container" style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 15px;">
        <div class="report-filters-bar" style="display: inline-flex; gap: 12px; align-items: flex-end; flex-wrap: wrap; background-color: #ffffff; padding: 14px 18px; border-radius: 12px; border: 1.5px solid #cbd5e1; box-shadow: 0 2px 8px rgba(0,0,0,0.04);">
            
            <!-- Desplegable Tipo de Activo -->
            <div style="width: 200px;">
                <label for="filterTipoActivo" style="display: block; font-size: 12.5px; font-weight: 700; color: #334155; margin-bottom: 4px;">Tipo de Activo:</label>
                <select id="filterTipoActivo" class="filter-select" style="width: 100%; padding: 8px 10px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 13px; font-weight: 500; background-color: #f8fafc; color: #0f172a; outline: none;">
                    <option value="all">-- Todos los Tipos --</option>
                    <option value="Inmueble">Inmueble</option>
                    <option value="Maquinaria">Maquinaria</option>
                    <option value="Herramienta">Herramienta</option>
                    <option value="Infraestructura">Infraestructura</option>
                    <option value="Otro">Otro</option>
                </select>
            </div>

            <!-- Desplegable Estado Operativo -->
            <div style="width: 200px;">
                <label for="filterEstadoOperativo" style="display: block; font-size: 12.5px; font-weight: 700; color: #334155; margin-bottom: 4px;">Estado Operativo:</label>
                <select id="filterEstadoOperativo" class="filter-select" style="width: 100%; padding: 8px 10px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 13px; font-weight: 500; background-color: #f8fafc; color: #0f172a; outline: none;">
                    <option value="all">-- Todos los Estados --</option>
                    <option value="Excelente">Excelente</option>
                    <option value="Bueno">Bueno</option>
                    <option value="Regular">Regular</option>
                    <option value="En Mantenimiento">En Mantenimiento</option>
                    <option value="Fuera de Servicio">Fuera de Servicio</option>
                </select>
            </div>

            <!-- Búsqueda por texto libre -->
            <div style="width: 240px;">
                <label for="searchReporte" style="display: block; font-size: 12.5px; font-weight: 700; color: #334155; margin-bottom: 4px;">Buscar por nombre:</label>
                <input type="text" id="searchReporte" placeholder="Nombre o descripción..." style="width: 100%; padding: 8px 12px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 13px; font-weight: 500; background-color: #f8fafc; color: #0f172a; outline: none;">
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

        <!-- Botones de Acción a la Derecha (Imprimir en PDF) -->
        <div style="display: flex; gap: 10px; align-items: center;">
            <button type="button" id="btnImprimirFiltrado" onclick="printReport();" class="btn-primary" disabled style="background-color: #0284c7; border: none; cursor: not-allowed; opacity: 0.4; padding: 10px 18px; border-radius: 8px; font-weight: 600; font-size: 13.5px; color: #ffffff; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 2px 6px rgba(0,0,0,0.06); height: 42px; transition: all 0.2s ease;" title="Imprimir los resultados filtrados en PDF">
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
                    <th style="white-space: nowrap;">Nombre del Activo</th>
                    <th style="width: 140px;">Tipo de Activo</th>
                    <th style="width: 150px; text-align: right;">Valor Estimado (Bs.)</th>
                    <th style="width: 130px; text-align: center;">Fecha Adquisición</th>
                    <th style="width: 150px; text-align: center;">Estado Operativo</th>
                    <th>Observaciones</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                if (count($activos_rows) > 0): 
                    $count = 1;
                    foreach ($activos_rows as $row):
                        $fechaAdq = !empty($row['fecha_adquisicion']) ? date('d/m/Y', strtotime($row['fecha_adquisicion'])) : '-';
                        $tipoClass = strtolower($row['tipo_activo']);
                        $estadoClass = strtolower(str_replace(' ', '-', $row['estado_operativo']));
                ?>
                    <tr data-tipo="<?php echo htmlspecialchars($row['tipo_activo']); ?>" data-estado="<?php echo htmlspecialchars($row['estado_operativo']); ?>">
                        <td style="text-align: center; font-weight: 500;"><?php echo $count++; ?></td>
                        <td style="font-weight: 600; color: var(--text-main); white-space: nowrap;"><?php echo htmlspecialchars($row['nombre_activo']); ?></td>
                        <td><span class="badge-tipo <?php echo $tipoClass; ?>"><?php echo htmlspecialchars($row['tipo_activo']); ?></span></td>
                        <td style="text-align: right; font-weight: 700; color: #27ae60; font-size: 13.5px;">Bs. <?php echo number_format($row['valor_estimado'], 2); ?></td>
                        <td style="text-align: center; font-size: 13px; white-space: nowrap;"><?php echo $fechaAdq; ?></td>
                        <td style="text-align: center;"><span class="badge-estado <?php echo $estadoClass; ?>"><?php echo htmlspecialchars($row['estado_operativo']); ?></span></td>
                        <td style="font-size: 13px; color: var(--text-muted); max-width: 250px;"><?php echo !empty($row['observaciones']) ? htmlspecialchars($row['observaciones']) : '-'; ?></td>
                    </tr>
                <?php 
                    endforeach;
                endif;
                ?>
                <tr id="emptyReportRow">
                    <td colspan="7" style="text-align: center; padding: 50px 20px; color: var(--text-muted);">
                        <i class="fa-solid fa-boxes-stacked" style="font-size: 44px; margin-bottom: 12px; display: block; opacity: 0.25;"></i>
                        <span id="emptyReportText" style="font-size: 14px; font-weight: 500;">Seleccione los filtros deseados y presione "Buscar" para consultar los registros.</span>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- ESTRUCTURA EXCLUSIVA PARA IMPRESIÓN (PDF) -->
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
                        <h3>REPORTE OFICIAL DE ACTIVOS Y PATRIMONIO INSTITUCIONAL</h3>
                    </div>
                    <div style="width: 60px;"></div>
                </div>
                <div class="print-meta">
                    <span><strong>Fecha de Generación:</strong> <?php echo date('d/m/Y H:i:s'); ?></span>
                    <span><strong>Total Activos:</strong> <?php echo number_format($stats['total_activos'] ?? 0); ?> | <strong>Valor Estimado:</strong> Bs. <?php echo number_format($stats['valor_total'] ?? 0, 2); ?></span>
                </div>
            </div>

            <!-- TABLA DE IMPRESIÓN DE ACTIVOS -->
            <table class="data-table print-table">
                <thead>
                    <tr>
                        <th style="width: 38px; text-align: center;">N°</th>
                        <th style="white-space: nowrap;">Nombre / Descripción del Activo</th>
                        <th style="width: 100px;">Tipo</th>
                        <th style="width: 110px; text-align: right;">Valor (Bs.)</th>
                        <th style="width: 100px; text-align: center;">Fecha Adq.</th>
                        <th style="width: 110px; text-align: center;">Estado Operativo</th>
                        <th>Observaciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    foreach ($chunk as $rowIdx => $row):
                        $globalCount = ($chunkIdx * $records_per_print_page) + $rowIdx + 1;
                        $fechaAdq = !empty($row['fecha_adquisicion']) ? date('d/m/Y', strtotime($row['fecha_adquisicion'])) : '-';
                    ?>
                        <tr>
                            <td style="text-align: center; font-weight: 500;"><?php echo $globalCount; ?></td>
                            <td style="font-weight: 600; color: #000; white-space: nowrap;"><?php echo htmlspecialchars($row['nombre_activo']); ?></td>
                            <td style="font-weight: 500;"><?php echo htmlspecialchars($row['tipo_activo']); ?></td>
                            <td style="text-align: right; font-weight: 700; color: #000;"><?php echo number_format($row['valor_estimado'], 2); ?></td>
                            <td style="text-align: center; white-space: nowrap;"><?php echo $fechaAdq; ?></td>
                            <td style="text-align: center; font-weight: 600;"><?php echo htmlspecialchars($row['estado_operativo']); ?></td>
                            <td style="font-size: 10.5px;"><?php echo !empty($row['observaciones']) ? htmlspecialchars($row['observaciones']) : '-'; ?></td>
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

<link rel="stylesheet" href="../css/activo.css">
<link rel="stylesheet" href="../css/reporte.css">
<script src="../js/reporte.js"></script>

<?php require_once __DIR__ . '/footer.php'; ?>
