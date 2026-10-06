<?php
// Controller/socio.controller.php
require_once __DIR__ . '/../Config/permissions.php';
require_permission('socios');
require_once __DIR__ . '/../Config/database.php';
require_once __DIR__ . '/../Model/msocio.php';

$database = new Database();
$db = $database->getConnection();
$socioModel = new SocioModel($db);

$action = isset($_GET['action']) ? $_GET['action'] : (isset($_POST['action']) ? $_POST['action'] : 'index');

switch($action) {
    case 'index':
        // Obtener la lista completa de socios (Activos, Inactivos y Pasivos) y cargar la vista
        $socios = $socioModel->getAll();
        require_once __DIR__ . '/../View/vsocio.php';
        break;

    case 'get_socio':
        // Obtener datos de un socio por ID para llenar el Modal de edición (JSON)
        if(isset($_GET['id'])) {
            $data = $socioModel->getById($_GET['id']);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($data);
        }
        break;

    case 'save':
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
        if ($isAjax) {
            header('Content-Type: application/json; charset=utf-8');
        }

        try {
            $data = [
                'ci' => trim($_POST['ci'] ?? ''),
                'complemento' => trim($_POST['complemento'] ?? ''),
                'ap_paterno' => trim($_POST['ap_paterno'] ?? ''),
                'ap_materno' => trim($_POST['ap_materno'] ?? ''),
                'nombre' => trim($_POST['nombre'] ?? ''),
                'telefono' => trim($_POST['telefono'] ?? ''),
                'correo' => trim($_POST['correo'] ?? ''),
                'fecha_ingreso' => $_POST['fecha_ingreso'] ?? date('Y-m-d'),
                'estado' => $_POST['estado'] ?? 'Activo',
                'acciones' => (int)($_POST['acciones'] ?? 1),
                'cuota_inicial' => (float)($_POST['cuota_inicial'] ?? 0)
            ];

            if (empty($data['ci'])) {
                if ($isAjax) {
                    echo json_encode(['success' => false, 'message' => 'El número de CI es obligatorio.']);
                } else {
                    header("Location: socio.controller.php?msg=" . urlencode('El número de CI es obligatorio.'));
                }
                break;
            }

            if(!empty($_POST['id_socio'])) {
                // Actualizar
                $data['id_socio'] = $_POST['id_socio'];
                $result = $socioModel->update($data);
                $defaultMsg = "Socio actualizado exitosamente.";
            } else {
                // Crear
                $result = $socioModel->create($data);
                $defaultMsg = "Socio registrado exitosamente.";
            }

            if (is_array($result)) {
                $success = $result['success'];
                $msg = $success ? $defaultMsg : ($result['message'] ?? 'Ocurrió un error al guardar los datos.');
            } else {
                $success = (bool)$result;
                $msg = $success ? $defaultMsg : 'Ocurrió un error al guardar los datos.';
            }

            if($isAjax) {
                echo json_encode(['success' => $success, 'message' => $msg]);
            } else {
                header("Location: socio.controller.php?msg=" . urlencode($msg));
            }
        } catch (Throwable $e) {
            if ($isAjax) {
                echo json_encode(['success' => false, 'message' => 'Error en el servidor: ' . $e->getMessage()]);
            } else {
                header("Location: socio.controller.php?msg=" . urlencode($e->getMessage()));
            }
        }
        break;

    case 'delete':
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
        if ($isAjax) {
            header('Content-Type: application/json; charset=utf-8');
        }

        try {
            if(isset($_POST['id_socio'])) {
                $result = $socioModel->delete($_POST['id_socio']);
                if (is_array($result)) {
                    $response = $result;
                } else {
                    $response = ['success' => (bool)$result, 'message' => $result ? 'Socio eliminado.' : 'No se pudo eliminar el socio.'];
                }
            } else {
                $response = ['success' => false, 'message' => 'ID de socio no especificado.'];
            }

            if ($isAjax) {
                echo json_encode($response);
            } else {
                header("Location: socio.controller.php?msg=" . urlencode($response['message']));
            }
        } catch (Throwable $e) {
            if ($isAjax) {
                echo json_encode(['success' => false, 'message' => 'Error al eliminar: ' . $e->getMessage()]);
            } else {
                header("Location: socio.controller.php?msg=" . urlencode($e->getMessage()));
            }
        }
        break;

    default:
        $socios = $socioModel->getAll();
        require_once __DIR__ . '/../View/vsocio.php';
        break;
}
?>
