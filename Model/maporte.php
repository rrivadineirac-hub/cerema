<?php
// Model/maporte.php
class AporteModel {
    private $conn;
    private $table_name = "aporte_extraordinario";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Obtener todos los aportes extraordinarios de un socio
    public function getBySocioId($id_socio) {
        $query = "SELECT a.*, s.nombre, s.ap_paterno, s.ap_materno 
                  FROM " . $this->table_name . " a
                  JOIN asociados s ON a.id_socio = s.id_socio
                  WHERE a.id_socio = ?
                  ORDER BY a.fecha_aporte DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id_socio);
        $stmt->execute();
        return $stmt;
    }

    // Obtener un aporte por su ID
    public function getById($id_aporte) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id_aporte = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id_aporte);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Crear un nuevo aporte
    public function create($data) {
        $query = "INSERT INTO " . $this->table_name . " 
                  (id_socio, numero_accion, motivo, monto, fecha_aporte, numero_recibo) 
                  VALUES (:id_socio, :numero_accion, :motivo, :monto, :fecha_aporte, :numero_recibo)";
        
        $stmt = $this->conn->prepare($query);

        $id_socio = htmlspecialchars(strip_tags($data['id_socio']));
        $numero_accion = htmlspecialchars(strip_tags($data['numero_accion'] ?? 1));
        $motivo = htmlspecialchars(strip_tags($data['motivo']));
        $monto = htmlspecialchars(strip_tags($data['monto']));
        $fecha_aporte = htmlspecialchars(strip_tags($data['fecha_aporte']));
        $numero_recibo = htmlspecialchars(strip_tags($data['numero_recibo'] ?? ''));

        $stmt->bindParam(":id_socio", $id_socio);
        $stmt->bindParam(":numero_accion", $numero_accion);
        $stmt->bindParam(":motivo", $motivo);
        $stmt->bindParam(":monto", $monto);
        $stmt->bindParam(":fecha_aporte", $fecha_aporte);
        $stmt->bindParam(":numero_recibo", $numero_recibo);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Actualizar aporte
    public function update($data) {
        $query = "UPDATE " . $this->table_name . " 
                  SET numero_accion = :numero_accion,
                      motivo = :motivo, 
                      monto = :monto, 
                      fecha_aporte = :fecha_aporte,
                      numero_recibo = :numero_recibo
                  WHERE id_aporte = :id_aporte";

        $stmt = $this->conn->prepare($query);

        $id_aporte = htmlspecialchars(strip_tags($data['id_aporte']));
        $numero_accion = htmlspecialchars(strip_tags($data['numero_accion'] ?? 1));
        $motivo = htmlspecialchars(strip_tags($data['motivo']));
        $monto = htmlspecialchars(strip_tags($data['monto']));
        $fecha_aporte = htmlspecialchars(strip_tags($data['fecha_aporte']));
        $numero_recibo = htmlspecialchars(strip_tags($data['numero_recibo'] ?? ''));

        $stmt->bindParam(":id_aporte", $id_aporte);
        $stmt->bindParam(":numero_accion", $numero_accion);
        $stmt->bindParam(":motivo", $motivo);
        $stmt->bindParam(":monto", $monto);
        $stmt->bindParam(":fecha_aporte", $fecha_aporte);
        $stmt->bindParam(":numero_recibo", $numero_recibo);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Eliminar aporte
    public function delete($id_aporte) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id_aporte = ?";
        $stmt = $this->conn->prepare($query);
        $id_aporte = htmlspecialchars(strip_tags($id_aporte));
        $stmt->bindParam(1, $id_aporte);
        
        if($stmt->execute()) {
            return true;
        }
        return false;
    }
}
?>
