<?php
// Controller/otro_ingreso.controller.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../Config/permissions.php';
require_permission('otros_ingresos');
require_once __DIR__ . '/../Config/database.php';
require_once __DIR__ . '/../Model/motro_ingreso.php';

$database = new Database();
$db = $database->getConnection();

$otroIngresoModel = new OtroIngresoModel($db);

// Obtener gestión activa
$query_gestion = "SELECT id_gestion, gestion FROM gestiones WHERE estado = 'En Curso' LIMIT 1";
$stmt_g = $db->prepare($query_gestion);
$stmt_g->execute();
$gestion_activa = $stmt_g->fetch(PDO::FETCH_ASSOC);
$id_gestion_activa = $gestion_activa ? $gestion_activa['id_gestion'] : null;

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    try {
        if ($action == 'save') {
            if (!$id_gestion_activa) {
                echo json_encode(["status" => "error", "message" => "No hay una gestión activa. No se pueden registrar otros ingresos."]);
                exit();
            }

            if (empty($_POST['monto']) || floatval($_POST['monto']) <= 0) {
                echo json_encode(["status" => "error", "message" => "El monto debe ser mayor a 0."]);
                exit();
            }

            if (empty($_POST['detalle'])) {
                echo json_encode(["status" => "error", "message" => "Debe ingresar el detalle o motivo del ingreso."]);
                exit();
            }

            $data = [
                'id_gestion' => $id_gestion_activa,
                'detalle' => $_POST['detalle'] ?? '',
                'monto' => $_POST['monto'] ?? 0,
                'fecha_pago' => $_POST['fecha_pago'] ?? date('Y-m-d'),
                'comprobante' => $_POST['comprobante'] ?? '',
                'estado' => $_POST['estado'] ?? 'Cobrado'
            ];
            
            if (!empty($_POST['id_otro_ingreso'])) {
                // Actualizar
                $data['id_otro_ingreso'] = $_POST['id_otro_ingreso'];
                if ($otroIngresoModel->update($data)) {
                    echo json_encode(["status" => "success", "message" => "Ingreso actualizado correctamente."]);
                } else {
                    echo json_encode(["status" => "error", "message" => "No se pudo actualizar el ingreso."]);
                }
            } else {
                // Crear
                if ($otroIngresoModel->create($data)) {
                    echo json_encode(["status" => "success", "message" => "Ingreso registrado correctamente."]);
                } else {
                    echo json_encode(["status" => "error", "message" => "No se pudo registrar el ingreso."]);
                }
            }
            exit();
        } elseif ($action == 'delete') {
            $id_otro_ingreso = $_POST['id_otro_ingreso'] ?? 0;
            if ($id_otro_ingreso > 0 && $otroIngresoModel->delete($id_otro_ingreso)) {
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
    $id_otro_ingreso = $_GET['id_otro_ingreso'] ?? 0;
    $data = $otroIngresoModel->getById($id_otro_ingreso);
    if ($data) {
        echo json_encode($data);
    } else {
        echo json_encode(["status" => "error", "message" => "Ingreso no encontrado."]);
    }
    exit();
}

// Si no es POST ni AJAX GET, cargamos la vista normal
if (!$action) {
    if ($id_gestion_activa) {
        $otros_ingresos = $otroIngresoModel->getByGestionId($id_gestion_activa);
    } else {
        $otros_ingresos = null;
    }
    include __DIR__ . '/../View/votro_ingreso.php';
}
?>
