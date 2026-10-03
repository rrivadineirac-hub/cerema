<?php
// Controller/matriz_pagos.controller.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../Config/database.php';
require_once __DIR__ . '/../Model/mmensualidad.php';
require_once __DIR__ . '/../Model/msocio.php';

$database = new Database();
$db = $database->getConnection();

$mensualidadModel = new MensualidadModel($db);
$socioModel = new SocioModel($db);

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
        $matrix_json = $_POST['matrix_json'] ?? '';
        $years_data = json_decode($matrix_json, true);

        if (!$id_socio || !is_array($years_data)) {
            echo json_encode(["status" => "error", "message" => "Datos inválidos."]);
            exit();
        }

        $socio = $socioModel->getById($id_socio);
        if (!$socio) {
            echo json_encode(["status" => "error", "message" => "Socio no encontrado."]);
            exit();
        }

        $count_added = 0;
        $count_deleted = 0;

        foreach ($years_data as $anio => $target_m_idx) {
            $anio = intval($anio);
            $target_m_idx = intval($target_m_idx);
            if ($anio < 2018 || $anio > 2029) continue;

            $monto_m = ($anio <= 2024) ? 30.00 : 50.00;
            $start_m_idx = ($anio == 2018) ? 5 : 1;

            if ($anio == 2018 && $target_m_idx > 0 && $target_m_idx < 5) {
                $target_m_idx = 5;
            }

            for ($m = $start_m_idx; $m <= 12; $m++) {
                $nombre_mes = $meses_nombres[$m];
                $exists = $mensualidadModel->checkExists($id_socio, $numero_accion, $nombre_mes, $anio);

                if ($m <= $target_m_idx) {
                    if (!$exists) {
                        $data_ins = [
                            'id_socio' => $id_socio,
                            'numero_accion' => $numero_accion,
                            'numero_recibo' => 'SYS-MATRIZ',
                            'mes' => $nombre_mes,
                            'anio' => $anio,
                            'monto' => $monto_m,
                            'fecha_pago' => date('Y-m-d H:i:s'),
                            'estado' => 'Pagado'
                        ];
                        if ($mensualidadModel->create($data_ins)) {
                            $count_added++;
                        }
                    }
                } else {
                    if ($exists) {
                        if ($mensualidadModel->deleteBySocioAccionMesAnio($id_socio, $numero_accion, $nombre_mes, $anio)) {
                            $count_deleted++;
                        }
                    }
                }
            }
        }

        $label_acc = ($socio['acciones'] > 1) ? " (Acción N°{$numero_accion})" : "";
        echo json_encode([
            "status" => "success",
            "message" => "Pagos actualizados para {$socio['nombre']} {$socio['ap_paterno']}{$label_acc}. Agregados: $count_added, Eliminados: $count_deleted."
        ]);
        exit();

    } elseif ($action === 'save_all_matrix') {
        $all_data_json = $_POST['all_data_json'] ?? '';
        $all_data = json_decode($all_data_json, true);

        if (!is_array($all_data)) {
            echo json_encode(["status" => "error", "message" => "Formato de datos global inválido."]);
            exit();
        }

        $total_added = 0;
        $total_deleted = 0;

        foreach ($all_data as $key => $years_data) {
            // Key is "idSocio_numeroAccion" (e.g. "148_1")
            $parts = explode('_', $key);
            $id_socio = intval($parts[0] ?? 0);
            $numero_accion = intval($parts[1] ?? 1);

            if (!$id_socio || !is_array($years_data)) continue;

            foreach ($years_data as $anio => $target_m_idx) {
                $anio = intval($anio);
                $target_m_idx = intval($target_m_idx);
                if ($anio < 2018 || $anio > 2029) continue;

                $monto_m = ($anio <= 2024) ? 30.00 : 50.00;
                $start_m_idx = ($anio == 2018) ? 5 : 1;

                if ($anio == 2018 && $target_m_idx > 0 && $target_m_idx < 5) {
                    $target_m_idx = 5;
                }

                for ($m = $start_m_idx; $m <= 12; $m++) {
                    $nombre_mes = $meses_nombres[$m];
                    $exists = $mensualidadModel->checkExists($id_socio, $numero_accion, $nombre_mes, $anio);

                    if ($m <= $target_m_idx) {
                        if (!$exists) {
                            $data_ins = [
                                'id_socio' => $id_socio,
                                'numero_accion' => $numero_accion,
                                'numero_recibo' => 'SYS-MATRIZ',
                                'mes' => $nombre_mes,
                                'anio' => $anio,
                                'monto' => $monto_m,
                                'fecha_pago' => date('Y-m-d H:i:s'),
                                'estado' => 'Pagado'
                            ];
                            if ($mensualidadModel->create($data_ins)) {
                                $total_added++;
                            }
                        }
                    } else {
                        if ($exists) {
                            if ($mensualidadModel->deleteBySocioAccionMesAnio($id_socio, $numero_accion, $nombre_mes, $anio)) {
                                $total_deleted++;
                            }
                        }
                    }
                }
            }
        }

        echo json_encode([
            "status" => "success",
            "message" => "Actualización global completada. Se registraron $total_added pagos y se eliminaron $total_deleted."
        ]);
        exit();
    }
}

// GET Request: Load View
$stmtSocios = $socioModel->getAll();
$socios = [];
while ($s = $stmtSocios->fetch(PDO::FETCH_ASSOC)) {
    $socios[] = $s;
}

$queryPayments = "SELECT id_socio, COALESCE(numero_accion, 1) as numero_accion, anio, mes FROM mensualidad WHERE (estado = 'Pagado' OR estado IS NULL OR estado = '')";
$stmtPayments = $db->prepare($queryPayments);
$stmtPayments->execute();

$paidMap = [];
while ($row = $stmtPayments->fetch(PDO::FETCH_ASSOC)) {
    $s_id = intval($row['id_socio']);
    $n_acc = intval($row['numero_accion']);
    if ($n_acc <= 0) $n_acc = 1;
    $anio = intval($row['anio']);
    $mes_nombre = $row['mes'];
    $mes_idx = $meses_map[$mes_nombre] ?? 0;

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

// Generar filas expandidas por cada acción de cada asociado
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

require_once __DIR__ . '/../View/vmatriz_pagos.php';
