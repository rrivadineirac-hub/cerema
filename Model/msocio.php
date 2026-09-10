<?php
// Model/msocio.php
class SocioModel {
    private $conn;
    private $table_name = "asociados";

    public function __construct($db) {
        $this->conn = $db;
        // Configurar el tipo ENUM exacto para los tres estados: Activo, Inactivo y Pasivo y asegurar columna cuota_inicial
        try {
            $this->conn->exec("ALTER TABLE asociados MODIFY COLUMN estado ENUM('Activo', 'Inactivo', 'Pasivo') NOT NULL DEFAULT 'Activo'");
            $this->conn->exec("UPDATE asociados SET estado = 'Pasivo' WHERE estado = 'Moroso'");
            $this->conn->exec("ALTER TABLE asociados ADD COLUMN cuota_inicial DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER acciones");
        } catch (Exception $e) {
            // Silencioso si ya está configurado
        }
    }

    // Obtener todos los socios
    public function getAll() {
        $query = "SELECT s.*, 
                         COALESCE((SELECT SUM(c.monto) FROM cuota_inicial_pagos c WHERE c.id_socio = s.id_socio AND (c.estado = 'Pagado' OR c.estado IS NULL OR c.estado = '' OR LOWER(c.estado) = 'pagado')), 0.00) as cuota_inicial_pagado 
                  FROM " . $this->table_name . " s 
                  ORDER BY s.ap_paterno ASC, s.ap_materno ASC, s.nombre ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Obtener solo los socios activos
    public function getActivos() {
        $query = "SELECT s.*, 
                         COALESCE((SELECT SUM(c.monto) FROM cuota_inicial_pagos c WHERE c.id_socio = s.id_socio AND (c.estado = 'Pagado' OR c.estado IS NULL OR c.estado = '' OR LOWER(c.estado) = 'pagado')), 0.00) as cuota_inicial_pagado 
                  FROM " . $this->table_name . " s 
                  WHERE s.estado = 'Activo' OR s.estado = '1' 
                  ORDER BY s.ap_paterno ASC, s.ap_materno ASC, s.nombre ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Obtener un socio por ID
    public function getById($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id_socio = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row && isset($row['estado']) && $row['estado'] === 'Moroso') {
            $row['estado'] = 'Pasivo';
        }
        return $row;
    }

    // Crear un nuevo socio
    public function create($data) {
        $query = "INSERT INTO " . $this->table_name . " 
                  (ci, ap_paterno, ap_materno, nombre, telefono, correo, fecha_ingreso, estado, acciones, cuota_inicial, fechaRegistro) 
                  VALUES (:ci, :ap_paterno, :ap_materno, :nombre, :telefono, :correo, :fecha_ingreso, :estado, :acciones, :cuota_inicial, :fechaRegistro)";
        
        $stmt = $this->conn->prepare($query);

        // Sanitize
        $ci = htmlspecialchars(strip_tags($data['ci']));
        $ap_paterno = htmlspecialchars(strip_tags($data['ap_paterno']));
        $ap_materno = htmlspecialchars(strip_tags($data['ap_materno']));
        $nombre = htmlspecialchars(strip_tags($data['nombre']));
        $telefono = htmlspecialchars(strip_tags($data['telefono']));
        $correo = htmlspecialchars(strip_tags($data['correo']));
        $fecha_ingreso = htmlspecialchars(strip_tags($data['fecha_ingreso']));
        $estado = htmlspecialchars(strip_tags($data['estado']));
        $acciones = htmlspecialchars(strip_tags($data['acciones'] ?? 1));
        $cuota_inicial = (float)($data['cuota_inicial'] ?? 0);

        // Bind parameters
        $stmt->bindParam(":ci", $ci);
        $stmt->bindParam(":ap_paterno", $ap_paterno);
        $stmt->bindParam(":ap_materno", $ap_materno);
        $stmt->bindParam(":nombre", $nombre);
        $stmt->bindParam(":telefono", $telefono);
        $stmt->bindParam(":correo", $correo);
        $stmt->bindParam(":fecha_ingreso", $fecha_ingreso);
        $stmt->bindParam(":estado", $estado);
        $stmt->bindParam(":acciones", $acciones);
        $stmt->bindParam(":cuota_inicial", $cuota_inicial);
        
        $fechaRegistro = date('Y-m-d H:i:s');
        $stmt->bindParam(":fechaRegistro", $fechaRegistro);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Actualizar un socio
    public function update($data) {
        $query = "UPDATE " . $this->table_name . " 
                  SET ci = :ci, 
                      ap_paterno = :ap_paterno, 
                      ap_materno = :ap_materno, 
                      nombre = :nombre, 
                      telefono = :telefono, 
                      correo = :correo, 
                      fecha_ingreso = :fecha_ingreso, 
                      estado = :estado,
                      acciones = :acciones,
                      cuota_inicial = :cuota_inicial,
                      fechaEdicion = :fechaEdicion
                  WHERE id_socio = :id_socio";

        $stmt = $this->conn->prepare($query);

        // Sanitize
        $id_socio = htmlspecialchars(strip_tags($data['id_socio']));
        $ci = htmlspecialchars(strip_tags($data['ci']));
        $ap_paterno = htmlspecialchars(strip_tags($data['ap_paterno']));
        $ap_materno = htmlspecialchars(strip_tags($data['ap_materno']));
        $nombre = htmlspecialchars(strip_tags($data['nombre']));
        $telefono = htmlspecialchars(strip_tags($data['telefono']));
        $correo = htmlspecialchars(strip_tags($data['correo']));
        $fecha_ingreso = htmlspecialchars(strip_tags($data['fecha_ingreso']));
        $estado = htmlspecialchars(strip_tags($data['estado']));
        $acciones = htmlspecialchars(strip_tags($data['acciones'] ?? 1));
        $cuota_inicial = (float)($data['cuota_inicial'] ?? 0);

        // Bind parameters
        $stmt->bindParam(":id_socio", $id_socio);
        $stmt->bindParam(":ci", $ci);
        $stmt->bindParam(":ap_paterno", $ap_paterno);
        $stmt->bindParam(":ap_materno", $ap_materno);
        $stmt->bindParam(":nombre", $nombre);
        $stmt->bindParam(":telefono", $telefono);
        $stmt->bindParam(":correo", $correo);
        $stmt->bindParam(":fecha_ingreso", $fecha_ingreso);
        $stmt->bindParam(":estado", $estado);
        $stmt->bindParam(":acciones", $acciones);
        $stmt->bindParam(":cuota_inicial", $cuota_inicial);

        $fechaEdicion = date('Y-m-d H:i:s');
        $stmt->bindParam(":fechaEdicion", $fechaEdicion);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Eliminar un socio
    public function delete($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id_socio = ?";
        $stmt = $this->conn->prepare($query);
        
        $id = htmlspecialchars(strip_tags($id));
        $stmt->bindParam(1, $id);
        
        if($stmt->execute()) {
            return true;
        }
        return false;
    }
}
?>
