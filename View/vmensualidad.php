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
                    if(isset($socios) && $socios->rowCount() > 0) {
                        while($row = $socios->fetch(PDO::FETCH_ASSOC)) {
                            $nombre_completo = htmlspecialchars($row['ap_paterno'] . ' ' . $row['ap_materno'] . ' ' . $row['nombre']);
                            $ci_full = htmlspecialchars($row['ci']) . (!empty($row['complemento']) ? '-' . htmlspecialchars($row['complemento']) : '');
                            echo "<option value='" . $row['id_socio'] . "'>" . $ci_full . " - " . $nombre_completo . "</option>";
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
<!-- PANTALLA DEL CRUD DE MENSUALIDADES -->
<div class="dashboard-header">
    <div class="header-actions" style="display: flex; justify-content: space-between; align-items: center;">
        <div>
            <a href="../Controller/mensualidad.controller.php" class="back-link"><i class="fa-solid fa-arrow-left"></i> Volver a selección</a>
            <h1 class="page-title">Aportes Mensuales</h1>
            <p class="page-subtitle">Socio: <strong><?php echo htmlspecialchars($socio_actual['ap_paterno'] . ' ' . $socio_actual['ap_materno'] . ', ' . $socio_actual['nombre']); ?></strong></p>
        </div>
        <div style="display: flex; gap: 10px; align-items: center;">
            <a href="../Controller/mensualidad.controller.php" class="btn-secondary" style="display: inline-flex; align-items: center; gap: 8px; text-decoration: none;">
                <i class="fa-solid fa-users"></i> Otro Asociado
            </a>
            <button class="btn-primary" onclick="openMensualidadModal()">
                <i class="fa-solid fa-plus"></i> Registrar Pago
            </button>
        </div>
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
                <th>Acciones</th>
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
                    
                    echo "<td class='actions-col'>
                            <button class='btn-icon edit-btn' onclick='editMensualidad(" . $row['id_mensualidad'] . ")' title='Editar'><i class='fa-solid fa-pen'></i></button>
                            <button class='btn-icon delete-btn' onclick='deleteMensualidad(" . $row['id_mensualidad'] . ", \"" . htmlspecialchars($descEliminar, ENT_QUOTES) . "\")' title='Eliminar'><i class='fa-solid fa-trash'></i></button>
                          </td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='8' style='text-align: center; padding: 20px;'>No hay pagos registrados para este socio.</td></tr>";
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
                        <label for="monto_unitario">Monto por Mes (Bs)</label>
                        <input type="number" step="0.01" id="monto_unitario" name="monto_unitario" required value="<?php echo number_format($monto_mensualidad_sugerido ?? 50.00, 2, '.', ''); ?>" oninput="updateCalculatedTotal()">
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
    <div class="modal-content" style="max-width: 450px; text-align: center; overflow: hidden; padding: 0;">
        <div style="background-color: #dc3545; color: white; padding: 15px 20px; display: flex; justify-content: space-between; align-items: center;">
            <h3 style="margin: 0; font-size: 16px; font-weight: 600; letter-spacing: 1px;">ELIMINAR REGISTRO</h3>
            <span onclick="closeDeleteModal()" style="color: white; font-size: 24px; cursor: pointer; line-height: 1;">&times;</span>
        </div>
        <div class="modal-body" style="padding: 40px 20px; background-color: white;">
            <p style="margin-bottom: 30px; font-size: 16px; color: #444;">
                ¿Está seguro de que desea eliminar el pago de: <br><strong id="deleteDesc" style="font-size: 18px; color: #222; display: inline-block; margin-top: 10px;"></strong>?
            </p>
            <div style="display: flex; justify-content: center; gap: 15px;">
                <button type="button" style="background-color: #28a745; color: white; border: none; padding: 10px 20px; border-radius: 4px; font-size: 14px; font-weight: 600; cursor: pointer; transition: opacity 0.2s;" onclick="closeDeleteModal()">Cancelar</button>
                <button type="button" style="background-color: #dc3545; color: white; border: none; padding: 10px 20px; border-radius: 4px; font-size: 14px; font-weight: 600; cursor: pointer; transition: opacity 0.2s;" id="btnConfirmDelete">Borrar registro</button>
            </div>
        </div>
    </div>
</div><!-- Extra styles/scripts for Mensualidad view -->
<link rel="stylesheet" href="../css/mensualidad.css">
<script>
    const MONTO_MENSUALIDAD_SUGERIDO = "<?php echo number_format($monto_mensualidad_sugerido ?? 50.00, 2, '.', ''); ?>";
</script>
<script src="../js/mensualidad.js"></script>

<?php include 'footer.php'; ?>
