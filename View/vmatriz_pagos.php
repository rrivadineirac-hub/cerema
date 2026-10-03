<?php
// View/vmatriz_pagos.php
require_once __DIR__ . '/header.php';

$anos = range(2018, 2029);
$meses_nombres_view = [
    1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
    5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
    9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
];
?>

<div class="main-content" style="padding: 20px; background-color: #F8FAFC;">
    <div class="content-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; margin-bottom: 20px;">
        <div>
            <h1 style="margin: 0; font-size: 24px; color: #1E293B; font-weight: 700; display: flex; align-items: center; gap: 10px;">
                <i class="fa-solid fa-table-cells" style="color: #2563EB;"></i> Matriz Rápida de Pagos por Asociado
            </h1>
            <p style="margin: 4px 0 0 0; color: #64748B; font-size: 14px;">
                Selecciona el <b>último mes pagado</b> para cada año (2018 - 2029). Al guardar, se registrarán automáticamente todas las mensualidades anteriores de ese año.
            </p>
        </div>
        <div style="display: flex; gap: 10px; align-items: center;">
            <a href="../Controller/mensualidad.controller.php" class="btn" style="background: #E2E8F0; color: #334155; padding: 10px 18px; border-radius: 8px; font-weight: 600; text-decoration: none; display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-arrow-left"></i> Aporte Mensual
            </a>
            <button id="btnSaveAllMatrix" class="btn" style="background: #10B981; color: white; border: none; padding: 10px 20px; border-radius: 8px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 8px; box-shadow: 0 2px 8px rgba(16, 185, 129, 0.3);">
                <i class="fa-solid fa-save"></i> Guardar Todos los Cambios
            </button>
        </div>
    </div>

    <!-- Filtros y Búsqueda -->
    <div style="background: white; border-radius: 12px; padding: 15px 20px; margin-bottom: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
        <div style="flex: 1; min-width: 280px; max-width: 500px; position: relative;">
            <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #94A3B8;"></i>
            <input type="text" id="matrixSearchInput" placeholder="Buscar por nombre de asociado o N° Acción..." style="width: 100%; padding: 10px 14px 10px 40px; border: 1px solid #CBD5E1; border-radius: 8px; font-size: 14px; outline: none; transition: border-color 0.2s;">
        </div>
        <div style="display: flex; gap: 15px; align-items: center; font-size: 13px; color: #475569;">
            <div style="display: flex; align-items: center; gap: 6px;">
                <span style="display: inline-block; width: 12px; height: 12px; background: #DCFCE7; border: 1px solid #86EFAC; border-radius: 3px;"></span> Completo (12 Meses)
            </div>
            <div style="display: flex; align-items: center; gap: 6px;">
                <span style="display: inline-block; width: 12px; height: 12px; background: #E0F2FE; border: 1px solid #7DD3FC; border-radius: 3px;"></span> Parcial
            </div>
            <div style="display: flex; align-items: center; gap: 6px;">
                <span style="display: inline-block; width: 12px; height: 12px; background: #F1F5F9; border: 1px solid #CBD5E1; border-radius: 3px;"></span> Sin Pago
            </div>
            <div style="font-weight: 700; color: #1E293B; margin-left: 10px;">
                Total Asociados: <span style="color: #2563EB;"><?php echo count($socios); ?></span>
            </div>
        </div>
    </div>

    <!-- Tabla Matriz de Pagos -->
    <div style="background: white; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); overflow: hidden;">
        <div style="overflow-x: auto; max-height: 72vh;">
            <table id="matrixTable" style="width: 100%; border-collapse: separate; border-spacing: 0; font-size: 13px; text-align: left;">
                <thead>
                    <tr style="background: #1E293B; color: white; position: sticky; top: 0; z-index: 10;">
                        <th style="padding: 12px 10px; width: 45px; text-align: center; border-bottom: 2px solid #334155; position: sticky; left: 0; background: #1E293B; z-index: 12;">#</th>
                        <th style="padding: 12px 12px; min-width: 90px; border-bottom: 2px solid #334155; position: sticky; left: 45px; background: #1E293B; z-index: 12;">Acción</th>
                        <th style="padding: 12px 15px; min-width: 220px; border-bottom: 2px solid #334155; position: sticky; left: 135px; background: #1E293B; z-index: 12; box-shadow: 4px 0 6px -2px rgba(0,0,0,0.2);">Asociado</th>
                        <?php foreach ($anos as $y): ?>
                            <th style="padding: 10px 8px; text-align: center; min-width: 130px; border-bottom: 2px solid #334155; border-left: 1px solid #334155;">
                                <div><?php echo $y; ?></div>
                                <div style="font-size: 11px; font-weight: normal; color: #94A3B8;">
                                    <?php echo ($y <= 2024) ? '30 Bs/mes' : '50 Bs/mes'; ?>
                                </div>
                            </th>
                        <?php endforeach; ?>
                        <th style="padding: 12px 15px; text-align: center; min-width: 100px; border-bottom: 2px solid #334155; position: sticky; right: 0; background: #1E293B; z-index: 12; box-shadow: -4px 0 6px -2px rgba(0,0,0,0.2);">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $index = 1;
                    foreach ($socios as $soc): 
                        $s_id = $soc['id_socio'];
                        $nombre_completo = trim($soc['ap_paterno'] . ' ' . $soc['ap_materno'] . ' ' . $soc['nombre']);
                        $nro_accion = !empty($soc['nro_accion']) ? $soc['nro_accion'] : $soc['id_socio'];
                    ?>
                        <tr class="socio-row" data-id="<?php echo $s_id; ?>" data-search="<?php echo htmlspecialchars(strtolower($nro_accion . ' ' . $nombre_completo)); ?>" style="border-bottom: 1px solid #E2E8F0; transition: background 0.15s;">
                            <td style="padding: 10px; text-align: center; color: #64748B; font-weight: 600; position: sticky; left: 0; background: white; z-index: 5; border-bottom: 1px solid #E2E8F0; border-right: 1px solid #F1F5F9;">
                                <?php echo $index++; ?>
                            </td>
                            <td style="padding: 10px 12px; font-weight: 700; color: #2563EB; position: sticky; left: 45px; background: white; z-index: 5; border-bottom: 1px solid #E2E8F0; border-right: 1px solid #F1F5F9;">
                                N° <?php echo $nro_accion; ?>
                            </td>
                            <td style="padding: 10px 15px; font-weight: 600; color: #1E293B; position: sticky; left: 135px; background: white; z-index: 5; border-bottom: 1px solid #E2E8F0; box-shadow: 4px 0 6px -2px rgba(0,0,0,0.06);">
                                <?php echo htmlspecialchars($nombre_completo); ?>
                            </td>

                            <?php foreach ($anos as $y): 
                                $selected_m_idx = $paidMap[$s_id][$y] ?? 0;
                                $min_m_idx = ($y == 2018) ? 5 : 1;
                                
                                // Determinar color de estilo inicial según mes seleccionado
                                $bgColor = "#F1F5F9";
                                $textColor = "#64748B";
                                $borderColor = "#CBD5E1";
                                if ($selected_m_idx == 12 || ($y == 2018 && $selected_m_idx == 12)) {
                                    $bgColor = "#DCFCE7";
                                    $textColor = "#15803D";
                                    $borderColor = "#86EFAC";
                                } elseif ($selected_m_idx > 0) {
                                    $bgColor = "#E0F2FE";
                                    $textColor = "#0369A1";
                                    $borderColor = "#7DD3FC";
                                }
                            ?>
                                <td style="padding: 8px 6px; text-align: center; border-left: 1px solid #F1F5F9; border-bottom: 1px solid #E2E8F0;">
                                    <select class="year-select" data-year="<?php echo $y; ?>" style="width: 100%; padding: 6px 8px; border-radius: 6px; border: 1px solid <?php echo $borderColor; ?>; background-color: <?php echo $bgColor; ?>; color: <?php echo $textColor; ?>; font-size: 12px; font-weight: 600; cursor: pointer; outline: none; transition: all 0.2s;">
                                        <option value="0" <?php echo ($selected_m_idx == 0) ? 'selected' : ''; ?>>Ninguno</option>
                                        <?php for ($m = $min_m_idx; $m <= 12; $m++): ?>
                                            <option value="<?php echo $m; ?>" <?php echo ($selected_m_idx == $m) ? 'selected' : ''; ?>>
                                                Hasta <?php echo $meses_nombres_view[$m]; ?>
                                            </option>
                                        <?php endfor; ?>
                                    </select>
                                </td>
                            <?php endforeach; ?>

                            <td style="padding: 8px 10px; text-align: center; position: sticky; right: 0; background: white; z-index: 5; border-bottom: 1px solid #E2E8F0; box-shadow: -4px 0 6px -2px rgba(0,0,0,0.06);">
                                <button class="btn-save-row" data-id="<?php echo $s_id; ?>" style="background: #2563EB; color: white; border: none; padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 4px; transition: background 0.2s;">
                                    <i class="fa-solid fa-floppy-disk"></i> Guardar
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Aplicar estilos dinámicos a los selects según la opción elegida
    function updateSelectStyle(select) {
        const val = parseInt(select.value);
        const year = parseInt(select.dataset.year);

        if (val === 12 || (year === 2018 && val === 12)) {
            select.style.backgroundColor = '#DCFCE7';
            select.style.color = '#15803D';
            select.style.borderColor = '#86EFAC';
        } else if (val > 0) {
            select.style.backgroundColor = '#E0F2FE';
            select.style.color = '#0369A1';
            select.style.borderColor = '#7DD3FC';
        } else {
            select.style.backgroundColor = '#F1F5F9';
            select.style.color = '#64748B';
            select.style.borderColor = '#CBD5E1';
        }
    }

    document.querySelectorAll('.year-select').forEach(select => {
        select.addEventListener('change', function() {
            updateSelectStyle(this);
        });
    });

    // 2. Buscador en tiempo real de asociados
    const searchInput = document.getElementById('matrixSearchInput');
    searchInput.addEventListener('input', function() {
        const query = this.value.toLowerCase().trim();
        document.querySelectorAll('.socio-row').forEach(row => {
            const searchData = row.dataset.search || '';
            if (searchData.includes(query)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });

    // 3. Guardar cambios por fila de asociado
    document.querySelectorAll('.btn-save-row').forEach(btn => {
        btn.addEventListener('click', function() {
            const row = this.closest('.socio-row');
            const socioId = this.dataset.id;
            const originalBtnText = this.innerHTML;

            const matrixData = {};
            row.querySelectorAll('.year-select').forEach(sel => {
                const y = sel.dataset.year;
                matrixData[y] = parseInt(sel.value);
            });

            this.disabled = true;
            this.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Guardando...';

            const formData = new FormData();
            formData.append('action', 'save_socio_matrix');
            formData.append('id_socio', socioId);
            formData.append('matrix_json', JSON.stringify(matrixData));

            fetch('../Controller/matriz_pagos.controller.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                this.disabled = false;
                this.innerHTML = originalBtnText;

                if (data.status === 'success') {
                    showToast('✅ ' + data.message);
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(err => {
                this.disabled = false;
                this.innerHTML = originalBtnText;
                console.error(err);
                alert('Error al conectar con el servidor.');
            });
        });
    });

    // 4. Guardar todos los asociados a la vez
    const btnSaveAll = document.getElementById('btnSaveAllMatrix');
    btnSaveAll.addEventListener('click', function() {
        if (!confirm('¿Estás seguro de que deseas guardar los cambios para TODOS los asociados mostrados en la matriz?')) {
            return;
        }

        const originalText = this.innerHTML;
        this.disabled = true;
        this.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Guardando Todo...';

        const globalMatrix = {};
        document.querySelectorAll('.socio-row').forEach(row => {
            const socioId = row.dataset.id;
            globalMatrix[socioId] = {};
            row.querySelectorAll('.year-select').forEach(sel => {
                const y = sel.dataset.year;
                globalMatrix[socioId][y] = parseInt(sel.value);
            });
        });

        const formData = new FormData();
        formData.append('action', 'save_all_matrix');
        formData.append('all_data_json', JSON.stringify(globalMatrix));

        fetch('../Controller/matriz_pagos.controller.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            this.disabled = false;
            this.innerHTML = originalText;

            if (data.status === 'success') {
                showToast('✅ ' + data.message);
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(err => {
            this.disabled = false;
            this.innerHTML = originalText;
            console.error(err);
            alert('Error al conectar con el servidor.');
        });
    });
});
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
