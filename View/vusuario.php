<?php 
// View/vusuario.php
include 'header.php'; 
?>

<!-- Estilos Independientes del Módulo -->
<link rel="stylesheet" href="../css/usuario.css?v=<?php echo filemtime(__DIR__ . '/../css/usuario.css'); ?>">

<div class="dashboard-header">
    <div class="header-actions" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
        <div>
            <h1 class="page-title"><i class="fa-solid fa-user-gear" style="color: #FF7A00; margin-right: 10px;"></i>Gestión de Usuarios del Sistema</h1>
            <p class="page-subtitle">Administración de cuentas de acceso, roles y vinculación con asociados</p>
        </div>
        <button class="btn-primary" onclick="openUserModal()">
            <i class="fa-solid fa-user-plus"></i> Nuevo Usuario
        </button>
    </div>
</div>

<!-- Tarjetas de Estadísticas -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon bg-primary">
            <i class="fa-solid fa-users-gear"></i>
        </div>
        <div class="stat-info">
            <span class="stat-value"><?php echo $stats['total_usuarios'] ?? 0; ?></span>
            <span class="stat-label">Total Usuarios</span>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon bg-success">
            <i class="fa-solid fa-user-check"></i>
        </div>
        <div class="stat-info">
            <span class="stat-value"><?php echo $stats['activos'] ?? 0; ?></span>
            <span class="stat-label">Usuarios Activos</span>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon bg-warning">
            <i class="fa-solid fa-user-shield"></i>
        </div>
        <div class="stat-info">
            <span class="stat-value"><?php echo $stats['administradores'] ?? 0; ?></span>
            <span class="stat-label">Administradores</span>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon bg-info">
            <i class="fa-solid fa-id-card"></i>
        </div>
        <div class="stat-info">
            <span class="stat-value"><?php echo $stats['enlazados_socio'] ?? 0; ?></span>
            <span class="stat-label">Asociados Vinculados</span>
        </div>
    </div>
</div>

<!-- Barra de Filtros y Búsqueda -->
<div class="filter-card">
    <div class="search-box">
        <i class="fa-solid fa-magnifying-glass search-icon"></i>
        <input type="text" id="searchUser" placeholder="Buscar por usuario, nombre de socio o CI..." onkeyup="filterUsersTable()">
    </div>
    <div class="filter-group">
        <label for="filterRole"><i class="fa-solid fa-filter"></i> Rol:</label>
        <select id="filterRole" onchange="filterUsersTable()">
            <option value="">Todos los Roles</option>
            <option value="Administrador">Administrador</option>
            <option value="Presidente">Presidente</option>
            <option value="Tesorero">Tesorero</option>
            <option value="Secretario">Secretario</option>
            <option value="Socio">Socio</option>
        </select>
    </div>
    <div class="filter-group">
        <label for="filterStatus"><i class="fa-solid fa-toggle-on"></i> Estado:</label>
        <select id="filterStatus" onchange="filterUsersTable()">
            <option value="">Todos</option>
            <option value="Activo">Activos</option>
            <option value="Inactivo">Inactivos</option>
        </select>
    </div>
</div>

<!-- Tabla de Usuarios -->
<div class="table-container">
    <table class="data-table" id="usersTable">
        <thead>
            <tr>
                <th style="width: 60px;">ID</th>
                <th>Usuario</th>
                <th>Socio Asignado</th>
                <th>Rol en Sistema</th>
                <th>Estado</th>
                <th>Fecha Registro</th>
                <th style="text-align: center;">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            if(isset($usuarios) && $usuarios->rowCount() > 0) {
                $currentUserId = $_SESSION['user_id'] ?? 0;
                while($row = $usuarios->fetch(PDO::FETCH_ASSOC)) {
                    $isCurrentUser = ($row['id_usuario'] == $currentUserId);
                    $rolNormalizado = strtolower(str_replace(' ', '', $row['rol_sistema']));
                    
                    // Nombre del socio o indicación de "Sin socio"
                    if (!empty($row['id_socio'])) {
                        $socioNombre = htmlspecialchars($row['socio_ap_paterno'] . ' ' . $row['socio_ap_materno'] . ', ' . $row['socio_nombre']);
                        $socioInfo = "<strong>" . $socioNombre . "</strong><br><small class='text-muted'>CI: " . htmlspecialchars($row['socio_ci'] ?? '-') . "</small>";
                    } else {
                        $socioInfo = "<span class='badge-no-socio'><i class='fa-solid fa-user-slash'></i> Sin socio vinculado</span>";
                    }

                    // Estado del usuario
                    $isActivo = ($row['activo'] == 1);
                    $statusBadge = $isActivo 
                        ? "<span class='status-badge status-active' onclick='toggleUserStatus(" . $row['id_usuario'] . ", 0, \"" . htmlspecialchars($row['username'], ENT_QUOTES) . "\")' title='Haga clic para desactivar'><i class='fa-solid fa-check-circle'></i> Activo</span>"
                        : "<span class='status-badge status-inactive' onclick='toggleUserStatus(" . $row['id_usuario'] . ", 1, \"" . htmlspecialchars($row['username'], ENT_QUOTES) . "\")' title='Haga clic para activar'><i class='fa-solid fa-times-circle'></i> Inactivo</span>";

                    echo "<tr data-username='" . strtolower(htmlspecialchars($row['username'])) . "' data-socio='" . strtolower(strip_tags($socioInfo)) . "' data-rol='" . htmlspecialchars($row['rol_sistema']) . "' data-estado='" . ($isActivo ? 'Activo' : 'Inactivo') . "'>";
                    echo "<td><strong>#" . $row['id_usuario'] . "</strong></td>";
                    
                    echo "<td>";
                    echo "  <div class='user-cell'>";
                    echo "      <div class='user-avatar'><i class='fa-solid fa-user'></i></div>";
                    echo "      <div class='user-details'>";
                    echo "          <span class='user-name'>" . htmlspecialchars($row['username']) . "</span>";
                    if ($isCurrentUser) {
                        echo "      <span class='badge-current-user'><i class='fa-solid fa-star'></i> Tú</span>";
                    }
                    echo "      </div>";
                    echo "  </div>";
                    echo "</td>";

                    echo "<td>" . $socioInfo . "</td>";
                    echo "<td><span class='badge-rol badge-rol-{$rolNormalizado}'>" . htmlspecialchars($row['rol_sistema']) . "</span></td>";
                    echo "<td>" . $statusBadge . "</td>";
                    echo "<td><i class='fa-regular fa-clock' style='color:#888;'></i> " . date('d/m/Y H:i', strtotime($row['created_at'])) . "</td>";

                    echo "<td class='actions-col' style='text-align: center;'>";
                    echo "  <button class='btn-icon edit-btn' onclick='editUser(" . $row['id_usuario'] . ")' title='Editar Usuario'><i class='fa-solid fa-pen-to-square'></i></button>";
                    
                    if (!$isCurrentUser) {
                        echo "  <button class='btn-icon delete-btn' onclick='deleteUser(" . $row['id_usuario'] . ", \"" . htmlspecialchars($row['username'], ENT_QUOTES) . "\")' title='Eliminar Usuario'><i class='fa-solid fa-trash'></i></button>";
                    } else {
                        echo "  <button class='btn-icon delete-btn disabled' disabled title='No puedes eliminar tu propio usuario en sesión'><i class='fa-solid fa-lock'></i></button>";
                    }
                    echo "</td>";

                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='7' style='text-align: center; padding: 30px; color: #777;'><i class='fa-solid fa-folder-open' style='font-size: 24px; margin-bottom: 8px; display: block;'></i>No hay usuarios registrados en el sistema.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<!-- Modal para Crear / Editar Usuario -->
<div id="usuarioModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="modalUserTitle"><i class="fa-solid fa-user-plus"></i> Crear Nuevo Usuario</h2>
            <span class="close-btn" onclick="closeUserModal()">&times;</span>
        </div>
        <div class="modal-body">
            <form id="usuarioForm">
                <input type="hidden" name="action" value="save">
                <input type="hidden" id="id_usuario" name="id_usuario" value="0">

                <div class="form-row">
                    <div class="form-group half">
                        <label for="username">Nombre de Usuario <span class="required">*</span></label>
                        <div class="input-with-icon">
                            <i class="fa-solid fa-at input-icon"></i>
                            <input type="text" id="username" name="username" placeholder="Ej. jperez" required autocomplete="off">
                        </div>
                        <small id="usernameMsg" class="form-hint"></small>
                    </div>

                    <div class="form-group half">
                        <label for="password">Contraseña <span id="passwordRequiredStar" class="required">*</span></label>
                        <div class="input-with-icon password-group">
                            <i class="fa-solid fa-key input-icon"></i>
                            <input type="password" id="password" name="password" placeholder="Mínimo 4 caracteres" autocomplete="new-password">
                            <i class="fa-solid fa-eye toggle-password" id="togglePasswordBtn" onclick="togglePasswordVisibility()"></i>
                        </div>
                        <small id="passwordHint" class="form-hint">Dejar en blanco si no desea cambiar la contraseña actual.</small>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group half">
                        <label for="rol_sistema">Rol en el Sistema <span class="required">*</span></label>
                        <select id="rol_sistema" name="rol_sistema" required>
                            <option value="Socio">Socio</option>
                            <option value="Administrador">Administrador</option>
                            <option value="Presidente">Presidente</option>
                            <option value="Tesorero">Tesorero</option>
                            <option value="Secretario">Secretario</option>
                        </select>
                    </div>

                    <div class="form-group half">
                        <label for="activo">Estado de la Cuenta <span class="required">*</span></label>
                        <select id="activo" name="activo" required>
                            <option value="1">Activo (Acceso permitido)</option>
                            <option value="0">Inactivo (Acceso bloqueado)</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="id_socio">Asociado Vinculado (Opcional)</label>
                    <select id="id_socio" name="id_socio">
                        <option value="">-- Sin socio vinculado (Usuario administrativo global) --</option>
                        <?php 
                        if (isset($sociosDisponibles) && is_array($sociosDisponibles)) {
                            foreach ($sociosDisponibles as $soc) {
                                $nom = htmlspecialchars($soc['ap_paterno'] . ' ' . $soc['ap_materno'] . ', ' . $soc['nombre']);
                                echo "<option value='" . $soc['id_socio'] . "'>" . $nom . " (CI: " . htmlspecialchars($soc['ci'] ?? '-') . ")</option>";
                            }
                        }
                        ?>
                    </select>
                    <small class="form-hint">Enlaza este usuario con la ficha de socio correspondiente en CEREMA.</small>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-secondary" onclick="closeUserModal()"><i class="fa-solid fa-times"></i> Cancelar</button>
            <button type="button" class="btn-primary" onclick="saveUser()"><i class="fa-solid fa-floppy-disk"></i> Guardar Usuario</button>
        </div>
    </div>
</div>

<!-- Modal de Confirmación de Eliminación -->
<div id="deleteUserModal" class="modal">
    <div class="modal-content" style="max-width: 450px; text-align: center; overflow: hidden; padding: 0;">
        <div style="background-color: #dc3545; color: white; padding: 15px 20px; display: flex; justify-content: space-between; align-items: center;">
            <h3 style="margin: 0; font-size: 16px; font-weight: 600; letter-spacing: 1px;"><i class="fa-solid fa-triangle-exclamation"></i> ELIMINAR USUARIO</h3>
            <span onclick="closeDeleteUserModal()" style="color: white; font-size: 24px; cursor: pointer; line-height: 1;">&times;</span>
        </div>
        <div class="modal-body" style="padding: 35px 20px; background-color: white;">
            <p style="margin-bottom: 25px; font-size: 15px; color: #444;">
                ¿Está seguro de que desea eliminar permanentemente la cuenta de usuario:<br>
                <strong id="deleteUsernameText" style="font-size: 18px; color: #dc3545; display: inline-block; margin-top: 10px; background: #fff0f0; padding: 6px 16px; border-radius: 6px; border: 1px solid #fecaca;"></strong>?
            </p>
            <p style="font-size: 13px; color: #888; margin-bottom: 25px;">Esta acción borrará las credenciales de acceso al sistema para este usuario.</p>
            <div style="display: flex; justify-content: center; gap: 15px;">
                <button type="button" style="background-color: #6c757d; color: white; border: none; padding: 10px 22px; border-radius: 6px; font-size: 14px; font-weight: 600; cursor: pointer; transition: opacity 0.2s;" onclick="closeDeleteUserModal()">Cancelar</button>
                <button type="button" style="background-color: #dc3545; color: white; border: none; padding: 10px 22px; border-radius: 6px; font-size: 14px; font-weight: 600; cursor: pointer; transition: opacity 0.2s;" id="btnConfirmDeleteUser">Eliminar Cuenta</button>
            </div>
        </div>
    </div>
</div>

<!-- Script JS Independiente del Módulo -->
<script src="../js/usuario.js?v=<?php echo filemtime(__DIR__ . '/../js/usuario.js'); ?>"></script>

<?php include 'footer.php'; ?>
