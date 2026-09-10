<?php
// View/vreporte_planilla_anual.php

$anio_actual = $anio_sel ?? date('Y');
$nombre_cat_planilla = $tipo_planilla_nombre ?? 'MENSUALIDADES';
$is_extraordinario_o_especial = ($nombre_cat_planilla !== 'MENSUALIDADES');
$matriz_rows = $matriz ?? [];
$records_per_print_page = $is_extraordinario_o_especial ? 36 : 38;
$chunks = array_chunk($matriz_rows, $records_per_print_page);
$meses_col = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
$meses_abrev = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];

$monto_meta_global = 0.00;
if (!empty($matriz_rows)) {
    $monto_meta_global = floatval($matriz_rows[0]['monto_meta'] ?? 0);
}

// MODO IMPRESIÓN DIRECTA (IFRAME) SIN NAVEGAR FUERA DE LA PANTALLA ACTUAL
if (isset($_GET['only_print']) && $_GET['only_print'] == '1'):
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title></title>
    <style>
        @page {
            size: letter portrait;
            margin: 0mm !important;
        }
        @page :left { margin: 0mm !important; }
        @page :right { margin: 0mm !important; }
        @page :first { margin: 0mm !important; }

        html, body {
            background: #ffffff !important;
            color: #000000 !important;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif !important;
            margin: 0 !important;
            padding: 0.3cm 1cm 0.3cm 1cm !important;
        }
        .print-page-block {
            box-sizing: border-box;
            width: 100% !important;
            page-break-inside: avoid !important;
            break-inside: avoid !important;
            page-break-after: always !important;
            break-after: page !important;
        }
        tr {
            page-break-inside: avoid !important;
            break-inside: avoid !important;
        }
        .print-page-block:last-child {
            page-break-after: auto !important;
            break-after: auto !important;
        }
        .print-header {
            display: block !important;
            text-align: center;
            margin-bottom: 4px;
            border-bottom: 2px solid #27AE60;
            padding-bottom: 2px;
        }
        .print-header-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 2px;
        }
        .print-logo {
            height: 35px;
            width: auto;
        }
        .print-title-group h2 {
            font-size: 14.5px;
            margin: 0;
            color: #1a252f;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            line-height: 1.15;
        }
        .print-title-group h3 {
            font-size: 10.5px;
            margin: 1px 0 0 0;
            color: #27AE60;
            font-weight: 600;
            line-height: 1.15;
        }
        .print-meta {
            font-size: 9.5px;
            color: #333;
            display: flex;
            justify-content: space-between;
            margin-top: 3px;
            line-height: 1.15;
        }
        .print-table {
            width: 100% !important;
            border-collapse: collapse !important;
            font-size: 9.5px !important;
            margin-bottom: 0px;
        }
        .print-table th, .print-table td {
            border: 1px solid #cbd5e1 !important;
            padding: 4.8px 4px !important;
            color: #000000 !important;
            white-space: nowrap !important;
            line-height: 1.22 !important;
        }
        .print-table th {
            background-color: #f1f5f9 !important;
            font-weight: 700 !important;
            text-transform: uppercase !important;
            font-size: 9.5px !important;
            padding: 5px 4px !important;
        }
    </style>
</head>
<body>
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
                            <h3>PLANILLA ANUAL DE CONTROL DE <?php echo $nombre_cat_planilla; ?> - GESTIÓN <?php echo $anio_actual; ?></h3>
                        </div>
                        <div style="width: 50px;"></div>
                    </div>
                    <div class="print-meta">
                        <?php if ($is_extraordinario_o_especial && $monto_meta_global > 0): ?>
                            <span><strong>MONTO TOTAL APORTE POR ASOCIADO:</strong> Bs. <?php echo number_format($monto_meta_global, 2); ?></span>
                        <?php else: ?>
                            <span><strong>Fraternidad CEREMA</strong></span>
                        <?php endif; ?>
                        <span><strong>Fecha de Emisión:</strong> <?php echo date('d/m/Y H:i:s'); ?></span>
                        <span><strong>Hoja <?php echo $chunkIdx + 1; ?> de <?php echo count($chunks); ?></strong></span>
                    </div>
                </div>

                <!-- TABLA PLANILLA IMPRESIÓN AJUSTADA -->
                <table class="data-table print-table" style="font-size: 9.5px; width: 100%; border-collapse: collapse;">
                    <thead>
                        <?php if ($is_extraordinario_o_especial): ?>
                            <tr>
                                <th style="width: 28px; text-align: center;">N°</th>
                                <th style="width: 65px;">C.I.</th>
                                <th style="min-width: 210px;">NOMBRE COMPLETO</th>
                                <th style="width: 42px; text-align: center;">ACCIÓN</th>
                                <th style="text-align: center;">FECHA / CONCEPTO DE PAGO</th>
                                <th style="width: 70px; text-align: right;">PAGADO (BS.)</th>
                                <th style="width: 70px; text-align: right;">FALTA (BS.)</th>
                                <th style="width: 80px; text-align: center;">ESTADO</th>
                            </tr>
                        <?php else: ?>
                            <tr>
                                <th style="width: 28px; text-align: center;">N°</th>
                                <th style="width: 70px;">C.I.</th>
                                <th style="white-space: nowrap;">NOMBRE COMPLETO</th>
                                <th style="width: 45px; text-align: center;">ACCIÓN</th>
                                <?php foreach ($meses_abrev as $mName): ?>
                                    <th style="text-align: center; width: 34px; padding: 3px 1px; font-size: 8.5px;"><?php echo $mName; ?></th>
                                <?php endforeach; ?>
                                <th style="width: 70px; text-align: right;">PAGADO</th>
                            </tr>
                        <?php endif; ?>
                    </thead>
                    <tbody>
                        <?php 
                        $chunk_total = count($chunk);
                        foreach ($chunk as $rowIdx => $row):
                            $globalCount = ($chunkIdx * $records_per_print_page) + $rowIdx + 1;
                            $is_multi_action = ($row['total_acciones_socio'] > 1);
                            $is_last_action = ($rowIdx == $chunk_total - 1) || (isset($chunk[$rowIdx + 1]) && $chunk[$rowIdx + 1]['id_socio'] != $row['id_socio']);

                            $printRowStyle = "";
                            if ($is_multi_action) {
                                if ($is_last_action) {
                                    $printRowStyle = "border-bottom: 2px solid #000000 !important;";
                                } else {
                                    $printRowStyle = "border-bottom: 1px dashed #64748b !important;";
                                }
                            } else {
                                $printRowStyle = "border-bottom: 1px solid #cbd5e1 !important;";
                            }
                        ?>
                            <tr style="<?php echo $printRowStyle; ?>">
                                <td style="text-align: center; font-weight: 500; font-size: 9.5px;"><?php echo $globalCount; ?></td>
                                <td style="font-weight: 600; font-family: monospace; font-size: 9.5px;"><?php echo htmlspecialchars($row['ci']); ?></td>
                                <td style="font-weight: 600; color: #000; white-space: nowrap; font-size: 10px;">
                                    <?php echo htmlspecialchars($row['nombre_completo']); ?>
                                </td>
                                <td style="text-align: center; font-weight: 700; color: #000; font-size: 9.5px;">
                                    <?php if ($is_multi_action): ?>
                                        <strong>Acc. <?php echo $row['numero_accion']; ?></strong>
                                    <?php else: ?>
                                        1
                                    <?php endif; ?>
                                </td>

                                <?php if ($is_extraordinario_o_especial): ?>
                                    <!-- FECHA / MES DE PAGO -->
                                    <td style="text-align: center; font-size: 8.5px; font-weight: 600; color: #334155;">
                                        <?php 
                                        if (!empty($row['fechas_pagos_arr'])) {
                                            foreach ($row['fechas_pagos_arr'] as $fLine) {
                                                echo '<div style="line-height: 1.2;">' . htmlspecialchars($fLine) . '</div>';
                                            }
                                        } elseif (!empty($row['meses_pagados_str']) && $row['meses_pagados_str'] !== 'Ninguno') {
                                            echo htmlspecialchars($row['meses_pagados_str']);
                                        } else {
                                            echo '<span style="color:#94a3b8;">-</span>';
                                        }
                                        ?>
                                    </td>
                                    <!-- PAGADO -->
                                    <td style="text-align: right; font-weight: 700; color: #15803d; font-size: 10px;">
                                        <?php echo number_format($row['total_pagado_anio'], 2); ?>
                                    </td>
                                    <!-- FALTA -->
                                    <td style="text-align: right; font-weight: 700; color: <?php echo ($row['falta_por_pagar'] > 0 ? '#b91c1c' : '#15803d'); ?>; font-size: 10px;">
                                        <?php echo number_format($row['falta_por_pagar'], 2); ?>
                                    </td>
                                    <!-- ESTADO -->
                                    <td style="text-align: center; font-weight: 700; font-size: 9px;">
                                        <?php if ($row['estado_pago'] == 'COMPLETO'): ?>
                                            <span style="color: #15803d;">✔ COMPLETO</span>
                                        <?php elseif ($row['estado_pago'] == 'PARCIAL'): ?>
                                            <span style="color: #d97706;">⚡ PARCIAL</span>
                                        <?php else: ?>
                                            <span style="color: #dc2626;">❌ PENDIENTE</span>
                                        <?php endif; ?>
                                    </td>
                                <?php else: ?>
                                    <!-- MENSUALIDADES -->
                                    <?php foreach ($meses_col as $mFull): 
                                        $info = $row['meses'][$mFull];
                                    ?>
                                        <td style="text-align: center; font-weight: bold; font-size: 9.5px; padding: 2px 1px;">
                                            <?php if ($info['pagado']): ?>
                                                <span style="color: #15803d;">✔</span>
                                            <?php else: ?>
                                                <span style="color: #cbd5e1;">-</span>
                                            <?php endif; ?>
                                        </td>
                                    <?php endforeach; ?>
                                    <td style="text-align: right; font-weight: 700; color: #000; font-size: 9.5px;">
                                        <?php echo number_format($row['total_pagado_anio'], 2); ?>
                                    </td>
                                <?php endif; ?>
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
</body>
</html>
<?php
exit;
endif;

require_once __DIR__ . '/header.php';
?>

<!-- VISTA EN PANTALLA (SI SE ACCEDE DIRECTAMENTE A LA VISTA) -->
<div id="screenReportView">
    <div class="dashboard-header">
        <div class="header-actions" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
            <div>
                <h1 class="page-title">Planilla Anual de Control de <?php echo htmlspecialchars($nombre_cat_planilla); ?></h1>
                <p class="page-subtitle">
                    Matriz general de pagos por asociado y número de acción - Gestión <?php echo $anio_actual; ?>
                    <?php if ($is_extraordinario_o_especial && $monto_meta_global > 0): ?>
                        | <strong>Monto Total Aporte:</strong> Bs. <?php echo number_format($monto_meta_global, 2); ?>
                    <?php endif; ?>
                </p>
            </div>
            <div style="display: flex; gap: 10px; align-items: center;">
                <form method="GET" action="../Controller/reporte.controller.php" style="display: flex; align-items: center; gap: 8px;">
                    <input type="hidden" name="type" value="<?php echo ($nombre_cat_planilla == 'APORTES EXTRAORDINARIOS' ? 'planilla_anual_extraordinarios' : ($nombre_cat_planilla == 'APORTES ESPECIALES' ? 'planilla_anual_especiales' : 'planilla_anual_mensualidades')); ?>">
                    <label for="selectAnioMatriz" style="font-size: 13.5px; font-weight: 700; color: #475569;">Gestión:</label>
                    <select name="anio" id="selectAnioMatriz" onchange="this.form.submit()" class="filter-select" style="padding: 7px 12px; border-radius: 8px; font-weight: 600; border: 1.5px solid #cbd5e1;">
                        <?php 
                        $maxAnio = (int)date('Y') + 3;
                        for ($y = $maxAnio; $y >= 2018; $y--): 
                        ?>
                            <option value="<?php echo $y; ?>" <?php echo ($y == $anio_actual) ? 'selected' : ''; ?>><?php echo $y; ?></option>
                        <?php endfor; ?>
                    </select>
                </form>
            </div>
        </div>
    </div>

    <!-- Tabla Matriz Interactivas (Pantalla) -->
    <div class="table-container" style="overflow-x: auto; margin-top: 15px;">
        <table class="data-table" id="matrizAnualTable" style="font-size: 12px; border-collapse: collapse; table-layout: auto;">
            <thead>
                <?php if ($is_extraordinario_o_especial): ?>
                    <tr>
                        <th style="width: 35px; text-align: center;">N°</th>
                        <th style="width: 85px;">C.I.</th>
                        <th style="min-width: 200px;">Nombre Completo</th>
                        <th style="width: 65px; text-align: center;">Acción</th>
                        <th style="text-align: center; min-width: 180px;">Fecha / Concepto de Pago</th>
                        <th style="width: 100px; text-align: right;">Pagado (Bs.)</th>
                        <th style="width: 100px; text-align: right;">Falta (Bs.)</th>
                        <th style="width: 110px; text-align: center;">Estado</th>
                    </tr>
                <?php else: ?>
                    <tr>
                        <th style="width: 40px; text-align: center;">N°</th>
                        <th style="width: 90px;">C.I.</th>
                        <th style="min-width: 220px;">Nombre Completo</th>
                        <th style="width: 80px; text-align: center;">Acción</th>
                        <?php foreach ($meses_abrev as $mName): ?>
                            <th style="text-align: center; width: 48px; padding: 8px 4px;"><?php echo $mName; ?></th>
                        <?php endforeach; ?>
                        <th style="width: 95px; text-align: right;">Total Pagado</th>
                    </tr>
                <?php endif; ?>
            </thead>
            <tbody>
                <?php 
                if (count($matriz_rows) > 0): 
                    $count = 1;
                    $total_registros = count($matriz_rows);
                    foreach ($matriz_rows as $idx => $row):
                        $is_last_action = ($idx == $total_registros - 1) || ($matriz_rows[$idx + 1]['id_socio'] != $row['id_socio']);
                        $is_multi_action = ($row['total_acciones_socio'] > 1);

                        // Estilos de bordes y separadores por acción
                        $rowStyle = "";
                        if ($is_multi_action) {
                            if ($is_last_action) {
                                $rowStyle = "border-bottom: 2.5px solid #334155; background-color: #f8fafc;";
                            } else {
                                $rowStyle = "border-bottom: 1px dashed #cbd5e1; background-color: #f8fafc;";
                            }
                        } else {
                            $rowStyle = "border-bottom: 1.5px solid #e2e8f0;";
                        }
                ?>
                    <tr style="<?php echo $rowStyle; ?>">
                        <td style="text-align: center; font-weight: 500; color: #64748b;"><?php echo $count++; ?></td>
                        <td style="font-weight: 600; font-family: monospace; font-size: 12px;"><?php echo htmlspecialchars($row['ci']); ?></td>
                        <td style="font-weight: 600; color: var(--text-main); white-space: nowrap;">
                            <?php echo htmlspecialchars($row['nombre_completo']); ?>
                        </td>
                        <td style="text-align: center;">
                            <?php if ($is_multi_action): ?>
                                <span style="font-weight: 700; color: #0284c7; background: #e0f2fe; padding: 2px 7px; border-radius: 6px; font-size: 11px; display: inline-block;">Acc. <?php echo $row['numero_accion']; ?></span>
                            <?php else: ?>
                                <span style="font-weight: 600; color: #475569;">1</span>
                            <?php endif; ?>
                        </td>
                        
                        <?php if ($is_extraordinario_o_especial): ?>
                            <!-- FECHA / MES PAGO -->
                            <td style="text-align: center; font-size: 11.5px; font-weight: 600; color: #334155;">
                                <?php 
                                if (!empty($row['fechas_pagos_arr'])) {
                                    foreach ($row['fechas_pagos_arr'] as $fLine) {
                                        echo '<div style="margin-bottom: 2px; line-height: 1.3;">' . htmlspecialchars($fLine) . '</div>';
                                    }
                                } elseif (!empty($row['meses_pagados_str']) && $row['meses_pagados_str'] !== 'Ninguno') {
                                    echo htmlspecialchars($row['meses_pagados_str']);
                                } else {
                                    echo '<span style="color:#94a3b8;">-</span>';
                                }
                                ?>
                            </td>
                            <!-- PAGADO -->
                            <td style="text-align: right; font-weight: 700; color: #27ae60; font-size: 12.5px;">
                                Bs. <?php echo number_format($row['total_pagado_anio'], 2); ?>
                            </td>
                            <!-- FALTA -->
                            <td style="text-align: right; font-weight: 700; color: <?php echo ($row['falta_por_pagar'] > 0 ? '#ef4444' : '#10b981'); ?>; font-size: 12.5px;">
                                Bs. <?php echo number_format($row['falta_por_pagar'], 2); ?>
                            </td>
                            <!-- ESTADO -->
                            <td style="text-align: center;">
                                <?php if ($row['estado_pago'] == 'COMPLETO'): ?>
                                    <span style="background: #dcfce7; color: #15803d; font-weight: 700; font-size: 11px; padding: 3px 7px; border-radius: 6px; display: inline-block;">✔ COMPLETO</span>
                                <?php elseif ($row['estado_pago'] == 'PARCIAL'): ?>
                                    <span style="background: #fef3c7; color: #b45309; font-weight: 700; font-size: 11px; padding: 3px 7px; border-radius: 6px; display: inline-block;">⚡ PARCIAL</span>
                                <?php else: ?>
                                    <span style="background: #fee2e2; color: #dc2626; font-weight: 700; font-size: 11px; padding: 3px 7px; border-radius: 6px; display: inline-block;">❌ PENDIENTE</span>
                                <?php endif; ?>
                            </td>
                        <?php else: ?>
                            <!-- MESES MENSUALIDADES -->
                            <?php foreach ($meses_col as $mFull): 
                                $info = $row['meses'][$mFull];
                            ?>
                                <td style="text-align: center; padding: 6px 2px;">
                                    <?php if ($info['pagado']): ?>
                                        <span title="Recibo N°: <?php echo htmlspecialchars($info['recibo']); ?> - Monto: Bs. <?php echo number_format($info['monto'], 2); ?>" style="display: inline-block; width: 22px; height: 22px; line-height: 22px; border-radius: 50%; background-color: #27ae60; color: #ffffff; font-size: 11px; font-weight: 700; cursor: help;">✔</span>
                                    <?php else: ?>
                                        <span style="color: #cbd5e1; font-weight: bold;">-</span>
                                    <?php endif; ?>
                                </td>
                            <?php endforeach; ?>
                            <td style="text-align: right; font-weight: 700; color: #27ae60; font-size: 12.5px;">
                                Bs. <?php echo number_format($row['total_pagado_anio'], 2); ?>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php 
                    endforeach;
                else:
                ?>
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 40px; color: var(--text-muted);">
                            No se encontraron datos para la gestión seleccionada.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<link rel="stylesheet" href="../css/reporte.css">
<script src="../js/reporte.js"></script>
