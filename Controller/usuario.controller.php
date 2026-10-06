<?php
// Controller/usuario.controller.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../Config/database.php';
require_once __DIR__ . '/../Model/musuario.php';

$database = new Database();
$db = $database->getConnection();

$usuarioModel = new UsuarioModel($db);

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

// Manejo de peticiones POST (AJAX)
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    try {
        if ($action == 'save') {
            $id_usuario = (int)($_POST['id_usuario'] ?? 0);
            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';
            $rol_sistema = trim($_POST['rol_sistema'] ?? 'Socio');
            $id_socio = !empty($_POST['id_socio']) ? (int)$_POST['id_socio'] : null;
            $activo = isset($_POST['activo']) ? (int)$_POST['activo'] : 1;

            // Validaciones
            if (empty($username)) {
                echo json_encode(["status" => "error", "message" => "El nombre de usuario es obligatorio."]);
                exit();
            }

            if (strlen($username) < 3) {
                echo json_encode(["status" => "error", "message" => "El nombre de usuario debe tener al menos 3 caracteres."]);
                exit();
            }

            $rolesValidos = ['Administrador', 'Presidente', 'Tesorero', 'Secretario', 'Socio'];
            if (!in_array($rol_sistema, $rolesValidos)) {
                echo json_encode(["status" => "error", "message" => "Debe seleccionar un rol de sistema válido."]);
                exit();
            }

            // Validar contraseña para nuevo usuario
            if ($id_usuario === 0 && empty($password)) {
                echo json_encode(["status" => "error", "message" => "La contraseña es obligatoria para nuevos usuarios."]);
                exit();
            }

            if (!empty($password) && strlen($password) < 4) {
                echo json_encode(["status" => "error", "message" => "La contraseña debe tener al menos 4 caracteres."]);
                exit();
            }

            // Validar si el username ya está en uso
            if ($usuarioModel->usernameExists($username, $id_usuario)) {
                echo json_encode(["status" => "error", "message" => "El nombre de usuario '$username' ya se encuentra registrado."]);
                exit();
            }

            // Validar si el socio ya tiene otro usuario asignado
            if ($id_socio !== null && $usuarioModel->socioHasUser($id_socio, $id_usuario)) {
                echo json_encode(["status" => "error", "message" => "El socio seleccionado ya tiene una cuenta de usuario asignada."]);
                exit();
            }

            $data = [
                'id_usuario'  => $id_usuario,
                'username'    => $username,
                'password'    => $password,
                'rol_sistema' => $rol_sistema,
                'id_socio'    => $id_socio,
                'activo'      => $activo
            ];

            if ($id_usuario > 0) {
                // Actualizar usuario
                if ($usuarioModel->update($data)) {
                    echo json_encode(["status" => "success", "message" => "Usuario actualizado correctamente."]);
                } else {
                    echo json_encode(["status" => "error", "message" => "No se pudo actualizar el usuario."]);
                }
            } else {
                // Crear usuario
                if ($usuarioModel->create($data)) {
                    echo json_encode(["status" => "success", "message" => "Usuario creado correctamente."]);
                } else {
                    echo json_encode(["status" => "error", "message" => "No se pudo crear el usuario."]);
                }
            }
            exit();

        } elseif ($action == 'delete') {
            $id_usuario = (int)($_POST['id_usuario'] ?? 0);

            // Prevenir eliminar la propia cuenta en uso
            if (isset($_SESSION['user_id']) && (int)$_SESSION['user_id'] === $id_usuario) {
                echo json_encode(["status" => "error", "message" => "No puedes eliminar tu propia cuenta de usuario en sesión actual."]);
                exit();
            }

            if ($id_usuario > 0 && $usuarioModel->delete($id_usuario)) {
                echo json_encode(["status" => "success", "message" => "Usuario eliminado correctamente."]);
            } else {
                echo json_encode(["status" => "error", "message" => "No se pudo eliminar el usuario."]);
            }
            exit();

        } elseif ($action == 'toggle_status') {
            $id_usuario = (int)($_POST['id_usuario'] ?? 0);
            $nuevo_estado = (int)($_POST['activo'] ?? 1);

            if (isset($_SESSION['user_id']) && (int)$_SESSION['user_id'] === $id_usuario && $nuevo_estado === 0) {
                echo json_encode(["status" => "error", "message" => "No puedes desactivar tu propia cuenta en sesión actual."]);
                exit();
            }

            if ($id_usuario > 0 && $usuarioModel->toggleStatus($id_usuario, $nuevo_estado)) {
                $estadoTexto = $nuevo_estado === 1 ? 'activado' : 'desactivado';
                echo json_encode(["status" => "success", "message" => "El usuario ha sido {$estadoTexto} correctamente."]);
            } else {
                echo json_encode(["status" => "error", "message" => "No se pudo cambiar el estado del usuario."]);
            }
            exit();

        } elseif ($action == 'check_username') {
            $username = trim($_POST['username'] ?? $_GET['username'] ?? '');
            $id_usuario = (int)($_POST['id_usuario'] ?? $_GET['id_usuario'] ?? 0);

            if (!empty($username) && $usuarioModel->usernameExists($username, $id_usuario)) {
                echo json_encode(["status" => "exists", "message" => "⚠️ El nombre de usuario '$username' ya existe."]);
                exit();
            }
            echo json_encode(["status" => "available", "message" => "Disponible"]);
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
    $id_usuario = (int)($_GET['id_usuario'] ?? 0);
    $data = $usuarioModel->getById($id_usuario);
    if ($data) {
        // Añadir lista de socios disponibles para este usuario
        $data['socios_disponibles'] = $usuarioModel->getSociosDisponibles($id_usuario);
        echo json_encode(["status" => "success", "data" => $data]);
    } else {
        echo json_encode(["status" => "error", "message" => "Usuario no encontrado."]);
    }
    exit();
}

if ($action == 'get_socios') {
    header('Content-Type: application/json');
    $id_usuario = (int)($_GET['id_usuario'] ?? 0);
    $socios = $usuarioModel->getSociosDisponibles($id_usuario);
    echo json_encode(["status" => "success", "data" => $socios]);
    exit();
}

// Carga normal de la vista
if (!$action) {
    $usuarios = $usuarioModel->getAll();
    $stats = $usuarioModel->getStats();
    $sociosDisponibles = $usuarioModel->getSociosDisponibles(0);
    include __DIR__ . '/../View/vusuario.php';
}
?>
