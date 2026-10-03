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

        $accion = 1;
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
                $exists = $mensualidadModel->checkExists($id_socio, $accion, $nombre_mes, $anio);

                if ($m <= $target_m_idx) {
                    if (!$exists) {
                        $data_ins = [
                            'id_socio' => $id_socio,
                            'numero_accion' => $accion,
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
                        if ($mensualidadModel->deleteBySocioAccionMesAnio($id_socio, $accion, $nombre_mes, $anio)) {
                            $count_deleted++;
                        }
                    }
                }
            }
        }

        echo json_encode([
            "status" => "success",
            "message" => "Pagos actualizados para {$socio['nombre']} {$socio['ap_paterno']}. Agregados: $count_added, Eliminados: $count_deleted."
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

        foreach ($all_data as $id_socio => $years_data) {
            $id_socio = intval($id_socio);
            if (!$id_socio || !is_array($years_data)) continue;

            $accion = 1;
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
                    $exists = $mensualidadModel->checkExists($id_socio, $accion, $nombre_mes, $anio);

                    if ($m <= $target_m_idx) {
                        if (!$exists) {
                            $data_ins = [
                                'id_socio' => $id_socio,
                                'numero_accion' => $accion,
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
                            if ($mensualidadModel->deleteBySocioAccionMesAnio($id_socio, $accion, $nombre_mes, $anio)) {
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

$queryPayments = "SELECT id_socio, anio, mes FROM mensualidad WHERE (estado = 'Pagado' OR estado IS NULL OR estado = '')";
$stmtPayments = $db->prepare($queryPayments);
$stmtPayments->execute();

$paidMap = [];
while ($row = $stmtPayments->fetch(PDO::FETCH_ASSOC)) {
    $s_id = intval($row['id_socio']);
    $anio = intval($row['anio']);
    $mes_nombre = $row['mes'];
    $mes_idx = $meses_map[$mes_nombre] ?? 0;

    if (!isset($paidMap[$s_id])) {
        $paidMap[$s_id] = [];
    }
    if (!isset($paidMap[$s_id][$anio]) || $mes_idx > $paidMap[$s_id][$anio]) {
        $paidMap[$s_id][$anio] = $mes_idx;
    }
}

require_once __DIR__ . '/../View/vmatriz_pagos.php';
