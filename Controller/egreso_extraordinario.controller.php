<?php
// Controller/egreso_extraordinario.controller.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../Config/database.php';
require_once __DIR__ . '/../Model/megreso_extraordinario.php';

$database = new Database();
$db = $database->getConnection();

$egresoModel = new EgresoExtraordinarioModel($db);

// Obtener gestión activa
$query_gestion = "SELECT id_gestion, gestion FROM gestiones WHERE estado = 'En Curso' LIMIT 1";
$stmt_g = $db->prepare($query_gestion);
$stmt_g->execute();
$gestion_activa = $stmt_g->fetch(PDO::FETCH_ASSOC);
$id_gestion_activa = $gestion_activa ? $gestion_activa['id_gestion'] : null;

// Obtener categorías de Extraordinario creadas en Configuración de Montos
$categorias = [];
if ($id_gestion_activa) {
    $query_cat = "SELECT nombre, monto_sugerido FROM categorias_ingreso WHERE id_gestion = ? AND requiere_socio = 1";
    $stmt_c = $db->prepare($query_cat);
    $stmt_c->bindParam(1, $id_gestion_activa);
    $stmt_c->execute();
    $categorias = $stmt_c->fetchAll(PDO::FETCH_ASSOC);
}

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    try {
        if ($action == 'save') {
            if (!$id_gestion_activa) {
                echo json_encode(["status" => "error", "message" => "No hay una gestión activa. No se pueden registrar gastos extraordinarios."]);
                exit();
            }

            if (empty($_POST['detalle'])) {
                echo json_encode(["status" => "error", "message" => "El detalle del gasto es obligatorio."]);
                exit();
            }

            if (empty($_POST['monto']) || floatval($_POST['monto']) <= 0) {
                echo json_encode(["status" => "error", "message" => "El monto debe ser mayor a 0."]);
                exit();
            }

            $data = [
                'id_gestion' => $id_gestion_activa,
                'motivo' => $_POST['motivo'] ?? '',
                'detalle' => $_POST['detalle'] ?? '',
                'monto' => $_POST['monto'] ?? 0,
                'fecha_pago' => $_POST['fecha_pago'] ?? date('Y-m-d'),
                'comprobante' => $_POST['comprobante'] ?? '',
                'estado' => $_POST['estado'] ?? 'Pagado'
            ];
            
            if (!empty($_POST['id_egreso_ext'])) {
                // Update
                $data['id_egreso_ext'] = $_POST['id_egreso_ext'];
                if ($egresoModel->update($data)) {
                    echo json_encode(["status" => "success", "message" => "Gasto Extraordinario actualizado correctamente."]);
                } else {
                    echo json_encode(["status" => "error", "message" => "No se pudo actualizar el gasto."]);
                }
            } else {
                // Create
                if ($egresoModel->create($data)) {
                    echo json_encode(["status" => "success", "message" => "Gasto Extraordinario registrado correctamente."]);
                } else {
                    echo json_encode(["status" => "error", "message" => "No se pudo registrar el gasto."]);
                }
            }
            exit();
        } elseif ($action == 'delete') {
            $id_egreso_ext = $_POST['id_egreso_ext'] ?? 0;
            if ($id_egreso_ext > 0 && $egresoModel->delete($id_egreso_ext)) {
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
    $id_egreso_ext = $_GET['id_egreso_ext'] ?? 0;
    $data = $egresoModel->getById($id_egreso_ext);
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
        $egresos_list = $egresoModel->getAll($id_gestion_activa);
    } else {
        $egresos_list = null;
    }
    include '../View/vegreso_extraordinario.php';
}
