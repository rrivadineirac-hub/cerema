<?php
// View/vmatriz_aportes.php
require_once __DIR__ . '/header.php';

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
                <i class="fa-solid fa-hand-holding-dollar" style="color: #0284c7;"></i> Matriz Rápida de Aportes Extraordinarios
            </h1>
            <p style="margin: 4px 0 0 0; color: #64748B; font-size: 14px;">
                Selecciona el motivo del aporte y el <b>último mes pagado</b> para cada año según la configuración de la gestión.
            </p>
        </div>
        <div style="display: flex; gap: 10px; align-items: center;">
            <a href="../Controller/aporte.controller.php" class="btn" style="background: #E2E8F0; color: #334155; padding: 10px 18px; border-radius: 8px; font-weight: 600; text-decoration: none; display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-arrow-left"></i> Aportes Extraordinarios
            </a>
            <button id="btnSaveAllMatrix" class="btn" style="background: #10B981; color: white; border: none; padding: 10px 20px; border-radius: 8px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 8px; box-shadow: 0 2px 8px rgba(16, 185, 129, 0.3);">
                <i class="fa-solid fa-save"></i> Guardar Todos los Cambios
            </button>
        </div>
    </div>

    <!-- Filtros: Selección de Motivo y Búsqueda -->
    <div style="background: white; border-radius: 12px; padding: 15px 20px; margin-bottom: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
        <div style="display: flex; gap: 15px; align-items: center; flex-wrap: wrap; flex: 1;">
            <div style="min-width: 280px; max-width: 450px;">
                <label style="font-size: 12px; font-weight: 700; color: #334155; margin-bottom: 4px; display: block;">Motivo del Aporte Extraordinario:</label>
                <select id="motivoSelect" onchange="window.location.href='../Controller/matriz_aportes.controller.php?cat_id='+this.value" style="width: 100%; padding: 10px 12px; border-radius: 8px; border: 1.5px solid #0284c7; background: #f0f9ff; color: #0369a1; font-weight: 700; font-size: 14px; outline: none; cursor: pointer;">
                    <?php foreach ($categorias_aporte as $cat): ?>
                        <?php 
                        $cat_periodo = "";
                        if (!empty($cat['anio_inicio'])) {
                            $cat_periodo = " (" . $cat['anio_inicio'] . " " . ($cat['mes_inicio'] ?? '') . " - " . ($cat['anio_fin'] ?? '') . " " . ($cat['mes_fin'] ?? '') . ")";
                        }
                        ?>
                        <option value="<?php echo $cat['id_cat_ingreso']; ?>" <?php echo ($selected_cat && $selected_cat['id_cat_ingreso'] == $cat['id_cat_ingreso']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['nombre']) . $cat_periodo . " - Bs " . number_format($cat['monto_mensual'] ?? $cat['monto_sugerido'] ?? 0, 2); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="flex: 1; min-width: 220px; position: relative; margin-top: 18px;">
                <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #94A3B8;"></i>
                <input type="text" id="matrixSearchInput" placeholder="Buscar por nombre de asociado..." style="width: 100%; padding: 10px 14px 10px 40px; border: 1px solid #CBD5E1; border-radius: 8px; font-size: 14px; outline: none; transition: border-color 0.2s;">
            </div>
        </div>

        <div style="display: flex; gap: 15px; align-items: center; font-size: 13px; color: #475569; margin-top: 18px;">
            <div style="display: flex; align-items: center; gap: 6px;">
                <span style="display: inline-block; width: 12px; height: 12px; background: #DCFCE7; border: 1px solid #86EFAC; border-radius: 3px;"></span> Completo
            </div>
            <div style="display: flex; align-items: center; gap: 6px;">
                <span style="display: inline-block; width: 12px; height: 12px; background: #E0F2FE; border: 1px solid #7DD3FC; border-radius: 3px;"></span> Parcial
            </div>
            <div style="display: flex; align-items: center; gap: 6px;">
                <span style="display: inline-block; width: 12px; height: 12px; background: #F1F5F9; border: 1px solid #CBD5E1; border-radius: 3px;"></span> Sin Pago
            </div>
            <div style="font-weight: 700; color: #1E293B; margin-left: 10px;">
                Total Registros: <span style="color: #0284c7;"><?php echo count($rows_matrix); ?></span>
            </div>
        </div>
    </div>

    <!-- Tabla Matriz de Aportes Extraordinarios -->
    <div style="background: white; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); overflow: hidden;">
        <div style="overflow-x: auto; max-height: 72vh;">
            <table id="matrixTable" style="width: 100%; border-collapse: separate; border-spacing: 0; font-size: 13px; text-align: left;">
                <thead>
                    <tr style="background: #1E293B; color: white; position: sticky; top: 0; z-index: 10;">
                        <th style="padding: 12px 10px; width: 45px; text-align: center; border-bottom: 2px solid #334155; position: sticky; left: 0; background: #1E293B; z-index: 12;">#</th>
                        <th style="padding: 12px 15px; min-width: 250px; border-bottom: 2px solid #334155; position: sticky; left: 45px; background: #1E293B; z-index: 12; box-shadow: 4px 0 6px -2px rgba(0,0,0,0.2);">Asociado</th>
                        <?php foreach ($anos_rango as $y): ?>
                            <th style="padding: 10px 8px; text-align: center; min-width: 130px; border-bottom: 2px solid #334155; border-left: 1px solid #334155;">
                                <div><?php echo $y; ?></div>
                                <div style="font-size: 9.5px; font-weight: normal; color: #94A3B8; opacity: 0.85;">
                                    <?php echo number_format($monto_mensual_cat, 2) . ' Bs/mes'; ?>
                                </div>
                            </th>
                        <?php endforeach; ?>
                        <th style="padding: 12px 15px; text-align: center; min-width: 100px; border-bottom: 2px solid #334155; position: sticky; right: 0; background: #1E293B; z-index: 12; box-shadow: -4px 0 6px -2px rgba(0,0,0,0.2);">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $index = 1;
                    foreach ($rows_matrix as $r): 
                        $s_id = $r['id_socio'];
                        $n_acc = $r['numero_accion'];
                        $display_name = $r['display_name'];
                        $search_text = $r['search_text'];
                        $paid_years = $r['paid_years'];
                    ?>
                        <tr class="socio-row" data-id="<?php echo $s_id; ?>" data-accion="<?php echo $n_acc; ?>" data-search="<?php echo htmlspecialchars($search_text); ?>" style="border-bottom: 1px solid #E2E8F0; transition: background 0.15s;">
                            <td style="padding: 10px; text-align: center; color: #64748B; font-weight: 600; position: sticky; left: 0; background: white; z-index: 5; border-bottom: 1px solid #E2E8F0; border-right: 1px solid #F1F5F9;">
                                <?php echo $index++; ?>
                            </td>
                            <td style="padding: 10px 15px; font-weight: 600; color: #1E293B; position: sticky; left: 45px; background: white; z-index: 5; border-bottom: 1px solid #E2E8F0; box-shadow: 4px 0 6px -2px rgba(0,0,0,0.06);">
                                <?php echo htmlspecialchars($display_name); ?>
                            </td>

                            <?php foreach ($anos_rango as $y): 
                                $selected_m_idx = $paid_years[$y] ?? 0;
                                
                                // Rango de meses válidos para este año específico
                                $start_m = 1;
                                $end_m = 12;
                                if ($y == $anio_inicio_cat) {
                                    $start_m = $mes_inicio_cat_idx;
                                }
                                if ($y == $anio_fin_cat) {
                                    $end_m = $mes_fin_cat_idx;
                                }

                                $is_complete = ($selected_m_idx > 0 && $selected_m_idx >= $end_m);

                                $bgColor = "#F1F5F9";
                                $textColor = "#64748B";
                                $borderColor = "#CBD5E1";
                                if ($is_complete) {
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
                                    <select class="year-select" data-year="<?php echo $y; ?>" data-end-month="<?php echo $end_m; ?>" style="width: 100%; padding: 6px 8px; border-radius: 6px; border: 1px solid <?php echo $borderColor; ?>; background-color: <?php echo $bgColor; ?>; color: <?php echo $textColor; ?>; font-size: 12px; font-weight: 600; cursor: pointer; outline: none; transition: all 0.2s;">
                                        <option value="0" <?php echo ($selected_m_idx == 0) ? 'selected' : ''; ?>>Ninguno</option>
                                        <?php for ($m = $start_m; $m <= $end_m; $m++): ?>
                                            <option value="<?php echo $m; ?>" <?php echo ($selected_m_idx == $m) ? 'selected' : ''; ?>>
                                                Hasta <?php echo $meses_nombres_view[$m]; ?>
                                            </option>
                                        <?php endfor; ?>
                                    </select>
                                </td>
                            <?php endforeach; ?>

                            <td style="padding: 8px 10px; text-align: center; position: sticky; right: 0; background: white; z-index: 5; border-bottom: 1px solid #E2E8F0; box-shadow: -4px 0 6px -2px rgba(0,0,0,0.06);">
                                <button class="btn-save-row" data-id="<?php echo $s_id; ?>" data-accion="<?php echo $n_acc; ?>" style="background: #0284c7; color: white; border: none; padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 4px; transition: background 0.2s;">
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
    const motivoActual = <?php echo json_encode($motivo_actual); ?>;

    function updateSelectStyle(select) {
        const val = parseInt(select.value);
        const endMonth = parseInt(select.dataset.endMonth || 12);

        if (val > 0 && val >= endMonth) {
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

    function saveRow(row) {
        if (!row) return;
        const btn = row.querySelector('.btn-save-row');
        const socioId = row.dataset.id;
        const numeroAccion = row.dataset.accion;
        const originalBtnText = btn ? btn.innerHTML : '';

        const matrixData = {};
        row.querySelectorAll('.year-select').forEach(sel => {
            const y = sel.dataset.year;
            matrixData[y] = parseInt(sel.value);
        });

        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>...';
        }

        const formData = new FormData();
        formData.append('action', 'save_socio_matrix');
        formData.append('id_socio', socioId);
        formData.append('numero_accion', numeroAccion);
        formData.append('motivo', motivoActual);
        formData.append('matrix_json', JSON.stringify(matrixData));

        return fetch('../Controller/matriz_aportes.controller.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-check"></i> Guardado';
                setTimeout(() => { btn.innerHTML = originalBtnText || '<i class="fa-solid fa-floppy-disk"></i> Guardar'; }, 2000);
            }

            if (data.status === 'success') {
                showToast('✅ ' + data.message);
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(err => {
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = originalBtnText;
            }
            console.error(err);
            alert('Error al conectar con el servidor.');
        });
    }

    document.querySelectorAll('.year-select').forEach(select => {
        select.addEventListener('change', function() {
            updateSelectStyle(this);
            saveRow(this.closest('.socio-row'));
        });
    });

    // Buscador en tiempo real
    const searchInput = document.getElementById('matrixSearchInput');
    if (searchInput) {
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
    }

    // Guardar cambios por fila al hacer click en el botón
    document.querySelectorAll('.btn-save-row').forEach(btn => {
        btn.addEventListener('click', function() {
            const row = this.closest('.socio-row');
            saveRow(row);
        });
    });

    // Guardar todos los asociados a la vez
    const btnSaveAll = document.getElementById('btnSaveAllMatrix');
    if (btnSaveAll) {
        btnSaveAll.addEventListener('click', function() {
            if (!confirm(`¿Estás seguro de que deseas guardar los cambios para TODOS los registros de (${motivoActual})?`)) {
                return;
            }

            const originalText = this.innerHTML;
            this.disabled = true;
            this.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Guardando Todo...';

            const globalMatrix = {};
            document.querySelectorAll('.socio-row').forEach(row => {
                const socioId = row.dataset.id;
                const numeroAccion = row.dataset.accion;
                const key = socioId + '_' + numeroAccion;

                globalMatrix[key] = {};
                row.querySelectorAll('.year-select').forEach(sel => {
                    const y = sel.dataset.year;
                    globalMatrix[key][y] = parseInt(sel.value);
                });
            });

            const formData = new FormData();
            formData.append('action', 'save_all_matrix');
            formData.append('motivo', motivoActual);
            formData.append('all_data_json', JSON.stringify(globalMatrix));

            fetch('../Controller/matriz_aportes.controller.php', {
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
    }
});
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
