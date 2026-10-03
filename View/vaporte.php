<?php 
// View/vaporte.php
include 'header.php'; 
?>

<?php if (!isset($socio_actual)): ?>
<!-- PANTALLA DE SELECCIÓN DE SOCIO -->
<div class="selection-container">
    <div class="selection-card">
        <div class="selection-icon">
            <i class="fa-solid fa-hand-holding-dollar"></i>
        </div>
        <h2>Seleccionar Socio</h2>
        <p>Para gestionar aportes extraordinarios, primero debes elegir a un socio de la lista.</p>
        
        <form action="../Controller/aporte.controller.php" method="GET" class="selection-form">
            <div class="form-group">
                <label for="id_socio_select">Socio:</label>
                <select name="id_socio" id="id_socio_select" required class="select-searchable">
                    <option value="" disabled selected>-- Selecciona un socio --</option>
                    <?php 
                    if(isset($socios) && $socios->rowCount() > 0) {
                        while($row = $socios->fetch(PDO::FETCH_ASSOC)) {
                            $nombre_completo = htmlspecialchars($row['ap_paterno'] . ' ' . $row['ap_materno'] . ' ' . $row['nombre']);
                            $ci_full = htmlspecialchars($row['ci']) . (!empty($row['complemento']) ? '-' . htmlspecialchars($row['complemento']) : '');
                            echo "<option value='" . $row['id_socio'] . "'>" . $ci_full . " - " . $nombre_completo . "</option>";
                        }
                    }
                    ?>
                </select>
            </div>
            <div style="display: flex; gap: 10px; margin-top: 15px; flex-wrap: wrap;">
                <button type="submit" class="btn-primary" style="flex: 1;">
                    Continuar <i class="fa-solid fa-arrow-right"></i>
                </button>
                <button type="button" class="btn-secondary" onclick="openAporteRangeModal()" style="background-color: #0284c7; color: white; border: none; padding: 12px; border-radius: 6px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-calendar-days"></i> Carga Masiva por Mes y Año
                </button>
            </div>
        </form>
    </div>
</div>

<?php else: ?>
<!-- PANTALLA DEL CRUD DE APORTES -->
<div class="dashboard-header">
    <div class="header-actions" style="display: flex; justify-content: space-between; align-items: center;">
        <div>
            <a href="../Controller/aporte.controller.php" class="back-link"><i class="fa-solid fa-arrow-left"></i> Volver a selección</a>
            <h1 class="page-title">Aportes Extraordinarios</h1>
            <p class="page-subtitle">Socio: <strong><?php echo htmlspecialchars($socio_actual['ap_paterno'] . ' ' . $socio_actual['ap_materno'] . ', ' . $socio_actual['nombre']); ?></strong></p>
        </div>
        <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
            <a href="../Controller/aporte.controller.php" class="btn-secondary" style="display: inline-flex; align-items: center; gap: 8px; text-decoration: none;">
                <i class="fa-solid fa-users"></i> Otro Asociado
            </a>
            <button class="btn-secondary" onclick="openAporteRangeModal()" style="background-color: #0284c7; color: white; border: none; display: inline-flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-calendar-days"></i> Carga Masiva por Mes y Año
            </button>
            <button class="btn-primary" onclick="openAporteModal()">
                <i class="fa-solid fa-plus"></i> Registrar Aporte
            </button>
        </div>
    </div>
</div>

<div class="table-container">
    <table class="data-table">
        <thead>
            <tr>
                <th>Motivo del Aporte</th>
                <th>Monto (Bs)</th>
                <th>Fecha del Aporte</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            if(isset($aportes) && $aportes->rowCount() > 0) {
                while($row = $aportes->fetch(PDO::FETCH_ASSOC)) {
                    echo "<tr>";
                    echo "<td><strong>" . htmlspecialchars($row['motivo']) . "</strong></td>";
                    echo "<td>Bs. " . number_format($row['monto'], 2) . "</td>";
                    echo "<td>" . date('d/m/Y', strtotime($row['fecha_aporte'])) . "</td>";
                    echo "<td class='actions-col'>
                            <button class='btn-icon edit-btn' onclick='editAporte(" . $row['id_aporte'] . ")' title='Editar'><i class='fa-solid fa-pen'></i></button>
                            <button class='btn-icon delete-btn' onclick='deleteAporte(" . $row['id_aporte'] . ", \"" . htmlspecialchars($row['motivo']) . "\")' title='Eliminar'><i class='fa-solid fa-trash'></i></button>
                          </td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='4' style='text-align: center; padding: 20px;'>No hay aportes extraordinarios registrados para este socio.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<!-- Modal Structure for Create/Edit -->
<div id="aporteModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="modalTitle">Registrar Aporte Extraordinario</h2>
            <span class="close-btn" onclick="closeAporteModal()">&times;</span>
        </div>
        <div class="modal-body">
            <form id="aporteForm">
                <input type="hidden" name="action" value="save">
                <input type="hidden" id="id_aporte" name="id_aporte" value="">
                <input type="hidden" id="id_socio" name="id_socio" value="<?php echo $socio_actual['id_socio']; ?>">
                
                <div class="form-row">
                    <div class="form-group" style="width: 100%;">
                        <label for="motivo">Motivo del Aporte (Gestión Actual)</label>
                        <?php if (empty($categorias)): ?>
                            <div style="color: #dc3545; font-size: 14px; margin-top: 5px;">
                                No hay aportes extraordinarios configurados para la gestión actual. <br>
                                <a href="../Controller/categorias_ingreso.controller.php" style="color: #007bff;">Configurar aquí</a>
                            </div>
                            <input type="hidden" id="motivo" name="motivo" value="">
                        <?php else: ?>
                            <select id="motivo" name="motivo" required style="width: 100%;">
                                <option value="" disabled selected>-- Selecciona un Motivo --</option>
                                <?php foreach ($categorias as $cat): ?>
                                    <option value="<?php echo htmlspecialchars($cat['nombre']); ?>" data-completado="<?php echo (!empty($cat['completado'])) ? '1' : '0'; ?>"><?php echo htmlspecialchars($cat['nombre']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div id="payment_info_box" style="display:none; background: #eef2f5; padding: 10px 15px; border-radius: 6px; margin-bottom: 15px; font-size: 13.5px; border-left: 4px solid #00B300;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                        <span>Total del Aporte:</span>
                        <strong id="info_total">Bs 0.00</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                        <span>Ya Pagado:</span>
                        <strong id="info_pagado" style="color: #28a745;">Bs 0.00</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; border-top: 1px solid #d1d5db; padding-top: 5px; margin-top: 5px;">
                        <span>Saldo Pendiente:</span>
                        <strong id="info_saldo" style="color: #dc3545;">Bs 0.00</strong>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group half">
                        <label for="monto">Monto (Bs)</label>
                        <input type="number" step="0.01" min="0.1" id="monto" name="monto" required value="0.00">
                    </div>
                    <div class="form-group half">
                        <label for="fecha_aporte">Fecha del Aporte</label>
                        <input type="date" id="fecha_aporte" name="fecha_aporte" min="2018-01-01" required value="<?php echo date('Y-m-d'); ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group" style="width: 100%;">
                        <label for="numero_recibo">Nro. de Recibo</label>
                        <input type="text" id="numero_recibo" name="numero_recibo" required placeholder="Ej: 001234" style="width: 100%;">
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-secondary" onclick="closeAporteModal()">Cancelar</button>
            <button type="button" class="btn-primary" id="btnGuardarAporte" onclick="saveAporte()">Guardar</button>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Reusing mensualidad CSS for identical layout structure -->
<link rel="stylesheet" href="../css/mensualidad.css">
<script src="../js/aporte.js"></script>

<!-- Modal de Confirmación de Eliminación -->
<div id="deleteModal" class="modal">
    <div class="modal-content" style="max-width: 450px; text-align: center; overflow: hidden; padding: 0;">
        <div style="background-color: #dc3545; color: white; padding: 15px 20px; display: flex; justify-content: space-between; align-items: center;">
            <h3 style="margin: 0; font-size: 16px; font-weight: 600; letter-spacing: 1px;">ELIMINAR REGISTRO</h3>
            <span onclick="closeDeleteModal()" style="color: white; font-size: 24px; cursor: pointer; line-height: 1;">&times;</span>
        </div>
        <div class="modal-body" style="padding: 40px 20px; background-color: white;">
            <p style="margin-bottom: 30px; font-size: 16px; color: #444;">
                ¿Estas seguro de que deseas eliminar el registro: <br><strong id="deleteDesc" style="font-size: 18px; color: #222; display: inline-block; margin-top: 10px;"></strong>?
            </p>
            <div style="display: flex; justify-content: center; gap: 15px;">
                <button type="button" style="background-color: #28a745; color: white; border: none; padding: 10px 20px; border-radius: 4px; font-size: 14px; font-weight: 600; cursor: pointer; transition: opacity 0.2s;" onclick="closeDeleteModal()">Cancelar</button>
                <button type="button" style="background-color: #dc3545; color: white; border: none; padding: 10px 20px; border-radius: 4px; font-size: 14px; font-weight: 600; cursor: pointer; transition: opacity 0.2s;" id="btnConfirmDelete">Borrar registro</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Carga Masiva Aportes Extraordinarios Multiaño (2018 - 2029) -->
<div id="aporteRangeModal" class="modal">
    <div class="modal-content" style="max-width: 850px; height: 90vh; max-height: 880px; display: flex; flex-direction: column; padding: 0; overflow: hidden; border-radius: 12px; background: #ffffff;">
        
        <!-- HEADER Y CONTROLES SUPERIORES (FIJOS ARRIBA) -->
        <div style="flex-shrink: 0; background: #ffffff; border-bottom: 1px solid #e2e8f0; z-index: 10;">
            <div class="modal-header" style="background: linear-gradient(135deg, #1e293b, #0f172a); color: white; padding: 16px 24px; display: flex; justify-content: space-between; align-items: center;">
                <h2 style="margin: 0; font-size: 18px; font-weight: 700; color: #fff;">
                    <i class="fa-solid fa-calendar-days" style="color: #0284c7; margin-right: 8px;"></i>
                    Carga Masiva de Aportes Extraordinarios (2018 - 2029)
                </h2>
                <span class="close-btn" onclick="closeAporteRangeModal()" style="color: #fff; font-size: 24px; cursor: pointer;">&times;</span>
            </div>

            <div style="padding: 16px 24px 0 24px;">
                <!-- Selector de Socio, Acción, Motivo y Recibo -->
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; padding: 12px 16px; border-radius: 10px; margin-bottom: 12px;">
                    <div class="form-row" style="display: flex; gap: 15px; flex-wrap: wrap;">
                        <div style="flex: 2; min-width: 220px;">
                            <label style="font-weight: 600; font-size: 12px; margin-bottom: 4px; display: block; color: #334155;">Socio a Cargar:</label>
                            <select name="id_socio" id="range_aporte_id_socio" required onchange="loadAllPaidMonthsAporteMultiYear()" style="width: 100%; padding: 8px; border-radius: 6px; border: 1px solid #cbd5e1; font-size: 13px; background: #fff;">
                                <option value="all" <?php echo !isset($socio_actual) ? 'selected' : ''; ?>>-- TODOS LOS SOCIOS (Carga Global Masiva) --</option>
                                <?php 
                                if (isset($socios) && $socios->rowCount() > 0) {
                                    $socios->execute();
                                    while ($sRow = $socios->fetch(PDO::FETCH_ASSOC)) {
                                        $isSel = (isset($socio_actual) && $socio_actual['id_socio'] == $sRow['id_socio']) ? 'selected' : '';
                                        $nombreFull = htmlspecialchars($sRow['ci'] . ' - ' . trim($sRow['ap_paterno'] . ' ' . $sRow['ap_materno'] . ' ' . $sRow['nombre']));
                                        echo "<option value='" . $sRow['id_socio'] . "' $isSel>" . $nombreFull . "</option>";
                                    }
                                }
                                ?>
                            </select>
                        </div>
                        <div style="flex: 1; min-width: 120px;">
                            <label style="font-weight: 600; font-size: 12px; margin-bottom: 4px; display: block; color: #334155;">Acción:</label>
                            <select name="numero_accion" id="range_aporte_numero_accion" onchange="loadAllPaidMonthsAporteMultiYear()" style="width: 100%; padding: 8px; border-radius: 6px; border: 1px solid #cbd5e1; background: #fff; font-size: 13px;">
                                <option value="all">Todas las Acciones</option>
                                <option value="1" selected>Acción 1</option>
                                <option value="2">Acción 2</option>
                                <option value="3">Acción 3</option>
                                <option value="4">Acción 4</option>
                                <option value="5">Acción 5</option>
                            </select>
                        </div>
                        <div style="flex: 2; min-width: 200px;">
                            <label style="font-weight: 600; font-size: 12px; margin-bottom: 4px; display: block; color: #334155;">Motivo del Aporte (*):</label>
                            <select name="motivo" id="range_aporte_motivo" onchange="loadAllPaidMonthsAporteMultiYear()" style="width: 100%; padding: 8px; border-radius: 6px; border: 1px solid #cbd5e1; background: #fff; font-size: 13px;">
                                <?php if (!empty($categorias)): ?>
                                    <?php foreach ($categorias as $cat): ?>
                                        <option value="<?php echo htmlspecialchars($cat['nombre']); ?>" data-monto="<?php echo htmlspecialchars($cat['monto_sugerido'] ?? 0); ?>">
                                            <?php echo htmlspecialchars($cat['nombre']); ?> (Bs <?php echo number_format($cat['monto_sugerido'] ?? 0, 2); ?>)
                                        </option>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <option value="APORTE EXTRAORDINARIO">APORTE EXTRAORDINARIO</option>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div style="flex: 1; min-width: 120px;">
                            <label style="font-weight: 600; font-size: 12px; margin-bottom: 4px; display: block; color: #334155;">Nº Recibo (Opcional):</label>
                            <input type="text" id="range_aporte_numero_recibo" placeholder="Ej: REC-2024" style="width: 100%; padding: 8px; border-radius: 6px; border: 1px solid #cbd5e1; background: #fff; font-size: 13px;">
                        </div>
                    </div>
                </div>

                <!-- Barra de Acciones Rápidas Globales -->
                <div style="background: #e0f2fe; border: 1px solid #bae6fd; padding: 10px 14px; border-radius: 8px; margin-bottom: 12px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px;">
                    <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                        <span style="font-weight: 700; font-size: 13px; color: #0369a1;">Monto/Mes:</span>
                        <input type="number" step="0.01" id="globalAporteMontoInput" value="50.00" style="width: 85px; padding: 5px 8px; border-radius: 6px; border: 1px solid #7dd3fc; font-weight: 700; text-align: center;">
                        <button type="button" class="btn-xs" onclick="applyGlobalAporteMonto()" style="background: #0284c7; color: white; border: none; padding: 6px 12px; border-radius: 4px; font-weight: 600; cursor: pointer;">Aplicar a Todos los Años</button>
                    </div>
                    <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                        <button type="button" class="btn-xs" onclick="selectAllAporteMonthsGlobal(true)" style="background: #27AE60; color: white; border: none; padding: 7px 12px; border-radius: 6px; font-weight: 700; cursor: pointer; font-size: 12px; display: inline-flex; align-items: center; gap: 5px;">
                            <i class="fa-solid fa-check-double"></i> Seleccionar TODOS los Meses (2018-2029)
                        </button>
                        <button type="button" class="btn-xs" onclick="selectAllAporteMonthsGlobal(false)" style="background: #64748b; color: white; border: none; padding: 7px 10px; border-radius: 6px; font-weight: 600; cursor: pointer; font-size: 12px;">
                            Desmarcar Todos
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- BODY CON SCROLL INDEPENDIENTE (AÑOS 2018 - 2029) -->
        <div class="modal-body" style="flex: 1; overflow-y: auto; padding: 16px 24px; background: #ffffff;">
            <div id="aporteYearsContainer" style="display: flex; flex-direction: column; gap: 12px;">
                <?php 
                $mesesNomAporte = ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
                for ($y = 2018; $y <= 2029; $y++): 
                ?>
                    <div class="year-card" id="aycard_<?php echo $y; ?>" style="background: #f8fafc; border: 1.5px solid #cbd5e1; border-radius: 10px; padding: 14px; transition: all 0.2s;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; flex-wrap: wrap; gap: 10px;">
                            <label style="display: flex; align-items: center; gap: 8px; font-weight: 700; font-size: 15px; color: #1e293b; cursor: pointer;">
                                <input type="checkbox" class="ayear-enable-cb" data-year="<?php echo $y; ?>" onchange="toggleAporteYearCard(<?php echo $y; ?>)" style="width: 18px; height: 18px; accent-color: #0284c7; cursor: pointer;">
                                <i class="fa-solid fa-calendar-day" style="color: #0284c7;"></i> Gestión <?php echo $y; ?>
                            </label>

                            <div style="display: flex; align-items: center; gap: 10px;">
                                <span style="font-size: 12px; font-weight: 600; color: #64748b;">Monto/Mes (Bs):</span>
                                <input type="number" step="0.01" class="ayear-monto-input" id="aymonto_<?php echo $y; ?>" data-year="<?php echo $y; ?>" value="50.00" oninput="updateAporteMultiYearTotals()" style="width: 85px; padding: 6px 8px; border-radius: 6px; border: 1px solid #cbd5e1; font-weight: 700; color: #27AE60; text-align: center;">
                                <div style="display: flex; gap: 4px;">
                                    <button type="button" class="btn-xs" onclick="setAporteYearMonths(<?php echo $y; ?>, 'all')" style="background: #e2e8f0; border: none; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: 600; cursor: pointer;">12 Meses</button>
                                    <button type="button" class="btn-xs" onclick="setAporteYearMonths(<?php echo $y; ?>, 'clear')" style="background: #f1f5f9; color: #64748b; border: none; padding: 4px 8px; border-radius: 4px; font-size: 11px; cursor: pointer;">Limpiar</button>
                                </div>
                            </div>
                        </div>

                        <!-- Grid de 12 Meses para esta Gestión -->
                        <div class="ymonths-grid" id="aymonths_<?php echo $y; ?>" style="display: grid; grid-template-columns: repeat(6, 1fr); gap: 6px;">
                            <?php foreach($mesesNomAporte as $mName): ?>
                                <label class="ymonth-label" id="aymlabel_<?php echo $y; ?>_<?php echo $mName; ?>" style="display: flex; align-items: center; gap: 5px; font-size: 12px; font-weight: 600; padding: 6px 8px; background: #ffffff; border: 1px solid #cbd5e1; border-radius: 6px; cursor: pointer; user-select: none;">
                                    <input type="checkbox" class="aymonth-cb aymonth-cb-<?php echo $y; ?>" data-year="<?php echo $y; ?>" value="<?php echo $mName; ?>" onchange="updateAporteMultiYearTotals()" style="width: 16px; height: 16px; accent-color: #27AE60; cursor: pointer;">
                                    <span><?php echo substr($mName, 0, 3); ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                        
                        <div style="margin-top: 8px; font-size: 11px; font-weight: 600; color: #64748b; text-align: right;" id="aysubtotal_<?php echo $y; ?>">
                            Subtotal 0 meses = Bs. 0.00
                        </div>
                    </div>
                <?php endfor; ?>
            </div>
        </div>

        <!-- STICKY FOOTER CON RESUMEN Y BOTONES FIJOS -->
        <div class="modal-footer" style="flex-shrink: 0; background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 14px 24px; display: flex; justify-content: space-between; align-items: center; z-index: 10;">
            <div>
                <div style="font-size: 14px; font-weight: 700; color: #1e293b;" id="totalAporteMultiYearSummary">
                    Total Aportes: <span style="color: #0284c7;">0 meses</span> | <span style="color: #27AE60; font-size: 16px;">Bs. 0.00</span>
                </div>
            </div>
            <div style="display: flex; gap: 10px;">
                <button type="button" class="btn-secondary" onclick="closeAporteRangeModal()" style="padding: 10px 20px; border-radius: 6px; font-weight: 600;">Cancelar</button>
                <button type="button" class="btn-primary" id="btnSaveAporteMultiYear" onclick="saveAporteMultiYear()" style="background: #0284c7; border: none; padding: 10px 24px; border-radius: 6px; font-weight: 700; color: white; cursor: pointer; box-shadow: 0 2px 8px rgba(2, 132, 199, 0.3);">
                    <i class="fa-solid fa-floppy-disk"></i> Guardar Aportes
                </button>
            </div>
        </div>

    </div>
</div>

<?php include 'footer.php'; ?>
