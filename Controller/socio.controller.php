<?php
// Controller/socio.controller.php
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
            echo json_encode($data);
        }
        break;

    case 'save':
        // Determina si es Crear o Actualizar según si viene un ID
        $data = [
            'ci' => $_POST['ci'],
            'ap_paterno' => $_POST['ap_paterno'],
            'ap_materno' => $_POST['ap_materno'],
            'nombre' => $_POST['nombre'],
            'telefono' => $_POST['telefono'],
            'correo' => $_POST['correo'],
            'fecha_ingreso' => $_POST['fecha_ingreso'],
            'estado' => $_POST['estado'],
            'acciones' => $_POST['acciones'],
            'cuota_inicial' => (float)($_POST['cuota_inicial'] ?? 0)
        ];

        if(!empty($_POST['id_socio'])) {
            // Actualizar
            $data['id_socio'] = $_POST['id_socio'];
            $success = $socioModel->update($data);
            $msg = "Socio actualizado exitosamente.";
        } else {
            // Crear
            $success = $socioModel->create($data);
            $msg = "Socio registrado exitosamente.";
        }

        // Retornar JSON (Ajax request) o Redirigir
        if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            echo json_encode(['success' => $success, 'message' => $msg]);
        } else {
            header("Location: socio.controller.php?msg=" . urlencode($msg));
        }
        break;

    case 'delete':
        if(isset($_POST['id_socio'])) {
            $success = $socioModel->delete($_POST['id_socio']);
            echo json_encode(['success' => $success, 'message' => 'Socio eliminado.']);
        }
        break;

    default:
        $socios = $socioModel->getAll();
        require_once __DIR__ . '/../View/vsocio.php';
        break;
}
?>
