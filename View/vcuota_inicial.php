<?php 
// View/vcuota_inicial.php
require_once __DIR__ . '/header.php'; 
?>
<link rel="stylesheet" href="../css/mensualidad.css">

<?php if (!$socio_actual): ?>
<!-- PANTALLA / MODAL DE SELECCIÓN DE ASOCIADO -->
<div class="selection-container">
    <div class="selection-card">
        <div class="selection-icon">
            <i class="fa-solid fa-gift"></i>
        </div>
        <h2>Seleccionar Asociado</h2>
        <p>Para gestionar la cuota inicial (pagos por acciones), primero debes elegir a un asociado de la lista.</p>
        
        <form action="../Controller/cuota_inicial.controller.php" method="GET" class="selection-form">
            <div class="form-group">
                <label for="id_socio_select" style="font-weight: 700; color: #334155; font-size: 13.5px; margin-bottom: 8px; display: block; text-align: left;">Asociado:</label>
                <select name="id_socio" id="id_socio_select" required class="select-searchable">
                    <option value="" disabled selected>-- Selecciona un asociado --</option>
                    <?php if (!empty($socios)): ?>
                        <?php foreach ($socios as $s): ?>
                            <option value="<?php echo $s['id_socio']; ?>">
                                <?php echo htmlspecialchars($s['ci'] . ' - ' . $s['ap_paterno'] . ' ' . $s['ap_materno'] . ' ' . $s['nombre']); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
            <button type="submit" class="btn-primary btn-block btn-orange">
                Continuar <i class="fa-solid fa-arrow-right"></i>
            </button>
        </form>
    </div>
</div>

<?php else: ?>
<?php 
$is_cuota_completada = false;
if ($resumen_socio && $resumen_socio['meta_total'] > 0 && $resumen_socio['saldo_pendiente'] <= 0) {
    $is_cuota_completada = true;
}
?>
<!-- PANTALLA DEL CRUD DE CUOTA INICIAL -->
<div class="dashboard-header">
    <div class="header-actions" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
        <div>
            <a href="../Controller/cuota_inicial.controller.php" class="back-link"><i class="fa-solid fa-arrow-left"></i> Volver a selección</a>
            <h1 class="page-title">Gestión de Cuota Inicial</h1>
            <p class="page-subtitle">Asociado: <strong><?php echo htmlspecialchars($socio_actual['ap_paterno'] . ' ' . $socio_actual['ap_materno'] . ', ' . $socio_actual['nombre'] . ' (CI: ' . $socio_actual['ci'] . ')'); ?></strong></p>
        </div>
        <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
            <a href="../Controller/cuota_inicial.controller.php" class="btn-secondary" style="display: inline-flex; align-items: center; gap: 8px; text-decoration: none; padding: 10px 16px; border-radius: 8px; font-size: 14px; font-weight: 600;">
                <i class="fa-solid fa-users"></i> Otro Asociado
            </a>
            <button class="btn-primary" id="btnOpenCuotaModal" onclick="openCuotaModal()" <?php echo $is_cuota_completada ? 'disabled title="La cuota inicial ya ha sido completada en su totalidad"' : ''; ?>>
                <i class="fa-solid fa-plus"></i> Registrar Pago Cuota Inicial
            </button>
        </div>
    </div>
</div>

<?php if ($resumen_socio): ?>
<!-- Tarjetas de Resumen de Cuota Inicial para el Socio Seleccionado -->
<div class="report-stats-grid" style="margin-bottom: 20px;">
    <div class="report-stat-card">
        <div class="report-stat-icon active-users" style="background-color: rgba(14, 165, 233, 0.1); color: #0284c7;">
            <i class="fa-solid fa-layer-group"></i>
        </div>
        <div class="report-stat-info">
            <h4>Cant. Acciones</h4>
            <p><?php echo number_format($resumen_socio['acciones']); ?></p>
        </div>
    </div>

    <div class="report-stat-card">
        <div class="report-stat-icon shares" style="background-color: rgba(99, 102, 241, 0.1); color: #6366f1;">
            <i class="fa-solid fa-coins"></i>
        </div>
        <div class="report-stat-info">
            <h4>Cuota / Acción</h4>
            <p>Bs. <?php echo number_format($resumen_socio['cuota_por_accion'], 2); ?></p>
        </div>
    </div>

    <div class="report-stat-card">
        <div class="report-stat-icon active-users" style="background-color: rgba(39, 174, 96, 0.1); color: #27ae60;">
            <i class="fa-solid fa-sack-dollar"></i>
        </div>
        <div class="report-stat-info">
            <h4>Total Pagado</h4>
            <p>Bs. <?php echo number_format($resumen_socio['total_pagado'], 2); ?></p>
        </div>
    </div>

    <div class="report-stat-card">
        <div class="report-stat-icon shares" style="background-color: <?php echo ($resumen_socio['saldo_pendiente'] > 0) ? 'rgba(239, 68, 68, 0.1)' : 'rgba(34, 197, 94, 0.1)'; ?>; color: <?php echo ($resumen_socio['saldo_pendiente'] > 0) ? '#ef4444' : '#22c55e'; ?>;">
            <i class="fa-solid <?php echo ($resumen_socio['saldo_pendiente'] > 0) ? 'fa-hourglass-half' : 'fa-check-double'; ?>"></i>
        </div>
        <div class="report-stat-info">
            <h4>Saldo Pendiente</h4>
            <p>Bs. <?php echo number_format($resumen_socio['saldo_pendiente'], 2); ?></p>
        </div>
    </div>
</div>

<?php if ($is_cuota_completada): ?>
<div style="background-color: #f0fdf4; border: 1.5px solid #bbf7d0; color: #166534; padding: 14px 20px; border-radius: 12px; margin-bottom: 20px; font-weight: 600; font-size: 14px; display: flex; align-items: center; gap: 12px; box-shadow: 0 2px 8px rgba(34, 197, 94, 0.08);">
    <i class="fa-solid fa-circle-check" style="font-size: 20px; color: #22c55e;"></i>
    <span>✔ <strong>Cuota Inicial Completada:</strong> El asociado ha pagado la totalidad del monto requerido por sus acciones (Saldo pendiente: <strong>Bs. 0.00</strong>). El botón para registrar nuevos abonos ha sido deshabilitado.</span>
</div>
<?php elseif ($resumen_socio['cuota_por_accion'] <= 0): ?>
<div style="background-color: #fffbeb; border: 1.5px solid #fef3c7; color: #92400e; padding: 14px 20px; border-radius: 12px; margin-bottom: 20px; font-weight: 500; font-size: 14px; display: flex; align-items: center; gap: 12px;">
    <i class="fa-solid fa-circle-info" style="font-size: 20px; color: #f59e0b;"></i>
    <span>⚠️ <strong>Cuota Inicial No Configurada:</strong> La cuota inicial por acción para este asociado figura en <strong>Bs. 0.00</strong>. Puedes asignarle una cuota inicial editando los datos del asociado en el listado de Asociados.</span>
</div>
<?php endif; ?>
<?php endif; ?>

<!-- Tabla de Pagos de Cuota Inicial -->
<div class="table-container">
    <table class="data-table" id="cuotaTable">
        <thead>
            <tr>
                <th style="width: 50px; text-align: center;">N°</th>
                <th style="width: 120px;">N° Recibo</th>
                <th style="width: 90px; text-align: center;">Acción N°</th>
                <th>Concepto / Detalle</th>
                <th style="width: 140px; text-align: right;">Monto (Bs.)</th>
                <th style="width: 130px; text-align: center;">Fecha de Pago</th>
                <th>Observaciones</th>
                <th style="width: 100px; text-align: center;">Opciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($pagos_cuota)): ?>
                <?php 
                $count = 1;
                foreach ($pagos_cuota as $pago):
                    $fechaP = !empty($pago['fecha_pago']) ? date('d/m/Y H:i', strtotime($pago['fecha_pago'])) : '-';
                    $reciboTxt = !empty($pago['numero_recibo']) ? htmlspecialchars($pago['numero_recibo']) : '-';
                ?>
                    <tr>
                        <td style="text-align: center; font-weight: 500;"><?php echo $count++; ?></td>
                        <td style="font-weight: 600; font-family: monospace; font-size: 13.5px; color: var(--primary);"><?php echo $reciboTxt; ?></td>
                        <td style="text-align: center; font-weight: 700;"><?php echo htmlspecialchars($pago['numero_accion'] ?? 1); ?></td>
                        <td style="font-weight: 600; color: var(--text-main);"><?php echo htmlspecialchars($pago['concepto']); ?></td>
                        <td style="text-align: right; font-weight: 700; color: #27ae60; font-size: 14px;">Bs. <?php echo number_format($pago['monto'], 2); ?></td>
                        <td style="text-align: center; font-size: 13px; white-space: nowrap;"><?php echo $fechaP; ?></td>
                        <td style="font-size: 13px; color: var(--text-muted); max-width: 250px;"><?php echo !empty($pago['observaciones']) ? htmlspecialchars($pago['observaciones']) : '-'; ?></td>
                        <td class="actions-col">
                            <button class="btn-icon edit-btn" onclick="editCuota(<?php echo $pago['id_cuota']; ?>)" title="Editar Pago">
                                <i class="fa-solid fa-pen"></i>
                            </button>
                            <button class="btn-icon delete-btn" onclick="deleteCuota(<?php echo $pago['id_cuota']; ?>, '<?php echo $reciboTxt; ?>')" title="Eliminar Pago">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8" style="text-align: center; padding: 45px; color: var(--text-muted);">
                        <i class="fa-solid fa-receipt" style="font-size: 40px; margin-bottom: 10px; display: block; opacity: 0.3;"></i>
                        No hay pagos de cuota inicial registrados para este asociado.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Modal para Crear/Editar Pago de Cuota Inicial -->
<div id="cuotaModal" class="modal">
    <div class="modal-content" style="max-width: 580px;">
        <div class="modal-header">
            <h2 id="modalTitle">Registrar Pago de Cuota Inicial</h2>
            <span class="close-btn" onclick="closeCuotaModal()">&times;</span>
        </div>
        <div class="modal-body">
            <form id="cuotaForm">
                <input type="hidden" name="action" value="save">
                <input type="hidden" id="id_cuota" name="id_cuota" value="">
                <input type="hidden" id="id_socio" name="id_socio" value="<?php echo $selected_socio_id; ?>">

                <div class="form-row" style="margin-bottom: 15px;">
                    <div class="form-group half">
                        <label for="numero_recibo">N° de Recibo</label>
                        <input type="text" id="numero_recibo" name="numero_recibo" placeholder="Ej: REC-001">
                        <small id="reciboCheckMsg" style="display: none; font-size: 11.5px; font-weight: 600; margin-top: 3px;"></small>
                    </div>
                    <div class="form-group half">
                        <label for="numero_accion">Acción N° (*)</label>
                        <select id="numero_accion" name="numero_accion" required>
                            <?php 
                            $numAcc = (int)($resumen_socio['acciones'] ?? 1);
                            for ($a = 1; $a <= $numAcc; $a++):
                            ?>
                                <option value="<?php echo $a; ?>">Acción N° <?php echo $a; ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>

                <?php 
                $saldo_pendiente_val = isset($resumen_socio['saldo_pendiente']) ? (float)$resumen_socio['saldo_pendiente'] : 0.00;
                ?>
                <script>
                    var SALDO_PENDIENTE_SOCIO = <?php echo json_encode($saldo_pendiente_val); ?>;
                </script>

                <div class="form-row" style="margin-bottom: 15px;">
                    <div class="form-group half">
                        <label for="monto">Monto Acreditado (Bs.) (*)</label>
                        <input type="number" step="0.01" min="0.01" id="monto" name="monto" required placeholder="0.00" value="<?php echo number_format($saldo_pendiente_val, 2, '.', ''); ?>" max="<?php echo number_format($saldo_pendiente_val, 2, '.', ''); ?>">
                        <small id="montoMaxMsg" style="display: block; font-size: 11.5px; color: #64748b; margin-top: 4px; font-weight: 600;">
                            Monto máx. a acreditar: <strong id="montoMaxSpan" style="color: #27ae60;">Bs. <?php echo number_format($saldo_pendiente_val, 2); ?></strong>
                        </small>
                    </div>
                    <div class="form-group half">
                        <label for="fecha_pago">Fecha de Pago (*)</label>
                        <input type="datetime-local" id="fecha_pago" name="fecha_pago" required value="<?php echo date('Y-m-d\TH:i'); ?>">
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 15px;">
                    <label for="concepto">Concepto / Detalle (*)</label>
                    <input type="text" id="concepto" name="concepto" required value="Pago de Cuota Inicial" placeholder="Ej: Aporte inicial de ingreso...">
                </div>

                <div class="form-group" style="margin-bottom: 15px;">
                    <label for="observaciones">Observaciones Adicionales</label>
                    <textarea id="observaciones" name="observaciones" rows="3" placeholder="Forma de pago, recibo de caja, notas..."></textarea>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-secondary" onclick="closeCuotaModal()">Cancelar</button>
            <button type="button" class="btn-primary" id="btnGuardarCuota" onclick="saveCuota()">Guardar Pago</button>
        </div>
    </div>
</div>

<!-- Modal de Confirmación de Eliminación -->
<div id="deleteModal" class="modal">
    <div class="modal-content" style="max-width: 450px; text-align: center; overflow: hidden; padding: 0;">
        <div style="background-color: #e74c3c; color: white; padding: 15px 20px; display: flex; justify-content: space-between; align-items: center;">
            <h3 style="margin: 0; font-size: 16px; font-weight: 600; letter-spacing: 1px;">ELIMINAR PAGO</h3>
            <span onclick="closeDeleteModal()" style="color: white; font-size: 24px; cursor: pointer; line-height: 1;">&times;</span>
        </div>
        <div class="modal-body" style="padding: 30px 20px; background-color: white;">
            <p style="margin-bottom: 25px; font-size: 15px; color: #444;">
                ¿Estás seguro de que deseas eliminar el registro de pago recibo: <br><strong id="deleteDesc" style="font-size: 17px; color: #222; display: inline-block; margin-top: 8px;"></strong>?
            </p>
            <div style="display: flex; justify-content: center; gap: 15px;">
                <button type="button" class="btn-secondary" onclick="closeDeleteModal()">Cancelar</button>
                <button type="button" class="btn-primary" id="btnConfirmDelete" style="background-color: #e74c3c; border-color: #e74c3c;">Eliminar</button>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<script src="../js/cuota_inicial.js"></script>

<?php require_once __DIR__ . '/footer.php'; ?>
