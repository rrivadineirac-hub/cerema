<?php
// View/vvalor_accion.php
require_once __DIR__ . '/header.php';
?>
<link rel="stylesheet" href="../css/mensualidad.css">
<link rel="stylesheet" href="../css/valor_accion.css">

<?php if (!$socio_actual): ?>
<div class="selection-container">
    <div class="selection-card">
        <div class="selection-icon"><i class="fa-solid fa-chart-line"></i></div>
        <h2>Seleccionar Asociado</h2>
        <p>Para gestionar el valor de cada acción, primero elige un asociado.</p>
        <form action="../Controller/valor_accion.controller.php" method="GET" class="selection-form">
            <div class="form-group">
                <label for="id_socio_select" class="va-label">Asociado:</label>
                <select name="id_socio" id="id_socio_select" required class="select-searchable">
                    <option value="" disabled selected>-- Selecciona un asociado --</option>
                    <?php foreach ($socios as $s): ?>
                        <?php $ci_full = $s['ci'] . (!empty($s['complemento']) ? '-' . $s['complemento'] : ''); ?>
                        <option value="<?php echo $s['id_socio']; ?>">
                            <?php echo htmlspecialchars($ci_full . ' - ' . $s['ap_paterno'] . ' ' . $s['ap_materno'] . ' ' . $s['nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn-primary btn-block btn-orange">
                Continuar <i class="fa-solid fa-arrow-right"></i>
            </button>
        </form>
    </div>
</div>

<?php else: ?>
<?php $ci_socio_full = $socio_actual['ci'] . (!empty($socio_actual['complemento']) ? '-' . $socio_actual['complemento'] : ''); ?>
<div class="dashboard-header">
    <div class="va-header">
        <div>
            <a href="../Controller/valor_accion.controller.php" class="back-link"><i class="fa-solid fa-arrow-left"></i> Volver a selección</a>
            <h1 class="page-title">Valor de la Acción</h1>
            <p class="page-subtitle">Asociado: <strong><?php echo htmlspecialchars($socio_actual['ap_paterno'] . ' ' . $socio_actual['ap_materno'] . ', ' . $socio_actual['nombre'] . ' (CI: ' . $ci_socio_full . ')'); ?></strong></p>
        </div>
        <div class="va-header-actions">
            <a href="../Controller/valor_accion.controller.php" class="btn-secondary va-btn-link"><i class="fa-solid fa-users"></i> Otro Asociado</a>
            <button class="btn-primary" id="btnOpenValorModal" onclick="openValorModal()"
                <?php echo ($resumen_socio['registradas'] >= $resumen_socio['acciones']) ? 'disabled title="Todas las acciones ya tienen valor asignado"' : ''; ?>>
                <i class="fa-solid fa-plus"></i> Asignar Valor
            </button>
        </div>
    </div>
</div>

<div class="report-stats-grid va-stats">
    <div class="report-stat-card">
        <div class="report-stat-icon va-ico-blue"><i class="fa-solid fa-layer-group"></i></div>
        <div class="report-stat-info"><h4>Cant. Acciones</h4><p><?php echo $resumen_socio['acciones']; ?></p></div>
    </div>
    <div class="report-stat-card">
        <div class="report-stat-icon va-ico-indigo"><i class="fa-solid fa-list-check"></i></div>
        <div class="report-stat-info"><h4>Con Valor Asignado</h4><p><?php echo $resumen_socio['registradas']; ?> / <?php echo $resumen_socio['acciones']; ?></p></div>
    </div>
    <div class="report-stat-card">
        <div class="report-stat-icon va-ico-amber"><i class="fa-solid fa-scale-balanced"></i></div>
        <div class="report-stat-info"><h4>Valor Promedio</h4><p>Bs. <?php echo number_format($resumen_socio['promedio'], 2); ?></p></div>
    </div>
    <div class="report-stat-card">
        <div class="report-stat-icon va-ico-green"><i class="fa-solid fa-sack-dollar"></i></div>
        <div class="report-stat-info"><h4>Valor Total</h4><p>Bs. <?php echo number_format($resumen_socio['valor_total'], 2); ?></p></div>
    </div>
</div>

<div class="table-container">
    <table class="data-table" id="valorTable">
        <thead>
            <tr>
                <th class="va-center" style="width:100px;">Acción N°</th>
                <th class="va-right" style="width:160px;">Valor (Bs.)</th>
                <th class="va-center" style="width:130px;">Fecha</th>
                <th>Observaciones</th>
                <th class="va-center" style="width:110px;">Estado</th>
                <th class="va-center" style="width:110px;">Opciones</th>
            </tr>
        </thead>
        <tbody>
        <?php for ($a = 1; $a <= $resumen_socio['acciones']; $a++): $v = $acciones_map[$a] ?? null; ?>
            <tr class="<?php echo $v ? '' : 'va-row-empty'; ?>">
                <td class="va-center va-bold">#<?php echo $a; ?></td>
                <?php if ($v): ?>
                    <td class="va-right va-valor">Bs. <?php echo number_format($v['valor'], 2); ?></td>
                    <td class="va-center"><?php echo date('d/m/Y', strtotime($v['fecha_valor'])); ?></td>
                    <td class="va-muted"><?php echo !empty($v['observaciones']) ? htmlspecialchars($v['observaciones']) : '-'; ?></td>
                    <td class="va-center"><span class="va-badge va-badge-ok">Asignado</span></td>
                    <td class="actions-col">
                        <button class="btn-icon edit-btn" onclick="editValor(<?php echo $v['id_valor']; ?>)" title="Editar"><i class="fa-solid fa-pen"></i></button>
                        <button class="btn-icon delete-btn" onclick="deleteValor(<?php echo $v['id_valor']; ?>, 'Acción N° <?php echo $a; ?>')" title="Eliminar"><i class="fa-solid fa-trash"></i></button>
                    </td>
                <?php else: ?>
                    <td class="va-right va-muted">—</td>
                    <td class="va-center va-muted">—</td>
                    <td class="va-muted">Sin valor asignado</td>
                    <td class="va-center"><span class="va-badge va-badge-pend">Pendiente</span></td>
                    <td class="actions-col">
                        <button class="btn-icon edit-btn" onclick="openValorModal(<?php echo $a; ?>)" title="Asignar valor"><i class="fa-solid fa-plus"></i></button>
                    </td>
                <?php endif; ?>
            </tr>
        <?php endfor; ?>
        </tbody>
    </table>
</div>

<!-- Modal Crear/Editar -->
<div id="valorModal" class="modal">
    <div class="modal-content va-modal">
        <div class="modal-header">
            <h2 id="modalTitle">Asignar Valor de Acción</h2>
            <span class="close-btn" onclick="closeValorModal()">&times;</span>
        </div>
        <div class="modal-body">
            <form id="valorForm">
                <input type="hidden" name="action" value="save">
                <input type="hidden" id="id_valor" name="id_valor" value="">
                <input type="hidden" id="id_socio" name="id_socio" value="<?php echo $selected_socio_id; ?>">
                <div class="form-row va-mb">
                    <div class="form-group half">
                        <label for="numero_accion">Acción N° (*)</label>
                        <select id="numero_accion" name="numero_accion" required>
                            <?php for ($a = 1; $a <= $resumen_socio['acciones']; $a++): ?>
                                <option value="<?php echo $a; ?>" data-used="<?php echo isset($acciones_map[$a]) ? '1' : '0'; ?>">Acción N° <?php echo $a; ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="form-group half">
                        <label for="valor">Valor (Bs.) (*)</label>
                        <input type="number" step="0.01" min="0.01" id="valor" name="valor" required placeholder="0.00">
                    </div>
                </div>
                <div class="form-group va-mb">
                    <label for="fecha_valor">Fecha (*)</label>
                    <input type="date" id="fecha_valor" name="fecha_valor" required value="<?php echo date('Y-m-d'); ?>">
                </div>
                <div class="form-group va-mb">
                    <label for="observaciones">Observaciones</label>
                    <textarea id="observaciones" name="observaciones" rows="3" placeholder="Notas sobre la valoración..."></textarea>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-secondary" onclick="closeValorModal()">Cancelar</button>
            <button type="button" class="btn-primary" id="btnGuardarValor" onclick="saveValor()">Guardar</button>
        </div>
    </div>
</div>

<!-- Modal Eliminar -->
<div id="deleteModal" class="modal">
    <div class="modal-content va-delete-modal">
        <div class="va-delete-header">
            <h3>ELIMINAR VALOR</h3>
            <span onclick="closeDeleteModal()" class="va-delete-close">&times;</span>
        </div>
        <div class="modal-body va-delete-body">
            <p>¿Estás seguro de eliminar el valor de: <br><strong id="deleteDesc"></strong>?</p>
            <div class="va-delete-actions">
                <button type="button" class="btn-secondary" onclick="closeDeleteModal()">Cancelar</button>
                <button type="button" class="btn-primary va-btn-danger" id="btnConfirmDelete">Eliminar</button>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<script src="../js/valor_accion.js"></script>
<?php require_once __DIR__ . '/footer.php'; ?>
