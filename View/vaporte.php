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
                            echo "<option value='" . $row['id_socio'] . "'>" . htmlspecialchars($row['ci']) . " - " . $nombre_completo . "</option>";
                        }
                    }
                    ?>
                </select>
            </div>
            <button type="submit" class="btn-primary btn-block">
                Continuar <i class="fa-solid fa-arrow-right"></i>
            </button>
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
        <div style="display: flex; gap: 10px; align-items: center;">
            <a href="../Controller/aporte.controller.php" class="btn-secondary" style="display: inline-flex; align-items: center; gap: 8px; text-decoration: none;">
                <i class="fa-solid fa-users"></i> Otro Asociado
            </a>
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

<?php include 'footer.php'; ?>
