<?php
// Controller/mensualidad.controller.php
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

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    try {
        if ($action == 'save') {
            $data = [
                'id_socio' => $_POST['id_socio'] ?? '',
                'numero_accion' => $_POST['numero_accion'] ?? 1,
                'numero_recibo' => $_POST['numero_recibo'] ?? '',
                'mes' => $_POST['mes'] ?? '',
                'anio' => $_POST['anio'] ?? date('Y'),
                'monto' => $_POST['monto'] ?? 0,
                'fecha_pago' => $_POST['fecha_pago'] ?? date('Y-m-d H:i:s'),
                'estado' => $_POST['estado'] ?? 'Pagado'
            ];
            
            if (!empty($_POST['id_mensualidad'])) {
                // Update single record
                $data['id_mensualidad'] = $_POST['id_mensualidad'];
                if ($mensualidadModel->update($data)) {
                    echo json_encode(["status" => "success", "message" => "Mensualidad actualizada correctamente."]);
                } else {
                    echo json_encode(["status" => "error", "message" => "No se pudo actualizar la mensualidad."]);
                }
            } else {
                // Create (Batch insert if multiple months selected)
                $meses_seleccionados = isset($_POST['meses']) && is_array($_POST['meses']) ? $_POST['meses'] : [];
                if (empty($meses_seleccionados) && !empty($_POST['mes'])) {
                    $meses_seleccionados = [$_POST['mes']];
                }

                if (empty($meses_seleccionados)) {
                    echo json_encode(["status" => "error", "message" => "Debes seleccionar al menos un mes para pagar."]);
                    exit();
                }

                $monto_unitario = isset($_POST['monto_unitario']) ? floatval($_POST['monto_unitario']) : 50.00;
                if (count($meses_seleccionados) > 0 && empty($_POST['monto_unitario']) && !empty($_POST['monto'])) {
                    $monto_unitario = floatval($_POST['monto']) / count($meses_seleccionados);
                }

                $success_count = 0;
                $total_months = count($meses_seleccionados);

                if (isset($_POST['numero_accion']) && $_POST['numero_accion'] === 'all') {
                    $socio = $socioModel->getById($data['id_socio']);
                    $max_acciones = intval($socio['acciones'] ?? 1);
                    
                    for ($i = 1; $i <= $max_acciones; $i++) {
                        $data['numero_accion'] = $i;
                        foreach ($meses_seleccionados as $m) {
                            $data['mes'] = $m;
                            $data['monto'] = $monto_unitario;
                            if ($mensualidadModel->create($data)) {
                                $success_count++;
                            }
                        }
                    }
                } else {
                    foreach ($meses_seleccionados as $m) {
                        $data['mes'] = $m;
                        $data['monto'] = $monto_unitario;
                        if ($mensualidadModel->create($data)) {
                            $success_count++;
                        }
                    }
                }
                
                if ($success_count > 0) {
                    echo json_encode(["status" => "success", "message" => "Se registraron exitosamente los pagos de mensualidad."]);
                } else {
                    echo json_encode(["status" => "error", "message" => "No se pudo registrar la mensualidad."]);
                }
            }
        } elseif ($action == 'delete') {
            $id = isset($_POST['id_mensualidad']) ? $_POST['id_mensualidad'] : die(json_encode(["status" => "error", "message" => "ID no proporcionado."]));
            if ($mensualidadModel->delete($id)) {
                echo json_encode(["status" => "success", "message" => "Mensualidad eliminada correctamente."]);
            } else {
                echo json_encode(["status" => "error", "message" => "No se pudo eliminar la mensualidad."]);
            }
        } elseif ($action == 'get') {
            $id = isset($_GET['id_mensualidad']) ? $_GET['id_mensualidad'] : die(json_encode(["status" => "error", "message" => "ID no proporcionado."]));
            $mensualidad = $mensualidadModel->getById($id);
            if ($mensualidad) {
                echo json_encode($mensualidad);
            } else {
                echo json_encode(["status" => "error", "message" => "Mensualidad no encontrada."]);
            }
        }
    } catch(Exception $e) {
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    }
    exit();
}

// GET Requests (AJAX Helpers & Views)
if ($action == 'get_paid_months') {
    header('Content-Type: application/json');
    $id_socio = $_GET['id_socio'] ?? '';
    $numero_accion = $_GET['numero_accion'] ?? 1;
    $anio = $_GET['anio'] ?? date('Y');
    
    if ($numero_accion === 'all') {
        $numero_accion = 1; 
    }
    
    $paidMonths = $mensualidadModel->getPaidMonthsByYear($id_socio, $numero_accion, $anio);
    echo json_encode(["status" => "success", "paid_months" => $paidMonths]);
    exit();
}

if ($action == 'check_status') {
    header('Content-Type: application/json');
    $id_socio = $_GET['id_socio'] ?? '';
    $numero_accion = $_GET['numero_accion'] ?? 1;
    $mes = $_GET['mes'] ?? '';
    $anio = $_GET['anio'] ?? '';
    
    if ($numero_accion === 'all') {
         $numero_accion = 1; 
    }
    
    $existing = $mensualidadModel->checkExists($id_socio, $numero_accion, $mes, $anio);
    if ($existing) {
        echo json_encode(["status" => "exists", "estado" => $existing['estado']]);
    } else {
        echo json_encode(["status" => "not_found"]);
    }
    exit();
}

if ($action == 'get' && isset($_GET['id_mensualidad'])) {
    header('Content-Type: application/json');
    $id = $_GET['id_mensualidad'];
    $mensualidad = $mensualidadModel->getById($id);
    if ($mensualidad) {
        echo json_encode($mensualidad);
    } else {
        echo json_encode(["status" => "error", "message" => "Mensualidad no encontrada."]);
    }
    exit();
}

// Fetch configured amount for Mensualidades
$query_cat_m = "SELECT monto_sugerido FROM categorias_ingreso WHERE nombre = 'Pago de Mensualidades de socios' LIMIT 1";
$stmt_cat_m = $db->prepare($query_cat_m);
$stmt_cat_m->execute();
$row_cat_m = $stmt_cat_m->fetch(PDO::FETCH_ASSOC);
$monto_mensualidad_sugerido = $row_cat_m ? (float)$row_cat_m['monto_sugerido'] : 50.00;

// If we have an id_socio, show the CRUD for that socio
if (isset($_GET['id_socio']) && !empty($_GET['id_socio'])) {
    $id_socio = $_GET['id_socio'];
    $socio_actual = $socioModel->getById($id_socio);
    
    if (!$socio_actual) {
        // Socio not found, redirect to selection
        header("Location: mensualidad.controller.php");
        exit();
    }
    
    $mensualidades = $mensualidadModel->getBySocioId($id_socio);
    include '../View/vmensualidad.php';
} else {
    // No id_socio, show the selection screen
    $socios = $socioModel->getAll();
    include '../View/vmensualidad.php';
}
?>
