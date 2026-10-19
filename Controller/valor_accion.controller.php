<?php
// Controller/valor_accion.controller.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../Config/permissions.php';
require_permission('valor_accion');
require_once __DIR__ . '/../Config/database.php';
require_once __DIR__ . '/../Model/mvalor_accion.php';
require_once __DIR__ . '/../Model/msocio.php';

$database = new Database();
$db = $database->getConnection();

$valorModel = new ValorAccionModel($db);
$socioModel = new SocioModel($db);

$action = $_REQUEST['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    try {
        if ($action === 'save') {
            $id_valor      = (int)($_POST['id_valor'] ?? 0);
            $id_socio      = (int)($_POST['id_socio'] ?? 0);
            $numero_accion = (int)($_POST['numero_accion'] ?? 0);
            $valor         = (float)($_POST['valor'] ?? 0);
            $fecha_valor   = trim($_POST['fecha_valor'] ?? date('Y-m-d'));
            $observaciones = trim($_POST['observaciones'] ?? '');

            if ($id_socio <= 0) {
                echo json_encode(["status" => "error", "message" => "Debe seleccionar un asociado."]); exit();
            }
            $resumen = $valorModel->getResumenSocio($id_socio);
            if ($numero_accion < 1 || $numero_accion > $resumen['acciones']) {
                echo json_encode(["status" => "error", "message" => "Número de acción inválido."]); exit();
            }
            if ($valor <= 0) {
                echo json_encode(["status" => "error", "message" => "El valor de la acción debe ser mayor a 0."]); exit();
            }
            if ($valorModel->existsAccion($id_socio, $numero_accion, $id_valor)) {
                echo json_encode(["status" => "error", "message" => "La Acción N° $numero_accion ya tiene un valor registrado. Edítelo en la tabla."]); exit();
            }

            $data = compact('id_socio', 'numero_accion', 'valor', 'fecha_valor', 'observaciones');

            if ($id_valor > 0) {
                $data['id_valor'] = $id_valor;
                $ok = $valorModel->update($data);
                $msg = $ok ? "Valor de la acción actualizado correctamente." : "No se pudo actualizar.";
            } else {
                $ok = $valorModel->create($data);
                $msg = $ok ? "Valor de la acción registrado correctamente." : "No se pudo registrar.";
            }
            echo json_encode(["status" => $ok ? "success" : "error", "message" => $msg]);
            exit();

        } elseif ($action === 'delete') {
            $id_valor = (int)($_POST['id_valor'] ?? 0);
            $ok = $id_valor > 0 && $valorModel->delete($id_valor);
            echo json_encode(["status" => $ok ? "success" : "error", "message" => $ok ? "Registro eliminado correctamente." : "No se pudo eliminar el registro."]);
            exit();
        }
    } catch (Exception $e) {
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
        exit();
    }
}

if ($action === 'get') {
    header('Content-Type: application/json');
    $data = $valorModel->getById((int)($_GET['id_valor'] ?? 0));
    echo json_encode($data ?: ["status" => "error", "message" => "Registro no encontrado."]);
    exit();
}

// Carga de la vista
$socios_stmt = $socioModel->getActivos();
$socios = $socios_stmt ? $socios_stmt->fetchAll(PDO::FETCH_ASSOC) : [];

$selected_socio_id = (isset($_GET['id_socio']) && $_GET['id_socio'] !== '') ? (int)$_GET['id_socio'] : null;
$socio_actual = null;
$valores = [];
$resumen_socio = null;
$acciones_map = [];

if ($selected_socio_id) {
    $socio_actual = $socioModel->getById($selected_socio_id);
    if ($socio_actual) {
        $valores = $valorModel->getBySocioId($selected_socio_id);
        $resumen_socio = $valorModel->getResumenSocio($selected_socio_id);
        foreach ($valores as $v) {
            $acciones_map[(int)$v['numero_accion']] = $v;
        }
    }
}

include __DIR__ . '/../View/vvalor_accion.php';
?>
