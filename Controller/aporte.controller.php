<?php
// Controller/aporte.controller.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../Config/permissions.php';
require_once __DIR__ . '/../Config/database.php';
require_once __DIR__ . '/../Model/maporte.php';
require_once __DIR__ . '/../Model/msocio.php';

// Si es Socio, forzar id_socio de sesión
if (is_socio()) {
    if (empty($_SESSION['id_socio'])) {
        header("Location: ../View/dashboard.php?error=socio_sin_id");
        exit();
    }
    $_GET['id_socio'] = $_SESSION['id_socio'];
}

$database = new Database();
$db = $database->getConnection();

$aporteModel = new AporteModel($db);
$socioModel = new SocioModel($db);

// Obtener gestión activa
$query_gestion = "SELECT id_gestion FROM gestiones WHERE estado = 'En Curso' LIMIT 1";
$stmt_g = $db->prepare($query_gestion);
$stmt_g->execute();
$gestion_activa = $stmt_g->fetch(PDO::FETCH_ASSOC);
$id_gestion_activa = $gestion_activa ? $gestion_activa['id_gestion'] : null;

// Obtener categorías de Aporte Extraordinario configuradas para los asociados
$query_cat = "SELECT id_cat_ingreso, nombre, monto_sugerido, monto_mensual, monto_total FROM categorias_ingreso WHERE requiere_socio = 1 ORDER BY id_cat_ingreso DESC";
$stmt_c = $db->prepare($query_cat);
$stmt_c->execute();
$categorias = $stmt_c->fetchAll(PDO::FETCH_ASSOC);

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    if (is_socio()) {
        echo json_encode(["status" => "error", "message" => "Los asociados no tienen permisos de modificación."]);
        exit();
    }
    
    try {
        if ($action == 'save') {
            $id_aporte = $_POST['id_aporte'] ?? 0;
            $motivo = $_POST['motivo'] ?? '';
            $monto = (float)($_POST['monto'] ?? 0);
            $numero_recibo = trim($_POST['numero_recibo'] ?? '');
            $id_socio = $_POST['id_socio'] ?? '';

            // 1. Validar que el monto no exceda el saldo pendiente
            if ($monto > 0 && !empty($motivo) && !empty($id_socio)) {
                // Total configurado
                $query_total = "SELECT monto_sugerido FROM categorias_ingreso WHERE nombre = ? AND (id_gestion = ? OR id_gestion IS NULL) LIMIT 1";
                $stmt_tot = $db->prepare($query_total);
                $stmt_tot->execute([$motivo, $id_gestion_activa]);
                $row_tot = $stmt_tot->fetch(PDO::FETCH_ASSOC);
                $total = $row_tot ? (float)$row_tot['monto_sugerido'] : 0.00;

                // Ya pagado (excluyendo el registro actual si es edición)
                $query_pagado = "SELECT SUM(monto) as total_pagado FROM aporte_extraordinario WHERE id_socio = ? AND motivo = ? AND id_aporte != ?";
                $stmt_pag = $db->prepare($query_pagado);
                $stmt_pag->execute([$id_socio, $motivo, $id_aporte]);
                $row_pag = $stmt_pag->fetch(PDO::FETCH_ASSOC);
                $pagado = $row_pag && $row_pag['total_pagado'] ? (float)$row_pag['total_pagado'] : 0.00;

                $saldo = max(0, $total - $pagado);
                
                // Redondear a 2 decimales para evitar problemas de flotantes
                $saldo_str = (string)round($saldo, 2);
                $monto_str = (string)round($monto, 2);
                
                if (round($monto, 2) > round($saldo, 2)) {
                    echo json_encode(["status" => "error", "message" => "El monto (Bs $monto_str) no puede ser mayor al saldo pendiente (Bs $saldo_str)."]);
                    exit();
                }
            }

            // 2. Validar que el número de recibo no esté duplicado
            if (!empty($numero_recibo)) {
                $query_recibo = "
                    SELECT 'Mensualidad' as origen FROM mensualidad WHERE numero_recibo = :nr
                    UNION
                    SELECT 'Aporte Extraordinario' as origen FROM aporte_extraordinario WHERE numero_recibo = :nr AND id_aporte != :ida
                    UNION
                    SELECT 'Aporte Especial' as origen FROM aportes_especiales WHERE numero_recibo = :nr
                ";
                $stmt_recibo = $db->prepare($query_recibo);
                $stmt_recibo->bindValue(':nr', $numero_recibo);
                $stmt_recibo->bindValue(':ida', $id_aporte);
                $stmt_recibo->execute();
                
                if ($stmt_recibo->rowCount() > 0) {
                    echo json_encode(["status" => "error", "message" => "El número de recibo '$numero_recibo' ya fue utilizado en otro pago."]);
                    exit();
                }
            }

            $data = [
                'id_socio' => $id_socio,
                'numero_accion' => $_POST['numero_accion'] ?? 1,
                'motivo' => $motivo,
                'monto' => $monto,
                'fecha_aporte' => $_POST['fecha_aporte'] ?? date('Y-m-d'),
                'numero_recibo' => $numero_recibo
            ];
            
            if (!empty($id_aporte)) {
                // Update
                $data['id_aporte'] = $id_aporte;
                $data['id_aporte'] = $_POST['id_aporte'];
                if ($aporteModel->update($data)) {
                    echo json_encode(["status" => "success", "message" => "Aporte actualizado correctamente."]);
                } else {
                    echo json_encode(["status" => "error", "message" => "No se pudo actualizar el aporte."]);
                }
            } else {
                // Create
                if ($aporteModel->create($data)) {
                    echo json_encode(["status" => "success", "message" => "Aporte registrado correctamente."]);
                } else {
                    echo json_encode(["status" => "error", "message" => "No se pudo registrar el aporte."]);
                }
            }
        } elseif ($action == 'delete') {
            $id = isset($_POST['id_aporte']) ? $_POST['id_aporte'] : die(json_encode(["status" => "error", "message" => "ID no proporcionado."]));
            if ($aporteModel->delete($id)) {
                echo json_encode(["status" => "success", "message" => "Aporte eliminado correctamente."]);
            } else {
                echo json_encode(["status" => "error", "message" => "No se pudo eliminar el aporte."]);
            }
        } elseif ($action == 'get') {
            $id = isset($_GET['id_aporte']) ? $_GET['id_aporte'] : die(json_encode(["status" => "error", "message" => "ID no proporcionado."]));
            $aporte = $aporteModel->getById($id);
            if ($aporte) {
                echo json_encode($aporte);
            } else {
                echo json_encode(["status" => "error", "message" => "Aporte no encontrado."]);
            }
        } elseif ($action == 'get_payment_info') {
            $id_socio = isset($_POST['id_socio']) ? $_POST['id_socio'] : null;
            $motivo = isset($_POST['motivo']) ? $_POST['motivo'] : null;
            $id_aporte = isset($_POST['id_aporte']) ? $_POST['id_aporte'] : 0;
            
            if (!$id_socio || !$motivo) {
                echo json_encode(["status" => "error", "message" => "Faltan parámetros."]);
                exit();
            }

            // 1. Obtener el Total (monto_total o monto_sugerido) y monto_mensual de categorias_ingreso
            $query_total = "SELECT monto_total, monto_sugerido, monto_mensual FROM categorias_ingreso WHERE nombre = ? LIMIT 1";
            $stmt_tot = $db->prepare($query_total);
            $stmt_tot->execute([$motivo]);
            $row_tot = $stmt_tot->fetch(PDO::FETCH_ASSOC);
            $total = $row_tot ? (float)(!empty($row_tot['monto_total']) && $row_tot['monto_total'] > 0 ? $row_tot['monto_total'] : $row_tot['monto_sugerido']) : 0.00;
            $monto_mensual = $row_tot ? (float)($row_tot['monto_mensual'] ?? 0.00) : 0.00;

            // 2. Obtener lo Ya Pagado de aporte_extraordinario
            $query_pagado = "SELECT SUM(monto) as total_pagado FROM aporte_extraordinario WHERE id_socio = ? AND motivo = ? AND id_aporte != ?";
            $stmt_pag = $db->prepare($query_pagado);
            $stmt_pag->execute([$id_socio, $motivo, $id_aporte]);
            $row_pag = $stmt_pag->fetch(PDO::FETCH_ASSOC);
            $pagado = $row_pag && $row_pag['total_pagado'] ? (float)$row_pag['total_pagado'] : 0.00;

            // 3. Calcular Saldo
            $saldo = max(0, $total - $pagado);

            echo json_encode([
                "status" => "success",
                "total" => $total,
                "pagado" => $pagado,
                "saldo" => $saldo,
                "monto_mensual" => $monto_mensual
            ]);
            exit();
        } elseif ($action == 'get_all_paid_months_aporte') {
            $id_socio = $_REQUEST['id_socio'] ?? '';
            $numero_accion = $_REQUEST['numero_accion'] ?? 1;
            $motivo = $_REQUEST['motivo'] ?? '';

            $query = "SELECT fecha_aporte FROM aporte_extraordinario WHERE id_socio = :id_socio AND motivo = :motivo";
            if ($numero_accion !== 'all' && !empty($numero_accion)) {
                $query .= " AND numero_accion = :numero_accion";
            }
            $stmt = $db->prepare($query);
            $stmt->bindValue(':id_socio', $id_socio);
            $stmt->bindValue(':motivo', $motivo);
            if ($numero_accion !== 'all' && !empty($numero_accion)) {
                $stmt->bindValue(':numero_accion', $numero_accion);
            }
            $stmt->execute();

            $meses_nombres = [
                1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
                5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
                9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
            ];

            $paid = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $dt = strtotime($row['fecha_aporte']);
                $y = date('Y', $dt);
                $m_idx = (int)date('n', $dt);
                $m_name = $meses_nombres[$m_idx] ?? '';
                if ($y && $m_name) {
                    if (!isset($paid[$y])) $paid[$y] = [];
                    if (!in_array($m_name, $paid[$y])) {
                        $paid[$y][] = $m_name;
                    }
                }
            }
            echo json_encode(["status" => "success", "paid" => $paid]);
            exit();
        } elseif ($action == 'save_multi_year_aporte') {
            $id_socio = $_POST['id_socio'] ?? '';
            $numero_accion = $_POST['numero_accion'] ?? 1;
            $motivo = trim($_POST['motivo'] ?? '');
            $numero_recibo = trim($_POST['numero_recibo'] ?? '');

            $years_input = [];
            if (!empty($_POST['years_json'])) {
                $years_input = json_decode($_POST['years_json'], true);
            }

            if (empty($id_socio)) {
                echo json_encode(["status" => "error", "message" => "Debe seleccionar un socio."]);
                exit();
            }

            if (empty($motivo)) {
                echo json_encode(["status" => "error", "message" => "Debe especificar o seleccionar un Motivo para el aporte."]);
                exit();
            }

            if (empty($years_input)) {
                echo json_encode(["status" => "error", "message" => "No se ha seleccionado ningún año ni meses a registrar."]);
                exit();
            }

            $socios_lista = [];
            if ($id_socio === 'all') {
                $stmtAllSocios = $socioModel->getAll();
                while ($s = $stmtAllSocios->fetch(PDO::FETCH_ASSOC)) {
                    $socios_lista[] = $s;
                }
            } else {
                $s = $socioModel->getById($id_socio);
                if ($s) $socios_lista[] = $s;
            }

            $meses_map = [
                'Enero' => 1, 'Febrero' => 2, 'Marzo' => 3, 'Abril' => 4,
                'Mayo' => 5, 'Junio' => 6, 'Julio' => 7, 'Agosto' => 8,
                'Septiembre' => 9, 'Octubre' => 10, 'Noviembre' => 11, 'Diciembre' => 12
            ];

            $count_added = 0;
            $count_deleted = 0;

            foreach ($socios_lista as $soc) {
                $s_id = $soc['id_socio'];
                $acciones_loop = [];
                if ($numero_accion === 'all') {
                    $max_acc = intval($soc['acciones'] ?? 1);
                    for ($a = 1; $a <= $max_acc; $a++) {
                        $acciones_loop[] = $a;
                    }
                } else {
                    $acciones_loop[] = intval($numero_accion);
                }

                foreach ($acciones_loop as $acc) {
                    foreach ($years_input as $yConfig) {
                        $anio_val = intval($yConfig['anio'] ?? 0);
                        $monto_val = floatval($yConfig['monto'] ?? 0.00);
                        $meses_list = isset($yConfig['meses']) && is_array($yConfig['meses']) ? $yConfig['meses'] : [];
                        $meses_eliminar = isset($yConfig['meses_eliminar']) && is_array($yConfig['meses_eliminar']) ? $yConfig['meses_eliminar'] : [];

                        if ($anio_val <= 0) continue;

                        // 1. Eliminar meses desmarcados que ya existían
                        if (!empty($meses_eliminar)) {
                            foreach ($meses_eliminar as $nombre_mes_del) {
                                $mes_num_del = $meses_map[$nombre_mes_del] ?? 0;
                                if ($mes_num_del > 0) {
                                    if ($aporteModel->deleteBySocioAccionMotivoMesAnio($s_id, $acc, $motivo, $anio_val, $mes_num_del)) {
                                        $count_deleted++;
                                    }
                                }
                            }
                        }

                        // 2. Registrar meses marcados
                        if (!empty($meses_list)) {
                            foreach ($meses_list as $nombre_mes) {
                                $mes_num = $meses_map[$nombre_mes] ?? 0;
                                if ($mes_num > 0) {
                                    $exists = $aporteModel->checkExistsMonth($s_id, $acc, $motivo, $anio_val, $mes_num);
                                    if (!$exists) {
                                        $str_m = str_pad($mes_num, 2, '0', STR_PAD_LEFT);
                                        $fecha_app = "{$anio_val}-{$str_m}-01";
                                        $data_ins = [
                                            'id_socio' => $s_id,
                                            'numero_accion' => $acc,
                                            'motivo' => $motivo,
                                            'monto' => $monto_val,
                                            'fecha_aporte' => $fecha_app,
                                            'numero_recibo' => $numero_recibo
                                        ];
                                        if ($aporteModel->create($data_ins)) {
                                            $count_added++;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }

            echo json_encode([
                "status" => "success",
                "message" => "Carga de Aportes Extraordinarios completada. Registrados: $count_added aportes. Eliminados: $count_deleted aportes."
            ]);
            exit();
        }
    } catch(Exception $e) {
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    }
    exit();
}

// GET Requests (Render Views)
if ($action == 'get' && isset($_GET['id_aporte'])) {
    header('Content-Type: application/json');
    $id = $_GET['id_aporte'];
    $aporte = $aporteModel->getById($id);
    if ($aporte) {
        echo json_encode($aporte);
    } else {
        echo json_encode(["status" => "error", "message" => "Aporte no encontrado."]);
    }
    exit();
}

// If we have an id_socio, show the CRUD for that socio
if (isset($_GET['id_socio']) && !empty($_GET['id_socio'])) {
    $id_socio = $_GET['id_socio'];
    $socio_actual = $socioModel->getById($id_socio);
    
    if (!$socio_actual) {
        header("Location: aporte.controller.php");
        exit();
    }
    
    // Verificar el saldo pendiente de cada categoría para este socio
    foreach ($categorias as &$cat) {
        $monto_objetivo = (float)(!empty($cat['monto_total']) && $cat['monto_total'] > 0 ? $cat['monto_total'] : ($cat['monto_sugerido'] ?? 0));
        
        $query_pag = "SELECT COALESCE(SUM(monto), 0) as total_pagado FROM aporte_extraordinario WHERE id_socio = ? AND motivo = ?";
        $stmt_p = $db->prepare($query_pag);
        $stmt_p->execute([$id_socio, $cat['nombre']]);
        $row_p = $stmt_p->fetch(PDO::FETCH_ASSOC);
        $total_pagado = $row_p && $row_p['total_pagado'] ? (float)$row_p['total_pagado'] : 0.00;

        $saldo = $monto_objetivo - $total_pagado;
        if ($monto_objetivo > 0 && round($saldo, 2) <= 0) {
            $cat['completado'] = true;
        } else {
            $cat['completado'] = false;
        }
    }
    unset($cat);

    $aportes = $aporteModel->getBySocioId($id_socio);
    include '../View/vaporte.php';
} else {
    // No id_socio, show the selection screen
    $socios = $socioModel->getAll();
    include '../View/vaporte.php';
}
?>
