<?php
// View/vactivo.php
require_once __DIR__ . '/header.php';
?>

<!-- PANTALLA PRINCIPAL DE GESTIÓN DE ACTIVOS -->
<div class="dashboard-header">
    <div class="header-actions" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
        <div>
            <h1 class="page-title">Gestión de Activos y Patrimonio</h1>
            <p class="page-subtitle">Registro y control operativo de los bienes institucionales de CEREMA</p>
        </div>
        <button class="btn-primary" onclick="openActivoModal()">
            <i class="fa-solid fa-plus"></i> Registrar Activo
        </button>
    </div>
</div>

<!-- Tarjetas de Estadísticas / Resumen -->
<div class="activo-stats-grid">
    <div class="activo-stat-card">
        <div class="activo-stat-icon total">
            <i class="fa-solid fa-boxes-stacked"></i>
        </div>
        <div class="activo-stat-info">
            <h4>Total Activos</h4>
            <p><?php echo number_format($stats['total_activos'] ?? 0); ?></p>
        </div>
    </div>

    <div class="activo-stat-card">
        <div class="activo-stat-icon valor">
            <i class="fa-solid fa-vault"></i>
        </div>
        <div class="activo-stat-info">
            <h4>Valor Estimado</h4>
            <p>Bs. <?php echo number_format($stats['valor_total'] ?? 0, 2); ?></p>
        </div>
    </div>

    <div class="activo-stat-card">
        <div class="activo-stat-icon operativo">
            <i class="fa-solid fa-circle-check"></i>
        </div>
        <div class="activo-stat-info">
            <h4>En Buen Estado</h4>
            <p><?php echo number_format($stats['operativos'] ?? 0); ?></p>
        </div>
    </div>

    <div class="activo-stat-card">
        <div class="activo-stat-icon observado">
            <i class="fa-solid fa-triangle-exclamation"></i>
        </div>
        <div class="activo-stat-info">
            <h4>Mantenimiento / Riesgo</h4>
            <p><?php echo number_format($stats['observados'] ?? 0); ?></p>
        </div>
    </div>
</div>

<!-- Toolbar de Búsqueda y Filtros -->
<div class="toolbar-container">
    <div class="search-filter-group">
        <div class="search-box">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" id="searchActivo" class="search-input" placeholder="Buscar activo por nombre...">
        </div>
        
        <select id="filterTipo" class="filter-select">
            <option value="">-- Todos los Tipos --</option>
            <option value="Inmueble">Inmueble</option>
            <option value="Maquinaria">Maquinaria</option>
            <option value="Herramienta">Herramienta</option>
            <option value="Infraestructura">Infraestructura</option>
            <option value="Otro">Otro</option>
        </select>

        <select id="filterEstado" class="filter-select">
            <option value="">-- Todos los Estados --</option>
            <option value="Excelente">Excelente</option>
            <option value="Bueno">Bueno</option>
            <option value="Regular">Regular</option>
            <option value="En Mantenimiento">En Mantenimiento</option>
            <option value="Fuera de Servicio">Fuera de Servicio</option>
        </select>
    </div>
</div>

<!-- Tabla de Activos -->
<div class="table-container">
    <table class="data-table" id="activosTable">
        <thead>
            <tr>
                <th style="width: 50px;">#</th>
                <th>Nombre del Activo</th>
                <th>Tipo</th>
                <th style="text-align: right;">Valor Estimado (Bs)</th>
                <th style="text-align: center;">Fecha Adquisición</th>
                <th style="text-align: center;">Estado Operativo</th>
                <th>Observaciones</th>
                <th style="text-align: center; width: 100px;">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            if (isset($activos) && $activos->rowCount() > 0): 
                $count = 1;
                while ($row = $activos->fetch(PDO::FETCH_ASSOC)):
                    $tipoClass = strtolower($row['tipo_activo']);
                    
                    // Clase de estado para badges
                    $estadoRaw = $row['estado_operativo'];
                    $estadoClass = strtolower(str_replace(' ', '-', $estadoRaw));
                    
                    $fechaAdq = !empty($row['fecha_adquisicion']) ? date('d/m/Y', strtotime($row['fecha_adquisicion'])) : '-';
                    $nombreEscaped = htmlspecialchars($row['nombre_activo'], ENT_QUOTES);
            ?>
                <tr>
                    <td><?php echo $count++; ?></td>
                    <td style="font-weight: 600; color: var(--text-main);"><?php echo htmlspecialchars($row['nombre_activo']); ?></td>
                    <td data-tipo="<?php echo htmlspecialchars($row['tipo_activo']); ?>">
                        <span class="badge-tipo <?php echo $tipoClass; ?>"><?php echo htmlspecialchars($row['tipo_activo']); ?></span>
                    </td>
                    <td style="text-align: right; font-weight: 600; color: #27AE60;">
                        Bs. <?php echo number_format($row['valor_estimado'], 2); ?>
                    </td>
                    <td style="text-align: center; font-size: 13.5px;"><?php echo $fechaAdq; ?></td>
                    <td style="text-align: center;" data-estado="<?php echo htmlspecialchars($estadoRaw); ?>">
                        <span class="badge-estado <?php echo $estadoClass; ?>"><?php echo htmlspecialchars($estadoRaw); ?></span>
                    </td>
                    <td style="font-size: 13px; color: var(--text-muted); max-width: 250px;">
                        <?php echo !empty($row['observaciones']) ? htmlspecialchars($row['observaciones']) : '<span style="opacity:0.5;">-</span>'; ?>
                    </td>
                    <td class="actions-col">
                        <button class="btn-icon edit-btn" onclick="editActivo(<?php echo $row['id_activo']; ?>)" title="Editar">
                            <i class="fa-solid fa-pen"></i>
                        </button>
                        <button class="btn-icon delete-btn" onclick="deleteActivo(<?php echo $row['id_activo']; ?>, '<?php echo $nombreEscaped; ?>')" title="Eliminar">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </td>
                </tr>
            <?php 
                endwhile;
            else:
            ?>
                <tr>
                    <td colspan="8" style="text-align: center; padding: 40px; color: var(--text-muted);">
                        <i class="fa-solid fa-boxes-stacked" style="font-size: 40px; margin-bottom: 10px; display: block; opacity: 0.3;"></i>
                        No hay activos registrados en el sistema.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Modal para Crear/Editar Activo -->
<div id="activoModal" class="modal">
    <div class="modal-content" style="max-width: 600px;">
        <div class="modal-header">
            <h2 id="modalTitle">Registrar Nuevo Activo</h2>
            <span class="close-btn" onclick="closeActivoModal()">&times;</span>
        </div>
        <div class="modal-body">
            <form id="activoForm">
                <input type="hidden" name="action" value="save">
                <input type="hidden" id="id_activo" name="id_activo" value="">
                
                <div class="form-group" style="margin-bottom: 15px;">
                    <label for="nombre_activo">Nombre / Descripción del Activo (*)</label>
                    <input type="text" id="nombre_activo" name="nombre_activo" required placeholder="Ej: Podadora Industrial, Sistema de Riego, etc.">
                </div>

                <div class="form-grid-2" style="margin-bottom: 15px;">
                    <div class="form-group">
                        <label for="tipo_activo">Tipo de Activo (*)</label>
                        <select id="tipo_activo" name="tipo_activo" required>
                            <option value="" disabled selected>-- Seleccione Tipo --</option>
                            <option value="Inmueble">Inmueble</option>
                            <option value="Maquinaria">Maquinaria</option>
                            <option value="Herramienta">Herramienta</option>
                            <option value="Infraestructura">Infraestructura</option>
                            <option value="Otro">Otro</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="valor_estimado">Valor Estimado (Bs.) (*)</label>
                        <input type="number" step="0.01" min="0.01" id="valor_estimado" name="valor_estimado" required placeholder="0.00">
                    </div>
                </div>

                <div class="form-grid-2" style="margin-bottom: 15px;">
                    <div class="form-group">
                        <label for="fecha_adquisicion">Fecha de Adquisición (*)</label>
                        <input type="date" id="fecha_adquisicion" name="fecha_adquisicion" min="2018-01-01" required value="<?php echo date('Y-m-d'); ?>">
                    </div>

                    <div class="form-group">
                        <label for="estado_operativo">Estado Operativo (*)</label>
                        <select id="estado_operativo" name="estado_operativo" required>
                            <option value="Excelente">Excelente</option>
                            <option value="Bueno" selected>Bueno</option>
                            <option value="Regular">Regular</option>
                            <option value="En Mantenimiento">En Mantenimiento</option>
                            <option value="Fuera de Servicio">Fuera de Servicio</option>
                        </select>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 15px;">
                    <label for="observaciones">Observaciones / Detalles Adicionales</label>
                    <textarea id="observaciones" name="observaciones" rows="3" placeholder="Estado físico, ubicación dentro del predio, historial de mantenimiento..."></textarea>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-secondary" onclick="closeActivoModal()">Cancelar</button>
            <button type="button" class="btn-primary" id="btnGuardarActivo" onclick="saveActivo()">Guardar Activo</button>
        </div>
    </div>
</div>

<!-- Modal de Confirmación de Eliminación -->
<div id="deleteModal" class="modal">
    <div class="modal-content" style="max-width: 450px; text-align: center; overflow: hidden; padding: 0;">
        <div style="background-color: #e74c3c; color: white; padding: 15px 20px; display: flex; justify-content: space-between; align-items: center;">
            <h3 style="margin: 0; font-size: 16px; font-weight: 600; letter-spacing: 1px;">ELIMINAR ACTIVO</h3>
            <span onclick="closeDeleteModal()" style="color: white; font-size: 24px; cursor: pointer; line-height: 1;">&times;</span>
        </div>
        <div class="modal-body" style="padding: 30px 20px; background-color: white;">
            <p style="margin-bottom: 25px; font-size: 15px; color: #444;">
                ¿Estás seguro de que deseas eliminar el activo: <br><strong id="deleteDesc" style="font-size: 17px; color: #222; display: inline-block; margin-top: 8px;"></strong>?
            </p>
            <div style="display: flex; justify-content: center; gap: 15px;">
                <button type="button" class="btn-secondary" onclick="closeDeleteModal()">Cancelar</button>
                <button type="button" class="btn-primary" id="btnConfirmDelete" style="background-color: #e74c3c; border-color: #e74c3c;">Eliminar</button>
            </div>
        </div>
    </div>
</div>

<link rel="stylesheet" href="../css/activo.css">
<script src="../js/activo.js"></script>
