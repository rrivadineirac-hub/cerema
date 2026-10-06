<?php
// Controller/cuota_inicial.controller.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../Config/permissions.php';
require_permission('cuota_inicial');
require_once __DIR__ . '/../Config/database.php';
require_once __DIR__ . '/../Model/mcuota_inicial.php';
require_once __DIR__ . '/../Model/msocio.php';

$database = new Database();
$db = $database->getConnection();

$cuotaModel = new CuotaInicialModel($db);
$socioModel = new SocioModel($db);

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    try {
        if ($action == 'save') {
            $id_cuota = $_POST['id_cuota'] ?? 0;
            $id_socio = $_POST['id_socio'] ?? '';
            $numero_accion = (int)($_POST['numero_accion'] ?? 1);
            $numero_recibo = trim($_POST['numero_recibo'] ?? '');
            $concepto = trim($_POST['concepto'] ?? 'Pago Cuota Inicial');
            $monto = (float)($_POST['monto'] ?? 0);
            $fecha_pago = trim($_POST['fecha_pago'] ?? date('Y-m-d H:i:s'));
            $observaciones = trim($_POST['observaciones'] ?? '');

            if (empty($id_socio)) {
                echo json_encode(["status" => "error", "message" => "Debe seleccionar un asociado."]);
                exit();
            }

            if ($monto <= 0) {
                echo json_encode(["status" => "error", "message" => "El monto pagado debe ser mayor a 0."]);
                exit();
            }

            // Validar que el monto no supere el saldo pendiente disponible
            $resumenS = $cuotaModel->getResumenSocio($id_socio);
            $saldoDisponible = (float)($resumenS['saldo_pendiente'] ?? 0);
            if (!empty($id_cuota)) {
                $pagoExistente = $cuotaModel->getById($id_cuota);
                if ($pagoExistente) {
                    $saldoDisponible += (float)($pagoExistente['monto'] ?? 0);
                }
            }
            if ($resumenS['meta_total'] > 0 && $monto > ($saldoDisponible + 0.01)) {
                echo json_encode(["status" => "error", "message" => "El monto acreditado (Bs. " . number_format($monto, 2) . ") no puede ser mayor al saldo pendiente disponible (Bs. " . number_format($saldoDisponible, 2) . ")." ]);
                exit();
            }

            // Validar que el número de recibo no esté duplicado
            if (!empty($numero_recibo)) {
                $query_chk = "SELECT id_cuota FROM cuota_inicial_pagos WHERE LOWER(TRIM(numero_recibo)) = LOWER(TRIM(?)) AND id_cuota != ? LIMIT 1";
                $stmt_chk = $db->prepare($query_chk);
                $stmt_chk->execute([$numero_recibo, $id_cuota]);
                if ($stmt_chk->rowCount() > 0) {
                    echo json_encode(["status" => "error", "message" => "El número de recibo '$numero_recibo' ya fue registrado."]);
                    exit();
                }
            }

            $data = [
                'id_socio' => $id_socio,
                'numero_accion' => $numero_accion,
                'numero_recibo' => $numero_recibo,
                'concepto' => $concepto,
                'monto' => $monto,
                'fecha_pago' => $fecha_pago,
                'observaciones' => $observaciones,
                'estado' => 'Pagado'
            ];

            if (!empty($id_cuota)) {
                $data['id_cuota'] = $id_cuota;
                if ($cuotaModel->update($data)) {
                    echo json_encode(["status" => "success", "message" => "Pago de Cuota Inicial actualizado correctamente."]);
                } else {
                    echo json_encode(["status" => "error", "message" => "No se pudo actualizar el pago."]);
                }
            } else {
                if ($cuotaModel->create($data)) {
                    echo json_encode(["status" => "success", "message" => "Pago de Cuota Inicial registrado correctamente."]);
                } else {
                    echo json_encode(["status" => "error", "message" => "No se pudo registrar el pago."]);
                }
            }
            exit();

        } elseif ($action == 'delete') {
            $id_cuota = $_POST['id_cuota'] ?? 0;
            if ($id_cuota > 0 && $cuotaModel->delete($id_cuota)) {
                echo json_encode(["status" => "success", "message" => "Pago de Cuota Inicial eliminado correctamente."]);
            } else {
                echo json_encode(["status" => "error", "message" => "No se pudo eliminar el registro."]);
            }
            exit();

        } elseif ($action == 'check_recibo') {
            $numero_recibo = trim($_REQUEST['numero_recibo'] ?? '');
            $id_cuota = (int)($_REQUEST['id_cuota'] ?? 0);

            if (!empty($numero_recibo)) {
                $query_chk = "SELECT id_cuota FROM cuota_inicial_pagos WHERE LOWER(TRIM(numero_recibo)) = LOWER(TRIM(?)) AND id_cuota != ? LIMIT 1";
                $stmt_chk = $db->prepare($query_chk);
                $stmt_chk->execute([$numero_recibo, $id_cuota]);
                if ($stmt_chk->rowCount() > 0) {
                    echo json_encode(["status" => "exists", "message" => "⚠️ Este número de recibo ya fue registrado."]);
                    exit();
                }
            }
            echo json_encode(["status" => "available"]);
            exit();
        }
    } catch (Exception $e) {
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
        exit();
    }
}

// Peticiones GET AJAX
if ($action == 'get' || $action == 'get_cuota') {
    header('Content-Type: application/json');
    $id_cuota = $_GET['id_cuota'] ?? 0;
    $data = $cuotaModel->getById($id_cuota);
    if ($data) {
        echo json_encode($data);
    } else {
        echo json_encode(["status" => "error", "message" => "Registro no encontrado."]);
    }
    exit();
}

if ($action == 'get_socio_resumen') {
    header('Content-Type: application/json');
    $id_socio = $_GET['id_socio'] ?? 0;
    $resumen = $cuotaModel->getResumenSocio($id_socio);
    echo json_encode($resumen);
    exit();
}

// Carga normal de la vista
$socios_stmt = $socioModel->getActivos();
$socios = $socios_stmt ? $socios_stmt->fetchAll(PDO::FETCH_ASSOC) : [];

$selected_socio_id = (isset($_GET['id_socio']) && $_GET['id_socio'] !== '') ? $_GET['id_socio'] : null;
$socio_actual = null;
$pagos_cuota = [];
$resumen_socio = null;

if ($selected_socio_id) {
    $socio_actual = $socioModel->getById($selected_socio_id);
    $pagos_stmt = $cuotaModel->getBySocioId($selected_socio_id);
    $pagos_cuota = $pagos_stmt ? $pagos_stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    $resumen_socio = $cuotaModel->getResumenSocio($selected_socio_id);
}

include __DIR__ . '/../View/vcuota_inicial.php';
?>
