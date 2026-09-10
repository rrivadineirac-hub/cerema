<?php
// Controller/servicio.controller.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../Config/database.php';
require_once __DIR__ . '/../Model/mservicio.php';

$database = new Database();
$db = $database->getConnection();

$servicioModel = new ServicioModel($db);

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
                echo json_encode(["status" => "error", "message" => "No hay una gestión activa. No se pueden registrar pagos de servicios."]);
                exit();
            }

            if (empty($_POST['monto']) || floatval($_POST['monto']) <= 0) {
                echo json_encode(["status" => "error", "message" => "El monto del pago debe ser mayor a 0."]);
                exit();
            }

            $data = [
                'id_gestion' => $id_gestion_activa,
                'tipo_servicio' => $_POST['tipo_servicio'] ?? '',
                'mes_pago' => $_POST['mes_pago'] ?? '',
                'monto' => $_POST['monto'] ?? 0,
                'fecha_pago' => $_POST['fecha_pago'] ?? date('Y-m-d'),
                'comprobante' => $_POST['comprobante'] ?? '',
                'estado' => $_POST['estado'] ?? 'Pagado'
            ];
            
            if (!empty($_POST['id_servicio'])) {
                // Update
                $data['id_servicio'] = $_POST['id_servicio'];
                if ($servicioModel->update($data)) {
                    echo json_encode(["status" => "success", "message" => "Servicio actualizado correctamente."]);
                } else {
                    echo json_encode(["status" => "error", "message" => "No se pudo actualizar el servicio."]);
                }
            } else {
                // Create
                if ($servicioModel->create($data)) {
                    echo json_encode(["status" => "success", "message" => "Pago de servicio registrado correctamente."]);
                } else {
                    echo json_encode(["status" => "error", "message" => "No se pudo registrar el pago."]);
                }
            }
            exit();
        } elseif ($action == 'delete') {
            $id_servicio = $_POST['id_servicio'] ?? 0;
            if ($id_servicio > 0 && $servicioModel->delete($id_servicio)) {
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
    $id_servicio = $_GET['id_servicio'] ?? 0;
    $data = $servicioModel->getById($id_servicio);
    if ($data) {
        echo json_encode($data);
    } else {
        echo json_encode(["status" => "error", "message" => "Servicio no encontrado."]);
    }
    exit();
}

// Si no es POST ni AJAX GET, cargamos la vista normal
if (!$action) {
    if ($id_gestion_activa) {
        $servicios = $servicioModel->getByGestionId($id_gestion_activa);
    } else {
        $servicios = null;
    }
    include '../View/vservicio.php';
}
?>
