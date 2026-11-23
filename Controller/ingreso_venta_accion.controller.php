<?php
// Controller/ingreso_venta_accion.controller.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../Config/permissions.php';
// Exigir permiso para ingreso_venta_accion (o categorías/otros ingresos)
if (!check_permission('ingreso_venta_accion') && !check_permission('categorias_ingreso') && !check_permission('otros_ingresos')) {
    header("Location: ../View/dashboard.php?error=acceso_denegado");
    exit();
}

require_once __DIR__ . '/../Config/database.php';
require_once __DIR__ . '/../Model/mingreso_venta_accion.php';
require_once __DIR__ . '/../Model/msocio.php';

$database = new Database();
$db = $database->getConnection();

$ventaModel = new IngresoVentaAccionModel($db);
$socioModel = new SocioModel($db);

// Obtener gestión activa
$query_gestion = "SELECT id_gestion, gestion FROM gestiones WHERE estado = 'En Curso' LIMIT 1";
$stmt_g = $db->prepare($query_gestion);
$stmt_g->execute();
$gestion_activa = $stmt_g->fetch(PDO::FETCH_ASSOC);
$id_gestion_activa = $gestion_activa ? $gestion_activa['id_gestion'] : null;

// Obtener todas las gestiones para filtros
$query_all_g = "SELECT id_gestion, gestion, estado FROM gestiones ORDER BY gestion DESC, id_gestion DESC";
$stmt_all_g = $db->prepare($query_all_g);
$stmt_all_g->execute();
$todas_gestiones = $stmt_all_g->fetchAll(PDO::FETCH_ASSOC);

$action = $_REQUEST['action'] ?? '';

// Operaciones POST (AJAX)
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    try {
        if ($action === 'save') {
            $id_gestion = !empty($_POST['id_gestion']) ? (int)$_POST['id_gestion'] : $id_gestion_activa;
            if (!$id_gestion) {
                echo json_encode(["status" => "error", "message" => "No se pudo determinar la gestión contable activa."]);
                exit();
            }

            $comprador = trim($_POST['comprador'] ?? '');
            if (empty($comprador)) {
                echo json_encode(["status" => "error", "message" => "Debe ingresar el nombre del comprador o seleccionar un asociado."]);
                exit();
            }

            $cantidad = (int)($_POST['cantidad_acciones'] ?? 1);
            if ($cantidad <= 0) {
                echo json_encode(["status" => "error", "message" => "La cantidad de acciones debe ser mayor a 0."]);
                exit();
            }

            $monto_total = (float)($_POST['monto_total'] ?? 0);
            $precio_unitario = (float)($_POST['precio_unitario'] ?? 0);
            if ($monto_total <= 0 && $precio_unitario > 0) {
                $monto_total = $cantidad * $precio_unitario;
            }

            if ($monto_total <= 0) {
                echo json_encode(["status" => "error", "message" => "El monto total de la venta debe ser mayor a 0."]);
                exit();
            }

            $data = [
                'id_gestion' => $id_gestion,
                'id_socio' => !empty($_POST['id_socio']) ? (int)$_POST['id_socio'] : null,
                'comprador' => $comprador,
                'cantidad_acciones' => $cantidad,
                'numero_accion' => trim($_POST['numero_accion'] ?? ''),
                'precio_unitario' => $precio_unitario,
                'monto_total' => $monto_total,
                'fecha_venta' => !empty($_POST['fecha_venta']) ? $_POST['fecha_venta'] : date('Y-m-d'),
                'numero_recibo' => trim($_POST['numero_recibo'] ?? ''),
                'metodo_pago' => $_POST['metodo_pago'] ?? 'Efectivo',
                'observaciones' => trim($_POST['observaciones'] ?? ''),
                'estado' => $_POST['estado'] ?? 'Cobrado'
            ];

            if (!empty($_POST['id_venta_accion'])) {
                $data['id_venta_accion'] = (int)$_POST['id_venta_accion'];
                if ($ventaModel->update($data)) {
                    echo json_encode(["status" => "success", "message" => "Venta de acción actualizada exitosamente."]);
                } else {
                    echo json_encode(["status" => "error", "message" => "No se pudo actualizar el registro."]);
                }
            } else {
                if ($ventaModel->create($data)) {
                    echo json_encode(["status" => "success", "message" => "Venta de acción registrada exitosamente."]);
                } else {
                    echo json_encode(["status" => "error", "message" => "No se pudo guardar la venta de acción."]);
                }
            }
            exit();

        } elseif ($action === 'delete') {
            $id = (int)($_POST['id_venta_accion'] ?? 0);
            if ($id > 0 && $ventaModel->delete($id)) {
                echo json_encode(["status" => "success", "message" => "Registro de venta eliminado exitosamente."]);
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

// Operaciones GET (AJAX)
if ($action === 'get') {
    header('Content-Type: application/json');
    $id = (int)($_GET['id_venta_accion'] ?? 0);
    $data = $ventaModel->getById($id);
    if ($data) {
        echo json_encode($data);
    } else {
        echo json_encode(["status" => "error", "message" => "Registro no encontrado."]);
    }
    exit();
}

// Renderizado Normal de la Vista
$filtro_gestion = isset($_GET['id_gestion']) ? $_GET['id_gestion'] : ($id_gestion_activa ?: 'all');
$ventas = $ventaModel->getAll($filtro_gestion === 'all' ? null : $filtro_gestion);
$estadisticas = $ventaModel->getEstadisticas($filtro_gestion === 'all' ? null : $filtro_gestion);

// Obtener lista completa de socios para el selector del modal
$sociosList = $socioModel->getAll()->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/../View/vingreso_venta_accion.php';
?>

