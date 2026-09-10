<?php
// Model/motro_ingreso.php

class OtroIngresoModel {
    private $conn;
    private $table_name = "otros_ingresos";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Obtener todos los ingresos de una gestión
    public function getByGestionId($id_gestion) {
        $query = "SELECT * FROM " . $this->table_name . "
                  WHERE id_gestion = ?
                  ORDER BY fecha_pago DESC, id_otro_ingreso DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id_gestion);
        $stmt->execute();
        return $stmt;
    }

    // Obtener por ID
    public function getById($id_otro_ingreso) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id_otro_ingreso = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id_otro_ingreso);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Crear un nuevo registro de ingreso
    public function create($data) {
        $query = "INSERT INTO " . $this->table_name . " 
                  (id_gestion, detalle, monto, fecha_pago, comprobante, estado) 
                  VALUES (:id_gestion, :detalle, :monto, :fecha_pago, :comprobante, :estado)";
        
        $stmt = $this->conn->prepare($query);

        $id_gestion = htmlspecialchars(strip_tags($data['id_gestion']));
        $detalle = htmlspecialchars(strip_tags($data['detalle']));
        $monto = htmlspecialchars(strip_tags($data['monto']));
        $fecha_pago = htmlspecialchars(strip_tags($data['fecha_pago']));
        $comprobante = htmlspecialchars(strip_tags($data['comprobante'] ?? ''));
        $estado = htmlspecialchars(strip_tags($data['estado'] ?? 'Cobrado'));

        $stmt->bindParam(":id_gestion", $id_gestion);
        $stmt->bindParam(":detalle", $detalle);
        $stmt->bindParam(":monto", $monto);
        $stmt->bindParam(":fecha_pago", $fecha_pago);
        $stmt->bindParam(":comprobante", $comprobante);
        $stmt->bindParam(":estado", $estado);

        return $stmt->execute();
    }

    // Actualizar un registro
    public function update($data) {
        $query = "UPDATE " . $this->table_name . " 
                  SET detalle = :detalle, 
                      monto = :monto, 
                      fecha_pago = :fecha_pago, 
                      comprobante = :comprobante, 
                      estado = :estado
                  WHERE id_otro_ingreso = :id_otro_ingreso";
        
        $stmt = $this->conn->prepare($query);

        $id_otro_ingreso = htmlspecialchars(strip_tags($data['id_otro_ingreso']));
        $detalle = htmlspecialchars(strip_tags($data['detalle']));
        $monto = htmlspecialchars(strip_tags($data['monto']));
        $fecha_pago = htmlspecialchars(strip_tags($data['fecha_pago']));
        $comprobante = htmlspecialchars(strip_tags($data['comprobante'] ?? ''));
        $estado = htmlspecialchars(strip_tags($data['estado']));

        $stmt->bindParam(":id_otro_ingreso", $id_otro_ingreso);
        $stmt->bindParam(":detalle", $detalle);
        $stmt->bindParam(":monto", $monto);
        $stmt->bindParam(":fecha_pago", $fecha_pago);
        $stmt->bindParam(":comprobante", $comprobante);
        $stmt->bindParam(":estado", $estado);

        return $stmt->execute();
    }

    // Eliminar un registro
    public function delete($id_otro_ingreso) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id_otro_ingreso = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id_otro_ingreso);
        return $stmt->execute();
    }
}
?>
