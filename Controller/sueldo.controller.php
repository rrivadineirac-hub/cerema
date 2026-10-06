<?php
// Controller/sueldo.controller.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../Config/permissions.php';
require_permission('sueldo');
require_once __DIR__ . '/../Config/database.php';
require_once __DIR__ . '/../Model/msueldo.php';

$database = new Database();
$db = $database->getConnection();

$sueldoModel = new SueldoModel($db);

// Obtener gestión activa
$query_gestion = "SELECT id_gestion, gestion FROM gestiones WHERE estado = 'En Curso' LIMIT 1";
$stmt_g = $db->prepare($query_gestion);
$stmt_g->execute();
$gestion_activa = $stmt_g->fetch(PDO::FETCH_ASSOC);
$id_gestion_activa = $gestion_activa ? $gestion_activa['id_gestion'] : null;

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    try {
        if ($action == 'save') {
            if (!$id_gestion_activa) {
                echo json_encode(["status" => "error", "message" => "No hay una gestión activa. No se pueden registrar pagos de sueldos."]);
                exit();
            }

            if (empty($_POST['monto']) || floatval($_POST['monto']) <= 0) {
                echo json_encode(["status" => "error", "message" => "El monto del sueldo debe ser mayor a 0."]);
                exit();
            }

            $data = [
                'id_gestion' => $id_gestion_activa,
                'cargo' => $_POST['cargo'] ?? '',
                'empleado' => $_POST['empleado'] ?? '',
                'mes' => $_POST['mes'] ?? '',
                'monto' => $_POST['monto'] ?? 0,
                'fecha_pago' => $_POST['fecha_pago'] ?? date('Y-m-d'),
                'comprobante' => $_POST['comprobante'] ?? '',
                'estado' => $_POST['estado'] ?? 'Pagado'
            ];
            
            if (!empty($_POST['id_sueldo'])) {
                // Update
                $data['id_sueldo'] = $_POST['id_sueldo'];
                if ($sueldoModel->update($data)) {
                    echo json_encode(["status" => "success", "message" => "Sueldo actualizado correctamente."]);
                } else {
                    echo json_encode(["status" => "error", "message" => "No se pudo actualizar el sueldo."]);
                }
            } else {
                // Create
                if ($sueldoModel->create($data)) {
                    echo json_encode(["status" => "success", "message" => "Pago de sueldo registrado correctamente."]);
                } else {
                    echo json_encode(["status" => "error", "message" => "No se pudo registrar el pago de sueldo."]);
                }
            }
            exit();
        } elseif ($action == 'delete') {
            $id_sueldo = $_POST['id_sueldo'] ?? 0;
            if ($id_sueldo > 0 && $sueldoModel->delete($id_sueldo)) {
                echo json_encode(["status" => "success", "message" => "Registro eliminado correctamente."]);
            } else {
                echo json_encode(["status" => "error", "message" => "No se pudo eliminar el registro."]);
            }
            exit();
        }
    } catch (Exception $e) {
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
        exit();
    }
}

// Peticiones GET
if ($action == 'get') {
    header('Content-Type: application/json');
    $id_sueldo = $_GET['id_sueldo'] ?? 0;
    $data = $sueldoModel->getById($id_sueldo);
    if ($data) {
        echo json_encode($data);
    } else {
        echo json_encode(["status" => "error", "message" => "Sueldo no encontrado."]);
    }
    exit();
}

// Si no es POST ni AJAX GET, cargamos la vista normal
if (!$action) {
    if ($id_gestion_activa) {
        $sueldos = $sueldoModel->getByGestionId($id_gestion_activa);
    } else {
        $sueldos = null;
    }
    include '../View/vsueldo.php';
}
?>
