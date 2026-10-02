<?php
// View/votro_gasto.php
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
            <h1 class="page-title">Otros Gastos</h1>
            <p class="page-subtitle">Gestión de gastos diversos u otros egresos</p>
        </div>
        <button class="btn-primary" onclick="openOtroGastoModal()">
            <i class="fa-solid fa-plus"></i> Registrar Gasto
        </button>
    </div>
</div>

<div class="table-container">
    <table class="data-table">
        <thead>
            <tr>
                <th>Detalle del Gasto</th>
                <th>Monto (Bs.)</th>
                <th>Fecha</th>
                <th>Comprobante</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if(isset($otros_gastos) && $otros_gastos->rowCount() > 0): ?>
                <?php while($gasto = $otros_gastos->fetch(PDO::FETCH_ASSOC)): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($gasto['detalle']); ?></strong></td>
                        <td><strong><?php echo number_format($gasto['monto'], 2); ?></strong></td>
                        <td><?php echo date('d/m/Y', strtotime($gasto['fecha_pago'])); ?></td>
                        <td><?php echo htmlspecialchars($gasto['comprobante']); ?></td>
                        <td>
                            <?php 
                                $estadoClass = '';
                                if ($gasto['estado'] == 'Pagado') $estadoClass = 'estado-realizado';
                                elseif ($gasto['estado'] == 'Anulado') $estadoClass = 'estado-cancelado';
                            ?>
                            <span class="badge-status <?php echo $estadoClass; ?>"><?php echo htmlspecialchars($gasto['estado']); ?></span>
                        </td>
                        <td class="actions">
                            <button class="btn-icon edit-btn" onclick="editOtroGasto(<?php echo $gasto['id_otro_gasto']; ?>)" title="Editar"><i class="fa-solid fa-pen"></i></button>
                            <button class="btn-icon delete-btn" onclick="deleteOtroGasto(<?php echo $gasto['id_otro_gasto']; ?>, '<?php echo addslashes($gasto['detalle']); ?>')" title="Eliminar"><i class="fa-solid fa-trash"></i></button>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" style="text-align:center;">No hay gastos registrados en esta gestión.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Modal para Crear/Editar -->
<div id="otroGastoModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitle">Registrar Gasto</h3>
            <span class="close" onclick="closeOtroGastoModal()">&times;</span>
        </div>
        <div class="modal-body">
            <form id="otroGastoForm" onsubmit="event.preventDefault(); saveOtroGasto();">
                <input type="hidden" id="id_otro_gasto" name="id_otro_gasto">

                <div class="form-row">
                    <div class="form-group half" style="width:100%">
                        <label for="nombre_gasto">Nombre del Gasto</label>
                        <input type="text" id="nombre_gasto" name="nombre_gasto" placeholder="Ej: COMPRA DE MATERIAL DE LIMPIEZA" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group half" style="width:100%">
                        <label for="detalle">Detalle o Descripción del Gasto</label>
                        <input type="text" id="detalle" name="detalle" placeholder="Ej: Descripción o especificaciones adicionales del gasto">
                    </div>
                </div>

                <div class="form-row" style="display: flex; gap: 12px;">
                    <div class="form-group" style="flex: 1;">
                        <label for="unidad_medida">Unidad de Medida</label>
                        <input type="text" id="unidad_medida" name="unidad_medida" placeholder="Ej: Glb, Unid, Kg, Mts" value="">
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label for="cantidad">Cantidad</label>
                        <input type="number" step="0.01" min="0.01" id="cantidad" name="cantidad" value="1.00" oninput="calcularTotalOtroGasto();" required>
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label for="precio">Precio Unitario (Bs.)</label>
                        <input type="number" step="0.01" min="0.00" id="precio" name="precio" placeholder="0.00" oninput="calcularTotalOtroGasto();">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group half">
                        <label for="monto">Total / Monto (Bs.)</label>
                        <input type="number" step="0.01" min="0.01" id="monto" name="monto" placeholder="0.00" required
                               oninvalid="this.setCustomValidity('El monto tiene que ser mayor a cero')"
                               oninput="this.setCustomValidity('')">
                    </div>
                    <div class="form-group half">
                        <label for="fecha_pago">Fecha</label>
                        <input type="date" id="fecha_pago" name="fecha_pago" min="2018-01-01" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="comprobante">Nº Comprobante / Recibo / Factura</label>
                    <input type="text" id="comprobante" name="comprobante" placeholder="Ej: FAC-12345" onkeyup="this.value = this.value.toUpperCase();">
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-secondary" onclick="closeOtroGastoModal()">Cancelar</button>
            <button type="button" class="btn-primary" id="btn-save-otro-gasto" onclick="saveOtroGasto()">Guardar</button>
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
            <p>¿Estás seguro que deseas eliminar el registro del gasto de <strong id="deleteDesc"></strong>?</p>
            <p style="color: #e74c3c; font-size: 0.9em; margin-top: 10px;">Esta acción no se puede deshacer.</p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-secondary" onclick="document.getElementById('deleteModal').style.display='none'">Cancelar</button>
            <button type="button" class="btn-primary" style="background-color: #e74c3c;" onclick="confirmDeleteOtroGasto()">Eliminar</button>
        </div>
    </div>
</div>

<link rel="stylesheet" href="../css/otro_gasto.css">
<script src="../js/otro_gasto.js"></script>

<?php include 'footer.php'; ?>
