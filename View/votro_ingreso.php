<?php
// View/votro_ingreso.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

include __DIR__ . '/header.php'; 
?>

<div class="dashboard-header">
    <div class="header-actions" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
        <div>
            <h1 class="page-title">Otros Ingresos</h1>
            <p class="page-subtitle">Gestión de ingresos diversos u otros conceptos de la fraternidad</p>
        </div>
        <button class="btn-primary" onclick="openOtroIngresoModal()">
            <i class="fa-solid fa-plus"></i> Registrar Ingreso
        </button>
    </div>
</div>

<div class="search-section" style="margin-bottom: 20px; display: flex; gap: 10px; align-items: center;">
    <div class="search-input-wrapper" style="position: relative; flex: 1; max-width: 400px;">
        <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: var(--text-muted, #94a3b8);"></i>
        <input type="text" id="searchIngreso" onkeyup="filterIngresosTable()" placeholder="Buscar por concepto o comprobante..." style="width: 100%; padding: 10px 15px 10px 40px; border: 1px solid var(--border-color, #e2e8f0); border-radius: var(--radius-sm, 6px); outline: none; font-family: inherit; text-transform: uppercase;">
    </div>
</div>

<div class="table-container card">
    <div class="table-responsive">
        <table class="data-table" id="tableOtrosIngresos" style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr>
                    <th>Detalle del Ingreso</th>
                    <th>Monto (Bs.)</th>
                    <th>Fecha</th>
                    <th>Nº Comprobante / Recibo</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if(isset($otros_ingresos) && $otros_ingresos->rowCount() > 0): ?>
                    <?php while($ingreso = $otros_ingresos->fetch(PDO::FETCH_ASSOC)): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($ingreso['detalle']); ?></strong></td>
                            <td class="monto-ingreso"><strong>Bs. <?php echo number_format($ingreso['monto'], 2); ?></strong></td>
                            <td><?php echo date('d/m/Y', strtotime($ingreso['fecha_pago'])); ?></td>
                            <td><?php echo htmlspecialchars($ingreso['comprobante'] ?: '-'); ?></td>
                            <td>
                                <?php 
                                    $estadoClass = '';
                                    if ($ingreso['estado'] == 'Cobrado') $estadoClass = 'estado-cobrado';
                                    elseif ($ingreso['estado'] == 'Anulado') $estadoClass = 'estado-anulado';
                                ?>
                                <span class="badge-status <?php echo $estadoClass; ?>"><?php echo htmlspecialchars($ingreso['estado']); ?></span>
                            </td>
                            <td class="actions">
                                <div class="action-buttons">
                                    <button class="btn-icon edit-btn" onclick="editOtroIngreso(<?php echo $ingreso['id_otro_ingreso']; ?>)" title="Editar"><i class="fa-solid fa-pen"></i></button>
                                    <button class="btn-icon delete-btn" onclick="deleteOtroIngreso(<?php echo $ingreso['id_otro_ingreso']; ?>, '<?php echo addslashes($ingreso['detalle']); ?>')" title="Eliminar"><i class="fa-solid fa-trash"></i></button>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align:center; padding: 25px; color: #718096;">No hay otros ingresos registrados en esta gestión.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal para Crear/Editar Otros Ingresos -->
<div id="otroIngresoModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitle">Registrar Nuevo Ingreso</h3>
            <span class="close" onclick="closeOtroIngresoModal()">&times;</span>
        </div>
        <div class="modal-body">
            <form id="otroIngresoForm" onsubmit="event.preventDefault(); saveOtroIngreso();">
                <input type="hidden" id="id_otro_ingreso" name="id_otro_ingreso">

                <div class="form-row" style="margin-bottom: 15px;">
                    <div class="form-group" style="width:100%">
                        <label for="detalle" style="display:block; margin-bottom: 5px; font-weight: 500;">Detalle o Motivo del Ingreso</label>
                        <input type="text" id="detalle" name="detalle" placeholder="Ej: ALQUILER DE SALÓN / DONACIÓN / VENTA DE SOUVENIRS" required style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px;">
                    </div>
                </div>

                <div class="form-row" style="display: flex; gap: 15px; margin-bottom: 15px;">
                    <div class="form-group half" style="flex: 1;">
                        <label for="monto" style="display:block; margin-bottom: 5px; font-weight: 500;">Monto (Bs.)</label>
                        <input type="number" step="0.01" min="0.01" id="monto" name="monto" placeholder="0.00" required style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px;"
                               oninvalid="this.setCustomValidity('El monto tiene que ser mayor a cero')"
                               oninput="this.setCustomValidity('')">
                    </div>
                    <div class="form-group half" style="flex: 1;">
                        <label for="fecha_pago" style="display:block; margin-bottom: 5px; font-weight: 500;">Fecha de Cobro</label>
                        <input type="date" id="fecha_pago" name="fecha_pago" min="2018-01-01" required style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px;">
                    </div>
                </div>

                <div class="form-row" style="display: flex; gap: 15px; margin-bottom: 15px;">
                    <div class="form-group half" style="flex: 1;">
                        <label for="comprobante" style="display:block; margin-bottom: 5px; font-weight: 500;">Nº Comprobante / Recibo</label>
                        <input type="text" id="comprobante" name="comprobante" placeholder="Ej: REC-00123" onkeyup="this.value = this.value.toUpperCase();" style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px;">
                    </div>
                    <div class="form-group half" id="group_estado" style="flex: 1; display: none;">
                        <label for="estado" style="display:block; margin-bottom: 5px; font-weight: 500;">Estado</label>
                        <select id="estado" name="estado" style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px;">
                            <option value="Cobrado">Cobrado / Pagado</option>
                            <option value="Anulado">Anulado</option>
                        </select>
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-secondary" onclick="closeOtroIngresoModal()">Cancelar</button>
            <button type="button" class="btn-primary" id="btn-save-otro-ingreso" onclick="saveOtroIngreso()">Guardar Ingreso</button>
        </div>
    </div>
</div>

<!-- Modal Eliminar -->
<div id="deleteModal" class="modal">
    <div class="modal-content" style="max-width: 400px;">
        <div class="modal-header">
            <h3>Confirmar Eliminación</h3>
            <span class="close" onclick="document.getElementById('deleteModal').style.display='none'">&times;</span>
        </div>
        <div class="modal-body">
            <p>¿Estás seguro que deseas eliminar el registro de ingreso de <strong id="deleteDesc"></strong>?</p>
            <p style="color: #e74c3c; font-size: 0.9em; margin-top: 10px;">Esta acción no se puede deshacer.</p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-secondary" onclick="document.getElementById('deleteModal').style.display='none'">Cancelar</button>
            <button type="button" class="btn-primary" style="background-color: #e74c3c;" onclick="confirmDeleteOtroIngreso()">Eliminar</button>
        </div>
    </div>
</div>

<link rel="stylesheet" href="../css/otro_ingreso.css">
<script src="../js/otro_ingreso.js"></script>

<?php include __DIR__ . '/footer.php'; ?>
