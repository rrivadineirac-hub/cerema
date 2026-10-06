<?php
session_start();
require_once __DIR__ . '/../Config/database.php';
require_once __DIR__ . '/../Model/mlogin.php';

$action = isset($_GET['action']) ? $_GET['action'] : (isset($_POST['action']) ? $_POST['action'] : 'view');

if ($action == 'view') {
    // Si ya tiene sesión, no necesita ver el login
    if (isset($_SESSION['user_id'])) {
        header("Location: ../View/dashboard.php");
        exit();
    }
    require_once __DIR__ . '/../View/vlogin.php';
}
elseif ($action == 'login') {
    $database = new Database();
    $db = $database->getConnection();
    
    if (!$db) {
        $msg = 'Error de conexión a la BD';
        if (!empty($database->error)) {
            $msg .= ': ' . $database->error;
        }
        echo json_encode(['success' => false, 'message' => $msg]);
        exit();
    }

    $loginModel = new LoginModel($db);
    
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // TRUCO PARA DESARROLLO: Si la tabla de usuarios está vacía, se auto-crea un admin con 12345
    try {
        $stmt = $db->query("SELECT count(*) FROM usuarios_sistema");
        if ($stmt && $stmt->fetchColumn() == 0 && $username === 'admin' && $password === '12345') {
            $hash = password_hash('12345', PASSWORD_DEFAULT);
            $db->query("INSERT INTO usuarios_sistema (username, password_hash, rol_sistema, activo) VALUES ('admin', '$hash', 'Administrador', 1)");
        }
    } catch(Exception $e) {}

    // Verificación de bloqueo temporal (Lockout)
    if (isset($_SESSION['lockout_time'])) {
        $remaining = $_SESSION['lockout_time'] - time();
        if ($remaining > 0) {
            $minutes = ceil($remaining / 60);
            echo json_encode(['success' => false, 'message' => "Por seguridad, el sistema está bloqueado por {$minutes} minutos."]);
            exit();
        } else {
            // El bloqueo expiró
            unset($_SESSION['lockout_time']);
            $_SESSION['login_attempts'] = 0;
        }
    }

    $user = $loginModel->authenticate($username, $password);
    
    if ($user) {
        // Reiniciar intentos si el login es exitoso
        $_SESSION['login_attempts'] = 0;
        unset($_SESSION['lockout_time']);

        // Establecer variables de sesión
        $_SESSION['user_id'] = $user['id_usuario'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['rol'] = $user['rol_sistema'];
        $_SESSION['last_activity'] = time(); // Guardar timestamp inicial
        
        // Si el usuario está vinculado a un socio, guardamos su nombre, sino, mostramos su rol o username
        $_SESSION['nombre_usuario'] = !empty($user['nombre']) ? $user['nombre'] . ' ' . $user['ap_paterno'] : $user['username'];
        
        echo json_encode(['success' => true, 'redirect' => '../View/dashboard.php']);
    } else {
        // Aumentar contador de intentos
        if (!isset($_SESSION['login_attempts'])) {
            $_SESSION['login_attempts'] = 0;
        }
        $_SESSION['login_attempts']++;
        
        if ($_SESSION['login_attempts'] >= 3) {
            $_SESSION['lockout_time'] = time() + 300; // 5 minutos = 300 segundos
            echo json_encode(['success' => false, 'message' => "Se superó el límite de intentos (3). Sistema bloqueado por 5 minutos."]);
        } else {
            $intentos_restantes = 3 - $_SESSION['login_attempts'];
            echo json_encode(['success' => false, 'message' => "Credenciales incorrectas. Te quedan {$intentos_restantes} intento(s)."]);
        }
    }
}
elseif ($action == 'logout') {
    session_destroy();
    header("Location: login.controller.php");
    exit();
}
?>
