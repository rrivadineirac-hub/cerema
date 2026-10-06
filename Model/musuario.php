<?php
// Model/musuario.php

class UsuarioModel {
    private $conn;
    private $table_name = "usuarios_sistema";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Obtener todos los usuarios con información del socio enlazado (si existe)
    public function getAll() {
        $query = "SELECT u.*, 
                         s.nombre AS socio_nombre, 
                         s.ap_paterno AS socio_ap_paterno, 
                         s.ap_materno AS socio_ap_materno,
                         s.ci AS socio_ci
                  FROM " . $this->table_name . " u 
                  LEFT JOIN asociados s ON u.id_socio = s.id_socio 
                  ORDER BY u.id_usuario DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Obtener un usuario por su ID
    public function getById($id_usuario) {
        $query = "SELECT u.*, 
                         s.nombre AS socio_nombre, 
                         s.ap_paterno AS socio_ap_paterno, 
                         s.ap_materno AS socio_ap_materno,
                         s.ci AS socio_ci
                  FROM " . $this->table_name . " u 
                  LEFT JOIN asociados s ON u.id_socio = s.id_socio 
                  WHERE u.id_usuario = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id_usuario, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Verificar si el username ya está registrado por otro usuario
    public function usernameExists($username, $id_usuario_actual = 0) {
        $query = "SELECT id_usuario FROM " . $this->table_name . " 
                  WHERE LOWER(TRIM(username)) = LOWER(TRIM(:username)) 
                  AND id_usuario != :id_usuario LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":username", $username);
        $stmt->bindParam(":id_usuario", $id_usuario_actual, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    // Verificar si un socio ya tiene una cuenta de usuario asignada
    public function socioHasUser($id_socio, $id_usuario_actual = 0) {
        if (empty($id_socio)) return false;
        $query = "SELECT id_usuario FROM " . $this->table_name . " 
                  WHERE id_socio = :id_socio 
                  AND id_usuario != :id_usuario LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id_socio", $id_socio, PDO::PARAM_INT);
        $stmt->bindParam(":id_usuario", $id_usuario_actual, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    // Obtener socios disponibles (que no tienen usuario asignado, o el socio actualmente asignado al usuario en edición)
    public function getSociosDisponibles($id_usuario_actual = 0) {
        $query = "SELECT s.id_socio, s.nombre, s.ap_paterno, s.ap_materno, s.ci 
                  FROM asociados s 
                  WHERE s.id_socio NOT IN (
                      SELECT u.id_socio FROM " . $this->table_name . " u 
                      WHERE u.id_socio IS NOT NULL 
                      AND u.id_usuario != :id_usuario
                  )
                  ORDER BY s.ap_paterno ASC, s.ap_materno ASC, s.nombre ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id_usuario", $id_usuario_actual, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Crear un nuevo usuario
    public function create($data) {
        $query = "INSERT INTO " . $this->table_name . " 
                  (id_socio, username, password_hash, rol_sistema, activo) 
                  VALUES (:id_socio, :username, :password_hash, :rol_sistema, :activo)";
        
        $stmt = $this->conn->prepare($query);

        $id_socio = !empty($data['id_socio']) ? (int)$data['id_socio'] : null;
        $username = trim($data['username']);
        $password_hash = password_hash($data['password'], PASSWORD_DEFAULT);
        $rol_sistema = htmlspecialchars(strip_tags($data['rol_sistema'] ?? 'Socio'));
        $activo = isset($data['activo']) ? (int)$data['activo'] : 1;

        $stmt->bindParam(":id_socio", $id_socio, is_null($id_socio) ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $stmt->bindParam(":username", $username);
        $stmt->bindParam(":password_hash", $password_hash);
        $stmt->bindParam(":rol_sistema", $rol_sistema);
        $stmt->bindParam(":activo", $activo, PDO::PARAM_INT);

        return $stmt->execute();
    }

    // Actualizar un usuario existente
    public function update($data) {
        if (!empty($data['password'])) {
            $query = "UPDATE " . $this->table_name . " 
                      SET id_socio = :id_socio, 
                          username = :username, 
                          password_hash = :password_hash, 
                          rol_sistema = :rol_sistema, 
                          activo = :activo 
                      WHERE id_usuario = :id_usuario";
        } else {
            $query = "UPDATE " . $this->table_name . " 
                      SET id_socio = :id_socio, 
                          username = :username, 
                          rol_sistema = :rol_sistema, 
                          activo = :activo 
                      WHERE id_usuario = :id_usuario";
        }

        $stmt = $this->conn->prepare($query);

        $id_usuario = (int)$data['id_usuario'];
        $id_socio = !empty($data['id_socio']) ? (int)$data['id_socio'] : null;
        $username = trim($data['username']);
        $rol_sistema = htmlspecialchars(strip_tags($data['rol_sistema']));
        $activo = isset($data['activo']) ? (int)$data['activo'] : 1;

        $stmt->bindParam(":id_usuario", $id_usuario, PDO::PARAM_INT);
        $stmt->bindParam(":id_socio", $id_socio, is_null($id_socio) ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $stmt->bindParam(":username", $username);
        $stmt->bindParam(":rol_sistema", $rol_sistema);
        $stmt->bindParam(":activo", $activo, PDO::PARAM_INT);

        if (!empty($data['password'])) {
            $password_hash = password_hash($data['password'], PASSWORD_DEFAULT);
            $stmt->bindParam(":password_hash", $password_hash);
        }

        return $stmt->execute();
    }

    // Cambiar estado activo/inactivo de un usuario
    public function toggleStatus($id_usuario, $nuevo_estado) {
        $query = "UPDATE " . $this->table_name . " SET activo = :activo WHERE id_usuario = :id_usuario";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":activo", $nuevo_estado, PDO::PARAM_INT);
        $stmt->bindParam(":id_usuario", $id_usuario, PDO::PARAM_INT);
        return $stmt->execute();
    }

    // Eliminar un usuario
    public function delete($id_usuario) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id_usuario = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id_usuario, PDO::PARAM_INT);
        return $stmt->execute();
    }

    // Obtener estadísticas de usuarios para tarjetas del dashboard
    public function getStats() {
        $query = "SELECT 
                    COUNT(*) as total_usuarios,
                    SUM(CASE WHEN activo = 1 THEN 1 ELSE 0 END) as activos,
                    SUM(CASE WHEN activo = 0 THEN 1 ELSE 0 END) as inactivos,
                    SUM(CASE WHEN rol_sistema = 'Administrador' THEN 1 ELSE 0 END) as administradores,
                    SUM(CASE WHEN id_socio IS NOT NULL THEN 1 ELSE 0 END) as enlazados_socio
                  FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
