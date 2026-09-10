<?php
// View/vegreso_especial.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

include 'header.php'; 
?>

<div class="dashboard-header">
    <div class="header-actions" style="display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h1 class="page-title">Gastos Especiales</h1>
            <p class="page-subtitle">Gestión de egresos y gastos especiales de la fraternidad</p>
        </div>
        <button class="btn-primary" onclick="openEgresoEspecialModal()">
            <i class="fa-solid fa-plus"></i> Registrar Gasto Especial
        </button>
    </div>
</div>

<div class="table-container">
    <table class="data-table">
        <thead>
            <tr>
                <th>Motivo / Detalle del Gasto</th>
                <th>Monto (Bs.)</th>
                <th>Fecha de Pago</th>
                <th>Comprobante / Factura</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if(isset($egresos_list) && $egresos_list->rowCount() > 0): ?>
                <?php while($egreso = $egresos_list->fetch(PDO::FETCH_ASSOC)): ?>
                    <tr>
                        <td>
                            <strong>
                                <?php 
                                    if (!empty($egreso['motivo'])) {
                                        echo htmlspecialchars($egreso['motivo']) . ' - ';
                                    }
                                    echo htmlspecialchars($egreso['detalle']); 
                                ?>
                            </strong>
                        </td>
                        <td><strong>Bs. <?php echo number_format($egreso['monto'], 2); ?></strong></td>
                        <td><?php echo date('d/m/Y', strtotime($egreso['fecha_pago'])); ?></td>
                        <td><?php echo htmlspecialchars($egreso['comprobante'] ?: '-'); ?></td>
                        <td>
                            <?php 
                                $estadoClass = '';
                                if ($egreso['estado'] == 'Pagado') $estadoClass = 'estado-realizado';
                                elseif ($egreso['estado'] == 'Anulado') $estadoClass = 'estado-cancelado';
                            ?>
                            <span class="badge-status <?php echo $estadoClass; ?>"><?php echo htmlspecialchars($egreso['estado']); ?></span>
                        </td>
                        <td class="actions">
                            <button class="btn-icon edit-btn" onclick="editEgresoEspecial(<?php echo $egreso['id_egreso_esp']; ?>)" title="Editar"><i class="fa-solid fa-pen"></i></button>
                            <button class="btn-icon delete-btn" onclick="deleteEgresoEspecial(<?php echo $egreso['id_egreso_esp']; ?>, '<?php echo addslashes($egreso['detalle']); ?>')" title="Eliminar"><i class="fa-solid fa-trash"></i></button>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" style="text-align:center; padding: 25px; color: #64748b;">No hay gastos especiales registrados en esta gestión.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Modal para Crear/Editar Gastos Especiales -->
<div id="egresoModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitle">Registrar Gasto Especial</h3>
            <span class="close" onclick="closeEgresoEspecialModal()">&times;</span>
        </div>
        <div class="modal-body">
            <form id="egresoForm" onsubmit="event.preventDefault(); saveEgresoEspecial();">
                <input type="hidden" name="action" value="save">
                <input type="hidden" id="id_egreso_esp" name="id_egreso_esp">

                <div class="form-row" style="margin-bottom: 15px;">
                    <div class="form-group" style="width:100%">
                        <label for="motivo">Motivo del Gasto</label>
                        <?php if (!empty($categorias)): ?>
                            <select id="motivo" name="motivo" style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px;">
                                <option value="" disabled selected>-- Selecciona un Motivo --</option>
                                <?php foreach ($categorias as $cat): ?>
                                    <option value="<?php echo htmlspecialchars($cat['nombre']); ?>"><?php echo htmlspecialchars($cat['nombre']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        <?php else: ?>
                            <input type="text" id="motivo" name="motivo" placeholder="Ej: ORGANIZACIÓN DE RIFA Y KERMES" style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px;">
                        <?php endif; ?>
                    </div>
                </div>

                <div class="form-row" style="display: flex; gap: 15px; margin-bottom: 15px;">
                    <div class="form-group half" style="flex: 1;">
                        <label for="monto">Monto (Bs.)</label>
                        <input type="number" step="0.01" min="0.01" id="monto" name="monto" placeholder="0.00" required style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px;"
                               oninvalid="this.setCustomValidity('El monto tiene que ser mayor a cero')"
                               oninput="this.setCustomValidity('')">
                    </div>
                    <div class="form-group half" style="flex: 1;">
                        <label for="fecha_pago">Fecha de Pago</label>
                        <input type="date" id="fecha_pago" name="fecha_pago" min="2018-01-01" required style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px;">
                    </div>
                </div>

                <div class="form-row" style="display: flex; gap: 15px; margin-bottom: 15px;">
                    <div class="form-group half" style="flex: 1;">
                        <label for="comprobante">Nº Comprobante / Factura</label>
                        <input type="text" id="comprobante" name="comprobante" placeholder="Ej: REC-00123" onkeyup="this.value = this.value.toUpperCase();" style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px;">
                    </div>
                    <div class="form-group half" id="group_estado" style="flex: 1; display: none;">
                        <label for="estado">Estado</label>
                        <select id="estado" name="estado" style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px;">
                            <option value="Pagado">Pagado</option>
                            <option value="Anulado">Anulado</option>
                        </select>
                    </div>
                </div>

                <!-- DEBAJO DE LA FACTURA: DETALLE OBLIGATORIO -->
                <div class="form-row" style="margin-bottom: 15px;">
                    <div class="form-group" style="width:100%">
                        <label for="detalle">Detalle del Gasto <span style="color: #ef4444; font-weight: bold;">*</span></label>
                        <input type="text" id="detalle" name="detalle" placeholder="Ej: Gastos de imprenta y organización de kermes" required style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px;">
                    </div>
                </div>

                <div class="modal-footer" style="margin-top: 20px; display: flex; justify-content: flex-end; gap: 10px;">
                    <button type="button" class="btn-secondary" onclick="closeEgresoEspecialModal()">Cancelar</button>
                    <button type="submit" class="btn-primary" id="btnGuardarEgreso">Guardar Gasto</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal de Confirmación de Eliminación -->
<div id="deleteModal" class="modal">
    <div class="modal-content" style="max-width: 450px; text-align: center; overflow: hidden; padding: 0;">
        <div style="background-color: #dc3545; color: white; padding: 15px 20px; display: flex; justify-content: space-between; align-items: center;">
            <h3 style="margin: 0; font-size: 16px; font-weight: 600;">ELIMINAR GASTO</h3>
            <span onclick="closeDeleteModal()" style="color: white; font-size: 24px; cursor: pointer; line-height: 1;">&times;</span>
        </div>
        <div class="modal-body" style="padding: 30px 20px; background-color: white;">
            <p style="margin-bottom: 25px; font-size: 15px; color: #444;">
                ¿Está seguro de que desea eliminar el registro: <br><strong id="deleteDesc" style="font-size: 16px; color: #222; display: inline-block; margin-top: 8px;"></strong>?
            </p>
            <div style="display: flex; justify-content: center; gap: 15px;">
                <button type="button" class="btn-secondary" onclick="closeDeleteModal()">Cancelar</button>
                <button type="button" class="btn-danger" style="background: #dc3545; color: white; border: none; padding: 9px 18px; border-radius: 6px; font-weight: 600; cursor: pointer;" id="btnConfirmDelete">Eliminar</button>
            </div>
        </div>
    </div>
</div>

<link rel="stylesheet" href="../css/otro_gasto.css">
<script src="../js/egreso_especial.js"></script>

<?php include 'footer.php'; ?>
