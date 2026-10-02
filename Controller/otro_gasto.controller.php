<?php
// Controller/otro_gasto.controller.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../Config/database.php';
require_once __DIR__ . '/../Model/motro_gasto.php';

$database = new Database();
$db = $database->getConnection();

$otroGastoModel = new OtroGastoModel($db);

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
                echo json_encode(["status" => "error", "message" => "No hay una gestión activa. No se pueden registrar otros gastos."]);
                exit();
            }

            if (empty($_POST['monto']) || floatval($_POST['monto']) <= 0) {
                echo json_encode(["status" => "error", "message" => "El monto debe ser mayor a 0."]);
                exit();
            }

            $data = [
                'id_gestion' => $id_gestion_activa,
                'nombre_gasto' => $_POST['nombre_gasto'] ?? $_POST['detalle'] ?? '',
                'detalle' => $_POST['detalle'] ?? '',
                'unidad_medida' => $_POST['unidad_medida'] ?? '',
                'cantidad' => $_POST['cantidad'] ?? 1.00,
                'precio' => $_POST['precio'] ?? null,
                'monto' => $_POST['monto'] ?? 0,
                'fecha_pago' => $_POST['fecha_pago'] ?? date('Y-m-d'),
                'comprobante' => $_POST['comprobante'] ?? '',
                'estado' => $_POST['estado'] ?? 'Pagado'
            ];
            
            if (!empty($_POST['id_otro_gasto'])) {
                // Update
                $data['id_otro_gasto'] = $_POST['id_otro_gasto'];
                if ($otroGastoModel->update($data)) {
                    echo json_encode(["status" => "success", "message" => "Gasto actualizado correctamente."]);
                } else {
                    echo json_encode(["status" => "error", "message" => "No se pudo actualizar el gasto."]);
                }
            } else {
                // Create
                if ($otroGastoModel->create($data)) {
                    echo json_encode(["status" => "success", "message" => "Gasto registrado correctamente."]);
                } else {
                    echo json_encode(["status" => "error", "message" => "No se pudo registrar el gasto."]);
                }
            }
            exit();
        } elseif ($action == 'delete') {
            $id_otro_gasto = $_POST['id_otro_gasto'] ?? 0;
            if ($id_otro_gasto > 0 && $otroGastoModel->delete($id_otro_gasto)) {
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
    $id_otro_gasto = $_GET['id_otro_gasto'] ?? 0;
    $data = $otroGastoModel->getById($id_otro_gasto);
    if ($data) {
        echo json_encode($data);
    } else {
        echo json_encode(["status" => "error", "message" => "Gasto no encontrado."]);
    }
    exit();
}

// Si no es POST ni AJAX GET, cargamos la vista normal
if (!$action) {
    if ($id_gestion_activa) {
        $otros_gastos = $otroGastoModel->getByGestionId($id_gestion_activa);
    } else {
        $otros_gastos = null;
    }
    include '../View/votro_gasto.php';
}
?>
