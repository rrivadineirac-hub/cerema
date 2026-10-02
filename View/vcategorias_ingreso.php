<?php include 'header.php'; ?>
<style>
    .data-table th { padding: 15px 20px; text-align: left; }
    .data-table td { padding: 15px 20px; vertical-align: middle; }
</style>

<div class="dashboard-header">
    <div class="header-actions" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
        <div>
            <h1 class="page-title">Configuración de Ingresos</h1>
            <p class="page-subtitle">Gestión de montos de ingresos por gestión</p>
        </div>
        <div style="display: flex; align-items: center; gap: 15px;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <label for="selectGestionFilter" style="font-weight: 600; color: var(--text-main); font-size: 13.5px;">Gestión:</label>
                <select id="selectGestionFilter" onchange="window.location.href='categorias_ingreso.controller.php?id_gestion='+this.value" style="padding: 8px 12px; border: 1.5px solid var(--border-color); border-radius: var(--radius-sm); outline: none; font-family: inherit; font-weight: 600; background-color: white; color: var(--text-main); height: 42px;">
                    <option value="all" <?php echo ($id_gestion_selected === 'all') ? 'selected' : ''; ?>>Todas las Gestiones</option>
                    <?php if (isset($gestiones_list) && is_array($gestiones_list)): ?>
                        <?php foreach ($gestiones_list as $g): ?>
                            <option value="<?php echo $g['id_gestion']; ?>" <?php echo ($id_gestion_selected == $g['id_gestion']) ? 'selected' : ''; ?>>
                                Gestión <?php echo htmlspecialchars($g['gestion']); ?><?php echo ($g['estado'] == 'En Curso') ? ' (En Curso)' : ''; ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
            <button class="btn-primary" onclick="openCreateModal()">
                <i class="fa-solid fa-plus"></i> Nuevo Ingreso
            </button>
        </div>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="data-table" style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr>
                    <th>Gestión</th>
                    <th>Categoría</th>
                    <th>Descripción</th>
                    <th>Tipo de Ingreso</th>
                    <th>Monto Sugerido (Bs)</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($categorias as $cat): ?>
                <tr>
                    <td>
                        <span class="badge-status activo" style="font-weight: 700;">
                            <?php echo !empty($cat['gestion']) ? htmlspecialchars($cat['gestion']) : 'Todas'; ?>
                        </span>
                    </td>
                    <td><strong><?php echo htmlspecialchars($cat['nombre']); ?></strong></td>
                    <td><?php echo htmlspecialchars($cat['descripcion']); ?></td>
                    <td>
                        <?php if ($cat['requiere_socio'] == 1): ?>
                            <span class="status-badge status-pagado">Extraordinario</span>
                        <?php elseif ($cat['requiere_socio'] == 2): ?>
                            <span class="status-badge" style="background: #fff3e0; color: #e65100;">Especial</span>
                        <?php else: ?>
                            <span class="status-badge status-pendiente">Genérico</span>
                        <?php endif; ?>
                    </td>
                    <td style="font-weight: bold; color: #00B300;">Bs. <?php echo number_format($cat['monto_sugerido'], 2); ?></td>
                    <td>
                        <div class="action-buttons">
                            <button class="btn-icon btn-edit" onclick="editCategoria(<?php echo $cat['id_cat_ingreso']; ?>)" title="Configurar Monto">
                                <i class="fa-solid fa-pen"></i>
                            </button>
                            <button class="btn-icon btn-delete" onclick="deleteCategoria(<?php echo $cat['id_cat_ingreso']; ?>, '<?php echo htmlspecialchars(addslashes($cat['nombre'])); ?>')" title="Eliminar Ingreso">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                
                <?php if(empty($categorias)): ?>
                <tr>
                    <td colspan="6" style="text-align: center;">No hay categorías registradas.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal para Configurar Monto -->
<div id="categoriaModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="modalTitle">Configurar Monto de Ingreso</h2>
            <span class="close" onclick="closeCategoriaModal()">&times;</span>
        </div>
        <div class="modal-body">
            <form id="categoriaForm">
                <input type="hidden" id="id_cat_ingreso" name="id_cat_ingreso">
                <input type="hidden" name="action" value="update_monto">
                
                <div class="form-grid" style="grid-template-columns: 1fr;">
                    <div class="form-group" id="group_gestion" style="display:none;">
                        <label for="id_gestion_modal">Gestión</label>
                        <select name="id_gestion" id="id_gestion_modal">
                            <option value="">Todas las Gestiones (Global)</option>
                            <?php if (isset($gestiones_list) && is_array($gestiones_list)): ?>
                                <?php foreach ($gestiones_list as $g): ?>
                                    <?php 
                                    $isSelected = false;
                                    if (isset($id_gestion_selected) && $id_gestion_selected !== 'all' && $id_gestion_selected == $g['id_gestion']) {
                                        $isSelected = true;
                                    } elseif ((!isset($id_gestion_selected) || $id_gestion_selected === 'all') && $g['estado'] == 'En Curso') {
                                        $isSelected = true;
                                    }
                                    ?>
                                    <option value="<?php echo $g['id_gestion']; ?>" <?php echo $isSelected ? 'selected' : ''; ?>>
                                        Gestión <?php echo htmlspecialchars($g['gestion']); ?><?php echo ($g['estado'] == 'En Curso') ? ' (En Curso)' : ''; ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div class="form-group" id="group_tipo" style="display:none;">
                        <label for="requiere_socio">Tipo de Ingreso</label>
                        <select name="requiere_socio" id="requiere_socio" required>
                            <option value="1">Aporte Extraordinario (Asociado)</option>
                            <option value="2">Aporte Especial (Asociado)</option>
                        </select>
                    </div>
                    
                    <div class="form-group" id="group_descripcion" style="display:none;">
                        <label for="nombre_categoria">Motivo / Detalle</label>
                        <input type="text" id="nombre_categoria" name="nombre">
                    </div>

                    <div class="form-group">
                        <label for="monto_sugerido">Monto Base o Sugerido (Bs.)</label>
                        <input type="number" step="0.01" id="monto_sugerido" name="monto_sugerido" required>
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-secondary" onclick="closeCategoriaModal()">Cancelar</button>
            <button type="button" class="btn-primary" onclick="saveCategoria()">Guardar Monto</button>
        </div>
    </div>
</div>

<link rel="stylesheet" href="../css/mensualidad.css">
<script src="../js/categorias_ingreso.js"></script>

<?php include 'footer.php'; ?>
