<?php
// Controller/categorias_ingreso.controller.php
require_once __DIR__ . '/../Config/database.php';
require_once __DIR__ . '/../Model/mcategorias_ingreso.php';

$database = new Database();
$db = $database->getConnection();
$categoriasModel = new CategoriasIngresoModel($db);

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    try {
        if ($action == 'update_monto') {
            $id_cat_ingreso = $_POST['id_cat_ingreso'] ?? '';
            $monto_sugerido = $_POST['monto_sugerido'] ?? 0;
            
            if (!empty($id_cat_ingreso)) {
                if ($categoriasModel->updateMonto($id_cat_ingreso, $monto_sugerido)) {
                    echo json_encode(['status' => 'success', 'message' => 'Monto actualizado correctamente']);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'No se pudo actualizar el monto']);
                }
            } else {
                echo json_encode(['status' => 'error', 'message' => 'ID de categoría requerido']);
            }
        } elseif ($action == 'create' || $action == 'update') {
            $data = [
                'id_cat_ingreso' => $_POST['id_cat_ingreso'] ?? '',
                'id_gestion' => $_POST['id_gestion'] ?? null,
                'nombre' => $_POST['nombre'] ?? '',
                'requiere_socio' => $_POST['requiere_socio'] ?? 1,
                'monto_sugerido' => $_POST['monto_mensual'] ?? $_POST['monto_sugerido'] ?? 0,
                'descripcion' => $_POST['descripcion'] ?? '',
                'anio_inicio' => $_POST['anio_inicio'] ?? null,
                'mes_inicio' => $_POST['mes_inicio'] ?? '',
                'anio_fin' => $_POST['anio_fin'] ?? null,
                'mes_fin' => $_POST['mes_fin'] ?? '',
                'monto_mensual' => $_POST['monto_mensual'] ?? 0,
                'monto_total' => $_POST['monto_total'] ?? 0
            ];
            
            if ($action == 'update' && !empty($data['id_cat_ingreso'])) {
                if ($categoriasModel->update($data)) {
                    echo json_encode(['status' => 'success', 'message' => 'Configuración de ingreso actualizada']);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'No se pudo actualizar el ingreso']);
                }
            } else {
                if ($categoriasModel->create($data)) {
                    echo json_encode(['status' => 'success', 'message' => 'Configuración de ingreso creada exitosamente']);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'No se pudo crear el ingreso']);
                }
            }
        } elseif ($action == 'delete') {
            $id_cat_ingreso = $_POST['id_cat_ingreso'] ?? '';
            if (!empty($id_cat_ingreso)) {
                if ($categoriasModel->delete($id_cat_ingreso)) {
                    echo json_encode(['status' => 'success', 'message' => 'Categoría eliminada correctamente']);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'No se pudo eliminar la categoría']);
                }
            } else {
                echo json_encode(['status' => 'error', 'message' => 'ID de categoría requerido']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Acción no válida']);
        }
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}

// Para peticiones GET AJAX (obtener un registro)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $action == 'get') {
    header('Content-Type: application/json');
    $id = isset($_GET['id_cat_ingreso']) ? $_GET['id_cat_ingreso'] : die();
    $categoria = $categoriasModel->getById($id);
    
    if ($categoria) {
        echo json_encode($categoria);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Categoría no encontrada']);
    }
    exit;
}

// Obtener lista de gestiones en orden descendente cronológico (gestiones superiores por encima de la actual)
$queryGestiones = "SELECT * FROM gestiones ORDER BY gestion DESC";
$stmtGestiones = $db->prepare($queryGestiones);
$stmtGestiones->execute();
$gestiones_list = $stmtGestiones->fetchAll(PDO::FETCH_ASSOC);

// Determinar la gestión seleccionada
$id_gestion_selected = isset($_GET['id_gestion']) ? $_GET['id_gestion'] : null;

if (!$id_gestion_selected) {
    // Buscar la gestión "En Curso" por defecto
    foreach ($gestiones_list as $g) {
        if ($g['estado'] == 'En Curso') {
            $id_gestion_selected = $g['id_gestion'];
            break;
        }
    }
    // Si sigue sin encontrarse, mostrar "all" para ver todas las categorías configuradas
    if (!$id_gestion_selected) {
        $id_gestion_selected = 'all';
    }
}

// Obtener categorías filtradas por gestión (o todas si id_gestion_selected es 'all')
$stmt = $categoriasModel->getAll($id_gestion_selected);
$categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Si para la gestión en curso no hay categorías, pero existen categorías globales/otras gestiones, mostrar todas por defecto
if (empty($categorias) && !isset($_GET['id_gestion'])) {
    $id_gestion_selected = 'all';
    $stmt = $categoriasModel->getAll('all');
    $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Cargar la vista
require_once __DIR__ . '/../View/vcategorias_ingreso.php';
?>
