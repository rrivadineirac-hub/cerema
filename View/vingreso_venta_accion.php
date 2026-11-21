<?php
// View/vingreso_venta_accion.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

include __DIR__ . '/header.php'; 
?>

<!-- Estilos específicos del módulo (Separados) -->
<link rel="stylesheet" href="../css/ingreso_venta_accion.css?v=<?php echo filemtime(__DIR__ . '/../css/ingreso_venta_accion.css'); ?>">

<div class="dashboard-header">
    <div class="header-actions" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
        <div>
            <h1 class="page-title">Ingreso por Venta de Acciones</h1>
            <p class="page-subtitle">Registro y administración de ingresos por adquisición y venta de acciones de la fraternidad</p>
        </div>
        <div class="va-header-actions">
            <!-- Selector de Gestión para Filtrar -->
            <form method="GET" action="../Controller/ingreso_venta_accion.controller.php" style="margin: 0; display: inline-flex; align-items: center; gap: 8px;">
                <label for="filtro_gestion" style="font-size: 13px; font-weight: 600; color: var(--text-muted, #64748b);">Gestión:</label>
                <select id="filtro_gestion" name="id_gestion" onchange="this.form.submit()" style="padding: 9px 12px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 13.5px; font-weight: 600; background: #fff; cursor: pointer;">
                    <option value="all" <?php echo ($filtro_gestion === 'all') ? 'selected' : ''; ?>>-- Todas las Gestiones --</option>
                    <?php if (!empty($todas_gestiones)): ?>
                        <?php foreach ($todas_gestiones as $g): ?>
                            <option value="<?php echo $g['id_gestion']; ?>" <?php echo ($filtro_gestion == $g['id_gestion']) ? 'selected' : ''; ?>>
                                Gestión <?php echo htmlspecialchars($g['gestion']); ?><?php echo ($g['estado'] === 'En Curso') ? ' (En Curso)' : ''; ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </form>

            <button class="btn-primary" onclick="openVentaAccionModal()" style="display: inline-flex; align-items: center; gap: 8px; padding: 10px 18px;">
                <i class="fa-solid fa-plus"></i> Registrar Venta de Acción
            </button>
        </div>
    </div>
</div>

<!-- Tarjetas de Resumen Estadístico -->
<div class="iva-stats-grid">
    <div class="iva-stat-card">
        <div class="iva-stat-icon iva-ico-green">
            <i class="fa-solid fa-hand-holding-dollar"></i>
        </div>
        <div class="iva-stat-info">
            <h4>Total Recaudado</h4>
            <p>Bs. <?php echo number_format($estadisticas['total_recaudado'] ?? 0, 2); ?></p>
        </div>
    </div>
    <div class="iva-stat-card">
        <div class="iva-stat-icon iva-ico-blue">
            <i class="fa-solid fa-layer-group"></i>
        </div>
        <div class="iva-stat-info">
            <h4>Acciones Vendidas</h4>
            <p><?php echo number_format($estadisticas['total_acciones_cobradas'] ?? 0); ?> acciones</p>
        </div>
    </div>
    <div class="iva-stat-card">
        <div class="iva-stat-icon iva-ico-orange">
            <i class="fa-solid fa-file-invoice-dollar"></i>
        </div>
        <div class="iva-stat-info">
            <h4>Registros de Venta</h4>
            <p><?php echo number_format($estadisticas['total_registros'] ?? 0); ?> registros</p>
        </div>
    </div>
</div>

<!-- Barra de Búsqueda Rápida -->
<div class="search-section" style="margin-bottom: 20px; display: flex; gap: 10px; align-items: center;">
    <div class="search-input-wrapper" style="position: relative; flex: 1; max-width: 450px;">
        <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: var(--text-muted, #94a3b8);"></i>
        <input type="text" id="searchVentaAccion" onkeyup="filterVentaAccionesTable()" placeholder="Buscar por comprador, asociado o comprobante..." style="width: 100%; padding: 10px 15px 10px 40px; border: 1.5px solid var(--border-color, #cbd5e1); border-radius: var(--radius-sm, 8px); outline: none; font-family: inherit; font-size: 14px;">
    </div>
</div>

<!-- Tabla de Ventas de Acciones -->
<div class="table-container card">
    <div class="table-responsive">
        <table class="data-table" id="tableVentasAcciones" style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr>
                    <th>Comprador / Asociado</th>
                    <th style="text-align: center;">Cant. Acciones</th>
                    <th style="text-align: center;">N° de Acción</th>
                    <th style="text-align: right;">Precio Unit. (Bs.)</th>
                    <th style="text-align: right;">Monto Total (Bs.)</th>
                    <th style="text-align: center;">Fecha Venta</th>
                    <th>Nº Recibo / Comprobante</th>
                    <th>Método Pago</th>
                    <th style="text-align: center;">Estado</th>
                    <th style="text-align: center;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (isset($ventas) && $ventas->rowCount() > 0): ?>
                    <?php while ($v = $ventas->fetch(PDO::FETCH_ASSOC)): ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($v['comprador']); ?></strong>
                                <?php if (!empty($v['id_socio'])): ?>
                                    <div style="font-size: 11px; color: #64748b; margin-top: 2px;">
                                        <i class="fa-solid fa-id-badge" style="color: #0284c7;"></i> Socio CI: <?php echo htmlspecialchars($v['ci'] . (!empty($v['complemento']) ? '-' . $v['complemento'] : '')); ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: center;">
                                <span class="badge-accion-num"><?php echo (int)$v['cantidad_acciones']; ?></span>
                            </td>
                            <td style="text-align: center;">
                                <?php if (!empty($v['numero_accion'])): ?>
                                    <span style="font-weight: 600; color: #1e293b;"><?php echo htmlspecialchars($v['numero_accion']); ?></span>
                                <?php else: ?>
                                    <span style="color: #94a3b8;">—</span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: right; font-weight: 600;">
                                Bs. <?php echo number_format($v['precio_unitario'], 2); ?>
                            </td>
                            <td style="text-align: right;" class="monto-highlight">
                                Bs. <?php echo number_format($v['monto_total'], 2); ?>
                            </td>
                            <td style="text-align: center; white-space: nowrap;">
                                <?php echo date('d/m/Y', strtotime($v['fecha_venta'])); ?>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($v['numero_recibo'] ?: '-'); ?>
                            </td>
                            <td>
                                <span style="font-size: 12.5px; color: #334155; font-weight: 500;">
                                    <?php echo htmlspecialchars($v['metodo_pago']); ?>
                                </span>
                            </td>
                            <td style="text-align: center;">
                                <?php 
                                    $estadoClass = ($v['estado'] === 'Cobrado') ? 'estado-cobrado' : 'estado-anulado';
                                ?>
                                <span class="badge-status <?php echo $estadoClass; ?>">
                                    <?php echo htmlspecialchars($v['estado']); ?>
                                </span>
                            </td>
                            <td class="actions" style="text-align: center;">
                                <div class="action-buttons" style="justify-content: center;">
                                    <button class="btn-icon edit-btn" onclick="editVentaAccion(<?php echo $v['id_venta_accion']; ?>)" title="Editar venta">
                                        <i class="fa-solid fa-pen"></i>
                                    </button>
                                    <button class="btn-icon delete-btn" onclick="deleteVentaAccion(<?php echo $v['id_venta_accion']; ?>, '<?php echo addslashes($v['comprador'] . ' (' . $v['cantidad_acciones'] . ' acc.)'); ?>')" title="Eliminar registro">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="10" style="text-align: center; padding: 35px 20px; color: #64748b;">
                            <i class="fa-solid fa-circle-info" style="font-size: 24px; color: #94a3b8; display: block; margin-bottom: 8px;"></i>
                            No hay ingresos por venta de acciones registrados en esta gestión.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal para Crear/Editar Venta de Acciones (Header y Footer Fijos) -->
<div id="ventaAccionModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitle" style="margin: 0; font-size: 18px; font-weight: 700; color: #1e293b;">Registrar Venta de Acción</h3>
            <span class="close-btn" onclick="closeVentaAccionModal()">&times;</span>
        </div>
        <div class="modal-body">
            <form id="ventaAccionForm" onsubmit="event.preventDefault(); saveVentaAccion();">
                <input type="hidden" id="id_venta_accion" name="id_venta_accion">
                <input type="hidden" id="id_gestion" name="id_gestion" value="<?php echo $id_gestion_activa; ?>">

                <!-- Vincular a Asociado (Opcional) -->
                <div class="form-group" style="margin-bottom: 14px;">
                    <label for="id_socio" style="display: block; margin-bottom: 5px; font-weight: 600; font-size: 13px; color: #334155;">
                        Asociado Existente (Opcional si ya está registrado):
                    </label>
                    <select id="id_socio" name="id_socio" onchange="onSocioSelectChange()" style="width: 100%; padding: 10px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 13.5px; background: #fff;">
                        <option value="">-- No vincular / Comprador externo --</option>
                        <?php if (!empty($sociosList)): ?>
                            <?php foreach ($sociosList as $s): ?>
                                <?php 
                                    $ciFull = $s['ci'] . (!empty($s['complemento']) ? '-' . $s['complemento'] : '');
                                    $nombreCompleto = trim($s['ap_paterno'] . ' ' . $s['ap_materno'] . ' ' . $s['nombre']);
                                ?>
                                <option value="<?php echo $s['id_socio']; ?>" data-nombre="<?php echo htmlspecialchars($nombreCompleto); ?>">
                                    <?php echo htmlspecialchars($ciFull . ' - ' . $nombreCompleto); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <!-- Nombre del Comprador -->
                <div class="form-group" style="margin-bottom: 14px;">
                    <label for="comprador" style="display: block; margin-bottom: 5px; font-weight: 600; font-size: 13px; color: #334155;">
                        Nombre Completo del Comprador / Titular (*)
                    </label>
                    <input type="text" id="comprador" name="comprador" placeholder="Ej: JUAN PÉREZ ROJAS" required style="width: 100%; padding: 10px 12px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 14px; text-transform: uppercase;">
                </div>

                <!-- Cantidad de Acciones y N° de Acción Asignada -->
                <div class="form-row" style="display: flex; gap: 15px; margin-bottom: 14px;">
                    <div class="form-group half" style="flex: 1;">
                        <label for="cantidad_acciones" style="display: block; margin-bottom: 5px; font-weight: 600; font-size: 13px; color: #334155;">
                            Cantidad de Acciones (*)
                        </label>
                        <input type="number" id="cantidad_acciones" name="cantidad_acciones" min="1" step="1" value="1" required oninput="calculateVentaTotal()" style="width: 100%; padding: 10px 12px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 14px; font-weight: 600;">
                    </div>
                    <div class="form-group half" style="flex: 1;">
                        <label for="numero_accion" style="display: block; margin-bottom: 5px; font-weight: 600; font-size: 13px; color: #334155;">
                            N° de Acción (Opcional)
                        </label>
                        <input type="text" id="numero_accion" name="numero_accion" placeholder="Ej: Acción 2 / Cert. #105" style="width: 100%; padding: 10px 12px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 14px;">
                    </div>
                </div>

                <!-- Precio Unitario y Monto Total Calculado -->
                <div class="form-row" style="display: flex; gap: 15px; margin-bottom: 14px;">
                    <div class="form-group half" style="flex: 1;">
                        <label for="precio_unitario" style="display: block; margin-bottom: 5px; font-weight: 600; font-size: 13px; color: #334155;">
                            Precio por Acción (Bs.)
                        </label>
                        <input type="number" id="precio_unitario" name="precio_unitario" min="0" step="0.01" value="0.00" oninput="calculateVentaTotal()" placeholder="0.00" style="width: 100%; padding: 10px 12px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 14px;">
                    </div>
                    <div class="form-group half" style="flex: 1;">
                        <label for="monto_total" style="display: block; margin-bottom: 5px; font-weight: 600; font-size: 13px; color: #334155;">
                            Monto Total Calculado (Bs.) (*)
                        </label>
                        <input type="number" id="monto_total" name="monto_total" min="0.01" step="0.01" required placeholder="0.00" style="width: 100%; padding: 10px 12px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 14px; font-weight: 700; color: #15803d; background: #f8fafc;">
                    </div>
                </div>

                <!-- Fecha de Venta y Nº Recibo -->
                <div class="form-row" style="display: flex; gap: 15px; margin-bottom: 14px;">
                    <div class="form-group half" style="flex: 1;">
                        <label for="fecha_venta" style="display: block; margin-bottom: 5px; font-weight: 600; font-size: 13px; color: #334155;">
                            Fecha de Venta (*)
                        </label>
                        <input type="date" id="fecha_venta" name="fecha_venta" required style="width: 100%; padding: 10px 12px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 14px;">
                    </div>
                    <div class="form-group half" style="flex: 1;">
                        <label for="numero_recibo" style="display: block; margin-bottom: 5px; font-weight: 600; font-size: 13px; color: #334155;">
                            Nº Recibo / Comprobante
                        </label>
                        <input type="text" id="numero_recibo" name="numero_recibo" placeholder="Ej: REC-0045" style="width: 100%; padding: 10px 12px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 14px; text-transform: uppercase;">
                    </div>
                </div>

                <!-- Método de Pago y Estado -->
                <div class="form-row" style="display: flex; gap: 15px; margin-bottom: 14px;">
                    <div class="form-group half" style="flex: 1;">
                        <label for="metodo_pago" style="display: block; margin-bottom: 5px; font-weight: 600; font-size: 13px; color: #334155;">
                            Método de Pago
                        </label>
                        <select id="metodo_pago" name="metodo_pago" style="width: 100%; padding: 10px 12px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 14px; background: #fff;">
                            <option value="Efectivo">Efectivo</option>
                            <option value="Transferencia">Transferencia Bancaria</option>
                            <option value="QR">Pago QR</option>
                            <option value="Depósito">Depósito Bancario</option>
                            <option value="Otro">Otro</option>
                        </select>
                    </div>
                    <div class="form-group half" id="group_estado" style="flex: 1; display: none;">
                        <label for="estado" style="display: block; margin-bottom: 5px; font-weight: 600; font-size: 13px; color: #334155;">
                            Estado del Pago
                        </label>
                        <select id="estado" name="estado" style="width: 100%; padding: 10px 12px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 14px; background: #fff;">
                            <option value="Cobrado">Cobrado / Pagado</option>
                            <option value="Anulado">Anulado</option>
                        </select>
                    </div>
                </div>

                <!-- Observaciones -->
                <div class="form-group" style="margin-bottom: 10px;">
                    <label for="observaciones" style="display: block; margin-bottom: 5px; font-weight: 600; font-size: 13px; color: #334155;">
                        Observaciones / Detalle Adicional
                    </label>
                    <textarea id="observaciones" name="observaciones" rows="2" placeholder="Detalles de la emisión o transferencia de acciones..." style="width: 100%; padding: 10px 12px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 13.5px; font-family: inherit;"></textarea>
                </div>
            </form>
        </div>

        <!-- Sticky Footer con Cancelar y Guardar siempre visibles -->
        <div class="modal-footer">
            <button type="button" class="btn-secondary" onclick="closeVentaAccionModal()">Cancelar</button>
            <button type="button" class="btn-primary" id="btnGuardarVentaAccion" onclick="saveVentaAccion()">Guardar Venta</button>
        </div>
    </div>
</div>

<!-- Modal Confirmar Eliminación -->
<div id="deleteModal" class="modal">
    <div class="modal-content" style="max-width: 420px; border-radius: 12px; overflow: hidden; background: #ffffff;">
        <div class="modal-header" style="background: #ef4444; color: white;">
            <h3 style="margin: 0; font-size: 16px; font-weight: 700; color: #ffffff;">Confirmar Eliminación</h3>
            <span class="close-btn" onclick="document.getElementById('deleteModal').style.display='none'" style="color: #ffffff;">&times;</span>
        </div>
        <div class="modal-body" style="padding: 24px 20px;">
            <p style="margin: 0 0 10px 0; font-size: 14.5px; color: #334155;">
                ¿Estás seguro de que deseas eliminar el registro de venta de acción de: <br><strong id="deleteDesc" style="color: #1e293b; display: inline-block; margin-top: 6px;"></strong>?
            </p>
            <p style="color: #dc2626; font-size: 13px; margin: 10px 0 0 0; font-weight: 500;">
                <i class="fa-solid fa-triangle-exclamation"></i> Esta acción no se puede deshacer.
            </p>
        </div>
        <div class="modal-footer" style="padding: 12px 20px; background: #f8fafc; border-top: 1px solid #e2e8f0; display: flex; justify-content: flex-end; gap: 10px;">
            <button type="button" class="btn-secondary" onclick="document.getElementById('deleteModal').style.display='none'">Cancelar</button>
            <button type="button" class="btn-primary" style="background-color: #ef4444; border: none;" onclick="confirmDeleteVentaAccion()">Eliminar</button>
        </div>
    </div>
</div>

<!-- Scripts del Módulo (Separados con cache-busting) -->
<script src="../js/ingreso_venta_accion.js?v=<?php echo filemtime(__DIR__ . '/../js/ingreso_venta_accion.js'); ?>"></script>

<?php include __DIR__ . '/footer.php'; ?>
