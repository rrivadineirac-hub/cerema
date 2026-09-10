<?php
// Model/mservicio.php
class ServicioModel {
    private $conn;
    private $table_name = "servicios_basicos";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Obtener todos los servicios de una gestión
    public function getByGestionId($id_gestion) {
        $query = "SELECT * FROM " . $this->table_name . "
                  WHERE id_gestion = ?
                  ORDER BY fecha_pago DESC, id_servicio DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id_gestion);
        $stmt->execute();
        return $stmt;
    }

    // Obtener por ID
    public function getById($id_servicio) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id_servicio = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id_servicio);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Crear un nuevo registro
    public function create($data) {
        $query = "INSERT INTO " . $this->table_name . " 
                  (id_gestion, tipo_servicio, mes_pago, monto, fecha_pago, comprobante, estado) 
                  VALUES (:id_gestion, :tipo_servicio, :mes_pago, :monto, :fecha_pago, :comprobante, :estado)";
        
        $stmt = $this->conn->prepare($query);

        $id_gestion = htmlspecialchars(strip_tags($data['id_gestion']));
        $tipo_servicio = htmlspecialchars(strip_tags($data['tipo_servicio']));
        $mes_pago = htmlspecialchars(strip_tags($data['mes_pago'] ?? ''));
        $monto = htmlspecialchars(strip_tags($data['monto']));
        $fecha_pago = htmlspecialchars(strip_tags($data['fecha_pago']));
        $comprobante = htmlspecialchars(strip_tags($data['comprobante'] ?? ''));
        $estado = htmlspecialchars(strip_tags($data['estado'] ?? 'Pagado'));

        $stmt->bindParam(":id_gestion", $id_gestion);
        $stmt->bindParam(":tipo_servicio", $tipo_servicio);
        $stmt->bindParam(":mes_pago", $mes_pago);
        $stmt->bindParam(":monto", $monto);
        $stmt->bindParam(":fecha_pago", $fecha_pago);
        $stmt->bindParam(":comprobante", $comprobante);
        $stmt->bindParam(":estado", $estado);

        return $stmt->execute();
    }

    // Actualizar un registro
    public function update($data) {
        $query = "UPDATE " . $this->table_name . " 
                  SET tipo_servicio = :tipo_servicio, 
                      mes_pago = :mes_pago, 
                      monto = :monto, 
                      fecha_pago = :fecha_pago, 
                      comprobante = :comprobante, 
                      estado = :estado
                  WHERE id_servicio = :id_servicio";
        
        $stmt = $this->conn->prepare($query);

        $id_servicio = htmlspecialchars(strip_tags($data['id_servicio']));
        $tipo_servicio = htmlspecialchars(strip_tags($data['tipo_servicio']));
        $mes_pago = htmlspecialchars(strip_tags($data['mes_pago'] ?? ''));
        $monto = htmlspecialchars(strip_tags($data['monto']));
        $fecha_pago = htmlspecialchars(strip_tags($data['fecha_pago']));
        $comprobante = htmlspecialchars(strip_tags($data['comprobante'] ?? ''));
        $estado = htmlspecialchars(strip_tags($data['estado']));

        $stmt->bindParam(":id_servicio", $id_servicio);
        $stmt->bindParam(":tipo_servicio", $tipo_servicio);
        $stmt->bindParam(":mes_pago", $mes_pago);
        $stmt->bindParam(":monto", $monto);
        $stmt->bindParam(":fecha_pago", $fecha_pago);
        $stmt->bindParam(":comprobante", $comprobante);
        $stmt->bindParam(":estado", $estado);

        return $stmt->execute();
    }

    // Eliminar un registro (Anulación lógica o física)
    public function delete($id_servicio) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id_servicio = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id_servicio);
        return $stmt->execute();
    }
}
?>
