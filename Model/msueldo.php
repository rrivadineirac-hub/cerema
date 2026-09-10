<?php
// Model/msueldo.php
class SueldoModel {
    private $conn;
    private $table_name = "sueldos";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Obtener todos los sueldos de una gestión
    public function getByGestionId($id_gestion) {
        $query = "SELECT * FROM " . $this->table_name . "
                  WHERE id_gestion = ?
                  ORDER BY fecha_pago DESC, id_sueldo DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id_gestion);
        $stmt->execute();
        return $stmt;
    }

    // Obtener por ID
    public function getById($id_sueldo) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id_sueldo = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id_sueldo);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Crear un nuevo registro
    public function create($data) {
        $query = "INSERT INTO " . $this->table_name . " 
                  (id_gestion, cargo, empleado, mes, monto, fecha_pago, comprobante, estado) 
                  VALUES (:id_gestion, :cargo, :empleado, :mes, :monto, :fecha_pago, :comprobante, :estado)";
        
        $stmt = $this->conn->prepare($query);

        $id_gestion = htmlspecialchars(strip_tags($data['id_gestion']));
        $cargo = htmlspecialchars(strip_tags($data['cargo']));
        $empleado = htmlspecialchars(strip_tags($data['empleado']));
        $mes = htmlspecialchars(strip_tags($data['mes'] ?? ''));
        $monto = htmlspecialchars(strip_tags($data['monto']));
        $fecha_pago = htmlspecialchars(strip_tags($data['fecha_pago']));
        $comprobante = htmlspecialchars(strip_tags($data['comprobante'] ?? ''));
        $estado = htmlspecialchars(strip_tags($data['estado'] ?? 'Pagado'));

        $stmt->bindParam(":id_gestion", $id_gestion);
        $stmt->bindParam(":cargo", $cargo);
        $stmt->bindParam(":empleado", $empleado);
        $stmt->bindParam(":mes", $mes);
        $stmt->bindParam(":monto", $monto);
        $stmt->bindParam(":fecha_pago", $fecha_pago);
        $stmt->bindParam(":comprobante", $comprobante);
        $stmt->bindParam(":estado", $estado);

        return $stmt->execute();
    }

    // Actualizar un registro
    public function update($data) {
        $query = "UPDATE " . $this->table_name . " 
                  SET cargo = :cargo, 
                      empleado = :empleado,
                      mes = :mes,
                      monto = :monto, 
                      fecha_pago = :fecha_pago, 
                      comprobante = :comprobante, 
                      estado = :estado
                  WHERE id_sueldo = :id_sueldo";
        
        $stmt = $this->conn->prepare($query);

        $id_sueldo = htmlspecialchars(strip_tags($data['id_sueldo']));
        $cargo = htmlspecialchars(strip_tags($data['cargo']));
        $empleado = htmlspecialchars(strip_tags($data['empleado']));
        $mes = htmlspecialchars(strip_tags($data['mes'] ?? ''));
        $monto = htmlspecialchars(strip_tags($data['monto']));
        $fecha_pago = htmlspecialchars(strip_tags($data['fecha_pago']));
        $comprobante = htmlspecialchars(strip_tags($data['comprobante'] ?? ''));
        $estado = htmlspecialchars(strip_tags($data['estado']));

        $stmt->bindParam(":id_sueldo", $id_sueldo);
        $stmt->bindParam(":cargo", $cargo);
        $stmt->bindParam(":empleado", $empleado);
        $stmt->bindParam(":mes", $mes);
        $stmt->bindParam(":monto", $monto);
        $stmt->bindParam(":fecha_pago", $fecha_pago);
        $stmt->bindParam(":comprobante", $comprobante);
        $stmt->bindParam(":estado", $estado);

        return $stmt->execute();
    }

    // Eliminar un registro
    public function delete($id_sueldo) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id_sueldo = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id_sueldo);
        return $stmt->execute();
    }
}
?>
