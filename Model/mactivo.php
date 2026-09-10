<?php
// Model/mactivo.php

class ActivoModel {
    private $conn;
    private $table_name = "activos";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Obtener todos los activos
    public function getAll() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY id_activo DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Obtener un activo por su ID
    public function getById($id_activo) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id_activo = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id_activo);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Crear un nuevo activo
    public function create($data) {
        $query = "INSERT INTO " . $this->table_name . " 
                  (nombre_activo, tipo_activo, valor_estimado, fecha_adquisicion, estado_operativo, observaciones) 
                  VALUES (:nombre_activo, :tipo_activo, :valor_estimado, :fecha_adquisicion, :estado_operativo, :observaciones)";
        
        $stmt = $this->conn->prepare($query);

        $nombre_activo = htmlspecialchars(strip_tags($data['nombre_activo']));
        $tipo_activo = htmlspecialchars(strip_tags($data['tipo_activo']));
        $valor_estimado = (float)($data['valor_estimado'] ?? 0);
        $fecha_adquisicion = !empty($data['fecha_adquisicion']) ? htmlspecialchars(strip_tags($data['fecha_adquisicion'])) : null;
        $estado_operativo = htmlspecialchars(strip_tags($data['estado_operativo'] ?? 'Bueno'));
        $observaciones = !empty($data['observaciones']) ? htmlspecialchars(strip_tags($data['observaciones'])) : null;

        $stmt->bindParam(":nombre_activo", $nombre_activo);
        $stmt->bindParam(":tipo_activo", $tipo_activo);
        $stmt->bindParam(":valor_estimado", $valor_estimado);
        $stmt->bindParam(":fecha_adquisicion", $fecha_adquisicion);
        $stmt->bindParam(":estado_operativo", $estado_operativo);
        $stmt->bindParam(":observaciones", $observaciones);

        return $stmt->execute();
    }

    // Actualizar un activo existente
    public function update($data) {
        $query = "UPDATE " . $this->table_name . " 
                  SET nombre_activo = :nombre_activo, 
                      tipo_activo = :tipo_activo, 
                      valor_estimado = :valor_estimado, 
                      fecha_adquisicion = :fecha_adquisicion, 
                      estado_operativo = :estado_operativo, 
                      observaciones = :observaciones
                  WHERE id_activo = :id_activo";
        
        $stmt = $this->conn->prepare($query);

        $id_activo = htmlspecialchars(strip_tags($data['id_activo']));
        $nombre_activo = htmlspecialchars(strip_tags($data['nombre_activo']));
        $tipo_activo = htmlspecialchars(strip_tags($data['tipo_activo']));
        $valor_estimado = (float)($data['valor_estimado'] ?? 0);
        $fecha_adquisicion = !empty($data['fecha_adquisicion']) ? htmlspecialchars(strip_tags($data['fecha_adquisicion'])) : null;
        $estado_operativo = htmlspecialchars(strip_tags($data['estado_operativo'] ?? 'Bueno'));
        $observaciones = !empty($data['observaciones']) ? htmlspecialchars(strip_tags($data['observaciones'])) : null;

        $stmt->bindParam(":id_activo", $id_activo);
        $stmt->bindParam(":nombre_activo", $nombre_activo);
        $stmt->bindParam(":tipo_activo", $tipo_activo);
        $stmt->bindParam(":valor_estimado", $valor_estimado);
        $stmt->bindParam(":fecha_adquisicion", $fecha_adquisicion);
        $stmt->bindParam(":estado_operativo", $estado_operativo);
        $stmt->bindParam(":observaciones", $observaciones);

        return $stmt->execute();
    }

    // Eliminar un activo
    public function delete($id_activo) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id_activo = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id_activo);
        return $stmt->execute();
    }

    // Resumen de estadísticas de activos
    public function getStats() {
        $query = "SELECT 
                    COUNT(*) as total_activos,
                    COALESCE(SUM(valor_estimado), 0) as valor_total,
                    SUM(CASE WHEN estado_operativo IN ('Excelente', 'Bueno') THEN 1 ELSE 0 END) as operativos,
                    SUM(CASE WHEN estado_operativo IN ('Regular', 'En Mantenimiento', 'Fuera de Servicio') THEN 1 ELSE 0 END) as observados
                  FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
