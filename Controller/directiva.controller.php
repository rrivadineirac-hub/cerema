<?php
// Controller/directiva.controller.php
require_once __DIR__ . '/../Config/permissions.php';
require_permission('directiva');
require_once __DIR__ . '/../Config/database.php';
require_once __DIR__ . '/../Model/mdirectiva.php';

$database = new Database();
$db = $database->getConnection();
$directivaModel = new DirectivaModel($db);

$action = isset($_GET['action']) ? $_GET['action'] : (isset($_POST['action']) ? $_POST['action'] : 'index');

switch($action) {
    case 'index':
        // Cargar listas necesarias para la tabla y el formulario modal
        $directivas = $directivaModel->getAll();
        $socios = $directivaModel->getSocios();
        $cargos = $directivaModel->getCargos();
        
        require_once __DIR__ . '/../View/vdirectiva.php';
        break;

    case 'get_registro':
        // Retornar JSON para el Modal de Editar
        if(isset($_GET['id'])) {
            $data = $directivaModel->getById($_GET['id']);
            echo json_encode($data);
        }
        break;

    case 'save':
        $data = [
            'id_socio' => $_POST['id_socio'],
            'id_cargo' => $_POST['id_cargo'],
            'gestion' => $_POST['gestion'],
            'fecha_inicio' => $_POST['fecha_inicio'],
            'fecha_fin' => !empty($_POST['fecha_fin']) ? $_POST['fecha_fin'] : null
        ];

        $exclude_id = !empty($_POST['id_directiva']) ? $_POST['id_directiva'] : null;

        if ($directivaModel->checkCargoOcupado($data['id_cargo'], $data['gestion'], $exclude_id)) {
            $success = false;
            $msg = "Este cargo ya está ocupado en la gestión " . $data['gestion'] . ". Por favor seleccione otro.";
        } else {
            if(!empty($_POST['id_directiva'])) {
                $data['id_directiva'] = $_POST['id_directiva'];
                $success = $directivaModel->update($data);
                $msg = "Rol actualizado exitosamente.";
            } else {
                $success = $directivaModel->create($data);
                $msg = "Rol asignado exitosamente.";
            }
        }

        // Respuesta AJAX
        if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            echo json_encode(['success' => $success, 'message' => $msg]);
        } else {
            header("Location: directiva.controller.php");
        }
        break;

    case 'delete':
        if(isset($_POST['id_directiva'])) {
            $success = $directivaModel->delete($_POST['id_directiva']);
            echo json_encode(['success' => $success, 'message' => 'Asignación eliminada.']);
        }
        break;

    default:
        header("Location: directiva.controller.php");
        break;
}
?>
