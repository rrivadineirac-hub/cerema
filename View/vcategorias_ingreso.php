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
                    <th>Categoría / Motivo</th>
                    <th>Tipo de Ingreso</th>
                    <th>Periodo (Inicio - Fin)</th>
                    <th>Monto Mensual</th>
                    <th>Monto Total</th>
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
                    <td>
                        <?php if ($cat['requiere_socio'] == 1): ?>
                            <span class="status-badge status-pagado">Extraordinario</span>
                        <?php elseif ($cat['requiere_socio'] == 2): ?>
                            <span class="status-badge" style="background: #fff3e0; color: #e65100;">Especial</span>
                        <?php else: ?>
                            <span class="status-badge status-pendiente">Genérico</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php 
                        if (!empty($cat['mes_inicio']) && !empty($cat['anio_inicio'])) {
                            $periodoStr = substr($cat['mes_inicio'], 0, 3) . ' ' . $cat['anio_inicio'];
                            if (!empty($cat['mes_fin']) && !empty($cat['anio_fin'])) {
                                $periodoStr .= ' a ' . substr($cat['mes_fin'], 0, 3) . ' ' . $cat['anio_fin'];
                            }
                            echo '<span style="font-weight: 600; color: #0284c7;"><i class="fa-solid fa-calendar-days"></i> ' . htmlspecialchars($periodoStr) . '</span>';
                        } else {
                            echo '<span style="color: #94a3b8;">Sin periodo</span>';
                        }
                        ?>
                    </td>
                    <td style="font-weight: bold; color: #0284c7;">Bs. <?php echo number_format($cat['monto_mensual'] > 0 ? $cat['monto_mensual'] : $cat['monto_sugerido'], 2); ?> / mes</td>
                    <td style="font-weight: 800; color: #27AE60;">Bs. <?php echo number_format($cat['monto_total'] > 0 ? $cat['monto_total'] : $cat['monto_sugerido'], 2); ?></td>
                    <td>
                        <div class="action-buttons">
                            <button class="btn-icon btn-edit" onclick="editCategoria(<?php echo $cat['id_cat_ingreso']; ?>)" title="Editar Configuración">
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
                    <td colspan="7" style="text-align: center;">No hay ingresos configurados.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal para Configurar / Crear Nuevo Ingreso -->
<div id="categoriaModal" class="modal">
    <div class="modal-content" style="max-width: 550px; border-radius: 14px; display: flex; flex-direction: column; max-height: 90vh; overflow: hidden; padding: 0;">
        <div class="modal-header" style="flex-shrink: 0; border-bottom: 1px solid #e2e8f0; padding: 16px 24px; background: #ffffff; display: flex; justify-content: space-between; align-items: center;">
            <h2 id="modalTitle" style="font-size: 18px; font-weight: 700; color: #1e293b; margin: 0;">Crear Nuevo Ingreso</h2>
            <span class="close" onclick="closeCategoriaModal()">&times;</span>
        </div>
        <div class="modal-body" style="flex: 1; overflow-y: auto; padding: 20px 24px; background: #ffffff;">
            <form id="categoriaForm">
                <input type="hidden" id="id_cat_ingreso" name="id_cat_ingreso">
                <input type="hidden" id="form_action" name="action" value="create">
                <input type="hidden" id="monto_total" name="monto_total" value="0.00">
                
                <div class="form-grid" style="display: flex; flex-direction: column; gap: 14px;">
                    
                    <!-- Gestión -->
                    <div class="form-group" id="group_gestion">
                        <label for="id_gestion_modal" style="font-weight: 600; font-size: 13px; color: #334155; margin-bottom: 4px; display: block;">Gestión</label>
                        <select name="id_gestion" id="id_gestion_modal" style="width: 100%; padding: 10px; border-radius: 8px; border: 1.5px solid #cbd5e1; font-weight: 600;">
                            <option value="">Todas las Gestiones (Global)</option>
                            <?php if (isset($gestiones_list) && is_array($gestiones_list)): ?>
                                <?php foreach ($gestiones_list as $g): ?>
                                    <option value="<?php echo $g['id_gestion']; ?>">
                                        Gestión <?php echo htmlspecialchars($g['gestion']); ?><?php echo ($g['estado'] == 'En Curso') ? ' (En Curso)' : ''; ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <!-- Tipo de Ingreso -->
                    <div class="form-group" id="group_tipo">
                        <label for="requiere_socio" style="font-weight: 600; font-size: 13px; color: #334155; margin-bottom: 4px; display: block;">Tipo de Ingreso</label>
                        <select name="requiere_socio" id="requiere_socio" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1.5px solid #cbd5e1;">
                            <option value="1">Aporte Extraordinario (Asociado)</option>
                            <option value="2">Aporte Especial (Asociado)</option>
                        </select>
                    </div>
                    
                    <!-- Motivo / Detalle -->
                    <div class="form-group" id="group_descripcion">
                        <label for="nombre_categoria" style="font-weight: 600; font-size: 13px; color: #334155; margin-bottom: 4px; display: block;">Motivo / Detalle</label>
                        <input type="text" id="nombre_categoria" name="nombre" placeholder="Ej: CONSTRUCCION, RIFA, KERMES" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1.5px solid #cbd5e1;">
                    </div>

                    <!-- Fila: Periodo de Inicio (Año y Mes) -->
                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; padding: 12px; border-radius: 8px;">
                        <label style="font-weight: 700; font-size: 13px; color: #0284c7; margin-bottom: 8px; display: block;">
                            <i class="fa-solid fa-calendar-play"></i> Inicio del Cobro (Año y Mes)
                        </label>
                        <div style="display: flex; gap: 10px;">
                            <div style="flex: 1;">
                                <label style="font-size: 11px; font-weight: 600; color: #64748b;">Año Inicio:</label>
                                <select name="anio_inicio" id="anio_inicio" onchange="calculateIncomeTotals()" style="width: 100%; padding: 8px; border-radius: 6px; border: 1px solid #cbd5e1; font-weight: 600;">
                                    <?php for ($y = 2018; $y <= 2029; $y++): ?>
                                        <option value="<?php echo $y; ?>" <?php echo ($y == date('Y')) ? 'selected' : ''; ?>><?php echo $y; ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div style="flex: 1;">
                                <label style="font-size: 11px; font-weight: 600; color: #64748b;">Mes Inicio:</label>
                                <select name="mes_inicio" id="mes_inicio" onchange="calculateIncomeTotals()" style="width: 100%; padding: 8px; border-radius: 6px; border: 1px solid #cbd5e1;">
                                    <?php 
                                    $mesesArr = ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
                                    foreach ($mesesArr as $idx => $mName): 
                                    ?>
                                        <option value="<?php echo $mName; ?>" <?php echo ($idx == 0) ? 'selected' : ''; ?>><?php echo $mName; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Fila: Periodo de Terminación / Fin (Año y Mes) -->
                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; padding: 12px; border-radius: 8px;">
                        <label style="font-weight: 700; font-size: 13px; color: #e65100; margin-bottom: 8px; display: block;">
                            <i class="fa-solid fa-calendar-check"></i> Terminación / Fin del Cobro (Año y Mes)
                        </label>
                        <div style="display: flex; gap: 10px;">
                            <div style="flex: 1;">
                                <label style="font-size: 11px; font-weight: 600; color: #64748b;">Año Fin:</label>
                                <select name="anio_fin" id="anio_fin" onchange="calculateIncomeTotals()" style="width: 100%; padding: 8px; border-radius: 6px; border: 1px solid #cbd5e1; font-weight: 600;">
                                    <?php for ($y = 2018; $y <= 2029; $y++): ?>
                                        <option value="<?php echo $y; ?>" <?php echo ($y == date('Y')) ? 'selected' : ''; ?>><?php echo $y; ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div style="flex: 1;">
                                <label style="font-size: 11px; font-weight: 600; color: #64748b;">Mes Fin:</label>
                                <select name="mes_fin" id="mes_fin" onchange="calculateIncomeTotals()" style="width: 100%; padding: 8px; border-radius: 6px; border: 1px solid #cbd5e1;">
                                    <?php foreach ($mesesArr as $idx => $mName): ?>
                                        <option value="<?php echo $mName; ?>" <?php echo ($idx == 11) ? 'selected' : ''; ?>><?php echo $mName; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Monto Mensual -->
                    <div class="form-group">
                        <label for="monto_mensual" style="font-weight: 600; font-size: 13px; color: #334155; margin-bottom: 4px; display: block;">Monto Mensual por Cuota (Bs./mes)</label>
                        <input type="number" step="0.01" id="monto_mensual" name="monto_mensual" placeholder="Ej: 50.00" oninput="calculateIncomeTotals()" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1.5px solid #cbd5e1; font-weight: 700; color: #27AE60;">
                    </div>

                </div>
            </form>
        </div>
        
        <!-- FOOTER FIJO SIEMPRE VISIBLE CON RESUMEN CALCULADO Y BOTONES -->
        <div class="modal-footer" style="flex-shrink: 0; border-top: 1px solid #e2e8f0; padding: 14px 24px; background: #ffffff; display: flex; flex-direction: column; gap: 12px; box-shadow: 0 -4px 12px rgba(0,0,0,0.03);">
            <!-- Resumen Calculado SIEMPRE VISIBLE -->
            <div id="calcSummaryBox" style="background: #e0f2fe; border: 1.5px solid #7dd3fc; padding: 12px 16px; border-radius: 8px; display: flex; justify-content: space-between; align-items: center; width: 100%;">
                <div>
                    <span style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: #0369a1; display: block;">Duración Calculada</span>
                    <strong id="calc_total_meses" style="font-size: 15px; color: #0284c7; font-weight: 800;">12 Meses</strong>
                </div>
                <div style="text-align: right;">
                    <span style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: #0369a1; display: block;">Monto Total Acumulado</span>
                    <strong id="calc_monto_total" style="font-size: 20px; color: #27AE60; font-weight: 800;">Bs. 0.00</strong>
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px; width: 100%;">
                <button type="button" class="btn-secondary" onclick="closeCategoriaModal()">Cancelar</button>
                <button type="button" class="btn-primary" onclick="saveCategoria()" style="background: #FF7A00; border: none; font-weight: 700; padding: 10px 22px; border-radius: 8px; color: white;">Guardar Ingreso</button>
            </div>
        </div>
    </div>
</div>

<link rel="stylesheet" href="../css/mensualidad.css">
<script src="../js/categorias_ingreso.js"></script>

<?php include 'footer.php'; ?>
