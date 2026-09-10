<?php 
// View/vsocio.php
include 'header.php'; 
?>

<div class="dashboard-header">
    <div class="header-actions" style="display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h1 class="page-title">Lista de Socios</h1>
            <p class="page-subtitle">Gestiona a los miembros de la fraternidad (Activos, Inactivos y Pasivos)</p>
        </div>
        <button class="btn-primary" onclick="openModal()">
            <i class="fa-solid fa-plus"></i> Nuevo Socio
        </button>
    </div>
</div>

<div class="search-section" style="margin-bottom: 20px; display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
    <div class="search-input-wrapper" style="position: relative; flex: 1; max-width: 400px;">
        <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: var(--text-muted);"></i>
        <input type="text" id="searchSocio" placeholder="Buscar por CI, Nombre o Código..." style="width: 100%; padding: 10px 15px 10px 40px; border: 1px solid var(--border-color); border-radius: var(--radius-sm); outline: none; font-family: inherit; text-transform: uppercase;">
    </div>
    <div class="filter-estado-wrapper">
        <select id="filterEstado" onchange="searchSocios()" style="padding: 10px 15px; border: 1px solid var(--border-color); border-radius: var(--radius-sm); outline: none; font-family: inherit; background-color: white; color: var(--text-main); font-weight: 500; height: 42px;">
            <option value="">Todos los Estados</option>
            <option value="Activo">Activos</option>
            <option value="Inactivo">Inactivos</option>
            <option value="Pasivo">Pasivos</option>
        </select>
    </div>
    <button class="btn-primary" onclick="searchSocios()" id="btnSearch">
        <i class="fa-solid fa-search"></i> Buscar
    </button>
    <button class="btn-secondary" onclick="clearSearch()" id="btnClearSearch" style="display: none; align-items: center; gap: 5px;">
        <i class="fa-solid fa-times"></i> Limpiar
    </button>
</div>

<div class="table-container">
    <table class="data-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>CI</th>
                <th>Nombre Completo</th>
                <th>Teléfono</th>
                <th>Fecha Ingreso</th>
                <th>Estado</th>
                <th>Acciones (Cant.)</th>
                <th>Cuota Inicial (Bs.)</th>
                <th>Opciones</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            if(isset($socios) && $socios->rowCount() > 0) {
                while($row = $socios->fetch(PDO::FETCH_ASSOC)) {
                    $estadoTexto = ($row['estado'] === 'Moroso') ? 'Pasivo' : $row['estado'];
                    $estadoClass = strtolower($estadoTexto);
                    $montoPagado = isset($row['cuota_inicial_pagado']) ? (float)$row['cuota_inicial_pagado'] : 0.00;
                    $cuotaAsignada = isset($row['cuota_inicial']) ? (float)$row['cuota_inicial'] : 0.00;
                    $acciones = isset($row['acciones']) ? (int)$row['acciones'] : 1;
                    $metaTotal = $acciones * $cuotaAsignada;

                    echo "<tr>";
                    echo "<td>" . sprintf("ASC-%03d", $row['id_socio']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['ci']) . "</td>";
                    echo "<td><strong>" . htmlspecialchars($row['ap_paterno']) . " " . htmlspecialchars($row['ap_materno']) . "</strong>, " . htmlspecialchars($row['nombre']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['telefono']) . "</td>";
                    echo "<td>" . date('d/m/Y', strtotime($row['fecha_ingreso'])) . "</td>";
                    echo "<td><span class='badge-status {$estadoClass}'>" . htmlspecialchars($estadoTexto) . "</span></td>";
                    echo "<td>" . $acciones . "</td>";

                    if ($montoPagado > 0) {
                        $isComplete = ($metaTotal > 0 && $montoPagado >= $metaTotal);
                        echo "<td style='font-weight: 700; color: #27ae60;' title='" . ($isComplete ? "Cuota Inicial completada en su totalidad" : "Monto pagado de Cuota Inicial") . "'>";
                        if ($isComplete) {
                            echo "<i class='fa-solid fa-circle-check' style='font-size: 12px; margin-right: 4px; color: #22c55e;'></i>";
                        }
                        echo "Bs. " . number_format($montoPagado, 2);
                        echo "</td>";
                    } else {
                        // NO CANCELÓ NADA (0 abonos) -> Muestra '-'
                        echo "<td style='text-align: center; color: #94a3b8; font-weight: 500;' title='Sin abonos registrados'>-</td>";
                    }
                    $nombreCompletoDelete = htmlspecialchars($row['nombre'] . ' ' . $row['ap_paterno'] . ' ' . $row['ap_materno'], ENT_QUOTES);
                    echo "<td class='actions-col'>
                            <button class='btn-icon edit-btn' onclick='editSocio(" . $row['id_socio'] . ")' title='Editar'><i class='fa-solid fa-pen'></i></button>
                            <button class='btn-icon delete-btn' onclick='deleteSocio(" . $row['id_socio'] . ", \"" . $nombreCompletoDelete . "\")' title='Eliminar'><i class='fa-solid fa-trash'></i></button>
                          </td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='9' style='text-align: center; padding: 20px;'>No hay asociados registrados.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<!-- Modal Structure for Create/Edit -->
<div id="socioModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="modalTitle">Nuevo Socio</h2>
            <span class="close-btn" onclick="closeModal()">&times;</span>
        </div>
        <div class="modal-body">
            <form id="socioForm">
                <input type="hidden" name="action" value="save">
                <input type="hidden" id="id_socio" name="id_socio" value="">
                
                <div class="form-row">
                    <div class="form-group half">
                        <label for="ci">CI</label>
                        <input type="text" id="ci" name="ci" required>
                    </div>
                    <div class="form-group half">
                        <label for="ap_paterno">Apellido Paterno</label>
                        <input type="text" id="ap_paterno" name="ap_paterno" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group half">
                        <label for="ap_materno">Apellido Materno</label>
                        <input type="text" id="ap_materno" name="ap_materno" required>
                    </div>
                    <div class="form-group half">
                        <label for="nombre">Nombre(s)</label>
                        <input type="text" id="nombre" name="nombre" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group half">
                        <label for="telefono">Teléfono</label>
                        <input type="text" id="telefono" name="telefono">
                    </div>
                    <div class="form-group half">
                        <label for="correo">Correo Electrónico</label>
                        <input type="email" id="correo" name="correo">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group half">
                        <label for="fecha_ingreso">Fecha de Ingreso</label>
                        <input type="date" id="fecha_ingreso" name="fecha_ingreso" min="2018-01-01" required value="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div class="form-group half">
                        <label for="estado">Estado</label>
                        <select id="estado" name="estado">
                            <option value="Activo">Activo</option>
                            <option value="Inactivo">Inactivo</option>
                            <option value="Pasivo">Pasivo</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group half">
                        <label for="acciones">Cant. Acciones</label>
                        <input type="number" id="acciones" name="acciones" min="1" required value="1">
                    </div>
                    <div class="form-group half">
                        <label for="cuota_inicial">Cuota Inicial por Acción (Bs.)</label>
                        <input type="number" step="0.01" min="0" id="cuota_inicial" name="cuota_inicial" required value="0.00" placeholder="0.00">
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-secondary" onclick="closeModal()">Cancelar</button>
            <button type="button" class="btn-primary" onclick="saveSocio()">Guardar</button>
        </div>
    </div>
</div>

<!-- Modal de Confirmación de Eliminación -->
<div id="deleteModal" class="modal">
    <div class="modal-content" style="max-width: 450px; text-align: center; overflow: hidden; padding: 0;">
        <div style="background-color: #dc3545; color: white; padding: 15px 20px; display: flex; justify-content: space-between; align-items: center;">
            <h3 style="margin: 0; font-size: 16px; font-weight: 600; letter-spacing: 1px;">ELIMINAR REGISTRO</h3>
            <span onclick="closeDeleteModal()" style="color: white; font-size: 24px; cursor: pointer; line-height: 1;">&times;</span>
        </div>
        <div class="modal-body" style="padding: 40px 20px; background-color: white;">
            <p style="margin-bottom: 30px; font-size: 16px; color: #444;">
                ¿Está seguro de que desea eliminar al asociado: <br><strong id="deleteDesc" style="font-size: 18px; color: #222; display: inline-block; margin-top: 10px;"></strong>?
            </p>
            <div style="display: flex; justify-content: center; gap: 15px;">
                <button type="button" style="background-color: #28a745; color: white; border: none; padding: 10px 20px; border-radius: 4px; font-size: 14px; font-weight: 600; cursor: pointer; transition: opacity 0.2s;" onclick="closeDeleteModal()">Cancelar</button>
                <button type="button" style="background-color: #dc3545; color: white; border: none; padding: 10px 20px; border-radius: 4px; font-size: 14px; font-weight: 600; cursor: pointer; transition: opacity 0.2s;" id="btnConfirmDelete">Borrar registro</button>
            </div>
        </div>
    </div>
</div>

<!-- Extra styles/scripts for Socios view -->
<link rel="stylesheet" href="../css/socio.css">
<script src="../js/socio.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const searchInput = document.getElementById("searchSocio");
    const btnClear = document.getElementById("btnClearSearch");
    
    // Mostrar/ocultar botón limpiar al escribir
    searchInput.addEventListener("input", function() {
        if (this.value.trim().length > 0) {
            btnClear.style.display = "inline-flex";
        } else {
            btnClear.style.display = "none";
            filterTable("");
        }
    });

    // Permitir buscar presionando Enter
    searchInput.addEventListener("keypress", function(e) {
        if (e.key === "Enter") {
            searchSocios();
        }
    });
});

function searchSocios() {
    const query = document.getElementById("searchSocio").value.trim().toLowerCase();
    filterTable(query);
}

function clearSearch() {
    const searchInput = document.getElementById("searchSocio");
    searchInput.value = "";
    document.getElementById("filterEstado").value = "";
    document.getElementById("btnClearSearch").style.display = "none";
    filterTable("");
}

function filterTable(query) {
    const rows = document.querySelectorAll(".data-table tbody tr");
    const estadoFilter = document.getElementById("filterEstado") ? document.getElementById("filterEstado").value.toLowerCase() : "";
    
    rows.forEach(row => {
        // Ignorar fila de "No hay socios registrados" si existe
        if (row.cells.length === 1) return; 

        // Buscar en las columnas: 0 (ID), 1 (CI), 2 (Nombre), 5 (Estado)
        const id = row.cells[0]?.textContent.toLowerCase() || "";
        const ci = row.cells[1]?.textContent.toLowerCase() || "";
        const nombre = row.cells[2]?.textContent.toLowerCase() || "";
        const estadoText = row.cells[5]?.textContent.trim().toLowerCase() || "";

        const matchesQuery = (id.includes(query) || ci.includes(query) || nombre.includes(query));
        const matchesEstado = (estadoFilter === "" || estadoText.includes(estadoFilter));

        if (matchesQuery && matchesEstado) {
            row.style.display = "";
        } else {
            row.style.display = "none";
        }
    });
}
</script>

<?php include 'footer.php'; ?>
