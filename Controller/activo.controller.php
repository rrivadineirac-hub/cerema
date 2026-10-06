<?php
// Controller/activo.controller.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../Config/permissions.php';
require_permission('activos');
require_once __DIR__ . '/../Config/database.php';
require_once __DIR__ . '/../Model/mactivo.php';

$database = new Database();
$db = $database->getConnection();

$activoModel = new ActivoModel($db);

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    try {
        if ($action == 'save') {
            $nombre_activo = trim($_POST['nombre_activo'] ?? '');
            $tipo_activo = trim($_POST['tipo_activo'] ?? '');
            
            $fecha_adquisicion = trim($_POST['fecha_adquisicion'] ?? '');
            $estado_operativo = trim($_POST['estado_operativo'] ?? '');

            if (empty($nombre_activo)) {
                echo json_encode(["status" => "error", "message" => "El nombre del activo es obligatorio."]);
                exit();
            }

            if (empty($tipo_activo)) {
                echo json_encode(["status" => "error", "message" => "Debe seleccionar un tipo de activo."]);
                exit();
            }

            if (!isset($_POST['valor_estimado']) || $_POST['valor_estimado'] === '' || floatval($_POST['valor_estimado']) <= 0) {
                echo json_encode(["status" => "error", "message" => "El valor estimado debe ser mayor a 0."]);
                exit();
            }

            if (empty($fecha_adquisicion)) {
                echo json_encode(["status" => "error", "message" => "La fecha de adquisición es obligatoria."]);
                exit();
            }

            if (empty($estado_operativo)) {
                echo json_encode(["status" => "error", "message" => "Debe seleccionar el estado operativo."]);
                exit();
            }

            $valor_estimado = (float)$_POST['valor_estimado'];

            // Validar que el nombre del activo no esté duplicado
            $id_activo_check = $_POST['id_activo'] ?? 0;
            $query_chk = "SELECT id_activo FROM activos WHERE LOWER(TRIM(nombre_activo)) = LOWER(TRIM(?)) AND id_activo != ? LIMIT 1";
            $stmt_chk = $db->prepare($query_chk);
            $stmt_chk->execute([$nombre_activo, $id_activo_check]);
            if ($stmt_chk->rowCount() > 0) {
                echo json_encode(["status" => "error", "message" => "Ya existe un activo registrado con el nombre '$nombre_activo'."]);
                exit();
            }

            $data = [
                'nombre_activo' => $nombre_activo,
                'tipo_activo' => $tipo_activo,
                'valor_estimado' => $valor_estimado,
                'fecha_adquisicion' => $fecha_adquisicion,
                'estado_operativo' => $estado_operativo,
                'observaciones' => $_POST['observaciones'] ?? ''
            ];
            
            if (!empty($_POST['id_activo'])) {
                // Actualizar
                $data['id_activo'] = $_POST['id_activo'];
                if ($activoModel->update($data)) {
                    echo json_encode(["status" => "success", "message" => "Activo actualizado correctamente."]);
                } else {
                    echo json_encode(["status" => "error", "message" => "No se pudo actualizar el activo."]);
                }
            } else {
                // Crear
                if ($activoModel->create($data)) {
                    echo json_encode(["status" => "success", "message" => "Activo registrado correctamente."]);
                } else {
                    echo json_encode(["status" => "error", "message" => "No se pudo registrar el activo."]);
                }
            }
            exit();
        } elseif ($action == 'delete') {
            $id_activo = $_POST['id_activo'] ?? 0;
            if ($id_activo > 0 && $activoModel->delete($id_activo)) {
                echo json_encode(["status" => "success", "message" => "Activo eliminado correctamente."]);
            } else {
                echo json_encode(["status" => "error", "message" => "No se pudo eliminar el activo."]);
            }
            exit();
        } elseif ($action == 'check_name') {
            $nombre_activo = trim($_REQUEST['nombre_activo'] ?? '');
            $id_activo = (int)($_REQUEST['id_activo'] ?? 0);

            if (!empty($nombre_activo)) {
                $query_chk = "SELECT id_activo FROM activos WHERE LOWER(TRIM(nombre_activo)) = LOWER(TRIM(?)) AND id_activo != ? LIMIT 1";
                $stmt_chk = $db->prepare($query_chk);
                $stmt_chk->execute([$nombre_activo, $id_activo]);
                if ($stmt_chk->rowCount() > 0) {
                    echo json_encode(["status" => "exists", "message" => "⚠️ Ya existe un activo registrado con este nombre."]);
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

// Peticiones GET
if ($action == 'get') {
    header('Content-Type: application/json');
    $id_activo = $_GET['id_activo'] ?? 0;
    $data = $activoModel->getById($id_activo);
    if ($data) {
        echo json_encode($data);
    } else {
        echo json_encode(["status" => "error", "message" => "Activo no encontrado."]);
    }
    exit();
}

// Carga normal de la vista
if (!$action) {
    $activos = $activoModel->getAll();
    $stats = $activoModel->getStats();
    include __DIR__ . '/../View/vactivo.php';
}
?>
