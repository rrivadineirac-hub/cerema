<?php 
// View/vdirectiva.php
include 'header.php'; 
?>

<div class="dashboard-header">
    <div class="header-actions" style="display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h1 class="page-title">Mesa Directiva</h1>
            <p class="page-subtitle">Asignación de cargos y roles a los socios</p>
        </div>
        <button class="btn-primary" onclick="openModal()">
            <i class="fa-solid fa-user-tie"></i> Asignar Rol
        </button>
    </div>
</div>

<div class="table-container">
    <table class="data-table">
        <thead>
            <tr>
                <th>Gestión</th>
                <th>Cargo</th>
                <th>Socio Asignado</th>
                <th>Fecha Inicio</th>
                <th>Fecha Fin</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            if(isset($directivas) && $directivas->rowCount() > 0) {
                while($row = $directivas->fetch(PDO::FETCH_ASSOC)) {
                    
                    // Identificamos el color del badge basado en el nombre del cargo
                    $cargoNormalizado = strtolower(str_replace(' ', '', $row['nombre_cargo']));
                    $cargoClase = "badge-" . $cargoNormalizado; 
                    
                    echo "<tr>";
                    echo "<td><strong>" . htmlspecialchars($row['gestion']) . "</strong></td>";
                    echo "<td><span class='badge-cargo {$cargoClase}'>" . htmlspecialchars($row['nombre_cargo']) . "</span></td>";
                    
                    $nombreCompleto = $row['ap_paterno'] . ' ' . $row['ap_materno'] . ', ' . $row['nombre'];
                    echo "<td>" . htmlspecialchars($nombreCompleto) . "</td>";
                    
                    echo "<td>" . date('d/m/Y', strtotime($row['fecha_inicio'])) . "</td>";
                    
                    $fechaFin = !empty($row['fecha_fin']) ? date('d/m/Y', strtotime($row['fecha_fin'])) : '<span style="color:#aaa;">Vigente</span>';
                    echo "<td>" . $fechaFin . "</td>";
                    
                    echo "<td class='actions-col'>
                            <button class='btn-icon edit-btn' onclick='editDirectiva(" . $row['id_directiva'] . ")' title='Editar'><i class='fa-solid fa-pen'></i></button>
                            <button class='btn-icon delete-btn' onclick='deleteDirectiva(" . $row['id_directiva'] . ", \"" . htmlspecialchars($row['nombre_cargo'] . ' (' . $row['gestion'] . ')') . "\")' title='Eliminar'><i class='fa-solid fa-trash'></i></button>
                          </td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='6' style='text-align: center; padding: 20px;'>No hay cargos asignados registrados.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<!-- Modal Structure for Create/Edit -->
<div id="directivaModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="modalTitle">Asignar Nuevo Rol</h2>
            <span class="close-btn" onclick="closeModal()">&times;</span>
        </div>
        <div class="modal-body">
            <form id="directivaForm">
                <input type="hidden" name="action" value="save">
                <input type="hidden" id="id_directiva" name="id_directiva" value="">
                
                <div class="form-row">
                    <div class="form-group half">
                        <label for="id_cargo">Cargo / Rol</label>
                        <select id="id_cargo" name="id_cargo" required>
                            <option value="">Seleccione un Cargo</option>
                            <?php 
                            if(isset($cargos)) {
                                // Reseteamos puntero si es que se ha usado, pero es nuevo query.
                                while($c = $cargos->fetch(PDO::FETCH_ASSOC)) {
                                    echo "<option value='".$c['id_cargo']."'>".htmlspecialchars($c['nombre_cargo'])."</option>";
                                }
                            }
                            ?>
                        </select>
                    </div>
                    
                    <div class="form-group half">
                        <label for="id_socio">Socio a Asignar</label>
                        <select id="id_socio" name="id_socio" required>
                            <option value="">Seleccione un Socio</option>
                            <?php 
                            if(isset($socios)) {
                                while($s = $socios->fetch(PDO::FETCH_ASSOC)) {
                                    $nombreC = $s['ap_paterno'] . " " . $s['ap_materno'] . ", " . $s['nombre'];
                                    echo "<option value='".$s['id_socio']."'>".htmlspecialchars($nombreC)."</option>";
                                }
                            }
                            ?>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group half">
                        <label for="gestion">Gestión (Año)</label>
                        <input type="number" id="gestion" name="gestion" min="2018" max="<?php echo ((int)date('Y') + 3); ?>" value="<?php echo date('Y'); ?>" required>
                    </div>
                    <div class="form-group half">
                        <!-- Espacio vacío para alinear -->
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group half">
                        <label for="fecha_inicio">Fecha de Inicio</label>
                        <input type="date" id="fecha_inicio" name="fecha_inicio" min="2018-01-01" required>
                    </div>
                    <div class="form-group half">
                        <label for="fecha_fin">Fecha de Fin (Opcional)</label>
                        <input type="date" id="fecha_fin" name="fecha_fin" min="2018-01-01">
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-secondary" onclick="closeModal()">Cancelar</button>
            <button type="button" class="btn-primary" onclick="saveDirectiva()">Guardar</button>
        </div>
    </div>
</div>

<!-- Estilos y Scripts Independientes -->
<link rel="stylesheet" href="../css/directiva.css">
<script src="../js/directiva.js"></script>

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
