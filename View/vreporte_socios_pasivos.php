<?php
// View/vreporte_socios_pasivos.php
require_once __DIR__ . '/header.php';

// Obtener todos los registros en array para el chunking de impresión (exactamente 45 asociados por hoja llenando la página hasta la parte inferior)
$socios_rows = [];
if (isset($socios) && $socios->rowCount() > 0) {
    $socios_rows = $socios->fetchAll(PDO::FETCH_ASSOC);
}
$records_per_print_page = 45;
$chunks = array_chunk($socios_rows, $records_per_print_page);
?>

<!-- VISTA EN PANTALLA (NO IMPRESIÓN) -->
<div id="screenReportView">
    <div class="dashboard-header">
        <div class="header-actions" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
            <div>
                <h1 class="page-title">Reporte de Asociados Pasivos</h1>
                <p class="page-subtitle">Listado oficial de los miembros pasivos registrados en la fraternidad CEREMA</p>
            </div>
            <button type="button" onclick="window.print();" class="btn-primary" style="background-color: #0284c7; border: none; cursor: pointer; padding: 10px 18px; border-radius: 8px; font-weight: 600; font-size: 13.5px; color: #ffffff; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 2px 6px rgba(0,0,0,0.06); height: 42px;">
                <i class="fa-solid fa-print"></i> Imprimir en PDF
            </button>
        </div>
    </div>

    <!-- Tarjetas de Estadísticas del Reporte -->
    <div class="report-stats-grid">
        <div class="report-stat-card">
            <div class="report-stat-icon" style="background-color: rgba(231, 76, 60, 0.1); color: #e74c3c;">
                <i class="fa-solid fa-user-clock"></i>
            </div>
            <div class="report-stat-info">
                <h4>Total Asociados Pasivos</h4>
                <p><?php echo number_format($stats['total_pasivos'] ?? 0); ?></p>
            </div>
        </div>

        <div class="report-stat-card">
            <div class="report-stat-icon shares">
                <i class="fa-solid fa-layer-group"></i>
            </div>
            <div class="report-stat-info">
                <h4>Total Acciones</h4>
                <p><?php echo number_format($stats['total_acciones'] ?? 0); ?></p>
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

    <!-- Toolbar de Búsqueda -->
    <div class="toolbar-container">
        <div class="search-filter-group">
            <div class="search-box">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" id="searchReporte" class="search-input" placeholder="Buscar por C.I., nombre, teléfono o correo...">
            </div>
        </div>
    </div>

    <!-- Tabla de Resultados Interactivas (Pantalla) -->
    <div class="table-container">
        <table class="data-table" id="reporteSociosTable">
            <thead>
                <tr>
                    <th style="width: 45px; text-align: center;">N°</th>
                    <th style="width: 100px;">C.I.</th>
                    <th style="white-space: nowrap;">Nombre Completo</th>
                    <th style="width: 80px; text-align: center;">Acciones</th>
                    <th style="width: 110px;">Teléfono</th>
                    <th>Correo Electrónico</th>
                    <th style="width: 110px; text-align: center;">Fecha Ingreso</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                if (count($socios_rows) > 0): 
                    $count = 1;
                    foreach ($socios_rows as $row):
                        $nombreCompleto = htmlspecialchars($row['ap_paterno'] . ' ' . $row['ap_materno'] . ', ' . $row['nombre']);
                        $fechaIngreso = !empty($row['fecha_ingreso']) ? date('d/m/Y', strtotime($row['fecha_ingreso'])) : '-';
                ?>
                    <tr>
                        <td style="text-align: center; font-weight: 500;"><?php echo $count++; ?></td>
                        <td style="font-weight: 600; font-family: monospace; font-size: 13px; white-space: nowrap;"><?php echo htmlspecialchars($row['ci'] . (!empty($row['complemento']) ? '-' . $row['complemento'] : '')); ?></td>
                        <td style="font-weight: 600; color: var(--text-main); white-space: nowrap;"><?php echo $nombreCompleto; ?></td>
                        <td style="text-align: center; font-weight: 700; color: var(--primary);"><?php echo htmlspecialchars($row['acciones']); ?></td>
                        <td style="white-space: nowrap;"><?php echo !empty($row['telefono']) ? htmlspecialchars($row['telefono']) : '-'; ?></td>
                        <td><?php echo !empty($row['correo']) ? htmlspecialchars($row['correo']) : '-'; ?></td>
                        <td style="text-align: center; font-size: 13px; white-space: nowrap;"><?php echo $fechaIngreso; ?></td>
                    </tr>
                <?php 
                    endforeach;
                else:
                ?>
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 40px; color: var(--text-muted);">
                            <i class="fa-solid fa-users-slash" style="font-size: 40px; margin-bottom: 10px; display: block; opacity: 0.3;"></i>
                            No se encontraron asociados pasivos registrados.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Controles de Paginación Eliminados -->
</div>

<!-- ESTRUCTURA EXCLUSIVA PARA IMPRESIÓN (EXACTAMENTE 40 ASOCIADOS POR HOJA EXTENDIDOS HASTA ABAJO) -->
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
                        <h3>REPORTE OFICIAL DE ASOCIADOS PASIVOS</h3>
                    </div>
                    <div style="width: 60px;"></div>
                </div>
                <div class="print-meta">
                    <span><strong>Fecha de Generación:</strong> <?php echo date('d/m/Y H:i:s'); ?></span>
                    <span><strong>Total Pasivos:</strong> <?php echo number_format($stats['total_pasivos'] ?? 0); ?> | <strong>Total Acciones:</strong> <?php echo number_format($stats['total_acciones'] ?? 0); ?></span>
                </div>
            </div>

            <!-- TABLA DE IMPRESIÓN AJUSTADA PARA 40 ASOCIADOS EXTENDIDOS -->
            <table class="data-table print-table">
                <thead>
                    <tr>
                        <th style="width: 42px; text-align: center;">N°</th>
                        <th style="width: 98px;">C.I.</th>
                        <th style="white-space: nowrap;">Nombre Completo</th>
                        <th style="width: 75px; text-align: center;">Acciones</th>
                        <th style="width: 105px;">Teléfono</th>
                        <th>Correo Electrónico</th>
                        <th style="width: 100px; text-align: center;">Fecha Ingreso</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    foreach ($chunk as $rowIdx => $row):
                        $globalCount = ($chunkIdx * $records_per_print_page) + $rowIdx + 1;
                        $nombreCompleto = htmlspecialchars($row['ap_paterno'] . ' ' . $row['ap_materno'] . ', ' . $row['nombre']);
                        $fechaIngreso = !empty($row['fecha_ingreso']) ? date('d/m/Y', strtotime($row['fecha_ingreso'])) : '-';
                    ?>
                        <tr>
                            <td style="text-align: center; font-weight: 500;"><?php echo $globalCount; ?></td>
                            <td style="font-weight: 600; font-family: monospace; white-space: nowrap;"><?php echo htmlspecialchars($row['ci'] . (!empty($row['complemento']) ? '-' . $row['complemento'] : '')); ?></td>
                            <td style="font-weight: 600; color: #000; white-space: nowrap;"><?php echo $nombreCompleto; ?></td>
                            <td style="text-align: center; font-weight: 700; color: #000;"><?php echo htmlspecialchars($row['acciones']); ?></td>
                            <td style="white-space: nowrap;"><?php echo !empty($row['telefono']) ? htmlspecialchars($row['telefono']) : '-'; ?></td>
                            <td><?php echo !empty($row['correo']) ? htmlspecialchars($row['correo']) : '-'; ?></td>
                            <td style="text-align: center; white-space: nowrap;"><?php echo $fechaIngreso; ?></td>
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
