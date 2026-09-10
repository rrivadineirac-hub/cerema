<?php
// Model/mdirectiva.php
class DirectivaModel {
    private $conn;
    private $table_name = "mesa_directiva";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Obtener toda la mesa directiva con INNER JOINs
    public function getAll() {
        $query = "SELECT m.id_directiva, m.id_socio, m.id_cargo, m.gestion, m.fecha_inicio, m.fecha_fin, 
                         s.nombre, s.ap_paterno, s.ap_materno, s.ci,
                         c.nombre_cargo
                  FROM " . $this->table_name . " m
                  INNER JOIN asociados s ON m.id_socio = s.id_socio
                  INNER JOIN cargos c ON m.id_cargo = c.id_cargo
                  ORDER BY m.gestion DESC, c.id_cargo ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Obtener lista de cargos para el select
    public function getCargos() {
        $query = "SELECT * FROM cargos ORDER BY id_cargo ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Obtener lista de socios (activos) para el select
    public function getSocios() {
        $query = "SELECT id_socio, nombre, ap_paterno, ap_materno FROM asociados WHERE estado = 'Activo' ORDER BY ap_paterno ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Verificar si un cargo ya está ocupado en una gestión
    public function checkCargoOcupado($id_cargo, $gestion, $exclude_id = null) {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " WHERE id_cargo = ? AND gestion = ?";
        if ($exclude_id) {
            $query .= " AND id_directiva != ?";
        }
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id_cargo);
        $stmt->bindParam(2, $gestion);
        if ($exclude_id) {
            $stmt->bindParam(3, $exclude_id);
        }
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'] > 0;
    }

    // Obtener un registro por ID
    public function getById($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id_directiva = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row;
    }

    // Asignar un rol / Crear directiva
    public function create($data) {
        $query = "INSERT INTO " . $this->table_name . " 
                  (id_socio, id_cargo, gestion, fecha_inicio, fecha_fin) 
                  VALUES (:id_socio, :id_cargo, :gestion, :fecha_inicio, :fecha_fin)";
        
        $stmt = $this->conn->prepare($query);

        $id_socio = htmlspecialchars(strip_tags($data['id_socio']));
        $id_cargo = htmlspecialchars(strip_tags($data['id_cargo']));
        $gestion = htmlspecialchars(strip_tags($data['gestion']));
        $fecha_inicio = htmlspecialchars(strip_tags($data['fecha_inicio']));
        $fecha_fin = !empty($data['fecha_fin']) ? htmlspecialchars(strip_tags($data['fecha_fin'])) : null;

        $stmt->bindParam(":id_socio", $id_socio);
        $stmt->bindParam(":id_cargo", $id_cargo);
        $stmt->bindParam(":gestion", $gestion);
        $stmt->bindParam(":fecha_inicio", $fecha_inicio);
        
        if($fecha_fin != null) {
            $stmt->bindParam(":fecha_fin", $fecha_fin);
        } else {
            $stmt->bindValue(':fecha_fin', null, PDO::PARAM_NULL);
        }

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Actualizar rol
    public function update($data) {
        $query = "UPDATE " . $this->table_name . " 
                  SET id_socio = :id_socio, 
                      id_cargo = :id_cargo, 
                      gestion = :gestion, 
                      fecha_inicio = :fecha_inicio, 
                      fecha_fin = :fecha_fin
                  WHERE id_directiva = :id_directiva";

        $stmt = $this->conn->prepare($query);

        $id_directiva = htmlspecialchars(strip_tags($data['id_directiva']));
        $id_socio = htmlspecialchars(strip_tags($data['id_socio']));
        $id_cargo = htmlspecialchars(strip_tags($data['id_cargo']));
        $gestion = htmlspecialchars(strip_tags($data['gestion']));
        $fecha_inicio = htmlspecialchars(strip_tags($data['fecha_inicio']));
        $fecha_fin = !empty($data['fecha_fin']) ? htmlspecialchars(strip_tags($data['fecha_fin'])) : null;

        $stmt->bindParam(":id_directiva", $id_directiva);
        $stmt->bindParam(":id_socio", $id_socio);
        $stmt->bindParam(":id_cargo", $id_cargo);
        $stmt->bindParam(":gestion", $gestion);
        $stmt->bindParam(":fecha_inicio", $fecha_inicio);
        
        if($fecha_fin != null) {
            $stmt->bindParam(":fecha_fin", $fecha_fin);
        } else {
            $stmt->bindValue(':fecha_fin', null, PDO::PARAM_NULL);
        }

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Eliminar registro
    public function delete($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id_directiva = ?";
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
