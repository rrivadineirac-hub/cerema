<?php
// Controller/evento.controller.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../Config/database.php';
require_once __DIR__ . '/../Model/mevento.php';

$database = new Database();
$db = $database->getConnection();

$eventoModel = new EventoModel($db);

// Obtener gestión activa
$query_gestion = "SELECT id_gestion FROM gestiones WHERE estado = 'En Curso' LIMIT 1";
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
                echo json_encode(["status" => "error", "message" => "No hay una gestión activa. No se pueden registrar eventos."]);
                exit();
            }

            $tipo_final = $_POST['tipo_evento'] ?? '';
            if ($tipo_final === 'Otros Eventos' && !empty($_POST['otro_tipo_evento'])) {
                $tipo_final = mb_strtoupper($_POST['otro_tipo_evento'], 'UTF-8');
            }

            $data = [
                'id_gestion' => $id_gestion_activa,
                'titulo' => $tipo_final,
                'tipo_evento' => $tipo_final,
                'fecha_evento' => $_POST['fecha_evento'] ?? date('Y-m-d'),
                'hora_evento' => $_POST['hora_evento'] ?? '',
                'lugar' => $_POST['lugar'] ?? '',
                'descripcion' => $_POST['descripcion'] ?? '',
                'estado' => $_POST['estado'] ?? 'Programado'
            ];
            
            if (!empty($_POST['id_evento'])) {
                // Update
                $data['id_evento'] = $_POST['id_evento'];
                if ($eventoModel->update($data)) {
                    echo json_encode(["status" => "success", "message" => "Evento actualizado correctamente."]);
                } else {
                    echo json_encode(["status" => "error", "message" => "No se pudo actualizar el evento."]);
                }
            } else {
                // Create
                if ($eventoModel->create($data)) {
                    echo json_encode(["status" => "success", "message" => "Evento registrado correctamente."]);
                } else {
                    echo json_encode(["status" => "error", "message" => "No se pudo registrar el evento."]);
                }
            }
        } elseif ($action == 'delete') {
            $id = isset($_POST['id_evento']) ? $_POST['id_evento'] : null;
            if ($id && $eventoModel->delete($id)) {
                echo json_encode(["status" => "success", "message" => "Evento eliminado correctamente."]);
            } else {
                echo json_encode(["status" => "error", "message" => "No se pudo eliminar el evento."]);
            }
        }
    } catch(Exception $e) {
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    }
    exit();
}

// GET Requests
if ($action == 'get' && isset($_GET['id_evento'])) {
    header('Content-Type: application/json');
    $id = $_GET['id_evento'];
    $evento = $eventoModel->getById($id);
    if ($evento) {
        echo json_encode($evento);
    } else {
        echo json_encode(["status" => "error", "message" => "Evento no encontrado."]);
    }
    exit();
}

// Renderizar Vista
if ($id_gestion_activa) {
    $eventos = $eventoModel->getByGestionId($id_gestion_activa);
} else {
    $eventos = [];
}
include '../View/vevento.php';
?>
