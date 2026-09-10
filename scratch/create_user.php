<?php
require_once 'Config/database.php';
$database = new Database();
$db = $database->getConnection();

$username = 'Infoser76';
$password = 'Serinfo*76*01';
$hash = password_hash($password, PASSWORD_DEFAULT);
$role = 'Administrador';

try {
    $query = "INSERT INTO usuarios_sistema (username, password_hash, rol_sistema, activo) VALUES (:username, :hash, :rol, 1)";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':hash', $hash);
    $stmt->bindParam(':rol', $role);
    
    if ($stmt->execute()) {
        echo "Usuario $username creado exitosamente.\n";
    } else {
        echo "Error al crear el usuario.\n";
    }
} catch (Exception $e) {
    echo "Excepcion: " . $e->getMessage() . "\n";
}
?>
