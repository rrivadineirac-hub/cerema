<?php
// Controller/matriz_aportes.controller.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../Config/database.php';
require_once __DIR__ . '/../Model/maporte.php';
require_once __DIR__ . '/../Model/msocio.php';
require_once __DIR__ . '/../Model/mcategorias_ingreso.php';

$database = new Database();
$db = $database->getConnection();

$aporteModel = new AporteModel($db);
$socioModel = new SocioModel($db);
$catModel = new CategoriasIngresoModel($db);

$action = $_REQUEST['action'] ?? '';

$meses_nombres = [
    1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
    5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
    9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
];

$meses_map = array_flip($meses_nombres);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    if ($action === 'save_socio_matrix') {
        $id_socio = intval($_POST['id_socio'] ?? 0);
        $numero_accion = intval($_POST['numero_accion'] ?? 1);
        $motivo = trim($_POST['motivo'] ?? '');
        $matrix_json = $_POST['matrix_json'] ?? '';
        $years_data = json_decode($matrix_json, true);

        if (!$id_socio || empty($motivo) || !is_array($years_data)) {
            echo json_encode(["status" => "error", "message" => "Datos inválidos."]);
            exit();
        }

        $socio = $socioModel->getById($id_socio);
        if (!$socio) {
            echo json_encode(["status" => "error", "message" => "Socio no encontrado."]);
            exit();
        }

        // Obtener la categoría para conocer su rango y monto mensual
        $query_cat = "SELECT * FROM categorias_ingreso WHERE nombre = ? LIMIT 1";
        $stmt_cat = $db->prepare($query_cat);
        $stmt_cat->execute([$motivo]);
        $cat_info = $stmt_cat->fetch(PDO::FETCH_ASSOC);

        $cat_monto_m = floatval($cat_info['monto_mensual'] ?? $cat_info['monto_sugerido'] ?? 50.00);
        $cat_anio_inicio = intval($cat_info['anio_inicio'] ?? 2018);
        $cat_mes_inicio_idx = $meses_map[$cat_info['mes_inicio'] ?? 'Enero'] ?? 1;
        $cat_anio_fin = intval($cat_info['anio_fin'] ?? 2029);
        $cat_mes_fin_idx = $meses_map[$cat_info['mes_fin'] ?? 'Diciembre'] ?? 12;

        $count_added = 0;
        $count_deleted = 0;

        foreach ($years_data as $anio => $target_m_idx) {
            $anio = intval($anio);
            $target_m_idx = intval($target_m_idx);
            if ($anio < 2018 || $anio > 2029) continue;

            // Determinar rango válido de meses para este año
            $start_m = 1;
            $end_m = 12;
            if ($anio == $cat_anio_inicio) {
                $start_m = $cat_mes_inicio_idx;
            }
            if ($anio == $cat_anio_fin) {
                $end_m = $cat_mes_fin_idx;
            }

            for ($m = $start_m; $m <= $end_m; $m++) {
                $exists = $aporteModel->checkExistsMonth($id_socio, $numero_accion, $motivo, $anio, $m);

                if ($m <= $target_m_idx) {
                    if (!$exists) {
                        $str_m = str_pad($m, 2, '0', STR_PAD_LEFT);
                        $fecha_app = "{$anio}-{$str_m}-01";
                        $data_ins = [
                            'id_socio' => $id_socio,
                            'numero_accion' => $numero_accion,
                            'motivo' => $motivo,
                            'monto' => $cat_monto_m,
                            'fecha_aporte' => $fecha_app,
                            'numero_recibo' => 'SYS-MATRIZ'
                        ];
                        if ($aporteModel->create($data_ins)) {
                            $count_added++;
                        }
                    }
                } else {
                    if ($exists) {
                        if ($aporteModel->deleteBySocioAccionMotivoMesAnio($id_socio, $numero_accion, $motivo, $anio, $m)) {
                            $count_deleted++;
                        }
                    }
                }
            }
        }

        $label_acc = ($socio['acciones'] > 1) ? " (Acción N°{$numero_accion})" : "";
        echo json_encode([
            "status" => "success",
            "message" => "Aportes ($motivo) actualizados para {$socio['nombre']} {$socio['ap_paterno']}{$label_acc}. Agregados: $count_added, Eliminados: $count_deleted."
        ]);
        exit();

    } elseif ($action === 'save_all_matrix') {
        $motivo = trim($_POST['motivo'] ?? '');
        $all_data_json = $_POST['all_data_json'] ?? '';
        $all_data = json_decode($all_data_json, true);

        if (empty($motivo) || !is_array($all_data)) {
            echo json_encode(["status" => "error", "message" => "Formato de datos global inválido."]);
            exit();
        }

        $query_cat = "SELECT * FROM categorias_ingreso WHERE nombre = ? LIMIT 1";
        $stmt_cat = $db->prepare($query_cat);
        $stmt_cat->execute([$motivo]);
        $cat_info = $stmt_cat->fetch(PDO::FETCH_ASSOC);

        $cat_monto_m = floatval($cat_info['monto_mensual'] ?? $cat_info['monto_sugerido'] ?? 50.00);
        $cat_anio_inicio = intval($cat_info['anio_inicio'] ?? 2018);
        $cat_mes_inicio_idx = $meses_map[$cat_info['mes_inicio'] ?? 'Enero'] ?? 1;
        $cat_anio_fin = intval($cat_info['anio_fin'] ?? 2029);
        $cat_mes_fin_idx = $meses_map[$cat_info['mes_fin'] ?? 'Diciembre'] ?? 12;

        $total_added = 0;
        $total_deleted = 0;

        foreach ($all_data as $key => $years_data) {
            $parts = explode('_', $key);
            $id_socio = intval($parts[0] ?? 0);
            $numero_accion = intval($parts[1] ?? 1);

            if (!$id_socio || !is_array($years_data)) continue;

            foreach ($years_data as $anio => $target_m_idx) {
                $anio = intval($anio);
                $target_m_idx = intval($target_m_idx);
                if ($anio < 2018 || $anio > 2029) continue;

                $start_m = 1;
                $end_m = 12;
                if ($anio == $cat_anio_inicio) {
                    $start_m = $cat_mes_inicio_idx;
                }
                if ($anio == $cat_anio_fin) {
                    $end_m = $cat_mes_fin_idx;
                }

                for ($m = $start_m; $m <= $end_m; $m++) {
                    $exists = $aporteModel->checkExistsMonth($id_socio, $numero_accion, $motivo, $anio, $m);

                    if ($m <= $target_m_idx) {
                        if (!$exists) {
                            $str_m = str_pad($m, 2, '0', STR_PAD_LEFT);
                            $fecha_app = "{$anio}-{$str_m}-01";
                            $data_ins = [
                                'id_socio' => $id_socio,
                                'numero_accion' => $numero_accion,
                                'motivo' => $motivo,
                                'monto' => $cat_monto_m,
                                'fecha_aporte' => $fecha_app,
                                'numero_recibo' => 'SYS-MATRIZ'
                            ];
                            if ($aporteModel->create($data_ins)) {
                                $total_added++;
                            }
                        }
                    } else {
                        if ($exists) {
                            if ($aporteModel->deleteBySocioAccionMotivoMesAnio($id_socio, $numero_accion, $motivo, $anio, $m)) {
                                $total_deleted++;
                            }
                        }
                    }
                }
            }
        }

        echo json_encode([
            "status" => "success",
            "message" => "Actualización global completada para ($motivo). Agregados: $total_added, Eliminados: $total_deleted."
        ]);
        exit();
    }
}

// GET Request: Render View
// 1. Obtener todas las categorías de aporte extraordinario (requiere_socio = 1)
$stmtCatAll = $catModel->getAll();
$categorias_aporte = [];
while ($c = $stmtCatAll->fetch(PDO::FETCH_ASSOC)) {
    if ($c['requiere_socio'] == 1 || $c['requiere_socio'] == '1') {
        $categorias_aporte[] = $c;
    }
}

// Determinar motivo seleccionado por defecto
$selected_cat_id = intval($_GET['cat_id'] ?? 0);
$selected_cat = null;

if ($selected_cat_id > 0) {
    foreach ($categorias_aporte as $c) {
        if ($c['id_cat_ingreso'] == $selected_cat_id) {
            $selected_cat = $c;
            break;
        }
    }
}

if (!$selected_cat && !empty($categorias_aporte)) {
    $selected_cat = $categorias_aporte[0];
}

$motivo_actual = $selected_cat ? $selected_cat['nombre'] : 'APORTE EXTRAORDINARIO';
$monto_mensual_cat = floatval($selected_cat['monto_mensual'] ?? $selected_cat['monto_sugerido'] ?? 50.00);

$anio_inicio_cat = !empty($selected_cat['anio_inicio']) ? intval($selected_cat['anio_inicio']) : 2018;
$mes_inicio_cat = !empty($selected_cat['mes_inicio']) ? $selected_cat['mes_inicio'] : 'Enero';
$mes_inicio_cat_idx = $meses_map[$mes_inicio_cat] ?? 1;

$anio_fin_cat = !empty($selected_cat['anio_fin']) ? intval($selected_cat['anio_fin']) : 2029;
$mes_fin_cat = !empty($selected_cat['mes_fin']) ? $selected_cat['mes_fin'] : 'Diciembre';
$mes_fin_cat_idx = $meses_map[$mes_fin_cat] ?? 12;

$anos_rango = range($anio_inicio_cat, $anio_fin_cat);

// 2. Obtener lista de asociados
$stmtSocios = $socioModel->getAll();
$socios = [];
while ($s = $stmtSocios->fetch(PDO::FETCH_ASSOC)) {
    $socios[] = $s;
}

// 3. Obtener todos los pagos registrados para este motivo en aporte_extraordinario
$queryPayments = "SELECT id_socio, COALESCE(numero_accion, 1) as numero_accion, fecha_aporte 
                  FROM aporte_extraordinario 
                  WHERE motivo = :motivo";
$stmtPayments = $db->prepare($queryPayments);
$stmtPayments->execute([':motivo' => $motivo_actual]);

$paidMap = [];
while ($row = $stmtPayments->fetch(PDO::FETCH_ASSOC)) {
    $s_id = intval($row['id_socio']);
    $n_acc = intval($row['numero_accion']);
    if ($n_acc <= 0) $n_acc = 1;

    $dt = strtotime($row['fecha_aporte']);
    $anio = intval(date('Y', $dt));
    $mes_idx = intval(date('n', $dt));

    if (!isset($paidMap[$s_id])) {
        $paidMap[$s_id] = [];
    }
    if (!isset($paidMap[$s_id][$n_acc])) {
        $paidMap[$s_id][$n_acc] = [];
    }
    if (!isset($paidMap[$s_id][$n_acc][$anio]) || $mes_idx > $paidMap[$s_id][$n_acc][$anio]) {
        $paidMap[$s_id][$n_acc][$anio] = $mes_idx;
    }
}

// 4. Generar filas expandidas por cada acción de cada asociado
$rows_matrix = [];
foreach ($socios as $soc) {
    $s_id = intval($soc['id_socio']);
    $max_acciones = max(1, intval($soc['acciones'] ?? 1));
    $nombre_base = trim($soc['ap_paterno'] . ' ' . $soc['ap_materno'] . ' ' . $soc['nombre']);

    for ($acc = 1; $acc <= $max_acciones; $acc++) {
        $display_name = $nombre_base;
        if ($max_acciones > 1) {
            $display_name .= " N°" . $acc;
        }

        $rows_matrix[] = [
            'id_socio' => $s_id,
            'numero_accion' => $acc,
            'display_name' => $display_name,
            'search_text' => strtolower($display_name),
            'paid_years' => $paidMap[$s_id][$acc] ?? []
        ];
    }
}

require_once __DIR__ . '/../View/vmatriz_aportes.php';
