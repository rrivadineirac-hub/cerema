<?php
// View/vservicio.php
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
            <h1 class="page-title">Servicios Básicos</h1>
            <p class="page-subtitle">Gestión de pagos de Electricidad, Agua y Telefonía</p>
        </div>
        <button class="btn-primary" onclick="openServicioModal()">
            <i class="fa-solid fa-plus"></i> Pago de servicios Básicos
        </button>
    </div>
</div>

<div class="table-container">
    <table class="data-table">
        <thead>
            <tr>
                <th>Servicio</th>
                <th>Mes Pagado</th>
                <th>Monto (Bs.)</th>
                <th>Fecha de Pago</th>
                <th>Comprobante / Factura</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if(isset($servicios) && $servicios->rowCount() > 0): ?>
                <?php while($servicio = $servicios->fetch(PDO::FETCH_ASSOC)): ?>
                    <tr>
                        <td>
                            <?php 
                                $badgeClass = '';
                                if ($servicio['tipo_servicio'] == 'Electricidad') $badgeClass = 'badge-electricidad';
                                elseif ($servicio['tipo_servicio'] == 'Agua') $badgeClass = 'badge-agua';
                                elseif ($servicio['tipo_servicio'] == 'Telefonia') $badgeClass = 'badge-telefonia';
                            ?>
                            <span class="badge-status <?php echo $badgeClass; ?>"><?php echo htmlspecialchars($servicio['tipo_servicio']); ?></span>
                        </td>
                        <td><strong><?php echo htmlspecialchars($servicio['mes_pago'] ?? '-'); ?></strong></td>
                        <td><strong><?php echo number_format($servicio['monto'], 2); ?></strong></td>
                        <td><?php echo date('d/m/Y', strtotime($servicio['fecha_pago'])); ?></td>
                        <td><?php echo htmlspecialchars($servicio['comprobante']); ?></td>
                        <td>
                            <?php 
                                $estadoClass = '';
                                if ($servicio['estado'] == 'Pagado') $estadoClass = 'estado-realizado'; // Usar verde
                                elseif ($servicio['estado'] == 'Anulado') $estadoClass = 'estado-cancelado'; // Usar naranja/rojo
                            ?>
                            <span class="badge-status <?php echo $estadoClass; ?>"><?php echo htmlspecialchars($servicio['estado']); ?></span>
                        </td>
                        <td class="actions">
                            <button class="btn-icon edit-btn" onclick="editServicio(<?php echo $servicio['id_servicio']; ?>)" title="Editar"><i class="fa-solid fa-pen"></i></button>
                            <button class="btn-icon delete-btn" onclick="deleteServicio(<?php echo $servicio['id_servicio']; ?>, '<?php echo addslashes($servicio['tipo_servicio'] . ' (' . date('d/m/Y', strtotime($servicio['fecha_pago'])) . ')'); ?>')" title="Eliminar"><i class="fa-solid fa-trash"></i></button>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" style="text-align:center;">No hay pagos registrados en esta gestión.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Modal para Crear/Editar -->
<div id="servicioModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitle">Registrar Pago de Servicio</h3>
            <span class="close" onclick="closeServicioModal()">&times;</span>
        </div>
        <div class="modal-body">
            <form id="servicioForm" onsubmit="event.preventDefault(); saveServicio();">
                <input type="hidden" id="id_servicio" name="id_servicio">

                <div class="form-row">
                    <div class="form-group half">
                        <label for="tipo_servicio">Servicio Básico</label>
                        <select id="tipo_servicio" name="tipo_servicio" required>
                            <option value="" disabled selected>Elegir Servicio</option>
                            <option value="Electricidad">Electricidad</option>
                            <option value="Agua">Agua</option>
                            <option value="Telefonia">Telefonía</option>
                        </select>
                    </div>
                    <div class="form-group half">
                        <label for="mes_pago">Mes Correspondiente</label>
                        <select id="mes_pago" name="mes_pago" required>
                            <option value="" disabled selected>Elegir Mes</option>
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
                    <label for="comprobante">Nº Comprobante / Factura</label>
                    <input type="text" id="comprobante" name="comprobante" placeholder="Ej: FAC-12345" onkeyup="this.value = this.value.toUpperCase();">
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-secondary" onclick="closeServicioModal()">Cancelar</button>
            <button type="button" class="btn-primary" id="btn-save-servicio" onclick="saveServicio()">Pagar</button>
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
            <p>¿Estás seguro que deseas eliminar el registro de pago de <strong id="deleteDesc"></strong>?</p>
            <p style="color: #e74c3c; font-size: 0.9em; margin-top: 10px;">Esta acción no se puede deshacer.</p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-secondary" onclick="document.getElementById('deleteModal').style.display='none'">Cancelar</button>
            <button type="button" class="btn-primary" style="background-color: #e74c3c;" onclick="confirmDeleteServicio()">Eliminar</button>
        </div>
    </div>
</div>

<link rel="stylesheet" href="../css/servicio.css">
<script src="../js/servicio.js"></script>

<?php include 'footer.php'; ?>
