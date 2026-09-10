<?php
// View/vsueldo.php
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
            <h1 class="page-title">Sueldos</h1>
            <p class="page-subtitle">Gestión de pagos de sueldos al personal</p>
        </div>
        <button class="btn-primary" onclick="openSueldoModal()">
            <i class="fa-solid fa-plus"></i> Pagar Sueldo
        </button>
    </div>
</div>

<div class="table-container">
    <table class="data-table">
        <thead>
            <tr>
                <th>Empleado</th>
                <th>Cargo</th>
                <th>Mes</th>
                <th>Monto (Bs.)</th>
                <th>Fecha de Pago</th>
                <th>Comprobante</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if(isset($sueldos) && $sueldos->rowCount() > 0): ?>
                <?php while($sueldo = $sueldos->fetch(PDO::FETCH_ASSOC)): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($sueldo['empleado']); ?></strong></td>
                        <td><?php echo htmlspecialchars($sueldo['cargo']); ?></td>
                        <td><strong><?php echo htmlspecialchars($sueldo['mes'] ?? '-'); ?></strong></td>
                        <td><strong><?php echo number_format($sueldo['monto'], 2); ?></strong></td>
                        <td><?php echo date('d/m/Y', strtotime($sueldo['fecha_pago'])); ?></td>
                        <td><?php echo htmlspecialchars($sueldo['comprobante']); ?></td>
                        <td>
                            <?php 
                                $estadoClass = '';
                                if ($sueldo['estado'] == 'Pagado') $estadoClass = 'estado-realizado';
                                elseif ($sueldo['estado'] == 'Anulado') $estadoClass = 'estado-cancelado';
                            ?>
                            <span class="badge-status <?php echo $estadoClass; ?>"><?php echo htmlspecialchars($sueldo['estado']); ?></span>
                        </td>
                        <td class="actions">
                            <button class="btn-icon edit-btn" onclick="editSueldo(<?php echo $sueldo['id_sueldo']; ?>)" title="Editar"><i class="fa-solid fa-pen"></i></button>
                            <button class="btn-icon delete-btn" onclick="deleteSueldo(<?php echo $sueldo['id_sueldo']; ?>, '<?php echo addslashes($sueldo['empleado'] . ' - ' . $sueldo['cargo']); ?>')" title="Eliminar"><i class="fa-solid fa-trash"></i></button>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8" style="text-align:center;">No hay sueldos registrados en esta gestión.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Modal para Crear/Editar -->
<div id="sueldoModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitle">Registrar Pago de Sueldo</h3>
            <span class="close" onclick="closeSueldoModal()">&times;</span>
        </div>
        <div class="modal-body">
            <form id="sueldoForm" onsubmit="event.preventDefault(); saveSueldo();">
                <input type="hidden" id="id_sueldo" name="id_sueldo">

                <div class="form-row">
                    <div class="form-group half" style="width:100%">
                        <label for="empleado">Nombre del Empleado</label>
                        <input type="text" id="empleado" name="empleado" placeholder="Ej: JUAN PEREZ" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group half">
                        <label for="cargo">Cargo</label>
                        <input type="text" id="cargo" name="cargo" placeholder="Ej: SECRETARIA" required>
                    </div>
                    <div class="form-group half">
                        <label for="mes">Mes Correspondiente</label>
                        <select id="mes" name="mes" required>
                            <option value="" disabled>Elegir Mes</option>
                            <option value="Enero">Enero</option>
                            <option value="Febrero">Febrero</option>
                            <option value="Marzo">Marzo</option>
                            <option value="Abril">Abril</option>
                            <option value="Mayo">Mayo</option>
                            <option value="Junio">Junio</option>
                            <option value="Julio">Julio</option>
                            <option value="Agosto">Agosto</option>
                            <option value="Septiembre">Septiembre</option>
                            <option value="Octubre">Octubre</option>
                            <option value="Noviembre">Noviembre</option>
                            <option value="Diciembre">Diciembre</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group half">
                        <label for="monto">Monto (Bs.)</label>
                        <input type="number" step="0.01" min="0.01" id="monto" name="monto" placeholder="0.00" required
                               oninvalid="this.setCustomValidity('El monto tiene que ser mayor a cero')"
                               oninput="this.setCustomValidity('')">
                    </div>
                    <div class="form-group half">
                        <label for="fecha_pago">Fecha de Pago</label>
                        <input type="date" id="fecha_pago" name="fecha_pago" min="2018-01-01" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="comprobante">Nº Comprobante / Recibo</label>
                    <input type="text" id="comprobante" name="comprobante" placeholder="Ej: REC-12345" onkeyup="this.value = this.value.toUpperCase();">
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-secondary" onclick="closeSueldoModal()">Cancelar</button>
            <button type="button" class="btn-primary" id="btn-save-sueldo" onclick="saveSueldo()">Pagar Sueldo</button>
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
            <p>¿Estás seguro que deseas eliminar el registro de sueldo de <strong id="deleteDesc"></strong>?</p>
            <p style="color: #e74c3c; font-size: 0.9em; margin-top: 10px;">Esta acción no se puede deshacer.</p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-secondary" onclick="document.getElementById('deleteModal').style.display='none'">Cancelar</button>
            <button type="button" class="btn-primary" style="background-color: #e74c3c;" onclick="confirmDeleteSueldo()">Eliminar</button>
        </div>
    </div>
</div>

<link rel="stylesheet" href="../css/sueldo.css">
<script src="../js/sueldo.js"></script>

<?php include 'footer.php'; ?>
