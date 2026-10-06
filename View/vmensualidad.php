<?php 
// View/vmensualidad.php
include 'header.php'; 
?>

<?php if (!isset($socio_actual)): ?>
<!-- PANTALLA DE SELECCIÓN DE SOCIO -->
<div class="selection-container">
    <div class="selection-card">
        <div class="selection-icon">
            <i class="fa-solid fa-user-check"></i>
        </div>
        <h2>Seleccionar Socio</h2>
        <p>Para gestionar las mensualidades, primero debes elegir a un socio de la lista.</p>
        
        <form action="../Controller/mensualidad.controller.php" method="GET" class="selection-form">
            <div class="form-group">
                <label for="id_socio_select">Socio:</label>
                <select name="id_socio" id="id_socio_select" required class="select-searchable">
                    <option value="" disabled selected>-- Selecciona un socio --</option>
                    <?php 
                    $list = isset($sociosList) ? $sociosList : (isset($socios) && is_array($socios) ? $socios : []);
                    if (!empty($list)) {
                        foreach ($list as $row) {
                            $nombre_completo = htmlspecialchars(trim($row['ap_paterno'] . ' ' . $row['ap_materno'] . ' ' . $row['nombre']));
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
                <button type="button" class="btn-secondary" onclick="openRangeModal()" style="background-color: #0284c7; color: white; border: none; padding: 12px;">
                    <i class="fa-solid fa-calendar-days"></i> Carga Masiva Rango
                </button>
                <a href="../Controller/matriz_pagos.controller.php" class="btn-secondary" style="background-color: #2563eb; color: white; border: none; padding: 12px; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-table-cells"></i> Matriz Rápida
                </a>
            </div>
        </form>
    </div>
</div>

<?php else: ?>
<!-- PANTALLA DEL CRUD DE MENSUALIDADES -->
<div class="dashboard-header">
    <div class="header-actions" style="display: flex; justify-content: space-between; align-items: center;">
        <div>
            <?php if (!is_socio()): ?>
            <a href="../Controller/mensualidad.controller.php" class="back-link"><i class="fa-solid fa-arrow-left"></i> Volver a selección</a>
            <?php endif; ?>
            <h1 class="page-title">Aportes Mensuales</h1>
            <p class="page-subtitle">Socio: <strong><?php echo htmlspecialchars($socio_actual['ap_paterno'] . ' ' . $socio_actual['ap_materno'] . ', ' . $socio_actual['nombre']); ?></strong></p>
        </div>
        <?php if (!is_socio()): ?>
        <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
            <a href="../Controller/mensualidad.controller.php" class="btn-secondary" style="display: inline-flex; align-items: center; gap: 8px; text-decoration: none;">
                <i class="fa-solid fa-users"></i> Otro Asociado
            </a>
            <a href="../Controller/matriz_pagos.controller.php" class="btn-secondary" style="background-color: #2563eb; color: white; border: none; display: inline-flex; align-items: center; gap: 8px; text-decoration: none;">
                <i class="fa-solid fa-table-cells"></i> Matriz Rápida
            </a>
            <button class="btn-secondary" onclick="openRangeModal()" style="background-color: #0284c7; color: white; border: none; display: inline-flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-calendar-days"></i> Cargar Rango (2018-2029)
            </button>
            <button class="btn-primary" onclick="openMensualidadModal()">
                <i class="fa-solid fa-plus"></i> Registrar Pago
            </button>
        </div>
        <?php endif; ?>
    </div>
</div>

<div class="table-container">
    <table class="data-table">
        <thead>
            <tr>
                <th>Acción #</th>
                <th>Nº Recibo</th>
                <th>Mes</th>
                <th>Año</th>
                <th>Monto (Bs)</th>
                <th>Fecha de Pago</th>
                <th>Estado</th>
                <?php if (!is_socio()): ?>
                <th>Acciones</th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php 
            if(isset($mensualidades) && $mensualidades->rowCount() > 0) {
                while($row = $mensualidades->fetch(PDO::FETCH_ASSOC)) {
                    $estadoClass = strtolower($row['estado']);
                    echo "<tr>";
                    echo "<td>Acción " . (isset($row['numero_accion']) ? htmlspecialchars($row['numero_accion']) : '1') . "</td>";
                    echo "<td>" . (isset($row['numero_recibo']) && $row['numero_recibo'] ? htmlspecialchars($row['numero_recibo']) : '-') . "</td>";
                    echo "<td><strong>" . htmlspecialchars($row['mes']) . "</strong></td>";
                    echo "<td>" . htmlspecialchars($row['anio']) . "</td>";
                    echo "<td>Bs. " . number_format($row['monto'], 2) . "</td>";
                    echo "<td>" . date('d/m/Y', strtotime($row['fecha_pago'])) . "</td>";
                    echo "<td><span class='badge-status {$estadoClass}'>" . htmlspecialchars($row['estado']) . "</span></td>";
                    $nombreSocio = $socio_actual['nombre'] . ' ' . $socio_actual['ap_paterno'];
                    $descEliminar = $row['mes'] . ' ' . $row['anio'] . ' de ' . $nombreSocio;
                    
                    if (!is_socio()) {
                        echo "<td class='actions-col'>
                                <button class='btn-icon edit-btn' onclick='editMensualidad(" . $row['id_mensualidad'] . ")' title='Editar'><i class='fa-solid fa-pen'></i></button>
                                <button class='btn-icon delete-btn' onclick='deleteMensualidad(" . $row['id_mensualidad'] . ", \"" . htmlspecialchars($descEliminar, ENT_QUOTES) . "\")' title='Eliminar'><i class='fa-solid fa-trash'></i></button>
                              </td>";
                    }
                    echo "</tr>";
                }
            } else {
                $cols = is_socio() ? 7 : 8;
                echo "<tr><td colspan='{$cols}' style='text-align: center; padding: 20px;'>No hay pagos registrados para este socio.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<!-- Modal Structure for Create/Edit -->
<div id="mensualidadModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="modalTitle">Registrar Pago de Mensualidad</h2>
            <span class="close-btn" onclick="closeMensualidadModal()">&times;</span>
        </div>
        <div class="modal-body">
            <form id="mensualidadForm">
                <input type="hidden" name="action" value="save">
                <input type="hidden" id="id_mensualidad" name="id_mensualidad" value="">
                <input type="hidden" id="id_socio" name="id_socio" value="<?php echo $socio_actual['id_socio']; ?>">
                
                <div class="form-row">
                    <div class="form-group half">
                        <label for="numero_accion">Acción (Si tiene más de una)</label>
                        <select id="numero_accion" name="numero_accion" required onchange="loadPaidMonths()">
                            <?php 
                            $max_acciones = isset($socio_actual['acciones']) ? intval($socio_actual['acciones']) : 1;
                            for($i = 1; $i <= $max_acciones; $i++) {
                                $selected = ($i === 1) ? 'selected' : '';
                                echo "<option value='$i' $selected>Acción $i</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group half">
                        <label for="numero_recibo">Número de Recibo</label>
                        <input type="text" id="numero_recibo" name="numero_recibo" required placeholder="Ej: 000111">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group half">
                        <label for="anio">Año</label>
                        <input type="number" id="anio" name="anio" required value="<?php echo date('Y'); ?>" min="2018" max="<?php echo ((int)date('Y') + 3); ?>" onchange="loadPaidMonths()">
                    </div>
                    <div class="form-group half">
                        <label for="fecha_pago">Fecha de Pago</label>
                        <input type="date" id="fecha_pago" name="fecha_pago" required value="<?php echo date('Y-m-d'); ?>">
                    </div>
                </div>

                <!-- Sección Checklist de los 12 Meses (Modo Registro Nuevo) -->
                <div id="checklistSection" class="form-group" style="margin-bottom: 18px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                        <label style="font-weight: 600; font-size: 13px; color: var(--text-muted); margin: 0;">Seleccionar Meses a Pagar (*)</label>
                        <div style="display: flex; gap: 6px;">
                            <button type="button" class="btn-xs" onclick="selectUnpaidMonths()" title="Marcar todos los meses pendientes de pago">Todos los Pendientes</button>
                            <button type="button" class="btn-xs btn-xs-secondary" onclick="uncheckAllMonths()">Desmarcar</button>
                        </div>
                    </div>

                    <div id="monthsGrid" class="months-grid">
                        <?php 
                        $listaMeses = ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
                        foreach($listaMeses as $m):
                        ?>
                            <label class="month-card" id="card_<?php echo $m; ?>" style="display: flex; align-items: center; gap: 8px; padding: 10px 12px; border: 1.5px solid #cbd5e1; border-radius: 8px; background-color: #ffffff; cursor: pointer; user-select: none; box-sizing: border-box;">
                                <input type="checkbox" name="meses[]" value="<?php echo $m; ?>" class="month-checkbox" onchange="updateCalculatedTotal()" style="margin: 0; width: 18px; height: 18px; cursor: pointer; accent-color: #27AE60; flex-shrink: 0;">
                                <span class="month-name" style="font-size: 14px; font-weight: 600; color: #1e293b; white-space: nowrap;"><?php echo $m; ?></span>
                                <span class="month-status-tag" id="tag_<?php echo $m; ?>" style="font-size: 10px; font-weight: 700; color: #27AE60; margin-left: auto;"></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Campo para Modo Edición Individual (Single Month) -->
                <div id="singleMonthRow" class="form-row" style="display: none;">
                    <div class="form-group half">
                        <label for="mes">Mes</label>
                        <select id="mes" name="mes">
                            <?php 
                            foreach($listaMeses as $m) {
                                echo "<option value='$m'>$m</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group half">
                        <label for="monto_unitario" style="font-size: 11.5px; font-weight: 600; color: #475569;">Monto por Mes (Bs)</label>
                        <input type="number" step="0.01" id="monto_unitario" name="monto_unitario" required value="<?php echo number_format($monto_mensualidad_sugerido ?? 50.00, 2, '.', ''); ?>" oninput="updateCalculatedTotal()" style="font-size: 12.5px; font-weight: 600; padding: 7px 10px;">
                    </div>
                    <div class="form-group half">
                        <label for="monto">Monto Total Calculado (Bs)</label>
                        <input type="number" step="0.01" id="monto" name="monto" required value="50.00" style="font-weight: 700; color: #27AE60; background-color: #f8fafc;">
                    </div>
                </div>

                <input type="hidden" id="estado" name="estado" value="Pagado">
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-secondary" onclick="closeMensualidadModal()">Cancelar</button>
            <button type="button" class="btn-primary" id="btnGuardarMensualidad" onclick="saveMensualidad()">Pagar</button>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Modal de Confirmación de Eliminación -->
<div id="deleteModal" class="modal">
    <div class="modal-content" style="max-width: 450px; text-align: center; overflow: hidden; padding: 0; border-radius: 12px; background: #ffffff;">
        <div style="background-color: #dc3545; color: white; padding: 16px 20px; display: flex; justify-content: space-between; align-items: center;">
            <h3 style="margin: 0; font-size: 16px; font-weight: 700; letter-spacing: 0.5px;">ELIMINAR REGISTRO</h3>
            <span onclick="closeDeleteModal()" style="color: white; font-size: 24px; cursor: pointer; line-height: 1;">&times;</span>
        </div>
        <div class="modal-body" style="padding: 30px 20px; background-color: white;">
            <p style="margin-bottom: 10px; font-size: 15px; color: #444;">
                ¿Está seguro de que desea eliminar el pago de: <br><strong id="deleteDesc" style="font-size: 17px; color: #111; display: inline-block; margin-top: 10px;"></strong>?
            </p>
        </div>
        <div class="modal-footer" style="padding: 14px 20px; background-color: #f8fafc; border-top: 1px solid #e2e8f0; display: flex; justify-content: flex-end; gap: 10px;">
            <button type="button" class="btn-secondary" onclick="closeDeleteModal()" style="padding: 9px 18px; border-radius: 6px; font-weight: 600; cursor: pointer;">Cancelar</button>
            <button type="button" class="btn-primary" id="btnConfirmDelete" style="background-color: #dc3545; border: none; padding: 9px 20px; border-radius: 6px; font-weight: 700; color: white; cursor: pointer;">Eliminar</button>
        </div>
    </div>
</div>

<!-- Modal Carga Masiva por Rango & Multiaño (2018 - 2029) -->
<div id="rangeModal" class="modal">
    <div class="modal-content" style="max-width: 850px; height: 90vh; max-height: 880px; display: flex; flex-direction: column; padding: 0; overflow: hidden; border-radius: 12px; background: #ffffff;">
        
        <!-- HEADER Y CONTROLES SUPERIORES (SIEMPRE FIJOS ARRIBA) -->
        <div style="flex-shrink: 0; background: #ffffff; border-bottom: 1px solid #e2e8f0; z-index: 10;">
            <div class="modal-header" style="background: linear-gradient(135deg, #1e293b, #0f172a); color: white; padding: 16px 24px; display: flex; justify-content: space-between; align-items: center;">
                <h2 style="margin: 0; font-size: 18px; font-weight: 700; color: #fff;">
                    <i class="fa-solid fa-calendar-days" style="color: #FF7A00; margin-right: 8px;"></i>
                    Carga Masiva de Mensualidades (2018 - 2029)
                </h2>
                <span class="close-btn" onclick="closeRangeModal()" style="color: #fff; font-size: 24px; cursor: pointer;">&times;</span>
            </div>

            <div style="padding: 16px 24px 0 24px;">
                <!-- Selector de Socio y Acción Común -->
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; padding: 12px 16px; border-radius: 10px; margin-bottom: 12px;">
                    <div class="form-row" style="display: flex; gap: 15px; flex-wrap: wrap;">
                        <div style="flex: 2; min-width: 250px;">
                            <label style="font-weight: 600; font-size: 12px; margin-bottom: 4px; display: block; color: #334155;">Socio a Cargar:</label>
                            <select name="id_socio" id="range_id_socio" required onchange="loadAllPaidMonthsMultiYear()" style="width: 100%; padding: 8px; border-radius: 6px; border: 1px solid #cbd5e1; font-size: 13px; background: #fff;">
                                <option value="all" <?php echo !isset($socio_actual) ? 'selected' : ''; ?>>-- TODOS LOS SOCIOS (Carga Global Masiva) --</option>
                                <?php 
                                $sList = isset($sociosList) ? $sociosList : [];
                                foreach ($sList as $sRow) {
                                    $isSel = (isset($socio_actual) && $socio_actual['id_socio'] == $sRow['id_socio']) ? 'selected' : '';
                                    $nombreFull = htmlspecialchars($sRow['ci'] . ' - ' . trim($sRow['ap_paterno'] . ' ' . $sRow['ap_materno'] . ' ' . $sRow['nombre']));
                                    echo "<option value='" . $sRow['id_socio'] . "' $isSel>" . $nombreFull . "</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div style="flex: 1; min-width: 130px;">
                            <label style="font-weight: 600; font-size: 12px; margin-bottom: 4px; display: block; color: #334155;">Acción:</label>
                            <select name="numero_accion" id="range_numero_accion" onchange="loadAllPaidMonthsMultiYear()" style="width: 100%; padding: 8px; border-radius: 6px; border: 1px solid #cbd5e1; background: #fff; font-size: 13px;">
                                <option value="all">Todas las Acciones</option>
                                <option value="1" selected>Acción 1</option>
                                <option value="2">Acción 2</option>
                                <option value="3">Acción 3</option>
                                <option value="4">Acción 4</option>
                                <option value="5">Acción 5</option>
                            </select>
                        </div>
                        <div style="flex: 1; min-width: 130px;">
                            <label style="font-weight: 600; font-size: 12px; margin-bottom: 4px; display: block; color: #334155;">Nº Recibo (Opcional):</label>
                            <input type="text" id="range_numero_recibo" placeholder="Ej: REC-2018-2029" style="width: 100%; padding: 8px; border-radius: 6px; border: 1px solid #cbd5e1; background: #fff; font-size: 13px;">
                        </div>
                    </div>
                </div>

                <!-- Pestañas de Modo de Carga -->
                <div style="display: flex; gap: 8px; background: #f1f5f9; padding: 4px; border-radius: 8px; margin-bottom: 12px;">
                    <button type="button" id="tabBtnMulti" class="modal-tab-btn active" onclick="switchRangeTab('multi')" style="flex: 1; padding: 8px; border: none; border-radius: 6px; font-weight: 700; font-size: 13px; cursor: pointer; background: #ffffff; color: #0284c7; box-shadow: 0 1px 3px rgba(0,0,0,0.1); display: flex; align-items: center; justify-content: center; gap: 8px;">
                        <i class="fa-solid fa-sliders"></i> Configurar Montos y Meses por Año (Multiaño)
                    </button>
                    <button type="button" id="tabBtnRange" class="modal-tab-btn" onclick="switchRangeTab('range')" style="flex: 1; padding: 8px; border: none; border-radius: 6px; font-weight: 700; font-size: 13px; cursor: pointer; background: transparent; color: #64748b; display: flex; align-items: center; justify-content: center; gap: 8px;">
                        <i class="fa-solid fa-arrows-left-right"></i> Rango Continuo (Desde - Hasta)
                    </button>
                </div>

                <!-- Barra de Acciones Rápidas Globales -->
                <div id="globalActionsBar" style="background: #e0f2fe; border: 1px solid #bae6fd; padding: 10px 14px; border-radius: 8px; margin-bottom: 12px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px;">
                    <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                        <span style="font-weight: 600; font-size: 11.5px; color: #0369a1;">Monto Global:</span>
                        <input type="number" step="0.01" id="globalMontoInput" value="50.00" style="width: 60px; padding: 3px 5px; border-radius: 5px; border: 1px solid #7dd3fc; font-weight: 600; font-size: 11.5px; text-align: center; height: 26px;">
                        <button type="button" class="btn-xs" onclick="applyGlobalMonto()" style="background: #0284c7; color: white; border: none; padding: 6px 10px; border-radius: 4px; font-weight: 600; cursor: pointer;">Aplicar a Todos</button>
                        <button type="button" class="btn-xs" onclick="applyDefaultRates()" style="background: #FF7A00; color: white; border: none; padding: 6px 12px; border-radius: 4px; font-weight: 700; cursor: pointer;" title="Fijar 30 Bs para 2018-2024 y 50 Bs para 2025+">
                            <i class="fa-solid fa-wand-magic-sparkles"></i> Tarifas 2018-24 (30 Bs) / 2025+ (50 Bs)
                        </button>
                    </div>
                    <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                        <button type="button" class="btn-xs" onclick="selectAllMonthsGlobal(true)" style="background: #27AE60; color: white; border: none; padding: 7px 12px; border-radius: 6px; font-weight: 700; cursor: pointer; font-size: 12px; display: inline-flex; align-items: center; gap: 5px;">
                            <i class="fa-solid fa-check-double"></i> Seleccionar TODOS los Meses (2018-2029)
                        </button>
                        <button type="button" class="btn-xs" onclick="selectAllMonthsGlobal(false)" style="background: #64748b; color: white; border: none; padding: 7px 10px; border-radius: 6px; font-weight: 600; cursor: pointer; font-size: 12px;">
                            Desmarcar Todos
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- BODY CON SCROLL INDEPENDIENTE (SOLO SCROLIAN LOS AÑOS) -->
        <div class="modal-body" style="flex: 1; overflow-y: auto; padding: 16px 24px; background: #ffffff;">
            
            <!-- TAB 1: CONFIGURACIÓN MULTIAÑO PERSONALIZADA POR AÑO -->
            <div id="tabContentMulti" style="display: block;">
                <!-- Lista de Tarjetas por Año (2018 a 2029) -->
                <div id="yearsContainer" style="display: flex; flex-direction: column; gap: 12px;">
                    <?php 
                    $mesesNom = ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
                    for ($y = 2018; $y <= 2029; $y++): 
                        $montoDefaultAno = ($y <= 2024) ? 30.00 : 50.00;
                    ?>
                        <div class="year-card" id="ycard_<?php echo $y; ?>" style="background: #f8fafc; border: 1.5px solid #cbd5e1; border-radius: 10px; padding: 14px; transition: all 0.2s;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; flex-wrap: wrap; gap: 10px;">
                                <label style="display: flex; align-items: center; gap: 8px; font-weight: 700; font-size: 15px; color: #1e293b; cursor: pointer;">
                                    <input type="checkbox" class="year-enable-cb" data-year="<?php echo $y; ?>" onchange="toggleYearCard(<?php echo $y; ?>)" style="width: 18px; height: 18px; accent-color: #0284c7; cursor: pointer;">
                                    <i class="fa-solid fa-calendar-day" style="color: #0284c7;"></i> Gestión <?php echo $y; ?>
                                    <span style="font-size: 9.5px; font-weight: 500; color: #64748b; background: #e2e8f0; padding: 1px 5px; border-radius: 3px; margin-left: 4px;">
                                        (<?php echo $y <= 2024 ? '30 Bs/mes' : '50 Bs/mes'; ?>)
                                    </span>
                                </label>

                                <div style="display: flex; align-items: center; gap: 6px;">
                                    <span style="font-size: 10.5px; font-weight: 600; color: #64748b;">Monto/Mes (Bs):</span>
                                    <input type="number" step="0.01" class="year-monto-input" id="ymonto_<?php echo $y; ?>" data-year="<?php echo $y; ?>" value="<?php echo number_format($montoDefaultAno, 2, '.', ''); ?>" oninput="updateMultiYearTotals()" style="width: 65px; padding: 3px 5px; border-radius: 5px; border: 1px solid #cbd5e1; font-weight: 600; font-size: 11.5px; color: #27AE60; text-align: center; height: 26px;">
                                    <div style="display: flex; gap: 4px;">
                                        <button type="button" class="btn-xs" onclick="setYearMonths(<?php echo $y; ?>, 'all')" style="background: #e2e8f0; border: none; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: 600; cursor: pointer;">12 Meses</button>
                                        <button type="button" class="btn-xs" onclick="setYearMonths(<?php echo $y; ?>, 'clear')" style="background: #f1f5f9; color: #64748b; border: none; padding: 4px 8px; border-radius: 4px; font-size: 11px; cursor: pointer;">Limpiar</button>
                                    </div>
                                </div>
                            </div>

                            <!-- Grid de 12 Meses para esta Gestión -->
                            <div class="ymonths-grid" id="ymonths_<?php echo $y; ?>" style="display: grid; grid-template-columns: repeat(6, 1fr); gap: 6px;">
                                <?php foreach($mesesNom as $mIdx => $mName): ?>
                                    <label class="ymonth-label" id="ymlabel_<?php echo $y; ?>_<?php echo $mName; ?>" style="display: flex; align-items: center; gap: 5px; font-size: 12px; font-weight: 600; padding: 6px 8px; background: #ffffff; border: 1px solid #cbd5e1; border-radius: 6px; cursor: pointer; user-select: none;">
                                        <input type="checkbox" class="ymonth-cb ymonth-cb-<?php echo $y; ?>" data-year="<?php echo $y; ?>" value="<?php echo $mName; ?>" onchange="updateMultiYearTotals()" style="width: 16px; height: 16px; accent-color: #27AE60; cursor: pointer;">
                                        <span><?php echo substr($mName, 0, 3); ?></span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                            
                            <div style="margin-top: 8px; font-size: 11px; font-weight: 600; color: #64748b; text-align: right;" id="ysubtotal_<?php echo $y; ?>">
                                Subtotal 0 meses = Bs. 0.00
                            </div>
                        </div>
                    <?php endfor; ?>
                </div>
            </div>

            <!-- TAB 2: RANGO RÁPIDO DESDE - HASTA -->
            <div id="tabContentRange" style="display: none;">
                <form id="rangeForm">
                    <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; padding: 16px; border-radius: 8px; margin-bottom: 16px;">
                        <label style="font-weight: 700; font-size: 14px; color: #1e293b; display: block; margin-bottom: 12px;">
                            <i class="fa-solid fa-play" style="color: #27AE60;"></i> Desde (Inicio de Pago):
                        </label>
                        <div class="form-row" style="display: flex; gap: 15px;">
                            <div style="flex: 1;">
                                <label style="font-size: 12px; color: #64748b;">Mes Inicio</label>
                                <select name="mes_inicio" style="width: 100%; padding: 10px; border-radius: 6px; border: 1px solid #cbd5e1;">
                                    <option value="1" selected>Enero</option>
                                    <option value="2">Febrero</option>
                                    <option value="3">Marzo</option>
                                    <option value="4">Abril</option>
                                    <option value="5">Mayo</option>
                                    <option value="6">Junio</option>
                                    <option value="7">Julio</option>
                                    <option value="8">Agosto</option>
                                    <option value="9">Septiembre</option>
                                    <option value="10">Octubre</option>
                                    <option value="11">Noviembre</option>
                                    <option value="12">Diciembre</option>
                                </select>
                            </div>
                            <div style="flex: 1;">
                                <label style="font-size: 12px; color: #64748b;">Año Inicio</label>
                                <select name="anio_inicio" style="width: 100%; padding: 10px; border-radius: 6px; border: 1px solid #cbd5e1;">
                                    <?php for($y=2018; $y<=2029; $y++): ?>
                                        <option value="<?php echo $y; ?>" <?php echo $y==2018?'selected':''; ?>><?php echo $y; ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; padding: 16px; border-radius: 8px; margin-bottom: 16px;">
                        <label style="font-weight: 700; font-size: 14px; color: #1e293b; display: block; margin-bottom: 12px;">
                            <i class="fa-solid fa-flag-checkered" style="color: #FF7A00;"></i> Hasta (Término de Pago):
                        </label>
                        <div class="form-row" style="display: flex; gap: 15px;">
                            <div style="flex: 1;">
                                <label style="font-size: 12px; color: #64748b;">Mes Término</label>
                                <select name="mes_fin" style="width: 100%; padding: 10px; border-radius: 6px; border: 1px solid #cbd5e1;">
                                    <option value="1">Enero</option>
                                    <option value="2">Febrero</option>
                                    <option value="3">Marzo</option>
                                    <option value="4">Abril</option>
                                    <option value="5">Mayo</option>
                                    <option value="6">Junio</option>
                                    <option value="7">Julio</option>
                                    <option value="8">Agosto</option>
                                    <option value="9">Septiembre</option>
                                    <option value="10">Octubre</option>
                                    <option value="11">Noviembre</option>
                                    <option value="12" selected>Diciembre</option>
                                </select>
                            </div>
                            <div style="flex: 1;">
                                <label style="font-size: 12px; color: #64748b;">Año Término</label>
                                <select name="anio_fin" style="width: 100%; padding: 10px; border-radius: 6px; border: 1px solid #cbd5e1;">
                                    <?php for($y=2018; $y<=2029; $y++): ?>
                                        <option value="<?php echo $y; ?>" <?php echo $y==2026?'selected':''; ?>><?php echo $y; ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div style="margin-bottom: 16px;">
                        <label style="font-size: 13px; font-weight: 600; color: #334155;">Monto Fijo por Mes para el Rango (Bs):</label>
                        <input type="number" step="0.01" name="monto_mensual" value="<?php echo number_format($monto_mensualidad_sugerido ?? 50.00, 2, '.', ''); ?>" style="width: 100%; padding: 10px; border-radius: 6px; border: 1px solid #cbd5e1;" required>
                    </div>
                </form>
            </div>

        </div>

        <!-- Sticky Footer con Resumen Total Fijo y Botones de Acción -->
        <div class="modal-footer" style="padding: 16px 24px; background-color: #f8fafc; border-top: 1.5px solid #e2e8f0; display: flex; flex-direction: column; gap: 12px; box-shadow: 0 -4px 12px rgba(0,0,0,0.05);">
            <!-- Resumen Total Calculado (Siempre Visible) -->
            <div style="background: #1e293b; color: white; padding: 12px 18px; border-radius: 8px; display: flex; justify-content: space-between; align-items: center; width: 100%; box-sizing: border-box;">
                <div>
                    <span style="font-size: 11px; color: #94a3b8; display: block; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600;">Resumen Total a Registrar:</span>
                    <strong style="font-size: 15px; color: #38bdf8;" id="summaryTotalText">0 Años seleccionados | 0 Meses Totales</strong>
                </div>
                <div>
                    <span style="font-size: 11px; color: #94a3b8; display: block; text-align: right; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600;">Monto Total Estimado:</span>
                    <strong style="font-size: 20px; color: #4ade80;" id="summaryTotalMonto">Bs. 0.00</strong>
                </div>
            </div>

            <!-- Botones de Acción -->
            <div style="display: flex; justify-content: flex-end; gap: 12px; width: 100%;">
                <button type="button" class="btn-secondary" onclick="closeRangeModal()">Cancelar</button>
                <button type="button" class="btn-primary" id="btnSubmitRange" onclick="submitRangeLoad()" style="background-color: #FF7A00; border: none; padding: 12px 24px; font-weight: 700; font-size: 14px;">
                    <i class="fa-solid fa-floppy-disk"></i> Guardar Cambios Multiaño
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Extra styles/scripts for Mensualidad view -->
<link rel="stylesheet" href="../css/mensualidad.css">
<script>
    const MONTO_MENSUALIDAD_SUGERIDO = "<?php echo number_format($monto_mensualidad_sugerido ?? 50.00, 2, '.', ''); ?>";
</script>
<script src="../js/mensualidad.js"></script>

<?php include 'footer.php'; ?>
